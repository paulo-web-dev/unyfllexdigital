<?php

namespace App\Http\Middleware;

use App\Models\ReferralClick;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackReferral
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->has('ref') && $request->get('ref')) {
            $token = substr(strip_tags($request->get('ref')), 0, 100);

            // Registra o clique
            try {
                ReferralClick::firstOrCreate([
                    'token'      => $token,
                    'ip'         => $request->ip(),
                    'clicked_at' => now()->toDateString(),
                ]);
            } catch (\Throwable $e) {}

            // Grava cookie SEM criptografia (não-httponly, não-secure para localhost)
            $response->headers->setCookie(
                new \Symfony\Component\HttpFoundation\Cookie(
                    'referral',           // nome
                    $token,               // valor
                    time() + (86400 * 30),// expira em 30 dias
                    '/',                  // path
                    null,                 // domain
                    false,                // secure (false para localhost)
                    false,                // httpOnly (false para JS poder ler)
                    false,                // raw
                    'Lax'                 // sameSite
                )
            );
        }

        return $response;
    }
}