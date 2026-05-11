<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Ava\CursosAvaController;
use App\Http\Controllers\Ava\PlayerController;
use App\Http\Controllers\Ava\PerfilController;

/*
|--------------------------------------------------------------------------
| Site Marketing — Unyflex Digital
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/minisseries',        [CursoController::class, 'index'])->name('cursos');
Route::get('/minisseries/{slug}', [CursoController::class, 'show'])->name('curso.show');

Route::get('/checkout', [PageController::class, 'checkout'])->name('checkout');
Route::get('/sobre',    [PageController::class, 'sobre'])->name('sobre');
Route::get('/contato',  [PageController::class, 'contato'])->name('contato');
Route::post('/contato', [PageController::class, 'contatoEnviar'])->name('contato.enviar');

/*
|--------------------------------------------------------------------------
| Auth — Login / Logout / Recuperação de senha
|--------------------------------------------------------------------------
*/

// Rotas acessíveis apenas para quem NÃO está logado
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    Route::get('/esqueci-senha',  [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/esqueci-senha', [AuthController::class, 'sendResetLink'])->name('password.email');

    Route::get('/redefinir-senha/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/redefinir-senha',        [AuthController::class, 'resetPassword'])->name('password.update');
});

// Logout — só quem está logado
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| AVA — Área do Aluno (/dashboard/*) — protegido por auth
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/dashboard/cursos', [CursosAvaController::class, 'index'])->name('ava.cursos');

    Route::get('/dashboard/player/{id?}',          [PlayerController::class, 'show'])->name('player');
    Route::post('/dashboard/player/{id}/concluir', [PlayerController::class, 'concluir'])->name('player.concluir');

    Route::get('/dashboard/perfil',  [PerfilController::class, 'index'])->name('perfil');
    Route::post('/dashboard/perfil', [PerfilController::class, 'update'])->name('perfil.update');
});
