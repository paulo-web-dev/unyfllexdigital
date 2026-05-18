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
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\SearchController;

/*
|--------------------------------------------------------------------------
| Site Marketing
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
| Auth
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/esqueci-senha',           [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/esqueci-senha',          [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/redefinir-senha/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/redefinir-senha',        [AuthController::class, 'resetPassword'])->name('password.update');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| AVA
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard',                       [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/cursos',                [CursosAvaController::class, 'index'])->name('ava.cursos');
    Route::get('/dashboard/player/{slug}',         [PlayerController::class, 'show'])->name('player');
    Route::post('/dashboard/player/{id}/concluir', [PlayerController::class, 'concluir'])->name('player.concluir');
    Route::get('/dashboard/perfil',                [PerfilController::class, 'index'])->name('perfil');
    Route::post('/dashboard/perfil',               [PerfilController::class, 'update'])->name('perfil.update');
});
Route::get('/busca', [SearchController::class, 'index'])->name('busca');

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {

        Route::get('/', fn () => redirect()->route('admin.dashboard'));

        Route::get('/dashboard',  [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/alunos',     [AdminController::class, 'alunos'])->name('alunos');
        Route::get('/matriculas', [AdminController::class, 'matriculas'])->name('matriculas');
        Route::get('/financeiro', [AdminController::class, 'financeiro'])->name('financeiro');
        Route::get('/analytics',  [AdminController::class, 'analytics'])->name('analytics');
        Route::get('/vendas',     [AdminController::class, 'vendas'])->name('vendas');
        Route::get('/cupons',     [AdminController::class, 'cupons'])->name('cupons');
        Route::get('/certif',     [AdminController::class, 'certif'])->name('certif');
        Route::get('/relatorios', [AdminController::class, 'relatorios'])->name('relatorios');
        Route::get('/suporte',    [AdminController::class, 'suporte'])->name('suporte');
        Route::get('/equipe',     [AdminController::class, 'equipe'])->name('equipe');
        Route::get('/permissoes', [AdminController::class, 'permissoes'])->name('permissoes');
        Route::get('/logs',       [AdminController::class, 'logs'])->name('logs');
        Route::get('/integ',      [AdminController::class, 'integ'])->name('integ');
        Route::get('/config',     [AdminController::class, 'config'])->name('config');

        // ── Cursos ────────────────────────────────────────────────────────
        Route::get('/cursos',             [AdminController::class, 'cursos'])->name('cursos');
        Route::get('/cursos/criar',       [AdminController::class, 'cursoCreate'])->name('cursos.create');
        Route::post('/cursos',            [AdminController::class, 'cursoStore'])->name('cursos.store');
        Route::get('/cursos/{id}',        [AdminController::class, 'cursoShow'])->name('cursos.show');
        Route::get('/cursos/{id}/editar', [AdminController::class, 'cursoEdit'])->name('cursos.edit');
        Route::put('/cursos/{id}',        [AdminController::class, 'cursoUpdate'])->name('cursos.update');

        // ── Panels ────────────────────────────────────────────────────────
        Route::get('/cursos/{classeId}/panels/criar', [AdminController::class, 'panelCreate'])->name('panels.create');
        Route::post('/cursos/{classeId}/panels',      [AdminController::class, 'panelStore'])->name('panels.store');
        Route::get('/panels/{id}/editar',             [AdminController::class, 'panelEdit'])->name('panels.edit');
        Route::put('/panels/{id}',                    [AdminController::class, 'panelUpdate'])->name('panels.update');

        // ── Vídeos ────────────────────────────────────────────────────────
        Route::put('/videos/{id}', [AdminController::class, 'videoUpdate'])->name('videos.update');

        // ── Materiais ─────────────────────────────────────────────────────
        Route::get('/materiais',              [AdminController::class, 'materiais'])->name('materiais');
        Route::get('/materiais/criar',        [AdminController::class, 'materialCreate'])->name('materiais.create');
        Route::post('/materiais',             [AdminController::class, 'materialStore'])->name('materiais.store');
        Route::get('/materiais/{id}/editar',  [AdminController::class, 'materialEdit'])->name('materiais.edit');
        Route::put('/materiais/{id}',         [AdminController::class, 'materialUpdate'])->name('materiais.update');
        Route::delete('/materiais/{id}',      [AdminController::class, 'materialDestroy'])->name('materiais.destroy');
        Route::post('/materiais/{materialId}/vincular/{panelId}',      [AdminController::class, 'materialVincular'])->name('materiais.vincular');
        Route::delete('/materiais/{materialId}/desvincular/{panelId}', [AdminController::class, 'materialDesvincular'])->name('materiais.desvincular');
    });
