<?php

return [
    // Webhook do n8n que gera as artes do dia.
    'n8n_gerar_webhook' => env('SOCIAL_N8N_WEBHOOK', 'https://n8n.unyflex.com.br/webhook/social/gerar-dia'),

    // Secret compartilhado (reusa o mesmo dos outros fluxos por padrão).
    'n8n_secret' => env('SOCIAL_N8N_SECRET', env('CURSOS_N8N_SECRET', 'P@o200471')),

    // Catálogo de fotos (CSV público) usado como banco de imagens.
    'catalogo_url' => env('SOCIAL_CATALOGO_URL', 'https://unyflex.com.br/storage/produtos_catalogo.csv'),

    // URL de callback (se null, usa url('/api/n8n/social/artes')).
    'callback_url' => env('SOCIAL_CALLBACK_URL', null),
];
