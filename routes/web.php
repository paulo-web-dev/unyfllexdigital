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
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\CursoLandingController;

// ── Site ──────────────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/minisseries',        [CursoController::class, 'index'])->name('cursos');
Route::get('/minisseries/{slug}', [CursoController::class, 'show'])->name('curso.show');
Route::get('/sobre',   [PageController::class, 'sobre'])->name('sobre');
Route::get('/contato', [PageController::class, 'contato'])->name('contato');
Route::post('/contato',[PageController::class, 'contatoEnviar'])->name('contato.enviar');
Route::get('/redirect',[PageController::class, 'redirect'])->name('redirect');
Route::get('/view/minisseries/{slug}', [CursoLandingController::class, 'show'])->name('curso.show');

// ── Checkout ──────────────────────────────────────────────────────────────
Route::get('/checkout',                    [CheckoutController::class, 'index'])->name('checkout');
Route::post('/checkout',                   [CheckoutController::class, 'processar'])->name('checkout.processar');
Route::get('/checkout/sucesso',            [CheckoutController::class, 'sucesso'])->name('checkout.sucesso');
Route::get('/checkout/status/{paymentId}', [CheckoutController::class, 'status'])->name('checkout.status');
Route::post('/webhooks/asaas',             [WebhookController::class, 'asaas'])->name('webhooks.asaas');

// ── Auth ──────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/esqueci-senha',           [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/esqueci-senha',          [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/redefinir-senha/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/redefinir-senha',        [AuthController::class, 'resetPassword'])->name('password.update');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ── AVA ───────────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/dashboard',                       [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/cursos',                [CursosAvaController::class, 'index'])->name('ava.cursos');
    Route::get('/dashboard/player/{slug}',         [PlayerController::class, 'show'])->name('player');
    Route::post('/dashboard/player/{id}/concluir', [PlayerController::class, 'concluir'])->name('player.concluir');
    Route::get('/dashboard/perfil',                [PerfilController::class, 'index'])->name('perfil');
    Route::post('/dashboard/perfil',               [PerfilController::class, 'update'])->name('perfil.update');


    // Route::get('/dashboard/player/{slug}',[PlayerController::class, 'show'])->name('player');
    Route::get('/dashboard/player/{slug}/{videoId}',[PlayerController::class, 'show'])->name('player.video');

 
    Route::post('/dashboard/player/{slug}/{videoId}/concluir',[PlayerController::class, 'concluir'])->name('player.concluir');

    
    Route::post('/dashboard/player/{slug}/material/{materialId}/registrar', [PlayerController::class, 'registrarMaterial'])->name('player.material.registrar');
});
Route::get('/busca', [SearchController::class, 'index'])->name('busca');
Route::post('/funil/registrar', [FunnelController::class, 'registrar'])->name('funil.registrar');

Route::get('/comprarealizada', fn () => view('pages.compra-realizada'))->name('compra.realizada');
// ══════════════════════════════════════════════════════════════════════════
// ADMIN — middleware 'admin' bloqueia power < 13
// Rotas sensíveis adicionalmente protegidas por 'admin.can:gate_name'
// ══════════════════════════════════════════════════════════════════════════
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {

        Route::get('/', fn () => redirect()->route('admin.dashboard'));

        // ── Dashboard (todos os admins, dados filtrados no controller) ────
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // ── Busca global (dados filtrados no controller) ──────────────────
        Route::get('/busca', [AdminController::class, 'adminBusca'])->name('busca');

        // ── Alunos (super admin + comercial) ─────────────────────────────
        Route::get('/alunos',             [AdminController::class, 'alunos'])->name('alunos');
        Route::get('/alunos/criar',       [AdminController::class, 'alunoCreate'])->name('alunos.create');
        Route::post('/alunos',            [AdminController::class, 'alunoStore'])->name('alunos.store');
        Route::get('/alunos/busca',       [AdminController::class, 'alunosBusca'])->name('alunos.busca');
        Route::get('/alunos/{id}/editar', [AdminController::class, 'alunoEdit'])->name('alunos.edit');
        Route::put('/alunos/{id}',        [AdminController::class, 'alunoUpdate'])->name('alunos.update');

        // ── Matrículas (super admin + comercial) ──────────────────────────
        Route::get('/matriculas',             [AdminController::class, 'matriculas'])->name('matriculas');
        Route::get('/matriculas/criar',       [AdminController::class, 'matriculaCreate'])->name('matriculas.create');
        Route::post('/matriculas',            [AdminController::class, 'matriculaStore'])->name('matriculas.store');
        Route::get('/matriculas/{id}/editar', [AdminController::class, 'matriculaEdit'])->name('matriculas.edit');
        Route::put('/matriculas/{id}',        [AdminController::class, 'matriculaUpdate'])->name('matriculas.update');

        // ── Financeiro / Analytics / Relatórios — apenas super admin ─────
        Route::get('/financeiro', [AdminController::class, 'financeiro'])->name('financeiro')->middleware('admin.can:admin.financeiro');
        Route::get('/analytics',  [AdminController::class, 'analytics'])->name('analytics')->middleware('admin.can:admin.analytics');
        Route::get('/relatorios', [AdminController::class, 'relatorios'])->name('relatorios')->middleware('admin.can:admin.relatorios');
        Route::get('/vendas',     [AdminController::class, 'vendas'])->name('vendas')->middleware('admin.can:admin.financeiro');
        Route::get('/cupons',     [AdminController::class, 'cupons'])->name('cupons')->middleware('admin.can:admin.financeiro');

        // ── Cursos / Materiais — apenas super admin ───────────────────────
        Route::get('/cursos',             [AdminController::class, 'cursos'])->name('cursos')->middleware('admin.can:admin.cursos');
        Route::get('/cursos/criar',       [AdminController::class, 'cursoCreate'])->name('cursos.create')->middleware('admin.can:admin.cursos');
        Route::post('/cursos',            [AdminController::class, 'cursoStore'])->name('cursos.store')->middleware('admin.can:admin.cursos');
        Route::get('/cursos/{id}',        [AdminController::class, 'cursoShow'])->name('cursos.show')->middleware('admin.can:admin.cursos');
        Route::get('/cursos/{id}/editar', [AdminController::class, 'cursoEdit'])->name('cursos.edit')->middleware('admin.can:admin.cursos');
        Route::put('/cursos/{id}',        [AdminController::class, 'cursoUpdate'])->name('cursos.update')->middleware('admin.can:admin.cursos');

        Route::get('/cursos/{classeId}/panels/criar', [AdminController::class, 'panelCreate'])->name('panels.create')->middleware('admin.can:admin.cursos');
        Route::post('/cursos/{classeId}/panels',      [AdminController::class, 'panelStore'])->name('panels.store')->middleware('admin.can:admin.cursos');
        Route::get('/panels/{id}/editar',             [AdminController::class, 'panelEdit'])->name('panels.edit')->middleware('admin.can:admin.cursos');
        Route::put('/panels/{id}',                    [AdminController::class, 'panelUpdate'])->name('panels.update')->middleware('admin.can:admin.cursos');
        Route::get('/panels/{panelId}/materiais/adicionar', [AdminController::class, 'materialParaPanel'])->name('panels.material.create')->middleware('admin.can:admin.cursos');
        Route::post('/panels/{panelId}/materiais',          [AdminController::class, 'materialParaPanelStore'])->name('panels.material.store')->middleware('admin.can:admin.cursos');
        Route::put('/videos/{id}',                    [AdminController::class, 'videoUpdate'])->name('videos.update')->middleware('admin.can:admin.cursos');

        Route::get('/materiais',              [AdminController::class, 'materiais'])->name('materiais')->middleware('admin.can:admin.cursos');
        Route::get('/materiais/criar',        [AdminController::class, 'materialCreate'])->name('materiais.create')->middleware('admin.can:admin.cursos');
        Route::post('/materiais',             [AdminController::class, 'materialStore'])->name('materiais.store')->middleware('admin.can:admin.cursos');
        Route::get('/materiais/{id}/editar',  [AdminController::class, 'materialEdit'])->name('materiais.edit')->middleware('admin.can:admin.cursos');
        Route::put('/materiais/{id}',         [AdminController::class, 'materialUpdate'])->name('materiais.update')->middleware('admin.can:admin.cursos');
        Route::delete('/materiais/{id}',      [AdminController::class, 'materialDestroy'])->name('materiais.destroy')->middleware('admin.can:admin.cursos');
        Route::post('/materiais/{mId}/vincular/{pId}',      [AdminController::class, 'materialVincular'])->name('materiais.vincular')->middleware('admin.can:admin.cursos');
        Route::delete('/materiais/{mId}/desvincular/{pId}', [AdminController::class, 'materialDesvincular'])->name('materiais.desvincular')->middleware('admin.can:admin.cursos');


        Route::get('/meu-link', [AdminController::class, 'meuLink'])->name('meu-link');
 
        // ── Sistema — apenas super admin ──────────────────────────────────
        Route::get('/referral', [AdminController::class, 'referralAnalytics'])
            ->name('referral')
            ->middleware('admin.can:admin.super');
        Route::get('/funil', [AdminController::class, 'funilAnalytics'])->name('funil')->middleware('admin.can:admin.super');

        Route::get('/certif',     [AdminController::class, 'certif'])->name('certif');
        Route::get('/suporte',    [AdminController::class, 'suporte'])->name('suporte');
        Route::get('/equipe',     [AdminController::class, 'equipe'])->name('equipe')->middleware('admin.can:admin.equipe');
        Route::get('/permissoes', [AdminController::class, 'permissoes'])->name('permissoes')->middleware('admin.can:admin.permissoes');
        Route::get('/logs',       [AdminController::class, 'logs'])->name('logs')->middleware('admin.can:admin.logs');
        Route::get('/integ',      [AdminController::class, 'integ'])->name('integ')->middleware('admin.can:admin.integ');
        Route::get('/config',     [AdminController::class, 'config'])->name('config')->middleware('admin.can:admin.config');
    });
