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

        $cookie = request()->cookie('referral');
    
        Log::info('[Checkout] Cookie referral', [
            'cookie_raw'     => $cookie,
            'all_cookies'    => request()->cookies->all(),
            'has_referral'   => request()->hasCookie('referral'),
        ]);

        Classes::where('id', $dto->classesId)
            ->where('express', '1')
            ->where('status', 'able')
            ->firstOrFail();

        $student = $this->resolverAluno($dto);

        $matriculaExistente = Enrollment::where('student_id', $student->id)
            ->where('classes_id', $dto->classesId)
            ->whereIn('status', ['checked', 'scheduled_billing'])
            ->first();

        if ($matriculaExistente) {
            throw new \RuntimeException('Você já possui matrícula ativa nesta minissérie.');
        }

        $externalRef    = 'unyflex_' . $student->id . '_' . $dto->classesId . '_' . time();
        $cardNumberFull = preg_replace('/\D/', '', request()->input('card_number', ''));
        $cvv            = request()->input('card_cvv', '');

        // ── Lê a carteira do cookie de referral ───────────────────────────
        // Prioridade: 1) cookie referral, 2) "Matrícula automatica ASAAS"
        $wallet = $this->resolverWallet();

        Log::info('[Checkout] Wallet resolvida', ['wallet' => $wallet]);

        return DB::transaction(function () use ($dto, $student, $externalRef, $cardNumberFull, $cvv, $wallet) {

            $customer   = $this->asaas->findOrCreateCustomer($dto);
            $customerId = $customer['id'];

            if (!$student->fingerprint || !str_starts_with((string) $student->fingerprint, 'cus_')) {
                $student->fingerprint = $customerId;
                $student->save();
            }

            $payment = $this->asaas->createPayment($customerId, $dto, $externalRef, $cardNumberFull, $cvv);

            $statusMatricula = $this->resolverStatusMatricula($dto->metodoPagamento, $payment['status'] ?? '');

            $enrollment = Enrollment::create([
                'student_id'       => $student->id,
                'classes_id'       => $dto->classesId,
                'modality'         => 'minisserie',
                'status'           => $statusMatricula,
                'value'            => $dto->valor,
                'discount'         => $dto->desconto,
                'final_value'      => $dto->valorFinal,
                'payment_method'   => $this->formatarMetodo($dto->metodoPagamento),
                'transaction_code' => $payment['id']                     ?? null,
                'invoice'          => $payment['invoiceUrl']             ?? ($payment['pixCopiaECola'] ?? null),
                'payment_slip'     => $payment['bankSlipUrl']            ?? ($payment['identificationField'] ?? null),
                'start_date'       => now()->toDateString(),
                'end_date'         => now()->addYear()->toDateString(),
                'plano'            => 'Anual',
                'wallet'           => $wallet,  // ← vendedor do cookie ou padrão
                'log'              => 'checkout_automatico',
            ]);

            Log::info('[Checkout] Matrícula criada', [
                'enrollment_id' => $enrollment->id,
                'status'        => $statusMatricula,
                'payment_id'    => $payment['id'] ?? null,
                'wallet'        => $wallet,
            ]);

            if ($dto->metodoPagamento === 'CREDIT_CARD'
                && in_array($payment['status'] ?? '', ['CONFIRMED', 'RECEIVED'])) {
                event(new PagamentoAprovado($enrollment, $student, $payment));
            }

            return $this->montarResposta($dto, $enrollment, $payment);
        });
    }

    // ══════════════════════════════════════════════════════════════════════
    // WALLET — resolve pelo cookie de referral
    // ══════════════════════════════════════════════════════════════════════

    private function resolverWallet(): string
    {
        $cookie = request()->cookie('referral');

        if ($cookie && strlen(trim($cookie)) > 0) {
            // Verifica se o token corresponde a um usuário comercial ativo
            $vendedor = User::where('power', 13)
                ->where(fn ($q) => $q
                    ->where('name', trim($cookie))                // token = nome
                    ->orWhereRaw('LOWER(name) = ?', [strtolower(trim($cookie))])
                )
                ->first();

            if ($vendedor) {
                return $vendedor->name;
            }

            // Se não achou usuário mas o token existe, usa o token mesmo
            // (pode ser que o vendedor use token customizado no futuro)
            return trim($cookie);
        }

        return 'Matrícula automatica ASAAS';
    }

    // ══════════════════════════════════════════════════════════════════════
    // ALUNO
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

        $student = Student::create([
            'name'       => $dto->nome,
            'email'      => $dto->email,
            'cpf'        => $dto->cpf,
            'phone'      => $dto->whatsapp,
            'entidade'   => $dto->orgao,
            'password'   => Hash::make($dto->cpf),
            'status'     => 'able',
            'minisserie' => '1',
        ]);

        User::create([
            'name'       => $dto->nome,
            'email'      => $dto->email,
            'cpf'        => $dto->cpf,
            'password'   => Hash::make($dto->cpf),
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
                'password'   => Hash::make($dto->cpf),
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
                'pix_qrcode'     => $payment['pixQrCode']         ?? null,
                'pix_copia_cola' => $payment['pixCopiaECola']     ?? null,
                'pix_expiracao'  => $payment['pixExpirationDate'] ?? null,
                'mensagem'       => 'PIX gerado! Escaneie o QR Code ou copie o código abaixo.',
            ]),
            'BOLETO' => array_merge($base, [
                'boleto_url'          => $payment['bankSlipUrl']         ?? ($payment['invoiceUrl'] ?? null),
                'boleto_linha'        => $payment['identificationField'] ?? ($payment['nossoNumero'] ?? null),
                'boleto_nosso_numero' => $payment['nossoNumero']         ?? null,
                'boleto_barcode'      => $payment['barCode']             ?? null,
                'boleto_vencimento'   => $payment['dueDate']             ?? null,
                'installment_id'      => $payment['installmentId']       ?? null,
                'mensagem'            => 'Boleto gerado! Pague até o vencimento para liberar seu acesso.',
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
