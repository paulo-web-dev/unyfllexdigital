<?php

namespace App\Services;

use App\DTOs\CheckoutDTO;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('asaas.base_url'), '/');
        $this->apiKey  = config('asaas.api_key');
    }

    // ── HTTP base ──────────────────────────────────────────────────────────

    private function http()
    {
        return Http::withHeaders([
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
            'User-Agent'   => 'Unyflex-Digital/1.0',
        ])->timeout(30);
    }

    // ══════════════════════════════════════════════════════════════════════
    // CUSTOMERS
    // ══════════════════════════════════════════════════════════════════════

    public function findCustomerByCpf(string $cpf): ?array
    {
        $response = $this->http()->get("{$this->baseUrl}/v3/customers", ['cpfCnpj' => $cpf]);

        if ($response->successful()) {
            return $response->json('data.0');
        }

        Log::warning('[Asaas] findCustomerByCpf falhou', ['status' => $response->status()]);
        return null;
    }

    public function createCustomer(CheckoutDTO $dto): array
    {
        $response = $this->http()->post("{$this->baseUrl}/v3/customers", [
            'name'        => $dto->nome,
            'email'       => $dto->email,
            'cpfCnpj'     => $dto->cpf,
            'mobilePhone' => $dto->whatsapp,
            'company'     => $dto->orgao ?: null,
        ]);

        if ($response->failed()) {
            Log::error('[Asaas] Falha ao criar customer', [
                'email'  => $dto->email,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('Falha ao registrar cliente no gateway.');
        }

        Log::info('[Asaas] Customer criado', ['id' => $response->json('id')]);
        return $response->json();
    }

    public function findOrCreateCustomer(CheckoutDTO $dto): array
    {
        $existing = $this->findCustomerByCpf($dto->cpf);
        if ($existing) {
            Log::info('[Asaas] Customer já existe', ['id' => $existing['id']]);
            return $existing;
        }
        return $this->createCustomer($dto);
    }

    // ══════════════════════════════════════════════════════════════════════
    // PIX  — usa /v3/lean/payments
    // ══════════════════════════════════════════════════════════════════════

    public function createPixPayment(string $customerId, CheckoutDTO $dto, string $externalRef): array
    {
        $response = $this->http()->post("{$this->baseUrl}/v3/lean/payments", [
            'customer'          => $customerId,
            'billingType'       => 'PIX',
            'value'             => $dto->valorFinal,
            'dueDate'           => now()->addDays(1)->format('Y-m-d'),
            'description'       => "Unyflex Digital — Minissérie #{$dto->classesId}",
            'externalReference' => $externalRef,
        ]);

        if ($response->failed()) {
            Log::error('[Asaas] Falha PIX', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('Falha ao gerar PIX: ' . ($response->json('errors.0.description') ?? $response->body()));
        }

        $payment = $response->json();

        // Busca QR Code
        $qr = $this->getPixQrCode($payment['id']);
        $payment['pixQrCode']         = $qr['encodedImage']   ?? null;
        $payment['pixCopiaECola']     = $qr['payload']        ?? null;
        $payment['pixExpirationDate'] = $qr['expirationDate'] ?? null;

        Log::info('[Asaas] PIX criado', ['id' => $payment['id']]);
        return $payment;
    }

    public function getPixQrCode(string $paymentId): array
    {
        // Tenta primeiro com /v3/payments (funciona mesmo para lean)
        $response = $this->http()->get("{$this->baseUrl}/v3/payments/{$paymentId}/pixQrCode");

        Log::info('[Asaas] getPixQrCode', [
            'payment_id' => $paymentId,
            'status'     => $response->status(),
            'keys'       => array_keys($response->json() ?? []),
            'body_raw'   => substr($response->body(), 0, 300),
        ]);

        if ($response->successful()) {
            $data = $response->json();
            // Sandbox retorna encodedImage, produção pode retornar encoded
            if (!isset($data['encodedImage']) && isset($data['encoded'])) {
                $data['encodedImage'] = $data['encoded'];
            }
            return $data;
        }

        Log::warning('[Asaas] getPixQrCode falhou', [
            'payment_id' => $paymentId,
            'status'     => $response->status(),
            'body'       => $response->body(),
        ]);

        return [];
    }

    // ══════════════════════════════════════════════════════════════════════
    // BOLETO  — usa /v3/lean/payments + busca linha digitável
    // ══════════════════════════════════════════════════════════════════════

    public function createBoletoPayment(string $customerId, CheckoutDTO $dto, string $externalRef): array
    {
        $payload = [
            'customer'          => $customerId,
            'billingType'       => 'BOLETO',
            'value'             => $dto->valorFinal,
            'dueDate'           => now()->addDays(3)->format('Y-m-d'),
            'description'       => "Unyflex Digital — Minissérie #{$dto->classesId}",
            'externalReference' => $externalRef,
        ];

        if ($dto->parcelas > 1) {
            $payload['installmentCount'] = $dto->parcelas;
            $payload['installmentValue'] = round($dto->valorFinal / $dto->parcelas, 2);
        }

        $response = $this->http()->post("{$this->baseUrl}/v3/lean/payments", $payload);

        if ($response->failed()) {
            Log::error('[Asaas] Falha Boleto', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('Falha ao gerar boleto: ' . ($response->json('errors.0.description') ?? $response->body()));
        }

        $payment = $response->json();

        // Busca payment completo (tem mais campos que o lean)
        $full = $this->getPaymentFull($payment['id']);
        if (!empty($full)) {
            // Sobrescreve com dados completos
            $payment = array_merge($payment, $full);
        }

        // Busca linha digitável se não veio no payload
        if (empty($payment['nossoNumero']) || empty($payment['bankSlipUrl'])) {
            $ident = $this->getBoletoIdentificationField($payment['id']);
            $payment['identificationField'] = $ident['identificationField'] ?? ($payment['nossoNumero'] ?? null);
            $payment['barCode']             = $ident['barCode']             ?? null;
        } else {
            $payment['identificationField'] = $payment['nossoNumero'] ?? null;
        }

        if (isset($payment['installment'])) {
            $payment['installmentId'] = $payment['installment'];
        }

        Log::info('[Asaas] Boleto criado', [
            'id'          => $payment['id'],
            'parcelas'    => $dto->parcelas,
            'bankSlipUrl' => $payment['bankSlipUrl'] ?? null,
            'ident'       => $payment['identificationField'] ?? null,
        ]);

        return $payment;
    }

    // Busca payment completo — retorna mais campos que o lean
    public function getPaymentFull(string $paymentId): array
    {
        $response = $this->http()->get("{$this->baseUrl}/v3/payments/{$paymentId}");

        Log::info('[Asaas] getPaymentFull', [
            'payment_id'  => $paymentId,
            'status'      => $response->status(),
            'bankSlipUrl' => $response->json('bankSlipUrl'),
            'nossoNumero' => $response->json('nossoNumero'),
        ]);

        return $response->successful() ? $response->json() : [];
    }

    public function getBoletoIdentificationField(string $paymentId): array
    {
        // Tenta lean primeiro
        $response = $this->http()->get("{$this->baseUrl}/v3/lean/payments/{$paymentId}/identificationField");

        Log::info('[Asaas] getBoletoIdentField', [
            'payment_id' => $paymentId,
            'status'     => $response->status(),
            'keys'       => array_keys($response->json() ?? []),
            'body_raw'   => substr($response->body(), 0, 300),
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        // Fallback: tenta sem lean
        $response2 = $this->http()->get("{$this->baseUrl}/v3/payments/{$paymentId}/identificationField");

        Log::info('[Asaas] getBoletoIdentField fallback', [
            'status'   => $response2->status(),
            'body_raw' => substr($response2->body(), 0, 300),
        ]);

        return $response2->successful() ? $response2->json() : [];
    }

    // ══════════════════════════════════════════════════════════════════════
    // CARTÃO DE CRÉDITO — usa /v3/payments
    // número e CVV apenas em memória, nunca persistidos
    // ══════════════════════════════════════════════════════════════════════

    public function createCreditCardPayment(
        string      $customerId,
        CheckoutDTO $dto,
        string      $externalRef,
        string      $cardNumberFull,
        string      $cvv
    ): array {
        $payload = [
            'customer'          => $customerId,
            'billingType'       => 'CREDIT_CARD',
            'value'             => $dto->valorFinal,
            'dueDate'           => now()->format('Y-m-d'),
            'description'       => "Unyflex Digital — Minissérie #{$dto->classesId}",
            'externalReference' => $externalRef,
            'installmentCount'  => $dto->parcelas > 1 ? $dto->parcelas : null,
            'installmentValue'  => $dto->parcelas > 1 ? round($dto->valorFinal / $dto->parcelas, 2) : null,
            'creditCard' => [
                'holderName'  => $dto->cardHolderName,
                'number'      => $cardNumberFull,
                'expiryMonth' => $dto->cardExpMonth,
                'expiryYear'  => $dto->cardExpYear,
                'ccv'         => $cvv,
            ],
            'creditCardHolderInfo' => [
                'name'              => $dto->nome,
                'email'             => $dto->email,
                'cpfCnpj'           => $dto->cpf,
                'mobilePhone'       => $dto->whatsapp,
                'postalCode'        => $dto->cardHolderCep,
                'addressNumber'     => $dto->cardHolderNum,
                'address'           => $dto->cardHolderEnd,
                'addressComplement' => null,
            ],
        ];

        $response = $this->http()->post("{$this->baseUrl}/v3/payments", $payload);

        if ($response->failed()) {
            $body = $response->json();
            $msg  = $body['errors'][0]['description'] ?? 'Pagamento recusado. Verifique os dados do cartão.';
            Log::warning('[Asaas] Cartão recusado', [
                'status' => $response->status(),
                'code'   => $body['errors'][0]['code'] ?? null,
            ]);
            throw new \RuntimeException($msg);
        }

        Log::info('[Asaas] Cartão aprovado', ['id' => $response->json('id'), 'parcelas' => $dto->parcelas]);
        return $response->json();
    }

    // ══════════════════════════════════════════════════════════════════════
    // STATUS — consulta polling pelo frontend
    // ══════════════════════════════════════════════════════════════════════

    public function getPaymentStatus(string $paymentId): array
    {
        $response = $this->http()->get("{$this->baseUrl}/v3/payments/{$paymentId}");

        if ($response->failed()) {
            Log::warning('[Asaas] getPaymentStatus falhou', [
                'id'     => $paymentId,
                'status' => $response->status(),
            ]);
            return ['status' => 'UNKNOWN'];
        }

        $data = $response->json();

        Log::info('[Asaas] Status consultado', [
            'id'     => $paymentId,
            'status' => $data['status'] ?? 'UNKNOWN',
        ]);

        return $data;
    }

    // ══════════════════════════════════════════════════════════════════════
    // DISPATCHER central
    // ══════════════════════════════════════════════════════════════════════

    public function createPayment(
        string      $customerId,
        CheckoutDTO $dto,
        string      $externalRef,
        string      $cardNumberFull = '',
        string      $cvv            = ''
    ): array {
        return match ($dto->metodoPagamento) {
            'PIX'         => $this->createPixPayment($customerId, $dto, $externalRef),
            'BOLETO'      => $this->createBoletoPayment($customerId, $dto, $externalRef),
            'CREDIT_CARD' => $this->createCreditCardPayment($customerId, $dto, $externalRef, $cardNumberFull, $cvv),
            default       => throw new \InvalidArgumentException("Método inválido: {$dto->metodoPagamento}"),
        };
    }

    // ══════════════════════════════════════════════════════════════════════
    // CANCELAR
    // ══════════════════════════════════════════════════════════════════════

    public function cancelPayment(string $paymentId): bool
    {
        $response = $this->http()->delete("{$this->baseUrl}/v3/payments/{$paymentId}");
        Log::info('[Asaas] Payment cancelado', ['id' => $paymentId, 'ok' => $response->successful()]);
        return $response->successful();
    }
}
