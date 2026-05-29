<?php

namespace App\Services;

use App\Models\FunnelEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FunnelService
{
    // ── Registra um evento no funil ───────────────────────────────────────
    public static function registrar(
        string  $etapa,
        ?int    $classesId = null,
        ?string $sessionId = null,
        ?Request $request  = null
    ): void {
        try {
            $req       = $request ?? request();
            $sessionId = $sessionId ?? self::getSessionId($req);
            $ip        = self::resolverIp($req);
            $geo       = self::resolverGeo($ip);
            $referral  = $req->cookie('referral');
            $origem    = $referral ? 'referral' : 'organico';

            FunnelEvent::create([
                'session_id' => $sessionId,
                'etapa'      => $etapa,
                'origem'     => $origem,
                'referral'   => $referral ?: null,
                'classes_id' => $classesId,
                'ip'         => $ip,
                'cidade'     => $geo['cidade'] ?? null,
                'estado'     => $geo['estado'] ?? null,
                'pais'       => $geo['pais']   ?? null,
                'user_agent' => substr($req->userAgent() ?? '', 0, 255),
            ]);

        } catch (\Throwable $e) {
            // Nunca quebra o fluxo principal
            Log::warning('[Funil] Erro ao registrar evento', [
                'etapa' => $etapa,
                'erro'  => $e->getMessage(),
            ]);
        }
    }

    // ── Gera/recupera session_id do cookie ────────────────────────────────
    public static function getSessionId(Request $request): string
    {
        return $request->cookie('_unyflex_sid') ?? self::novoSessionId();
    }

    public static function novoSessionId(): string
    {
        return bin2hex(random_bytes(16));
    }

    // ── IP real do visitante ──────────────────────────────────────────────
    private static function resolverIp(Request $request): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_REAL_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
        ];

        foreach ($headers as $header) {
            $val = $_SERVER[$header] ?? null;
            if ($val) {
                $ip = trim(explode(',', $val)[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $request->ip();
    }

    // ── Geolocalização via ip-api.com (gratuito, sem chave) ───────────────
    private static function resolverGeo(string $ip): array
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return ['cidade' => 'Local', 'estado' => 'Dev', 'pais' => 'BR'];
        }

        try {
            $res = Http::timeout(2)->get("http://ip-api.com/json/{$ip}?fields=status,city,regionName,countryCode");

            if ($res->successful() && $res->json('status') === 'success') {
                return [
                    'cidade' => $res->json('city'),
                    'estado' => $res->json('regionName'),
                    'pais'   => $res->json('countryCode'),
                ];
            }
        } catch (\Throwable $e) {}

        return [];
    }
}
