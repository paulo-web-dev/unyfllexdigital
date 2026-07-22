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

        $messageId = Arr::get($payload, 'message.id');

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
                'from_me'         => (bool) Arr::get($payload, 'message.fromMe', false),
                'tipo'            => $this->primeiro($payload, [
                    'message.messageType', 'message.type', 'message.mediaType',
                ]) ?? 'texto',
                'texto'           => $this->primeiro($payload, [
                    'message.text', 'message.content', 'message.body', 'message.caption',
                ]),
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
        $chatIdBruto = $this->primeiro($payload, ['chat.id', 'chat.wa_chatid', 'message.chatid']);

        $isGroup = (bool) (Arr::get($payload, 'message.wa_isGroup')
            ?? Arr::get($payload, 'chat.wa_isGroup')
            ?? ($chatIdBruto !== null && str_contains($chatIdBruto, '@g.us')));

        // Grupo não tem telefone único: o chat é de várias pessoas.
        $telefone = $isGroup
            ? null
            : TelefoneCanonico::normalizar(
                $this->primeiro($payload, ['chat.phone', 'message.sender_pn'])
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
            'nome_exibicao' => $this->primeiro($payload, [
                'chat.name', 'chat.wa_contactName', 'message.senderName',
            ]),
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
        $ms = $this->primeiro($payload, [
            'message.messageTimestamp', 'message.timestamp', 'message.t',
        ]);

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
     * Primeiro valor escalar não vazio entre os caminhos dados.
     *
     * Mesma ressalva do UazapiWebhookController: vários destes nomes de campo
     * NÃO foram conferidos contra um payload real da Uazapi. Os que estão
     * documentados no CLAUDE.md (chat.phone, message.id, sender_pn,
     * wa_isGroup) são firmes; os de texto, tipo e timestamp são candidatos.
     * Reduzir a lista ao nome certo quando a primeira mensagem real chegar.
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
