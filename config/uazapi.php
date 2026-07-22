<?php

return [
    'base_url'       => env('UAZAPI_BASE_URL', ''),
    'instance_name'  => env('UAZAPI_INSTANCE_NAME', ''),
    'instance_token' => env('UAZAPI_INSTANCE_TOKEN', ''),

    // Denylist de instancias de producao. Fora de producao, o provedor se recusa
    // a operar quando 'instance_name' cair aqui — ver UazapiProvider::guardaDeAmbiente().
    // Aceita mais de uma, separadas por virgula.
    'prod_instances' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('UAZAPI_PROD_INSTANCE_NAME', ''))
    ))),
];
