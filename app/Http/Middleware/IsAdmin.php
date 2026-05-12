<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Deve estar logado E ter power > 10
        if (!Auth::check() || (int) Auth::user()->power <= 10) {
            abort(403, 'Acesso restrito.');
        }

        return $next($request);
    }
}
