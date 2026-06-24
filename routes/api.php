<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ModularCourseController;

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
