<?php

return [

    /*
    | URL do webhook de produção do n8n (workflow "Cursos Modulares — Gerar").
    | Pode sobrescrever no .env com CURSOS_N8N_WEBHOOK=...
    */
    'n8n_webhook_url' => env('CURSOS_N8N_WEBHOOK', 'https://n8n.unyflex.com.br/webhook/cursos-modulares/gerar'),

    /*
    | Segredo compartilhado entre o Laravel e o n8n.
    | DEVE ser igual ao header "X-Webhook-Secret" do nó "Callback Laravel" no n8n.
    | Troque nos dois lados para algo forte antes de ir para produção.
    */
    'n8n_secret' => env('CURSOS_N8N_SECRET', 'P@o200471'),

    /*
    | Base pública usada para montar a URL da apostila enviada ao n8n.
    | A apostila precisa ser acessível por este domínio para a IA conseguir lê-la.
    */
'public_base_url' => env('CURSOS_PUBLIC_URL', 'https://digital.unyflex.com.br'),
    /*
    | Tipos de conteúdo gerados, na ordem de exibição.
    */
    'tipos' => [
        'resumo'  => 'Resumo',
        'podcast' => 'Roteiro de Podcast',
        'video'   => 'Roteiro de Vídeo',
    ],

];
