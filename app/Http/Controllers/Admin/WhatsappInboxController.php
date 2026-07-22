<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappConversation;
use Illuminate\View\View;

/**
 * Inbox Uazapi — leitura, Fatia 3.
 *
 * MODO SOMBRA. Roda em paralelo ao Chatwoot, que continua sendo o
 * atendimento de produção (regra de ouro 9). Esta tela é só para conferir
 * que a captura está completa. Nada aqui envia, responde ou atribui.
 *
 * SEM PAINEL DE CRM. Identificar o contato é Fatia 4, travada até a Q8c
 * fechar a cobertura em linhas.
 */
class WhatsappInboxController extends Controller
{
    public function index(): View
    {
        // O FILTRO DE GRUPO VIVE AQUI, e só aqui. Grupos são persistidos
        // integralmente na ingestão (regra de ouro 8) — o que o MVP restringe
        // é a EXIBIÇÃO. Mover este where() para o parser ou para o webhook
        // jogaria fora dado que o provedor não reenvia.
        $conversas = WhatsappConversation::query()
            ->where('is_group', false)
            ->orderByRaw('ultima_mensagem_em IS NULL, ultima_mensagem_em DESC')
            ->paginate(50);

        // Contagem dos grupos ocultos: sem isto, "não aparece na tela" e "não
        // foi capturado" ficam indistinguíveis para quem confere o modo
        // sombra — que é exatamente o que esta tela existe para responder.
        $gruposOcultos = WhatsappConversation::where('is_group', true)->count();

        return view('admin.whatsapp.index', compact('conversas', 'gruposOcultos'));
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

        return view('admin.whatsapp.thread', compact('conversa', 'mensagens'));
    }
}
