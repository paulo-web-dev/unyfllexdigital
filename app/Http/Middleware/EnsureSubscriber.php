<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSubscriber
{
    /**
     * Libera o acesso apenas para alunos com assinatura ativa.
     *
     * Sem assinatura vigente:
     *  - já teve assinatura e não tem minissérie comprada => tela "assinatura expirada";
     *  - demais casos (nunca assinou, ou tem matrícula em minissérie) => AVA (/dashboard).
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user || ! $user->assinaturaVigente()) {
            if ($user && $user->assinaturaExpiradaSemAcesso()) {
                return redirect()->route('assinante.expirada');
            }

            return redirect()->route('dashboard')
                ->with('warning', 'Você não tem uma assinatura ativa.');
        }

        return $next($request);
    }
}
