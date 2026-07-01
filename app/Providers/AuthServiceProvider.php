<?php

namespace App\Providers;

use App\Enums\AdminRole;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        // ─────────────────────────────────────────────────────────────────
        // GATE: acesso ao painel admin em geral (power >= 13)
        // ─────────────────────────────────────────────────────────────────
        Gate::define('admin.access', function (User $user) {
            return $user->power >= 13;
        });
        Gate::define('admin.blog', function (User $user) {
            return $user->power >= 14;   // super admin (mesmo nível de Cursos)
        });
        
        Gate::define('admin.social', function (User $user) {
            return $user->power >= 14;
        });
    
        // ─────────────────────────────────────────────────────────────────
        // GATE: super admin (power >= 14)
        // Dá acesso a tudo sem restrição de carteira
        // ─────────────────────────────────────────────────────────────────
        Gate::define('admin.super', function (User $user) {
            return $user->power >= 14;
        });

        // ─────────────────────────────────────────────────────────────────
        // GATE: usuário comercial (power == 13)
        // ─────────────────────────────────────────────────────────────────
        Gate::define('admin.comercial', function (User $user) {
            return AdminRole::fromPower($user->power) === AdminRole::COMERCIAL;
        });

        // ─────────────────────────────────────────────────────────────────
        // GATES de seções específicas
        // Comercial NÃO pode acessar estas áreas
        // ─────────────────────────────────────────────────────────────────
        Gate::define('admin.financeiro', function (User $user) {
            return $user->power >= 14;
        });

        Gate::define('admin.analytics', function (User $user) {
            return $user->power >= 14;
        });

        Gate::define('admin.dashboard', function (User $user) {
            // Comercial acessa dashboard, mas verá dados filtrados
            return $user->power >= 13;
        });

        Gate::define('admin.cursos', function (User $user) {
            return $user->power >= 14;
        });

        Gate::define('admin.relatorios', function (User $user) {
            return $user->power >= 14;
        });

        Gate::define('admin.config', function (User $user) {
            return $user->power >= 14;
        });

        Gate::define('admin.equipe', function (User $user) {
            return $user->power >= 14;
        });

        Gate::define('admin.permissoes', function (User $user) {
            return $user->power >= 14;
        });

        Gate::define('admin.logs', function (User $user) {
            return $user->power >= 14;
        });

        Gate::define('admin.integ', function (User $user) {
            return $user->power >= 14;
        });

        // ─────────────────────────────────────────────────────────────────
        // GATES que comercial pode usar (alunos e matrículas)
        // ─────────────────────────────────────────────────────────────────
        Gate::define('admin.alunos', function (User $user) {
            return $user->power >= 13;
        });

        Gate::define('admin.matriculas', function (User $user) {
            return $user->power >= 13;
        });
    }
}
