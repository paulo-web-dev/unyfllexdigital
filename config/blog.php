<?php

return [

    /*
    | URL do webhook do n8n (workflow "Blog — Gerar Artigo").
    | Sobrescreva no .env com BLOG_N8N_WEBHOOK=...
    */
    'n8n_webhook_url' => env('BLOG_N8N_WEBHOOK', 'https://n8n.unyflex.com.br/webhook/blog/gerar-artigo'),

    /*
    | Segredo compartilhado Laravel <-> n8n (header X-Webhook-Secret).
    | Por padrão reaproveita o mesmo secret dos cursos modulares; defina
    | BLOG_N8N_SECRET no .env se quiser um segredo separado para o blog.
    */
    'n8n_secret' => env('BLOG_N8N_SECRET', env('CURSOS_N8N_SECRET', 'P@o200471')),

];  
