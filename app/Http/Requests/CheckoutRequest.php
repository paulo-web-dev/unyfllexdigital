<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $metodo = strtoupper($this->input('metodo_pagamento', ''));

        $rules = [
            'nome'             => ['required', 'string', 'max:255', 'min:3'],
            'email'            => ['required', 'email', 'max:255'],
            'cpf'              => ['required', 'string', function ($attr, $val, $fail) {
                $cpf = preg_replace('/\D/', '', $val);
                if (strlen($cpf) !== 11 || !$this->validarCpf($cpf)) {
                    $fail('CPF inválido.');
                }
            }],
            'whatsapp'         => ['nullable', 'string', 'max:20'],
            'orgao'            => ['nullable', 'string', 'max:255'],
            'classes_id'       => ['required', 'integer', 'exists:classes,id'],
            'metodo_pagamento' => ['required', 'in:CREDIT_CARD,PIX,BOLETO'],
            'valor'            => ['required', 'numeric', 'min:0'],
            'desconto'         => ['nullable', 'numeric', 'min:0'],
            'valor_final'      => ['required', 'numeric', 'min:0'],
            'parcelas'         => ['nullable', 'integer', 'min:1', 'max:12'],
        ];

        if ($metodo === 'CREDIT_CARD') {
            $rules['card_holder_name'] = ['required', 'string', 'max:255'];
            $rules['card_number']      = ['required', 'string', function ($attr, $value, $fail) {
                $clean = preg_replace('/\D/', '', $value);
                if (strlen($clean) < 13 || strlen($clean) > 19) {
                    $fail('Número do cartão inválido.');
                }
            }];
            $rules['card_expiry']      = ['required', 'string', 'regex:/^\d{2}\/\d{2}$/'];
            $rules['card_cvv']         = ['required', 'string', 'min:3', 'max:4'];
            // Endereço do titular — obrigatório pelo Asaas
            $rules['card_cep']         = ['required', 'string', 'min:8', 'max:9'];
            $rules['card_endereco']    = ['required', 'string', 'max:255'];
            $rules['card_numero']      = ['required', 'string', 'max:20'];
            $rules['card_bairro']      = ['required', 'string', 'max:255'];
            $rules['card_cidade']      = ['required', 'string', 'max:255'];
            $rules['card_uf']          = ['required', 'string', 'size:2'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'nome.required'             => 'Informe seu nome completo.',
            'email.required'            => 'Informe seu e-mail.',
            'email.email'               => 'E-mail inválido.',
            'cpf.required'              => 'Informe seu CPF.',
            'classes_id.required'       => 'Nenhum curso selecionado.',
            'classes_id.exists'         => 'Curso não encontrado.',
            'metodo_pagamento.required' => 'Selecione uma forma de pagamento.',
            'metodo_pagamento.in'       => 'Forma de pagamento inválida.',
            'valor_final.required'      => 'Valor inválido.',
            'card_holder_name.required' => 'Informe o nome impresso no cartão.',
            'card_number.required'      => 'Informe o número do cartão.',
            'card_expiry.required'      => 'Informe a validade do cartão.',
            'card_expiry.regex'         => 'Validade inválida. Use MM/AA.',
            'card_cvv.required'         => 'Informe o CVV do cartão.',
            'card_cep.required'         => 'Informe o CEP do titular do cartão.',
            'card_cep.min'              => 'CEP inválido.',
            'card_endereco.required'    => 'Informe o endereço do titular.',
            'card_numero.required'      => 'Informe o número do endereço.',
            'card_bairro.required'      => 'Informe o bairro.',
            'card_cidade.required'      => 'Informe a cidade.',
            'card_uf.required'          => 'Informe o estado (UF).',
            'card_uf.size'              => 'UF deve ter 2 letras.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }

    private function validarCpf(string $cpf): bool
    {
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1+$/', $cpf)) return false;

        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) return false;
        }

        return true;
    }
}
