<?php

namespace App\Http\Controllers;

use App\DTOs\CheckoutDTO;
use App\Http\Requests\CheckoutRequest;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Events\PagamentoAprovado;
use App\Models\Student;
use App\Services\AsaasService;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkoutService,
        private readonly AsaasService    $asaas,
    ) {}

    // ── GET /checkout ────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $minisseries = Classes::where('express', '1')
            ->where('status', 'able')
            ->select('id', 'title', 'subtitle', 'photo', 'valor')
            ->orderBy('title')
            ->get()
            ->map(fn ($c) => [
                'id'    => $c->id,
                'title' => $c->title,
                'thumb' => $c->photo ? "https://unyflex.com.br/storage/cursos/banner/{$c->photo}" : null,
                'valor' => (float) ($c->valor ?? 998),
            ]);

        return view('pages.checkout', compact('minisseries'));
    }

    // ── POST /checkout ───────────────────────────────────────────────────
    public function processar(CheckoutRequest $request)
    {
        $key = 'checkout:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Muitas tentativas. Aguarde e tente novamente.',
            ], 429);
        }
        RateLimiter::hit($key, 60);

        try {
            $dto       = CheckoutDTO::fromArray($request->validated());
            $resultado = $this->checkoutService->processar($dto);
            return response()->json($resultado);

        } catch (\RuntimeException $e) {
            Log::warning('[Checkout] Erro controlado', ['msg' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);

        } catch (\Throwable $e) {
            Log::error('[Checkout] Erro inesperado', [
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno. Tente novamente ou entre em contato.',
            ], 500);
        }
    }

    // ── GET /checkout/status/{paymentId} — polling de status (PIX/Boleto)
    public function status(Request $request, string $paymentId)
    {
        // Rate limit: 30 consultas por minuto por IP
        $key = 'checkout_status:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 30)) {
            return response()->json(['status' => 'RATE_LIMITED'], 429);
        }
        RateLimiter::hit($key, 60);

        try {
            $data   = $this->asaas->getPaymentStatus($paymentId);
            $status = $data['status'] ?? 'UNKNOWN';

            // Se confirmado, atualiza matrícula automaticamente
            if (in_array($status, ['CONFIRMED', 'RECEIVED'])) {
                $enrollment = Enrollment::where('transaction_code', $paymentId)->first();
                if ($enrollment && $enrollment->status !== 'checked') {
                    $enrollment->status = 'checked';
                    $enrollment->save();

                    $student = Student::find($enrollment->student_id);
                    event(new PagamentoAprovado($enrollment, $student, $data));

                    Log::info('[Checkout] Matrícula confirmada via polling', [
                        'enrollment_id' => $enrollment->id,
                        'payment_id'    => $paymentId,
                    ]);
                }
            }

            return response()->json([
                'status'   => $status,
                'approved' => in_array($status, ['CONFIRMED', 'RECEIVED']),
                'redirect' => in_array($status, ['CONFIRMED', 'RECEIVED']) ? route('dashboard') : null,
            ]);

        } catch (\Throwable $e) {
            Log::warning('[Checkout] Polling erro', ['msg' => $e->getMessage()]);
            return response()->json(['status' => 'ERROR'], 500);
        }
    }

    // ── GET /checkout/sucesso ────────────────────────────────────────────
    public function sucesso(Request $request)
    {
        $enrollmentId = $request->get('enrollment');
        return view('pages.checkout-sucesso', compact('enrollmentId'));
    }
}
