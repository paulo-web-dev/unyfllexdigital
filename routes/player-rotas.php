<?php

/*
|--------------------------------------------------------------------------
| INSTRUÇÃO — substitua o bloco de rotas do AVA no web.php pelas abaixo
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Ava\PlayerController;

Route::middleware('auth')->group(function () {
    // ...demais rotas do AVA...

    // Player — carrega a cápsula
    Route::get('/dashboard/player/{slug}',                          [PlayerController::class, 'show'])->name('player');
    Route::get('/dashboard/player/{slug}/{videoId}',                [PlayerController::class, 'show'])->name('player.video');

    // Registra view de cápsula (AJAX POST)
    Route::post('/dashboard/player/{slug}/{videoId}/concluir',      [PlayerController::class, 'concluir'])->name('player.concluir');

    // Registra acesso/download de material (AJAX POST) — NOVO
    Route::post('/dashboard/player/{slug}/material/{materialId}/registrar', [PlayerController::class, 'registrarMaterial'])->name('player.material.registrar');
});
