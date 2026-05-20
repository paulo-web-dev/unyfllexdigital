<?php

namespace App\Http\Controllers;

use App\DTOs\CheckoutDTO;
use App\Http\Requests\CheckoutRequest;
use App\Models\Classes;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkoutService
    ) {}

    // ── GET /checkout ────────────────────────────────────────────────────
    public function index(Request $request)
    {
        // Carrega minisséries disponíveis para o JS popular o carrinho
        $minisseries = Classes::where('express', '1')
            ->where('status', 'able')
            ->select('id', 'title', 'subtitle', 'photo', 'valor')
            ->orderBy('title')
            ->get()
            ->map(fn ($c) => [
                'id'     => $c->id,
                'title'  => $c->title,
                'thumb'  => $c->photo ? "https://unyflex.com.br/storage/cursos/banner/{$c->photo}" : null,
                'valor'  => (float) ($c->valor ?? 998),
            ]);

        return view('pages.checkout', compact('minisseries'));
    }

    // ── POST /checkout ───────────────────────────────────────────────────
    public function processar(CheckoutRequest $request)
    {
        // Rate limit: 5 tentativas por IP por minuto
        $key = 'checkout:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Muitas tentativas. Aguarde um momento e tente novamente.',
            ], 429);
        }
        RateLimiter::hit($key, 60);

        try {
            $dto       = CheckoutDTO::fromArray($request->validated());
            $resultado = $this->checkoutService->processar($dto);

            return response()->json($resultado);

        } catch (\RuntimeException $e) {
            Log::warning('[Checkout] Erro controlado', ['msg' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Throwable $e) {
            Log::error('[Checkout] Erro inesperado', [
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro interno. Tente novamente ou entre em contato.',
            ], 500);
        }
    }

    // ── GET /checkout/sucesso ────────────────────────────────────────────
    public function sucesso(Request $request)
    {
        $enrollmentId = $request->get('enrollment');
        return view('pages.checkout-sucesso', compact('enrollmentId'));
    }
}
