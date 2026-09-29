<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Celular brasileiro com DDD: aceita máscara, +55 ou 0 na frente; o número normalizado tem
 * 11 dígitos (DDD válido + 9 + 8 dígitos), no mesmo formato só-dígitos já usado em
 * users.telefone e students.phone.
 */
class CelularBrasil implements ValidationRule
{
    private const DDDS = [
        11, 12, 13, 14, 15, 16, 17, 18, 19, 21, 22, 24, 27, 28,
        31, 32, 33, 34, 35, 37, 38, 41, 42, 43, 44, 45, 46, 47, 48, 49,
        51, 53, 54, 55, 61, 62, 63, 64, 65, 66, 67, 68, 69, 71, 73, 74, 75, 77, 79,
        81, 82, 83, 84, 85, 86, 87, 88, 89, 91, 92, 93, 94, 95, 96, 97, 98, 99,
    ];

    /** '(49) 99999-0000', '+55 49 99999-0000', '049999990000' → '49999990000'; inválido → null. */
    public static function normalizar(?string $valor): ?string
    {
        $d = preg_replace('/\D/', '', (string) $valor);

        if (strlen($d) === 13 && str_starts_with($d, '55')) {
            $d = substr($d, 2);
        } elseif (strlen($d) === 12 && str_starts_with($d, '0')) {
            $d = substr($d, 1);
        }

        if (strlen($d) !== 11 || $d[2] !== '9' || ! in_array((int) substr($d, 0, 2), self::DDDS, true)) {
            return null;
        }
        return $d;
    }

    /** '49999990000' → '(49) 99999-0000' (para exibição). */
    public static function formatar(?string $valor): string
    {
        $d = self::normalizar($valor);
        return $d ? sprintf('(%s) %s-%s', substr($d, 0, 2), substr($d, 2, 5), substr($d, 7)) : (string) $valor;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::normalizar(is_scalar($value) ? (string) $value : null)) {
            $fail('Informe um celular válido com DDD, ex.: (49) 99999-0000.');
        }
    }
}
