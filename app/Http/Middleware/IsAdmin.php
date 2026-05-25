<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloqueia acesso ao painel admin para qualquer usuário com power < 13.
 * Super Admin = power >= 14
 * Comercial   = power == 13
 */
class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->power < 13) {
            abort(403, 'Acesso não autorizado.');
        }

        return $next($request);
    }
}
