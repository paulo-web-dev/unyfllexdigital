<?php

/*
|--------------------------------------------------------------------------
| INSTRUÇÃO — substitua as rotas de checkout no web.php
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\WebhookController;

// ── Checkout ──────────────────────────────────────────────────────────────
Route::get('/checkout',                        [CheckoutController::class, 'index'])->name('checkout');
Route::post('/checkout',                       [CheckoutController::class, 'processar'])->name('checkout.processar');
Route::get('/checkout/sucesso',                [CheckoutController::class, 'sucesso'])->name('checkout.sucesso');

// Polling de status — chamado pelo JS a cada 5s após PIX/Boleto ser gerado
Route::get('/checkout/status/{paymentId}',     [CheckoutController::class, 'status'])->name('checkout.status');

// ── Webhook Asaas (excluir do CSRF em VerifyCsrfToken) ────────────────────
Route::post('/webhooks/asaas',                 [WebhookController::class, 'asaas'])->name('webhooks.asaas');
