<?php
/*
|--------------------------------------------------------------------------
| INSTRUÇÃO — não substituir este arquivo inteiro no seu projeto.
|
| Abra  app/Http/Kernel.php  e localize o array  $middlewareAliases.
| Adicione UMA linha dentro dele:
|
|     'admin' => \App\Http\Middleware\IsAdmin::class,
|
| Exemplo de como ficará o trecho:
|
|     protected $middlewareAliases = [
|         'auth'   => \App\Http\Middleware\Authenticate::class,
|         ...
|         'admin'  => \App\Http\Middleware\IsAdmin::class,   // ← adicionar
|     ];
|--------------------------------------------------------------------------
*/
