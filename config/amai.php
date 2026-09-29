<?php

return [

    /*
    | Aviso por e-mail a cada cadastro na estrutura AMAI (usuário ou ponto focal).
    | Lista separada por vírgula no .env. Vazia = nenhum aviso (registra no log e segue).
    | O envio usa o SMTP padrão (MAIL_*); falha de envio nunca desfaz o cadastro.
    */
    'aviso_cadastro_para' => array_values(array_filter(array_map('trim',
        explode(',', (string) env('AMAI_AVISO_CADASTRO_PARA', ''))
    ))),

];
