<?php

namespace App\Jobs;

use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use App\Models\WhatsappRawEvent;
use App\Support\TelefoneCanonico;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Transforma um payload cru (whatsapp_raw_events) na camada estruturada
 * (whatsapp_conversations + whatsapp_messages).
 *
 * RECEBE O ID, NUNCA O PAYLOAD. O job é serializado na tabela `jobs`, que
 * não é lugar para conteúdo de conversa (LGPD). O id custa 8 bytes e o
 * payload já está persistido — não há razão para carregá-lo junto.
 *
 * IDEMPOTENTE POR DESENHO, porque o mesmo cru PODE ser processado duas
 * vezes: uma pelo `afterResponse` do webhook, outra pela varredura por cron
 * se a primeira não tiver marcado `processed_at` a tempo. A dedução vem do
 * índice único `wa_msg_provider_id`, via updateOrCreate.
 *
 * GRUPOS SÃO PROCESSADOS NORMALMENTE. O filtro é de exibição (regra de ouro
 * 8) e vive na consulta do controller, não aqui.
 */
class ProcessarEventoWhatsapp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private int $rawEventId)
    {
    }

    public function handle(): void
    {
        $cru = WhatsappRawEvent::find($this->rawEventId);

        if (! $cru || $cru->processed_at !== null) {
            return; // já processado, ou removido — nada a fazer
        }

        try {
            $this->processar($cru);

            $cru->forceFill([
                'processed_at'  => now(),
                'process_error' => null,
            ])->save();
        } catch (\Throwable $e) {
            // process_error recebe SÓ a mensagem da exceção — nunca o payload
            // nem trecho de conversa.
            $cru->forceFill([
                'process_error'    => mb_substr($e->getMessage(), 0, 500),
                'process_attempts' => $cru->process_attempts + 1,
            ])->save();

            Log::warning('whatsapp: falha ao processar evento cru', [
                'raw_event_id' => $cru->id,
                'tentativa'    => $cru->process_attempts,
                'erro'         => $e->getMessage(),
            ]);
        }
    }

    private function processar(WhatsappRawEvent $cru): void
    {
        $payload = $cru->payload; // cast 'array'

        if (! is_array($payload)) {
            throw new \RuntimeException('Payload não é JSON de objeto.');
        }

        $messageId = $this->primeiro($payload, self::caminhos('message.id'));

        // Evento sem message.id é presença, status de conexão e afins. Não é
        // mensagem, não vira linha em whatsapp_messages — mas TAMBÉM NÃO É
        // ERRO: marca-se processado e segue. Tratar isto como falha encheria
        // process_error de ruído e esconderia falha de verdade.
        if (! is_string($messageId) || $messageId === '') {
            return;
        }

        $conversa = $this->conversaDe($payload);

        $enviadaEm = $this->instanteDe($payload);

        // updateOrCreate contra o índice único: reprocessar o mesmo cru
        // atualiza a linha existente em vez de duplicar.
        WhatsappMessage::updateOrCreate(
            ['provider_message_id' => $messageId],
            [
                'conversation_id' => $conversa->id,
                'from_me'         => (bool) (Arr::get($payload, 'message.fromMe') ?? false),
                // Nomes confirmados no schema Message do openapi-bundled.json
                // (uazapiGO 2.1.1). `type`/`mediaType`/`body`/`caption` NAO
                // existem no schema — eram chute e sairam.
                'tipo'            => $this->primeiro($payload, self::caminhos('message.messageType')) ?? 'texto',
                // `content` fica DE FORA de proposito: a doc o descreve como
                // "conteudo bruto da mensagem (JSON serializado ou texto)".
                // Como fallback, gravaria JSON dentro de `texto`.
                'texto'           => $this->primeiro($payload, self::caminhos('message.text')),
                'enviada_em'      => $enviadaEm,
            ]
        );

        if ($enviadaEm && (! $conversa->ultima_mensagem_em || $enviadaEm->gt($conversa->ultima_mensagem_em))) {
            $conversa->forceFill(['ultima_mensagem_em' => $enviadaEm])->save();
        }
    }

    /**
     * Encontra ou cria a conversa.
     *
     * TELEFONE: `chat.phone` primeiro, `message.sender_pn` como alternativa —
     * NUNCA `message.sender` nem `sender_lid`, que são LID interno da Uazapi
     * e não telefone (regra de ouro 4). Esta é a linha do arquivo que mais
     * importa acertar: usar o LID aqui produziria matching errado na Fatia 4,
     * silenciosamente.
     *
     * CHAVE DA CONVERSA — a regra, porque não é óbvia:
     *   * 1:1   → o TELEFONE CANÔNICO. É a identidade real do contato, é o
     *             que a regra de ouro 4 manda usar, e é o que a Fatia 4 vai
     *             casar contra o CRM.
     *   * grupo → o id `@g.us` do provedor, que é a única identidade que um
     *             grupo tem (não há telefone único: são várias pessoas).
     *
     * A alternativa seria exigir sempre um `chat.id` do payload e, quando
     * ausente, fabricar um (ex.: telefone + '@s.whatsapp.net'). Rejeitada: se
     * o payload real trouxer um id em formato diferente, as duas formas não
     * colidiriam no índice único e a MESMA conversa viraria duas linhas. Usar
     * dado que temos é melhor do que inventar formato que talvez não bata.
     */
    private function conversaDe(array $payload): WhatsappConversation
    {
        // ATENCAO AO QUE NAO ESTA AQUI: `chat.id` SAIU da lista. A doc
        // (openapi-bundled.json, schema Chat) diz que `chat.id` e o "ID unico
        // da conversa (r + 7 bytes aleatorios em hex)" — id INTERNO da Uazapi,
        // nao o JID. Ele estava em PRIMEIRO lugar aqui, entao um grupo teria
        // sido chaveado por `r1a2b3c4` em vez do `@g.us`, contra a regra desta
        // fatia. Falha silenciosa: a coluna preenche, o indice unico nao
        // reclama, e so aparece ao cruzar com o provedor.
        // `chat.wa_chatid` e que e o "ID completo do chat no WhatsApp".
        $chatIdBruto = $this->primeiro($payload, self::caminhos('chat.wa_chatid', 'message.chatid'));

        // `message.isGroup` (nao `wa_isGroup`) e o nome no schema Message; o
        // prefixo `wa_` so existe no schema Chat. No payload real de grupo
        // (23/07) os DOIS vieram `true` — mantemos os dois mais o fallback pelo
        // `@g.us` do chatid, por seguranca.
        $isGroup = (bool) (Arr::get($payload, 'message.isGroup')
            ?? Arr::get($payload, 'chat.wa_isGroup')
            ?? ($chatIdBruto !== null && str_contains($chatIdBruto, '@g.us')));

        // Grupo não tem telefone único: o chat é de várias pessoas.
        $telefone = $isGroup
            ? null
            : TelefoneCanonico::normalizar(
                // `sender_pn` e um JID ("JID PN resolvido do remetente"), entao
                // pode chegar como 5511...@s.whatsapp.net. O dominio nao tem
                // digito, e o normalizar() so guarda digitos — mas isso e
                // consequencia de implementacao, nao promessa: se o dominio um
                // dia trouxer numero, este e o lugar a revisar.
                $this->primeiro($payload, self::caminhos('chat.phone', 'message.sender_pn'))
            );

        if ($isGroup) {
            $waChatId = $chatIdBruto
                ?? throw new \RuntimeException('Payload de grupo sem identificador de chat.');
        } else {
            // Sem telefone utilizável não há conversa 1:1 que faça sentido —
            // e cair no chatIdBruto aqui esconderia o problema numa linha
            // órfã, sem telefone, que a Fatia 4 nunca casaria.
            $waChatId = $telefone
                ?? throw new \RuntimeException('Payload 1:1 sem telefone normalizável em chat.phone/sender_pn.');
        }

        $conversa = WhatsappConversation::firstOrNew(['wa_chat_id' => $waChatId]);

        $conversa->fill(array_filter([
            'chat_phone'    => $telefone,
            // Ordem pela doc: `name` ja "consolida o melhor valor disponivel";
            // `wa_contactName` so existe se o numero estiver na agenda da
            // instancia; `wa_name` e o push name do perfil, que e a fonte
            // comum de quem NAO esta salvo — por isso entrou.
            'nome_exibicao' => $this->primeiro($payload, self::caminhos(
                'chat.name', 'chat.wa_contactName', 'chat.wa_name', 'message.senderName',
            )),
        ], fn ($v) => $v !== null));

        $conversa->is_group = $isGroup;
        $conversa->save();

        return $conversa;
    }

    /**
     * Timestamps da Uazapi vêm em MILISSEGUNDOS (CLAUDE.md). Tratar como
     * segundos jogaria a data para 1970 — erro que passa despercebido porque
     * a tela ainda ordena, só ordena errado.
     */
    private function instanteDe(array $payload): ?Carbon
    {
        $ms = $this->primeiro($payload, self::caminhos('message.messageTimestamp'));

        if (! is_numeric($ms)) {
            return null;
        }

        $ms = (int) $ms;

        // Heurística de segurança: valor de 10 dígitos é segundo, não
        // milissegundo. Se a Uazapi mudar de unidade, isto evita a data de
        // 1970 silenciosa.
        return $ms < 100000000000
            ? Carbon::createFromTimestamp($ms)
            : Carbon::createFromTimestampMs($ms);
    }

    /**
     * Os caminhos candidatos de um campo — hoje a forma PLANA, e só ela.
     *
     * Até 22/07 esta função dobrava cada caminho com uma versão sob `data.`,
     * porque o schema `WebhookEvent` (openapi-bundled.json) declara o corpo como
     * `{event, instance, data}` sem exemplo de payload de mensagem, e não dava
     * para decidir entre a forma plana (do briefing) e a aninhada. O comentário
     * antigo previa: "quando o primeiro payload real chegar, um dos dois lados
     * morre e a lista encolhe pela metade."
     *
     * FOI O QUE ACONTECEU (23/07/2026): o payload real veio PLANO
     * (`{message.*, chat.*, ...}` no topo, nada sob `data.`). O lado `data.`
     * morreu e saiu. A função continua sendo o único lugar onde os aliases de
     * um campo são expressos (ex.: `chat.wa_chatid` / `message.chatid`).
     */
    private static function caminhos(string ...$planos): array
    {
        return $planos;
    }

    /**
     * Primeiro valor escalar não vazio entre os caminhos dados.
     *
     * NOMES CONFERIDOS EM 22/07/2026 contra a doc oficial — docs.uazapi.com
     * serve um SPA, mas o spec está em https://docs.uazapi.com/openapi-bundled.json
     * (uazapiGO 2.1.1, OpenAPI 3.1), schemas `Message` e `Chat`. Texto, tipo e
     * timestamp deixaram de ser lista de candidatos: `message.text`,
     * `message.messageType` e `message.messageTimestamp` são os nomes reais, e
     * `body`/`caption`/`type`/`mediaType`/`timestamp`/`t` não existem no schema.
     *
     * RESOLVIDO PELO PRIMEIRO PAYLOAD REAL (23/07/2026): o envelope é PLANO
     * (ver caminhos(), que perdeu o lado `data.`), e `message.id` chega como
     * `owner:messageid` (ex. `554195906685:3EB0…`), confirmando o CLAUDE.md —
     * é essa a chave do índice único, e ela NÃO muda. `message.messageid` (só o
     * id do provedor) existe em paralelo, mas trocar a chave com linhas já
     * gravadas continua fora de questão.
     */
    private function primeiro(array $payload, array $caminhos): ?string
    {
        foreach ($caminhos as $caminho) {
            $valor = Arr::get($payload, $caminho);

            if (is_scalar($valor) && (string) $valor !== '') {
                return (string) $valor;
            }
        }

        return null;
    }
}
