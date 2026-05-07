<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Site Marketing — Unyflex Digital
|--------------------------------------------------------------------------
*/

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Catálogo de miniséries
Route::get('/minisseries',         [CursoController::class, 'index'])->name('cursos');
Route::get('/minisseries/{slug}',  [CursoController::class, 'show'])->name('curso.show');

// Checkout
Route::get('/checkout', [PageController::class, 'checkout'])->name('checkout');

// Institucional
Route::get('/sobre',   [PageController::class, 'sobre'])->name('sobre');
Route::get('/contato', [PageController::class, 'contato'])->name('contato');
Route::post('/contato', [PageController::class, 'contatoEnviar'])->name('contato.enviar');

// Auth (visuais)
Route::get('/login',  [PageController::class, 'login'])->name('login');
Route::get('/logout', function () {
    auth()->logout();
    return redirect()->route('home');
})->name('logout');

// AVA / Dashboard (proteger com middleware('auth') quando auth estiver configurado)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/cursos',       fn() => view('pages.cursos'))->name('ava.cursos');
Route::get('/dashboard/player/{id?}', fn($id = 1) => view('pages.curso-show', ['slug' => $id]))->name('player');
Route::get('/dashboard/perfil',       fn() => redirect()->route('dashboard'))->name('perfil');
