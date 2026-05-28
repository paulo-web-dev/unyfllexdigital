<?php

namespace App\Http\Middleware;

use App\Models\ReferralClick;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;

class TrackReferral
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->has('ref') && $request->get('ref')) {
            $token = substr(strip_tags($request->get('ref')), 0, 100);

            // Pega o IP real do visitante
            $ip = $this->resolverIp($request);

            // Busca geolocalização
            $geo = $this->resolverGeo($ip);

            try {
                ReferralClick::create([
                    'token'      => $token,
                    'ip'         => $ip,
                    'cidade'     => $geo['cidade']  ?? null,
                    'estado'     => $geo['estado']  ?? null,
                    'pais'       => $geo['pais']    ?? null,
                    'clicked_at' => now()->toDateString(),
                ]);
            } catch (\Throwable $e) {
                \Log::error('[Referral] Erro ao registrar clique', [
                    'erro' => $e->getMessage()
                ]);
            }

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

    private function resolverIp(Request $request): string
    {
        // Tenta pegar o IP real por ordem de prioridade
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_REAL_IP',            // Nginx proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer / proxy genérico
            'HTTP_CLIENT_IP',
        ];

        foreach ($headers as $header) {
            $val = $_SERVER[$header] ?? null;
            if ($val) {
                // X-Forwarded-For pode ter múltiplos IPs — pega o primeiro (real)
                $ip = trim(explode(',', $val)[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $request->ip();
    }

    private function resolverGeo(string $ip): array
    {
        // IP local/privado — não tenta geolocalizar
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return ['cidade' => 'Local', 'estado' => 'Dev', 'pais' => 'BR'];
        }

        try {
            $res = Http::timeout(3)->get("http://ip-api.com/json/{$ip}?fields=status,city,regionName,country,countryCode&lang=pt-BR");

            if ($res->successful() && $res->json('status') === 'success') {
                return [
                    'cidade' => $res->json('city'),
                    'estado' => $res->json('regionName'),
                    'pais'   => $res->json('countryCode'),
                ];
            }
        } catch (\Throwable $e) {
            // Silencioso — geo é opcional, não pode quebrar o fluxo
        }

        return [];
    }
}