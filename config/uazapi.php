<?php

return [
    'base_url'       => env('UAZAPI_BASE_URL', ''),
    'instance_name'  => env('UAZAPI_INSTANCE_NAME', ''),
    'instance_token' => env('UAZAPI_INSTANCE_TOKEN', ''),

    // PORTAO DE ENVIO (Fatia 6). Default false: com ele desligado,
    // UazapiProvider::enviarTexto() lanca LogicException e NENHUM pacote sai.
    // Ligar e checkpoint humano explicito, nunca efeito colateral de outra
    // tarefa.
    //
    // ISTO NAO DESARMA A GUARDA DE INSTANCIA: mesmo com o portao aberto, fora
    // de producao o provedor continua recusando a instancia da denylist abaixo.
    // Sao duas travas independentes, de proposito.
    'envio_habilitado' => filter_var(env('UAZAPI_ENVIO_HABILITADO', false), FILTER_VALIDATE_BOOL),

    // Denylist de instancias de producao. Fora de producao, o provedor se recusa
    // a operar quando 'instance_name' cair aqui — ver UazapiProvider::guardaDeAmbiente().
    // Aceita mais de uma, separadas por virgula.
    'prod_instances' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('UAZAPI_PROD_INSTANCE_NAME', ''))
    ))),
];
