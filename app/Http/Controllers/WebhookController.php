<?php

namespace App\Http\Controllers;

use App\Events\PagamentoAprovado;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    // ── POST /webhooks/asaas ────────────────────────────────────────────
    public function asaas(Request $request)
    {
        // Valida token de segurança do header (configurar no painel Asaas)
        $token = config('asaas.webhook_token');
        if ($token && $request->header('asaas-access-token') !== $token) {
            Log::warning('[Webhook] Token inválido', ['ip' => $request->ip()]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $payload = $request->all();
        $evento  = $payload['event']   ?? null;
        $payment = $payload['payment'] ?? null;

        Log::info('[Webhook] Evento recebido', ['event' => $evento, 'payment_id' => $payment['id'] ?? null]);

        match($evento) {
            'PAYMENT_CONFIRMED', 'PAYMENT_RECEIVED' => $this->confirmarPagamento($payment),
            'PAYMENT_OVERDUE'                       => $this->marcarVencido($payment),
            'PAYMENT_DELETED', 'PAYMENT_REFUNDED'   => $this->cancelarMatricula($payment),
            default => Log::info('[Webhook] Evento ignorado', ['event' => $evento]),
        };

        return response()->json(['received' => true]);
    }

    // ──────────────────────────────────────────────────────────────────────

    private function confirmarPagamento(array $payment): void
    {
        $enrollment = Enrollment::where('transaction_code', $payment['id'])->first();
        if (!$enrollment) {
            Log::warning('[Webhook] Matrícula não encontrada', ['payment_id' => $payment['id']]);
            return;
        }

        if ($enrollment->status === 'checked') {
            Log::info('[Webhook] Matrícula já confirmada, ignorando', ['id' => $enrollment->id]);
            return;
        }

        $enrollment->status = 'checked';
        $enrollment->save();

        $student = Student::find($enrollment->student_id);

        Log::info('[Webhook] Matrícula confirmada via webhook', [
            'enrollment_id' => $enrollment->id,
            'student_id'    => $enrollment->student_id,
        ]);

        // Dispara evento para enviar e-mail, liberar acesso, etc.
        event(new PagamentoAprovado($enrollment, $student, $payment));
    }

    private function marcarVencido(array $payment): void
    {
        $enrollment = Enrollment::where('transaction_code', $payment['id'])->first();
        if (!$enrollment) return;

        $enrollment->status = 'not_checked';
        $enrollment->log    = 'boleto_vencido_' . now()->toDateString();
        $enrollment->save();

        Log::info('[Webhook] Boleto vencido', ['enrollment_id' => $enrollment->id]);
    }

    private function cancelarMatricula(array $payment): void
    {
        $enrollment = Enrollment::where('transaction_code', $payment['id'])->first();
        if (!$enrollment) return;

        $enrollment->status      = 'canceled';
        $enrollment->canceledLog = 'cancelado_via_asaas_' . now()->toDateString();
        $enrollment->save();

        Log::info('[Webhook] Matrícula cancelada via webhook', ['enrollment_id' => $enrollment->id]);
    }
}
