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
    'n8n_secret' => env('BLOG_N8N_SECRET', env('CURSOS_N8N_SECRET', 'TROQUE-ESTE-SECRET')),

    /*
    | URL de callback que o Laravel envia ao n8n. Por padrão é montada a
    | partir do domínio atual (url('/api/n8n/blog/artigo')). Em ambiente
    | LOCAL (127.0.0.1) o n8n na VPS não alcança seu localhost — defina
    | BLOG_CALLBACK_URL com uma URL pública (produção ou túnel ngrok/cloudflared):
    |   BLOG_CALLBACK_URL=https://SEU-TUNEL.ngrok.io/api/n8n/blog/artigo
    */
    'callback_url' => env('BLOG_CALLBACK_URL'),

];
