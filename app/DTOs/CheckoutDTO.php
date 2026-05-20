<?php

namespace App\DTOs;

class CheckoutDTO
{
    public function __construct(
        public readonly string  $nome,
        public readonly string  $email,
        public readonly string  $cpf,
        public readonly string  $whatsapp,
        public readonly string  $orgao,
        public readonly int     $classesId,
        public readonly string  $metodoPagamento,
        public readonly float   $valor,
        public readonly float   $desconto,
        public readonly float   $valorFinal,
        public readonly int     $parcelas,
        // Cartão
        public readonly ?string $cardHolderName   = null,
        public readonly ?string $cardNumberLast4  = null,
        public readonly ?string $cardExpMonth     = null,
        public readonly ?string $cardExpYear      = null,
        public readonly ?string $cardCvv          = null,
        // Endereço do titular (obrigatório pelo Asaas para cartão)
        public readonly ?string $cardHolderCep    = null,
        public readonly ?string $cardHolderEnd    = null,
        public readonly ?string $cardHolderNum    = null,
        public readonly ?string $cardHolderBairro = null,
        public readonly ?string $cardHolderCidade = null,
        public readonly ?string $cardHolderUf     = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $valor      = (float) ($data['valor']       ?? 0);
        $desconto   = (float) ($data['desconto']    ?? 0);
        $valorFinal = (float) ($data['valor_final'] ?? max(0, $valor - $desconto));

        $cardNumber = isset($data['card_number']) ? preg_replace('/\D/', '', $data['card_number']) : null;
        $cardExpiry = $data['card_expiry'] ?? null;
        $expParts   = $cardExpiry ? explode('/', $cardExpiry) : [null, null];
        $expMonth   = $expParts[0] ?? null;
        $expYear    = isset($expParts[1]) ? '20' . $expParts[1] : null;

        return new self(
            nome:              trim($data['nome']),
            email:             strtolower(trim($data['email'])),
            cpf:               preg_replace('/\D/', '', $data['cpf']),
            whatsapp:          preg_replace('/\D/', '', $data['whatsapp'] ?? ''),
            orgao:             trim($data['orgao'] ?? ''),
            classesId:         (int) $data['classes_id'],
            metodoPagamento:   strtoupper($data['metodo_pagamento']),
            valor:             $valor,
            desconto:          $desconto,
            valorFinal:        $valorFinal,
            parcelas:          (int) ($data['parcelas'] ?? 1),
            cardHolderName:    $data['card_holder_name']   ?? null,
            cardNumberLast4:   $cardNumber ? substr($cardNumber, -4) : null,
            cardExpMonth:      $expMonth ? ltrim($expMonth, '0') : null,
            cardExpYear:       $expYear,
            cardCvv:           null, // nunca persiste
            cardHolderCep:     isset($data['card_cep'])     ? preg_replace('/\D/', '', $data['card_cep'])  : null,
            cardHolderEnd:     $data['card_endereco']       ?? null,
            cardHolderNum:     $data['card_numero']         ?? null,
            cardHolderBairro:  $data['card_bairro']         ?? null,
            cardHolderCidade:  $data['card_cidade']         ?? null,
            cardHolderUf:      $data['card_uf']             ?? null,
        );
    }
}
