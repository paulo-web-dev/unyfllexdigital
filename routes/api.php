<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ModularCourseController;
use App\Http\Controllers\Admin\CourseMaterialController;
use App\Http\Controllers\Admin\AdCreativeController;
use App\Http\Controllers\Admin\CourseCoverController;
use App\Http\Controllers\Admin\CourseVideoController;
use App\Http\Controllers\Admin\BlogGeneratorController;
use App\Http\Controllers\Admin\SocialGeneratorController;
use App\Http\Controllers\Admin\SocialPublisherController;
use App\Http\Controllers\Webhooks\UazapiWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Callback do n8n com os rascunhos gerados (resumo / podcast / vídeo).
// Sem CSRF (grupo 'api'); protegido pelo header X-Webhook-Secret no controller.
Route::post('/n8n/cursos-modulares/assets', [ModularCourseController::class, 'callback'])
    ->name('api.cursos-modulares.callback');

// Callback do n8n com as artes do media kit (card / story).
Route::post('/n8n/cursos-modulares/media-kit', [ModularCourseController::class, 'mediaKitCallback'])
    ->name('api.cursos-modulares.media-kit');

// Callback do n8n com o áudio do podcast (.wav em base64).
Route::post('/n8n/cursos-modulares/podcast-audio', [ModularCourseController::class, 'podcastAudioCallback'])
    ->name('api.cursos-modulares.podcast-audio');

Route::post('/n8n/cursos-modulares/materiais', [CourseMaterialController::class, 'materialCallback'])
    ->name('api.cursos-modulares.materiais');

Route::post('/n8n/cursos-modulares/ads', [AdCreativeController::class, 'adsCallback'])
    ->name('api.cursos-modulares.ads');

Route::post('/n8n/cursos-modulares/capa', [CourseCoverController::class, 'capaCallback'])
    ->name('api.cursos-modulares.capa');

// Callback do n8n com a URL do vídeo de resumo (.mp4 hospedado no video-assembler).
Route::post('/n8n/cursos-modulares/video', [CourseVideoController::class, 'videoCallback'])
    ->name('api.cursos-modulares.video');

// Callback do n8n com o artigo de blog gerado pela IA (cria rascunho).
// Sem CSRF (grupo 'api'); protegido pelo header X-Webhook-Secret no controller.
Route::post('/n8n/blog/artigo', [BlogGeneratorController::class, 'callback'])
    ->name('api.blog.artigo');

// Callback do n8n com as artes do dia geradas pela IA (cria posts pré-agendados).
Route::post('/n8n/social/artes', [SocialGeneratorController::class, 'callback'])
    ->name('api.social.artes');

// Fase 3 — publicação automática:
// due: n8n pede os posts agendados vencidos (e trava como "publicando").
Route::post('/n8n/social/due', [SocialPublisherController::class, 'due'])
    ->name('api.social.due');
// publicado: n8n devolve o resultado da publicação (marca publicado/falhou).
Route::post('/n8n/social/publicado', [SocialPublisherController::class, 'publicado'])
    ->name('api.social.publicado');

// ── Inbox Uazapi (WhatsApp) — INSTANCIA DE TESTE ──────────────────────────
// Sem CSRF (grupo 'api'); protegido pelo token no BODY, conferido no controller.
// Persiste o payload cru e responde; nenhum processamento aqui (Fatia 3).
// NOTA para a Fatia 8: o grupo 'api' aplica ThrottleRequests:api (60 req/min).
// Em teste sobra folga; em producao webhook barrado por throttle e mensagem
// perdida em silencio — rever antes do modo sombra.
Route::post('/webhooks/uazapi', [UazapiWebhookController::class, 'receber'])
    ->name('api.uazapi.webhook');
