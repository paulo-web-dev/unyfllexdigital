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
    // PIX
    // ══════════════════════════════════════════════════════════════════════

    public function createPixPayment(string $customerId, CheckoutDTO $dto, string $externalRef): array
    {
        $response = $this->http()->post("{$this->baseUrl}/v3/payments", [
            'customer'          => $customerId,
            'billingType'       => 'PIX',
            'value'             => $dto->valorFinal,
            'dueDate'           => now()->addDays(1)->format('Y-m-d'),
            'description'       => "Unyflex Digital — Minissérie #{$dto->classesId}",
            'externalReference' => $externalRef,
        ]);

        if ($response->failed()) {
            Log::error('[Asaas] Falha PIX', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Falha ao gerar PIX.');
        }

        $payment             = $response->json();
        $qr                  = $this->getPixQrCode($payment['id']);
        $payment['pixQrCode']          = $qr['encodedImage']   ?? null;
        $payment['pixCopiaECola']      = $qr['payload']        ?? null;
        $payment['pixExpirationDate']  = $qr['expirationDate'] ?? null;

        Log::info('[Asaas] PIX criado', ['id' => $payment['id']]);
        return $payment;
    }

    public function getPixQrCode(string $paymentId): array
    {
        $response = $this->http()->get("{$this->baseUrl}/v3/payments/{$paymentId}/pixQrCode");
        return $response->successful() ? $response->json() : [];
    }

    // ══════════════════════════════════════════════════════════════════════
    // BOLETO
    // ══════════════════════════════════════════════════════════════════════

    public function createBoletoPayment(string $customerId, CheckoutDTO $dto, string $externalRef): array
    {
        $response = $this->http()->post("{$this->baseUrl}/v3/payments", [
            'customer'          => $customerId,
            'billingType'       => 'BOLETO',
            'value'             => $dto->valorFinal,
            'dueDate'           => now()->addDays(3)->format('Y-m-d'),
            'description'       => "Unyflex Digital — Minissérie #{$dto->classesId}",
            'externalReference' => $externalRef,
        ]);

        if ($response->failed()) {
            Log::error('[Asaas] Falha Boleto', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Falha ao gerar boleto.');
        }

        Log::info('[Asaas] Boleto criado', ['id' => $response->json('id')]);
        return $response->json();
    }

    // ══════════════════════════════════════════════════════════════════════
    // CARTÃO DE CRÉDITO
    // número e CVV chegam apenas em memória — nunca persistidos
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
                'name'          => $dto->nome,
                'email'         => $dto->email,
                'cpfCnpj'       => $dto->cpf,
                'mobilePhone'   => $dto->whatsapp,
                'postalCode'    => $dto->cardHolderCep,
                'addressNumber' => $dto->cardHolderNum,
                'address'       => $dto->cardHolderEnd,
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
    // DISPATCHER
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

    public function cancelPayment(string $paymentId): bool
    {
        $response = $this->http()->delete("{$this->baseUrl}/v3/payments/{$paymentId}");
        Log::info('[Asaas] Payment cancelado', ['id' => $paymentId, 'ok' => $response->successful()]);
        return $response->successful();
    }
}
