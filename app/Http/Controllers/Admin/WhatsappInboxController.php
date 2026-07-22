<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WhatsappConversation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Inbox Uazapi — Fatias 3 (ver) e 7 (atribuir atendente).
 *
 * MODO SOMBRA. Roda em paralelo ao Chatwoot, que continua sendo o
 * atendimento de produção (regra de ouro 9).
 *
 * A ÚNICA ESCRITA AQUI É A ATRIBUIÇÃO, e ela não sai do nosso banco: nada é
 * enviado ao WhatsApp. Envio continua fechado atrás do checkpoint explícito
 * das Fatias 6/8.
 *
 * SEM PAINEL DE CRM. Identificar o contato é Fatia 4, travada até a Q9
 * fechar a cobertura em linhas.
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

        return view('admin.whatsapp.index', compact('conversas', 'gruposOcultos', 'filtro'));
    }

    public function show(WhatsappConversation $conversa): View
    {
        // Grupo não é exibido nem por link direto. Sem esta linha, o filtro da
        // lista seria contornável trocando o id na URL.
        abort_if($conversa->is_group, 404);

        $mensagens = $conversa->messages()
            ->orderByRaw('enviada_em IS NULL, enviada_em ASC')
            ->orderBy('id')
            ->get();

        $atendentes = User::comercial()->orderBy('name')->get(['id', 'name']);

        return view('admin.whatsapp.thread', compact('conversa', 'mensagens', 'atendentes'));
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
}
