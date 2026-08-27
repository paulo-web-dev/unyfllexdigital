<?php

return [

    /*
    | Canal comercial para renovação da assinatura (tela "assinatura expirada").
    | Não existe renovação pelo checkout: a assinatura é criada/renovada pelo admin.
    */
    'whatsapp_comercial' => env('ASSINANTE_WHATSAPP', '554188980259'),
    'email_comercial'    => env('ASSINANTE_EMAIL', 'atendimento@unyflex.com.br'),

];
