<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Ava\CursosAvaController;
use App\Http\Controllers\Ava\PlayerController;
use App\Http\Controllers\Ava\SubscriptionAreaController;
use App\Http\Controllers\Ava\PerfilController;
use App\Http\Controllers\Ava\ModularStudyController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\CursoLandingController;
use App\Http\Controllers\Admin\PropostaController;
use App\Http\Controllers\GuiaLicitacoesController;
use App\Http\Controllers\Admin\LeadsGuiaController;
use App\Http\Controllers\Admin\ModularCourseController;
use App\Http\Controllers\Admin\CourseMaterialController;
use App\Http\Controllers\Admin\AdCreativeController;
use App\Http\Controllers\Admin\CourseCoverController;
use App\Http\Controllers\Admin\CourseVideoController;
use App\Http\Controllers\Admin\ModularEnrollmentController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Admin\BlogPostController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogTagController;
use App\Http\Controllers\Admin\BlogGeneratorController;
use App\Http\Controllers\Admin\SocialAccountController;
use App\Http\Controllers\Admin\SocialPostController;
use App\Http\Controllers\Admin\SocialGeneratorController;
use App\Http\Controllers\Admin\SocialArtReviewController;
// ── Site ──────────────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/minisseries',        [CursoController::class, 'index'])->name('cursos');
Route::get('/minisseries/{slug}', [CursoController::class, 'show'])->name('curso.show');
Route::get('/minisseriespos/{slug}', [CursoController::class, 'showPos'])->name('curso.showPos');

Route::get('/sobre',   [PageController::class, 'sobre'])->name('sobre');
Route::get('/contato', [PageController::class, 'contato'])->name('contato');
Route::post('/contato',[PageController::class, 'contatoEnviar'])->name('contato.enviar');
Route::get('/redirect',[PageController::class, 'redirect'])->name('redirect');
Route::get('/view/minisseries/{slug}', [CursoLandingController::class, 'show'])->name('curso.show');
Route::get('/lp/licitacoes/{slug}', [CursoLandingController::class, 'showLicitacoes'])->name('lp.licitacoes');

//Blog
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/categoria/{slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/tag/{slug}', [BlogController::class, 'tag'])->name('blog.tag');


// ── Checkout ──────────────────────────────────────────────────────────────
Route::get('/checkout',                    [CheckoutController::class, 'index'])->name('checkout');
Route::post('/checkout',                   [CheckoutController::class, 'processar'])->name('checkout.processar');
Route::get('/checkout/sucesso',            [CheckoutController::class, 'sucesso'])->name('checkout.sucesso');
Route::get('/checkout/status/{paymentId}', [CheckoutController::class, 'status'])->name('checkout.status');
Route::post('/webhooks/asaas',             [WebhookController::class, 'asaas'])->name('webhooks.asaas');
//ACESSO PÓS — mantida por compatibilidade com links antigos. Nome próprio para não colidir
// com a rota autenticada 'player' (route('player') deve resolver /dashboard/player/{slug}).
Route::get('/dashboard/playerpos/{slug}',         [PlayerController::class, 'show'])->name('player.pos');

// ---- Landing page publica + captura de lead ----
Route::get('/guia-licitacoes',          [GuiaLicitacoesController::class, 'landing'])->name('guia.landing');
Route::post('/guia-licitacoes',         [GuiaLicitacoesController::class, 'store'])->name('guia.store');
Route::get('/guia-licitacoes/obrigado', [GuiaLicitacoesController::class, 'obrigado'])->name('guia.obrigado');

// Link do arquivo enviado por e-mail (URL assinada — middleware 'signed').
Route::get('/guia-licitacoes/arquivo', [GuiaLicitacoesController::class, 'arquivo'])
    ->name('guia.arquivo')
    ->middleware('signed');
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
    Route::get('/dashboard/modulares',                 [ModularStudyController::class, 'index'])->name('ava.modulares');
    Route::get('/dashboard/modulares/{slug}',          [ModularStudyController::class, 'show'])->name('ava.modulares.show');
    Route::post('/dashboard/modulares/{id}/prova/resultado', [ModularStudyController::class, 'provaResultado'])->name('ava.modulares.prova')->whereNumber('id');


    // Route::get('/dashboard/player/{slug}',[PlayerController::class, 'show'])->name('player');
    Route::get('/dashboard/player/{slug}/{videoId}',[PlayerController::class, 'show'])->name('player.video');

 
    Route::post('/dashboard/player/{slug}/{videoId}/concluir',[PlayerController::class, 'concluir'])->name('player.concluir');

    
    Route::post('/dashboard/player/{slug}/material/{materialId}/registrar', [PlayerController::class, 'registrarMaterial'])->name('player.material.registrar');
});
Route::get('/busca', [SearchController::class, 'index'])->name('busca');
// Route::post('/funil/registrar', [FunnelController::class, 'registrar'])->name('funil.registrar');

Route::get('/comprarealizada', fn () => view('pages.compra-realizada'))->name('compra.realizada');
// ══════════════════════════════════════════════════════════════════════════
// ADMIN — middleware 'admin' bloqueia power < 13
// Rotas sensíveis adicionalmente protegidas por 'admin.can:gate_name'
// ══════════════════════════════════════════════════════════════════════════
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin', 'admin.can:admin.blog'])->group(function () {

    // Gerador por IA
    Route::get('/blog/gerar',  [BlogGeneratorController::class, 'form'])->name('blog.generate.form');
    Route::post('/blog/gerar', [BlogGeneratorController::class, 'gerar'])->name('blog.generate.run');

    // Posts
    Route::get('/blog/posts',                [BlogPostController::class, 'index'])->name('blog.posts.index');
    Route::get('/blog/posts/criar',          [BlogPostController::class, 'create'])->name('blog.posts.create');
    Route::post('/blog/posts',               [BlogPostController::class, 'store'])->name('blog.posts.store');
    Route::get('/blog/posts/{post}/editar',  [BlogPostController::class, 'edit'])->name('blog.posts.edit');
    Route::put('/blog/posts/{post}',         [BlogPostController::class, 'update'])->name('blog.posts.update');
    Route::delete('/blog/posts/{post}',      [BlogPostController::class, 'destroy'])->name('blog.posts.destroy');
    Route::get('/blog/posts/{post}/preview', [BlogPostController::class, 'preview'])->name('blog.posts.preview');

    // Categorias
    Route::get('/blog/categorias',               [BlogCategoryController::class, 'index'])->name('blog.categories.index');
    Route::post('/blog/categorias',              [BlogCategoryController::class, 'store'])->name('blog.categories.store');
    Route::put('/blog/categorias/{category}',    [BlogCategoryController::class, 'update'])->name('blog.categories.update');
    Route::delete('/blog/categorias/{category}', [BlogCategoryController::class, 'destroy'])->name('blog.categories.destroy');

    // Tags
    Route::get('/blog/tags',          [BlogTagController::class, 'index'])->name('blog.tags.index');
    Route::post('/blog/tags',         [BlogTagController::class, 'store'])->name('blog.tags.store');
    Route::put('/blog/tags/{tag}',    [BlogTagController::class, 'update'])->name('blog.tags.update');
    Route::delete('/blog/tags/{tag}', [BlogTagController::class, 'destroy'])->name('blog.tags.destroy');
});
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {

        Route::get('/', fn () => redirect()->route('admin.dashboard'));
        //Proposta
        Route::get('/proposta',  [PropostaController::class, 'form'])->name('proposta');
        Route::post('/proposta', [PropostaController::class, 'gerar'])->name('proposta.gerar');
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
        // ── Leads do Guia de Licitacoes ──────────────────────────────
        Route::get('/leads-guia',                  [LeadsGuiaController::class, 'index'])->name('leads-guia');
        Route::get('/leads-guia/exportar',         [LeadsGuiaController::class, 'exportar'])->name('leads-guia.export');
        Route::post('/leads-guia/{id}/contatado',  [LeadsGuiaController::class, 'toggleContatado'])->name('leads-guia.toggle');
        Route::post('/leads-guia/{id}/observacao', [LeadsGuiaController::class, 'salvarObservacao'])->name('leads-guia.note');

        // ── Matrículas (super admin + comercial) ──────────────────────────
        Route::get('/matriculas',             [AdminController::class, 'matriculas'])->name('matriculas');
        Route::get('/matriculas/criar',       [AdminController::class, 'matriculaCreate'])->name('matriculas.create');
        Route::post('/matriculas',            [AdminController::class, 'matriculaStore'])->name('matriculas.store');
        Route::get('/matriculas/{id}/editar', [AdminController::class, 'matriculaEdit'])->name('matriculas.edit');
        Route::put('/matriculas/{id}',        [AdminController::class, 'matriculaUpdate'])->name('matriculas.update');

        // ── Assinaturas (modalidade que libera TODOS os cursos) ──────────
        Route::get('/assinaturas',                [SubscriptionController::class, 'index'])->name('assinaturas.index');
        Route::get('/assinaturas/criar',          [SubscriptionController::class, 'create'])->name('assinaturas.create');
        Route::post('/assinaturas',               [SubscriptionController::class, 'store'])->name('assinaturas.store');
        Route::get('/assinaturas/{id}/editar',    [SubscriptionController::class, 'edit'])->name('assinaturas.edit');
        Route::put('/assinaturas/{id}',           [SubscriptionController::class, 'update'])->name('assinaturas.update');
        Route::post('/assinaturas/{id}/cancelar', [SubscriptionController::class, 'cancel'])->name('assinaturas.cancel');
        Route::get('/assinaturas/{id}/acessos',   [SubscriptionController::class, 'acessos'])->name('assinaturas.acessos');

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

        // ── Cursos Modulares (apostila PDF → curso) — apenas super admin ──
        Route::get('/cursos-modulares',              [ModularCourseController::class, 'index'])->name('cursos-modulares')->middleware('admin.can:admin.cursos');
        Route::get('/cursos-modulares/criar',        [ModularCourseController::class, 'create'])->name('cursos-modulares.create')->middleware('admin.can:admin.cursos');
        Route::post('/cursos-modulares',             [ModularCourseController::class, 'store'])->name('cursos-modulares.store')->middleware('admin.can:admin.cursos');
        Route::get('/cursos-modulares/{id}',         [ModularCourseController::class, 'show'])->name('cursos-modulares.show')->middleware('admin.can:admin.cursos')->whereNumber('id');
        Route::get('/cursos-modulares/{id}/arquivo', [ModularCourseController::class, 'download'])->name('cursos-modulares.download')->middleware('admin.can:admin.cursos')->whereNumber('id');
        Route::delete('/cursos-modulares/{id}',      [ModularCourseController::class, 'destroy'])->name('cursos-modulares.destroy')->middleware('admin.can:admin.cursos')->whereNumber('id');

        // Cursos Modulares — geração (n8n) + gerência dos assets
        Route::post('/cursos-modulares/{id}/gerar',                    [ModularCourseController::class, 'gerar'])->name('cursos-modulares.gerar')->middleware('admin.can:admin.cursos')->whereNumber('id');
        Route::post('/cursos-modulares/{id}/assets/{assetId}/aprovar', [ModularCourseController::class, 'assetApprove'])->name('cursos-modulares.assets.approve')->middleware('admin.can:admin.cursos')->whereNumber('id')->whereNumber('assetId');
        Route::post('/cursos-modulares/{id}/assets/{assetId}/reprovar',[ModularCourseController::class, 'assetReject'])->name('cursos-modulares.assets.reject')->middleware('admin.can:admin.cursos')->whereNumber('id')->whereNumber('assetId');
        Route::put('/cursos-modulares/{id}/assets/{assetId}',          [ModularCourseController::class, 'assetUpdate'])->name('cursos-modulares.assets.update')->middleware('admin.can:admin.cursos')->whereNumber('id')->whereNumber('assetId');
        Route::delete('/cursos-modulares/{id}/assets/{assetId}',       [ModularCourseController::class, 'assetDestroy'])->name('cursos-modulares.assets.destroy')->middleware('admin.can:admin.cursos')->whereNumber('id')->whereNumber('assetId');

        // Cursos Modulares — Media Kit (card + story) + gerar tudo
        Route::post('/cursos-modulares/{id}/media-kit/gerar',           [ModularCourseController::class, 'gerarMediaKit'])->name('cursos-modulares.media.gerar')->middleware('admin.can:admin.cursos')->whereNumber('id');
        Route::post('/cursos-modulares/{id}/gerar-tudo',                [ModularCourseController::class, 'gerarTudo'])->name('cursos-modulares.gerar-tudo')->middleware('admin.can:admin.cursos')->whereNumber('id');
        Route::post('/cursos-modulares/{id}/media/{assetId}/aprovar',   [ModularCourseController::class, 'mediaApprove'])->name('cursos-modulares.media.approve')->middleware('admin.can:admin.cursos')->whereNumber('id')->whereNumber('assetId');
        Route::post('/cursos-modulares/{id}/media/{assetId}/reprovar',  [ModularCourseController::class, 'mediaReject'])->name('cursos-modulares.media.reject')->middleware('admin.can:admin.cursos')->whereNumber('id')->whereNumber('assetId');
        Route::put('/cursos-modulares/{id}/media/{assetId}/legenda',    [ModularCourseController::class, 'mediaUpdateCaption'])->name('cursos-modulares.media.caption')->middleware('admin.can:admin.cursos')->whereNumber('id')->whereNumber('assetId');
        Route::delete('/cursos-modulares/{id}/media/{assetId}',         [ModularCourseController::class, 'mediaDestroy'])->name('cursos-modulares.media.destroy')->middleware('admin.can:admin.cursos')->whereNumber('id')->whereNumber('assetId');

        // Cursos Modulares — Podcast em áudio (Gemini TTS)
        Route::post('/cursos-modulares/{id}/podcast-audio/gerar', [ModularCourseController::class, 'gerarPodcastAudio'])->name('cursos-modulares.podcast.gerar')->middleware('admin.can:admin.cursos')->whereNumber('id');
        Route::delete('/cursos-modulares/{id}/podcast-audio',     [ModularCourseController::class, 'podcastAudioDestroy'])->name('cursos-modulares.podcast.destroy')->middleware('admin.can:admin.cursos')->whereNumber('id');
        Route::post('/cursos-modulares/{id}/resumo-pdf/gerar',       [CourseMaterialController::class, 'gerarResumoPdf'])->name('cursos-modulares.resumo-pdf.gerar')->middleware('admin.can:admin.cursos')->whereNumber('id');
        Route::post('/cursos-modulares/{id}/cartoes/gerar',          [CourseMaterialController::class, 'gerarCartoes'])->name('cursos-modulares.cartoes.gerar')->middleware('admin.can:admin.cursos')->whereNumber('id');
        Route::post('/cursos-modulares/{id}/prova/gerar',            [CourseMaterialController::class, 'gerarProva'])->name('cursos-modulares.prova.gerar')->middleware('admin.can:admin.cursos')->whereNumber('id');
        Route::post('/cursos-modulares/{id}/ads/gerar',              [AdCreativeController::class, 'gerarAds'])->name('cursos-modulares.ads.gerar')->middleware('admin.can:admin.cursos')->whereNumber('id');
        Route::delete('/cursos-modulares/{id}/ads',                  [AdCreativeController::class, 'adDestroy'])->name('cursos-modulares.ads.destroy')->middleware('admin.can:admin.cursos')->whereNumber('id');
        Route::post('/cursos-modulares/{id}/capa/gerar',             [CourseCoverController::class, 'gerarCapa'])->name('cursos-modulares.capa.gerar')->middleware('admin.can:admin.cursos')->whereNumber('id');
        Route::delete('/cursos-modulares/{id}/capa',                 [CourseCoverController::class, 'capaDestroy'])->name('cursos-modulares.capa.destroy')->middleware('admin.can:admin.cursos')->whereNumber('id');
        Route::post('/cursos-modulares/{id}/video/gerar',            [CourseVideoController::class, 'gerar'])->name('cursos-modulares.video.gerar')->middleware('admin.can:admin.cursos')->whereNumber('id');
        Route::delete('/cursos-modulares/{id}/video/{videoId}',      [CourseVideoController::class, 'videoDestroy'])->name('cursos-modulares.video.destroy')->middleware('admin.can:admin.cursos')->whereNumber('id')->whereNumber('videoId');
        Route::post('/cursos-modulares/{id}/matriculas',             [ModularEnrollmentController::class, 'matricular'])->name('cursos-modulares.matriculas.store')->middleware('admin.can:admin.cursos')->whereNumber('id');
        Route::patch('/cursos-modulares/{id}/matriculas/{matricula}/cancelar', [ModularEnrollmentController::class, 'cancelar'])->name('cursos-modulares.matriculas.cancelar')->middleware('admin.can:admin.cursos')->whereNumber('id')->whereNumber('matricula');
        Route::delete('/cursos-modulares/{id}/materiais/{type}',     [CourseMaterialController::class, 'materialDestroy'])->name('cursos-modulares.materiais.destroy')->middleware('admin.can:admin.cursos')->whereNumber('id');


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

    Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin', 'admin.can:admin.social'])->group(function () {

        // Conta conectada
        Route::get('social/conta', [SocialAccountController::class, 'index'])->name('social.accounts.index');
        Route::put('social/conta', [SocialAccountController::class, 'update'])->name('social.accounts.update');
    
        // Calendário editorial
        Route::get('social/calendario', [SocialPostController::class, 'calendar'])->name('social.calendar');
    
        // Geração com IA (dispara para o n8n)
        Route::post('social/gerar', [SocialGeneratorController::class, 'gerar'])->name('social.generate');
    
        // Fila de aprovação das artes geradas
        Route::get('social/aprovacao', [SocialArtReviewController::class, 'index'])->name('social.review.index');
        Route::post('social/aprovacao/{draft}/aprovar', [SocialArtReviewController::class, 'aprovar'])->name('social.review.aprovar');
        Route::post('social/aprovacao/{draft}/descartar', [SocialArtReviewController::class, 'descartar'])->name('social.review.descartar');
        Route::post('social/aprovacao/{draft}/reprovar',  [SocialArtReviewController::class, 'reprovar'])->name('social.review.reprovar');
    
        // Posts
        Route::get('social/posts', [SocialPostController::class, 'index'])->name('social.posts.index');
        Route::get('social/posts/novo', [SocialPostController::class, 'create'])->name('social.posts.create');
        Route::post('social/posts', [SocialPostController::class, 'store'])->name('social.posts.store');
        Route::get('social/posts/{post}/editar', [SocialPostController::class, 'edit'])->name('social.posts.edit');
        Route::put('social/posts/{post}', [SocialPostController::class, 'update'])->name('social.posts.update');
        Route::delete('social/posts/{post}', [SocialPostController::class, 'destroy'])->name('social.posts.destroy');
        Route::delete('social/posts/{post}/media/{media}', [SocialPostController::class, 'destroyMedia'])->name('social.posts.media.destroy');
    });
    
    



// ── Área do Assinante (assinatura ativa) ──────────────────────────────────
Route::middleware(['auth', 'subscriber'])->group(function () {
    Route::get('/assinante', [SubscriptionAreaController::class, 'home'])->name('assinante.home');
});
