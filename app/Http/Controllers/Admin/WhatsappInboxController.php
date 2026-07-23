<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\WhatsappProviderContract;
use App\Enums\AdminRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use App\Services\IdentificacaoContato;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Inbox Uazapi — Fatias 3 (ver), 6 (enviar) e 7 (atribuir atendente).
 *
 * MODO SOMBRA. Roda em paralelo ao Chatwoot, que continua sendo o
 * atendimento de produção (regra de ouro 9).
 *
 * DUAS ESCRITAS: a atribuição (Fatia 7), que não sai do nosso banco; e o
 * envio (Fatia 6). O ENVIO CHAMA O PROVEDOR, mas o PORTÃO
 * (`uazapi.envio_habilitado`, default false) está fechado: com ele fechado,
 * `enviarTexto()` lança LogicException e NADA sai nem é gravado. A UI desenha
 * o campo desabilitado como afordância honesta; a trava real é o provedor, não
 * o `disabled` do botão. Abrir o portão é checkpoint humano explícito
 * (Fatias 6/8), nunca efeito colateral.
 *
 * O PAINEL DE CRM (Fatia 4) TAMBÉM SÓ LÊ. `IdentificacaoContato` não escreve
 * em tabela de CRM, e match pela variante do 9º dígito não autoriza corrigir a
 * origem — normalização é na leitura.
 */
class WhatsappInboxController extends Controller
{
    /** Filtros aceitos na querystring. Whitelist: id arbitrário na URL, não. */
    private const FILTROS = ['todos', 'meus', 'sem'];

    public function index(Request $request): View
    {
        // O FILTRO DE GRUPO VIVE AQUI, e só aqui. Grupos são persistidos
        // integralmente na ingestão (regra de ouro 8) — o que o MVP restringe
        // é a EXIBIÇÃO. Mover este where() para o parser ou para o webhook
        // jogaria fora dado que o provedor não reenvia.
        $conversas = WhatsappConversation::query()
            ->with('atendente')
            ->where('is_group', false);

        $filtro = in_array($request->query('atendente'), self::FILTROS, true)
            ? $request->query('atendente')
            : 'todos';

        if ($filtro === 'meus') {
            $conversas->atribuidaA((int) auth()->id());
        } elseif ($filtro === 'sem') {
            $conversas->atribuidaA(null);
        }

        $conversas = $conversas
            ->orderByRaw('ultima_mensagem_em IS NULL, ultima_mensagem_em DESC')
            ->paginate(50)
            ->withQueryString();

        // Contagem dos grupos ocultos: sem isto, "não aparece na tela" e "não
        // foi capturado" ficam indistinguíveis para quem confere o modo
        // sombra — que é exatamente o que esta tela existe para responder.
        $gruposOcultos = WhatsappConversation::where('is_group', true)->count();

        // Marco temporal do polling (Fatia 5). VEM DO SERVIDOR, nunca de
        // Date.now() no cliente: relógio de máquina de escritório erra minutos,
        // e o banner ficaria preso em "nunca tem novidade" ou em "sempre tem".
        $desde = now()->toIso8601String();

        // E um marco por ID DE MENSAGEM, porque só o tempo não basta — ver o
        // comentário em novidades(). Vale para a base inteira, não por
        // conversa: o que interessa é "chegou mensagem depois que eu carreguei".
        $cursor = (int) (WhatsappMessage::max('id') ?? 0);

        return view('admin.whatsapp.index', compact('conversas', 'gruposOcultos', 'filtro', 'desde', 'cursor'));
    }

    public function show(WhatsappConversation $conversa, IdentificacaoContato $identificacao): View
    {
        // Grupo não é exibido nem por link direto. Sem esta linha, o filtro da
        // lista seria contornável trocando o id na URL.
        abort_if($conversa->is_group, 404);

        $mensagens = $conversa->messages()
            ->orderByRaw('enviada_em IS NULL, enviada_em ASC')
            ->orderBy('id')
            ->get();

        $atendentes = User::comercial()->orderBy('name')->get(['id', 'name']);

        // Cursor inicial do polling: o MAIOR id já renderizado, não o id do
        // último balão da tela. A thread é ordenada por enviada_em, então a
        // última linha exibida pode não ser a de id mais alto — usar ela faria
        // o primeiro delta reenviar mensagens já visíveis.
        $cursor = (int) ($mensagens->max('id') ?? 0);

        // Identificação do contato (Fatia 4). Resolvida no RENDER, não no
        // polling: cadastro de CRM não muda enquanto a thread está aberta, e
        // acrescentá-la ao payload do delta mexeria no contrato da Fatia 5
        // (só rótulo + hash) sem ganho nenhum.
        $contato = $identificacao->identificar($conversa->chat_phone);

        // Estado do portão de envio (Fatia 6), só para a AFORDÂNCIA da tela
        // desenhar o campo habilitado ou não. Não é a trava: quem barra o
        // envio de verdade é o provedor (LogicException com o portão fechado),
        // como a validação — e não o <select> — é que barra a atribuição.
        $envioHabilitado = (bool) config('uazapi.envio_habilitado');

        return view('admin.whatsapp.thread', compact('conversa', 'mensagens', 'atendentes', 'cursor', 'contato', 'envioHabilitado'));
    }

    /**
     * Delta da thread — Fatia 5. Só as mensagens novas desde o cursor.
     *
     * O CURSOR É `whatsapp_messages.id`, NUNCA `enviada_em`. O timestamp vem do
     * provedor e pode ser MAIS ANTIGO que uma mensagem já exibida: entrega
     * atrasada, ou reprocessamento pela varredura por cron (rede de segurança
     * por desenho). Um cursor por tempo pularia essa mensagem para sempre — e o
     * sintoma seria uma mensagem que simplesmente nunca aparece, sem erro
     * nenhum. `id` é ordem de inserção, monotônica.
     *
     * O preço disso é assumido: o delta pode trazer mensagem que pertence ao
     * MEIO da thread, e por isso o JS insere por posição em vez de empilhar.
     */
    public function mensagens(Request $request, WhatsappConversation $conversa): JsonResponse
    {
        // Mesma guarda do show(). Sem ela, o grupo excluído da tela continuaria
        // legível por este endpoint, e o filtro de exibição teria um furo.
        abort_if($conversa->is_group, 404);

        $cursor = $this->cursorDe($request->query('depois_de'));

        $novas = $conversa->messages()
            ->where('id', '>', $cursor)
            ->orderBy('id')
            ->get();

        $conversa->load('atendente');

        return response()->json([
            'cursor'    => (int) ($novas->max('id') ?? $cursor),
            'mensagens' => $novas
                ->map(fn ($mensagem) => view('admin.whatsapp._mensagem', compact('mensagem'))->render())
                ->all(),

            // SÓ RÓTULO E HASH. O id do atendente NÃO vai no payload de
            // propósito: sem ele, o JS não consegue sincronizar o campo
            // escondido `atendente_atual_id` nem por descuido — e sincronizar
            // esse campo desarmaria a guarda de sobrescrita da Fatia 7.
            'atribuicao' => [
                'rotulo'     => $conversa->rotuloAtribuicao(),
                'assinatura' => $conversa->assinaturaAtribuicao(),
            ],
        ]);
    }

    /**
     * Contagem de conversas com novidade desde o carregamento da lista —
     * Fatia 5. Alimenta só o banner "há novidades"; a tabela não é reconstruída
     * em JS, então paginação e filtro continuam valendo sem caso especial.
     */
    public function novidades(Request $request): JsonResponse
    {
        $desde  = $this->instanteDe($request->query('desde'));
        $cursor = $this->cursorDe($request->query('depois_de'));

        if ($desde === null) {
            return response()->json(['novidades' => 0]);
        }

        // DUAS FONTES, E PRECISA SER DUAS.
        //
        // (a) Mensagem nova, por ID. Só `updated_at` NÃO cobre este caso: o
        //     parser atualiza `ultima_mensagem_em` apenas quando a mensagem é
        //     mais NOVA que a última (ProcessarEventoWhatsapp:116). Uma entrega
        //     atrasada — ou o reprocessamento pela varredura por cron — insere
        //     a mensagem sem tocar a conversa, o updated_at não sobe, e o
        //     banner nunca anunciaria aquela conversa. É a mesma armadilha que
        //     fez o cursor da thread ser `id` em vez de timestamp; aqui ela
        //     reapareceu pela porta dos fundos.
        //
        // (b) Mudança de atribuição, por tempo. Ela não cria mensagem nenhuma,
        //     então o id não a enxerga. É o caso da Fatia 7.
        $comMensagemNova = WhatsappMessage::query()
            ->where('id', '>', $cursor)
            ->distinct()
            ->pluck('conversation_id');

        // `>=` E NÃO `>`. A coluna é TIMESTAMP (precisão de segundo, 0003) e o
        // marco é o instante do render: com `>`, uma conversa tocada no MESMO
        // segundo em que a página carregou ficaria de fora — e como o marco não
        // avança sozinho, ela nunca seria anunciada. O `>=` troca isso por um
        // banner eventualmente supérfluo no segundo do render, que some no
        // primeiro clique em "Atualizar". Aviso a mais incomoda; novidade que
        // some sem deixar rastro é o defeito que esta fatia existe para não ter.
        //
        // Sem índice em updated_at, e isso é escolha: a tabela tem dezenas de
        // linhas. Se a inbox crescer para centenas de milhares, o índice entra
        // aí — não agora, para não versionar SQL que ninguém precisa.
        $novidades = WhatsappConversation::query()
            ->where('is_group', false)
            ->where(function ($q) use ($comMensagemNova, $desde) {
                $q->whereIn('id', $comMensagemNova)
                  ->orWhere('updated_at', '>=', $desde);
            })
            ->count();

        return response()->json(['novidades' => $novidades]);
    }

    /** Cursor da querystring. Lixo vira 0 — nunca erro. */
    private function cursorDe(mixed $bruto): int
    {
        return is_numeric($bruto) && (int) $bruto > 0 ? (int) $bruto : 0;
    }

    /**
     * Instante da querystring. Entrada inválida devolve null, e o chamador
     * responde "0 novidades" — um parâmetro de URL malformado não pode virar
     * 500 num endpoint que roda a cada 5 segundos.
     */
    private function instanteDe(mixed $bruto): ?CarbonImmutable
    {
        if (! is_string($bruto) || $bruto === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($bruto)->setTimezone(config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Atribui (ou desatribui) o atendente da conversa — Fatia 7.
     *
     * `lead_assignedAttendant_id` da Uazapi continua ignorado: a atribuição é
     * nossa, ponta a ponta (regra de ouro 5).
     */
    public function atribuir(Request $request, WhatsappConversation $conversa): RedirectResponse
    {
        // Mesma guarda do show(). Sem ela, o grupo excluído da tela continua
        // atribuível por POST direto.
        abort_if($conversa->is_group, 404);

        // A checagem de power mora AQUI, não só no <select>: um <option>
        // forjado não pode virar atribuição. null é entrada legítima —
        // significa desatribuir.
        $dados = $request->validate([
            'atendente_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('power', AdminRole::COMERCIAL->value),
            ],
            'atendente_atual_id' => ['nullable', 'integer'],
        ]);

        // Guarda contra sobrescrita silenciosa: a tela mandou de volta quem
        // era o atendente quando ela foi carregada. Se mudou nesse meio-tempo,
        // avisa em vez de passar por cima.
        //
        // É check-then-write: ESTREITA a janela, não fecha. Dois POSTs
        // simultâneos ainda podem cruzar. Lock de verdade é desproporcional
        // para uma tela em modo sombra — se um dia doer, a solução é UPDATE
        // condicional, não um select-for-update aqui.
        $atual = $conversa->atendente_id;
        $visto = isset($dados['atendente_atual_id']) ? (int) $dados['atendente_atual_id'] : null;

        if ($atual !== $visto) {
            $conversa->load('atendente');
            $quem = $conversa->atendente?->name ?? 'ninguém';

            return back()->with('aviso', "A conversa foi atribuída a {$quem} enquanto esta tela estava aberta. Nada foi alterado — confira e tente de novo.");
        }

        $novo = isset($dados['atendente_id']) ? (int) $dados['atendente_id'] : null;

        // Os três campos andam juntos. atribuida_em preenchida com
        // atendente_id nulo seria estado mentiroso.
        $conversa->update([
            'atendente_id'     => $novo,
            'atribuida_em'     => $novo === null ? null : now(),
            'atribuida_por_id' => $novo === null ? null : auth()->id(),
        ]);

        return back()->with('sucesso', $novo === null
            ? 'Atribuição removida.'
            : 'Conversa atribuída.');
    }

    /**
     * Envia uma mensagem de texto pela conversa — Fatia 6.
     *
     * PORTÃO FECHADO POR PADRÃO, e é o PROVEDOR quem barra, não este método:
     * com `uazapi.envio_habilitado` false (o default), `enviarTexto()` lança
     * LogicException antes de qualquer request — nada sai e NADA é gravado. O
     * botão desabilitado da tela é só afordância; forjar este POST não fura a
     * trava, que mora uma camada abaixo. Mesmo desenho da Fatia 7: a regra vive
     * na validação/no provedor, nunca só no controle de tela.
     *
     * PERSISTE SÓ APÓS CONFIRMAÇÃO (decisão de desenho). Não existe estado
     * "enviando" no banco: só uma mensagem com desfecho DEFINIDO pelo provedor
     * vira linha. Portão fechado, guarda de ambiente, 401/429/timelock — nenhum
     * grava, e a thread nunca fica com balão órfão "meio-enviado".
     */
    public function enviar(Request $request, WhatsappConversation $conversa, WhatsappProviderContract $provider): RedirectResponse
    {
        // Mesma guarda do show()/atribuir(): grupo não é exibido nem operável,
        // e não tem telefone canônico para onde enviar.
        abort_if($conversa->is_group, 404);

        $dados = $request->validate([
            'texto' => ['required', 'string', 'max:4096'],
        ]);

        // 1:1 sempre tem telefone canônico (o parser recusa criar conversa 1:1
        // sem ele). Defensivo: sem alvo não há o que enviar, e cair no provedor
        // com string vazia só trocaria a mensagem de erro por outra.
        if (($conversa->chat_phone ?? '') === '') {
            return back()->with('erro', 'Conversa sem telefone — não há para onde enviar.');
        }

        try {
            // O telefone JÁ chega canônico do banco; o provedor recusa qualquer
            // coisa que não esteja, de propósito (não normaliza no envio, para
            // não esconder um alvo errado — mensagem para estranho não tem ctrl+z).
            $id = $provider->enviarTexto($conversa->chat_phone, $dados['texto']);
        } catch (\InvalidArgumentException $e) {
            // Entrada inválida (texto só de espaços; telefone não-canônico).
            // InvalidArgumentException É SUBCLASSE de LogicException — TEM que
            // ser capturada ANTES do portão, senão "texto vazio" cairia no catch
            // de baixo e viraria a mensagem "envio desabilitado", que é falsa.
            return back()->with('erro', 'Não foi possível enviar: ' . $e->getMessage());
        } catch (\LogicException $e) {
            // PORTÃO fechado (Fatia 6) — o caminho normal desta rodada. É aviso,
            // não erro: o envio está desligado de propósito, não quebrado.
            return back()->with('aviso', 'Envio desabilitado (Fatia 6): aguardando checkpoint. Nada foi enviado.');
        } catch (\Throwable $e) {
            // Guarda de ambiente, config vazia, 401/429, 500/timelock. As
            // mensagens do provedor são LGPD-safe por construção (telefone
            // mascarado, conteúdo nunca logado), então dá para mostrar ao operador.
            return back()->with('erro', 'Não foi possível enviar: ' . $e->getMessage());
        }

        // Só chega aqui com sucesso. 200-SEM-ID → id local sintético: o provedor
        // já logou o warning e NÃO relança (relançar faria reenviar e duplicar
        // para uma pessoa real). Não persistir perderia uma mensagem que
        // provavelmente foi entregue. O prefixo `local:` marca "sem id do
        // provedor" e mantém o índice único (provider_message_id é NOT NULL).
        $providerMessageId = $id !== '' ? $id : 'local:' . Str::ulid();

        $mensagem = WhatsappMessage::create([
            'conversation_id'     => $conversa->id,
            'provider_message_id' => $providerMessageId,
            'from_me'             => true,
            'tipo'                => 'texto',
            'texto'               => $dados['texto'],
            'enviada_em'          => now(),
        ]);

        // Mesmo bump do parser (ProcessarEventoWhatsapp:114): a conversa sobe na
        // lista só se esta for mais nova que a última. Aqui é sempre "agora", mas
        // a condição fica idêntica ao outro caminho de propósito, para não
        // divergirem na primeira mudança.
        if (! $conversa->ultima_mensagem_em || $mensagem->enviada_em->gt($conversa->ultima_mensagem_em)) {
            $conversa->forceFill(['ultima_mensagem_em' => $mensagem->enviada_em])->save();
        }

        return back()->with('sucesso', 'Mensagem enviada.');
    }
}
