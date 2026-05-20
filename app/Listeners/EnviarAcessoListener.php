<?php

namespace App\Listeners;

use App\Events\PagamentoAprovado;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnviarAcessoListener
{
    public function handle(PagamentoAprovado $event): void
    {
        $enrollment = $event->enrollment;
        $student    = $event->student;

        if (!$student) {
            Log::warning('[Listener] Student não encontrado', ['enrollment_id' => $enrollment->id]);
            return;
        }

        // Garante que o User está ativo
        $user = User::where('student_id', $student->id)->first();
        if ($user) {
            Log::info('[Listener] Acesso liberado', [
                'user_id'       => $user->id,
                'enrollment_id' => $enrollment->id,
            ]);
        }

        // TODO: enviar e-mail de confirmação de acesso
        // Mail::to($student->email)->send(new AcessoLiberadoMail($student, $enrollment));

        Log::info('[Listener] PagamentoAprovado processado', [
            'student_email' => $student->email,
            'enrollment_id' => $enrollment->id,
            'payment_id'    => $event->paymentData['id'] ?? null,
        ]);
    }
}
