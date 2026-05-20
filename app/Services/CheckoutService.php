<?php

namespace App\Services;

use App\DTOs\CheckoutDTO;
use App\Events\PagamentoAprovado;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(
        private readonly AsaasService $asaas
    ) {}

    // ══════════════════════════════════════════════════════════════════════
    // ENTRY POINT
    // ══════════════════════════════════════════════════════════════════════

    public function processar(CheckoutDTO $dto): array
    {
        Log::info('[Checkout] Iniciando processamento', [
            'email'  => $dto->email,
            'curso'  => $dto->classesId,
            'metodo' => $dto->metodoPagamento,
            'valor'  => $dto->valorFinal,
        ]);

        // Valida curso
        $classe = Classes::where('id', $dto->classesId)
            ->where('express', '1')
            ->where('status', 'able')
            ->firstOrFail();

        // Garante aluno
        $student = $this->resolverAluno($dto);

        // Idempotência
        $matriculaExistente = Enrollment::where('student_id', $student->id)
            ->where('classes_id', $dto->classesId)
            ->whereIn('status', ['checked', 'scheduled_billing'])
            ->first();

        if ($matriculaExistente) {
            throw new \RuntimeException('Você já possui matrícula ativa nesta minissérie.');
        }

        $externalRef = 'unyflex_' . $student->id . '_' . $dto->classesId . '_' . time();

        // Captura dados sensíveis direto do request — nunca persistem
        $cardNumberFull = preg_replace('/\D/', '', request()->input('card_number', ''));
        $cvv            = request()->input('card_cvv', '');

        return DB::transaction(function () use ($dto, $student, $externalRef, $cardNumberFull, $cvv) {

            // Customer no Asaas
            $customer   = $this->asaas->findOrCreateCustomer($dto);
            $customerId = $customer['id'];

            // Salva asaas_customer_id no student
            if (!$student->fingerprint || !str_starts_with((string) $student->fingerprint, 'cus_')) {
                $student->fingerprint = $customerId;
                $student->save();
            }

            // Cria cobrança — número e CVV só passam por aqui, nunca são logados ou persistidos
            $payment = $this->asaas->createPayment(
                $customerId,
                $dto,
                $externalRef,
                $cardNumberFull,
                $cvv
            );

            // Define status da matrícula
            $statusMatricula = $this->resolverStatusMatricula($dto->metodoPagamento, $payment['status'] ?? '');

            // Cria matrícula
            $enrollment = Enrollment::create([
                'student_id'       => $student->id,
                'classes_id'       => $dto->classesId,
                'modality'         => 'minisserie',
                'status'           => $statusMatricula,
                'value'            => $dto->valor,
                'discount'         => $dto->desconto,
                'final_value'      => $dto->valorFinal,
                'payment_method'   => $this->formatarMetodo($dto->metodoPagamento),
                'transaction_code' => $payment['id']          ?? null,
                'invoice'          => $payment['invoiceUrl']  ?? null,
                'payment_slip'     => $payment['bankSlipUrl'] ?? null,
                'start_date'       => now()->toDateString(),
                'end_date'         => now()->addYear()->toDateString(),
                'plano'            => 'Anual',
                'wallet'           => 'Matrícula automatica ASAAS',
                'log'              => 'checkout_automatico',
            ]);

            Log::info('[Checkout] Matrícula criada', [
                'enrollment_id' => $enrollment->id,
                'status'        => $statusMatricula,
                'payment_id'    => $payment['id'] ?? null,
            ]);

            // Se cartão aprovado imediatamente, dispara evento
            if ($dto->metodoPagamento === 'CREDIT_CARD' && in_array($payment['status'] ?? '', ['CONFIRMED', 'RECEIVED'])) {
                event(new PagamentoAprovado($enrollment, $student, $payment));
            }

            return $this->montarResposta($dto, $enrollment, $payment);
        });
    }

    // ══════════════════════════════════════════════════════════════════════
    // ALUNO — localiza ou cria
    // ══════════════════════════════════════════════════════════════════════

    private function resolverAluno(CheckoutDTO $dto): Student
    {
        $student = Student::where('cpf', $dto->cpf)->first()
                ?? Student::where('email', $dto->email)->first();

        if ($student) {
            Log::info('[Checkout] Aluno existente reutilizado', ['id' => $student->id]);
            $this->garantirUser($student, $dto);
            return $student;
        }

        $senha   = Str::random(10);
        $student = Student::create([
            'name'       => $dto->nome,
            'email'      => $dto->email,
            'cpf'        => $dto->cpf,
            'phone'      => $dto->whatsapp,
            'entidade'   => $dto->orgao,
            'password'   => Hash::make($senha),
            'status'     => 'able',
            'minisserie' => '1',
        ]);

        User::create([
            'name'       => $dto->nome,
            'email'      => $dto->email,
            'cpf'        => $dto->cpf,
            'password'   => Hash::make(substr($dto->cpf, 0, 14)),
            'student_id' => $student->id,
            'setor'      => $dto->orgao,
            'power'      => 1,
        ]);

        Log::info('[Checkout] Novo aluno criado', ['id' => $student->id]);
        return $student;
    }

    private function garantirUser(Student $student, CheckoutDTO $dto): void
    {
        if (!User::where('student_id', $student->id)->exists()) {
            User::create([
                'name'       => $dto->nome,
                'email'      => $dto->email,
                'cpf'        => $dto->cpf,
                'password'   => Hash::make(substr($dto->cpf, 0, 14)),
                'student_id' => $student->id,
                'power'      => 1,
            ]);
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════════════════

    private function resolverStatusMatricula(string $metodo, string $asaasStatus): string
    {
        if ($metodo === 'CREDIT_CARD' && in_array($asaasStatus, ['CONFIRMED', 'RECEIVED'])) {
            return 'checked';
        }
        return 'not_checked';
    }

    private function formatarMetodo(string $metodo): string
    {
        return match ($metodo) {
            'CREDIT_CARD' => 'Cartão de crédito',
            'PIX'         => 'PIX',
            'BOLETO'      => 'Boleto',
            default       => $metodo,
        };
    }

    private function montarResposta(CheckoutDTO $dto, Enrollment $enrollment, array $payment): array
    {
        $base = [
            'success'       => true,
            'enrollment_id' => $enrollment->id,
            'status'        => $enrollment->status,
            'metodo'        => $dto->metodoPagamento,
            'valor'         => $dto->valorFinal,
            'payment_id'    => $payment['id'] ?? null,
        ];

        return match ($dto->metodoPagamento) {
            'PIX' => array_merge($base, [
                'pix_qrcode'     => $payment['pixQrCode']       ?? null,
                'pix_copia_cola' => $payment['pixCopiaECola']   ?? null,
                'pix_expiracao'  => $payment['pixExpirationDate'] ?? null,
                'mensagem'       => 'PIX gerado! Escaneie o QR Code ou copie o código para pagar.',
            ]),
            'BOLETO' => array_merge($base, [
                'boleto_url'       => $payment['bankSlipUrl'] ?? null,
                'boleto_linha'     => $payment['nossoNumero'] ?? null,
                'boleto_vencimento'=> $payment['dueDate']     ?? null,
                'mensagem'         => 'Boleto gerado! O acesso é liberado após confirmação (1-2 dias úteis).',
            ]),
            'CREDIT_CARD' => array_merge($base, [
                'aprovado'    => in_array($payment['status'] ?? '', ['CONFIRMED', 'RECEIVED']),
                'invoice_url' => $payment['invoiceUrl'] ?? null,
                'mensagem'    => 'Pagamento aprovado! Seu acesso já está liberado.',
                'redirect'    => route('dashboard'),
            ]),
            default => $base,
        };
    }
}
