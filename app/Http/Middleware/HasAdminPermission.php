<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware granular para proteger rotas específicas do admin.
 *
 * Uso nas rotas: ->middleware('admin.can:admin.financeiro')
 *
 * O parâmetro é o nome do Gate definido no AuthServiceProvider.
 */
class HasAdminPermission
{
    public function handle(Request $request, Closure $next, string $gate): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->can($gate)) {
            // Retorna JSON para requisições AJAX, redireciona para dashboard para as demais
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Sem permissão para esta área.'], 403);
            }

            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Você não tem permissão para acessar esta área.');
        }

        return $next($request);
    }
}
