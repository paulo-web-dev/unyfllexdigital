<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Home por tipo de usuário: área do assinante, tela de assinatura expirada ou AVA (/dashboard).
                $user = Auth::guard($guard)->user();
                if ($user && method_exists($user, 'rotaHome')) {
                    return redirect()->to($user->rotaHome());
                }

                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}
