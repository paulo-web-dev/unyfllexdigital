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

        // Pega token da URL ou do cookie já gravado
        $token = null;

        if ($request->has('ref') && $request->get('ref')) {
            $token = substr(strip_tags($request->get('ref')), 0, 100);
        } elseif ($request->cookies->get('referral')) {
            $token = $request->cookies->get('referral');
        }

        if ($token) {
            // Registra SEMPRE — sem nenhuma condição
            try {
                ReferralClick::create([
                    'token'      => $token,
                    'ip'         => $request->ip(),
                    'clicked_at' => now()->toDateString(),
                ]);
            } catch (\Throwable $e) {
                \Log::error('[Referral] Erro', ['erro' => $e->getMessage()]);
            }

            // Renova o cookie
            $response->headers->setCookie(
                new \Symfony\Component\HttpFoundation\Cookie(
                    'referral',
                    $token,
                    time() + (86400 * 30),
                    '/',
                    null,
                    false,
                    false,
                    false,
                    'Lax'
                )
            );
        }

        return $response;
    }
}