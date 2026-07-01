<?php

use App\Http\Controllers\Admin\SocialAccountController;
use App\Http\Controllers\Admin\SocialGeneratorController;
use App\Http\Controllers\Admin\SocialPostController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Gestor de Mídias — Instagram (painel admin)
|--------------------------------------------------------------------------
| Registrado via require em routes/web.php.
| Protegido por auth + admin + gate admin.social (power >= 14).
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin', 'admin.can:admin.social'])->group(function () {

    // Conta conectada
    Route::get('social/conta', [SocialAccountController::class, 'index'])->name('social.accounts.index');
    Route::put('social/conta', [SocialAccountController::class, 'update'])->name('social.accounts.update');

    // Calendário editorial
    Route::get('social/calendario', [SocialPostController::class, 'calendar'])->name('social.calendar');

    // Geração com IA (dispara para o n8n)
    Route::post('social/gerar', [SocialGeneratorController::class, 'gerar'])->name('social.generate');

    // Posts
    Route::get('social/posts', [SocialPostController::class, 'index'])->name('social.posts.index');
    Route::get('social/posts/novo', [SocialPostController::class, 'create'])->name('social.posts.create');
    Route::post('social/posts', [SocialPostController::class, 'store'])->name('social.posts.store');
    Route::get('social/posts/{post}/editar', [SocialPostController::class, 'edit'])->name('social.posts.edit');
    Route::put('social/posts/{post}', [SocialPostController::class, 'update'])->name('social.posts.update');
    Route::delete('social/posts/{post}', [SocialPostController::class, 'destroy'])->name('social.posts.destroy');
    Route::delete('social/posts/{post}/media/{media}', [SocialPostController::class, 'destroyMedia'])->name('social.posts.media.destroy');
});
