<?php

namespace App\Enums;

/**
 * Papéis administrativos baseados em users.power
 *
 * power >= 14 → Super Admin  (acesso total)
 * power == 13 → Comercial    (acesso restrito à própria carteira)
 * power <= 10 → Aluno        (sem acesso ao admin)
 */
enum AdminRole: int
{
    case SUPER_ADMIN = 14;
    case COMERCIAL   = 13;
    case ALUNO       = 1;

    /**
     * Resolve o papel a partir do valor de power do user.
     */
    public static function fromPower(int $power): self
    {
        return match(true) {
            $power >= 14 => self::SUPER_ADMIN,
            $power === 13 => self::COMERCIAL,
            default       => self::ALUNO,
        };
    }

    public function label(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::COMERCIAL   => 'Comercial',
            self::ALUNO       => 'Aluno',
        };
    }
}
