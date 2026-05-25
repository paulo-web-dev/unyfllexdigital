<?php

namespace App\Traits;

use App\Enums\AdminRole;
use App\Models\Enrollment;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait EnrollmentScope
 *
 * Aplica filtro automático de carteira (wallet) para usuários comerciais.
 *
 * Uso nos controllers:
 *   $query = $this->enrollmentQuery();
 *   // ... aplica filtros adicionais normalmente
 *   $resultado = $query->paginate(25);
 *
 * Para o super admin, retorna todos os enrollments sem restrição.
 * Para o comercial (power=13), filtra por wallet = users.name do logado.
 */
trait EnrollmentScope
{
    /**
     * Retorna uma query base de Enrollment já com o scope de carteira aplicado.
     * Aceita um closure opcional para encadear condições adicionais.
     */
    protected function enrollmentQuery(?callable $extra = null): Builder
    {
        $query = Enrollment::query();

        // Aplica filtro de carteira para comercial
        if ($this->isComercial()) {
            $query->where('wallet', auth()->user()->name);
        }

        if ($extra) {
            $extra($query);
        }

        return $query;
    }

    /**
     * Verifica se o usuário logado é comercial (power = 13).
     */
    protected function isComercial(): bool
    {
        return auth()->check()
            && AdminRole::fromPower(auth()->user()->power) === AdminRole::COMERCIAL;
    }

    /**
     * Verifica se o usuário logado é super admin (power >= 14).
     */
    protected function isSuperAdmin(): bool
    {
        return auth()->check() && auth()->user()->power >= 14;
    }

    /**
     * Retorna o nome da carteira do usuário logado (usado para filtros e defaults).
     */
    protected function carteiraAtual(): string
    {
        return auth()->user()->name ?? '';
    }
}
