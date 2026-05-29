<?php

namespace App\Http\Middleware;

use App\Services\FunnelService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Registra a etapa "visita" para toda requisição GET no site público.
 * Também injeta o cookie _unyflex_sid para identificar o visitante.
 * Registra "checkout" quando acessa /checkout.
 */
class TrackFunnel
{
    // Rotas que NÃO devem ser rastreadas
    private const IGNORAR = [
        'admin',
        'dashboard',
        'api',
        'webhooks',
        '_ignition',
        'favicon',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Só rastreia GET de páginas públicas
        if (!$request->isMethod('GET')) {
            return $response;
        }

        $path = ltrim($request->path(), '/');

        // Ignora rotas administrativas e internas
        foreach (self::IGNORAR as $ignorar) {
            if (str_starts_with($path, $ignorar)) {
                return $response;
            }
        }

        // Gera ou recupera session_id
        $sessionId = $request->cookie('_unyflex_sid') ?? FunnelService::novoSessionId();

        // Injeta cookie de sessão (1 ano)
        $response->headers->setCookie(
            new \Symfony\Component\HttpFoundation\Cookie(
                '_unyflex_sid',
                $sessionId,
                time() + (86400 * 365),
                '/',
                null,
                false,
                false,
                false,
                'Lax'
            )
        );

        // Define a etapa baseada na URL
        $etapa = null;

        if ($path === '' || $path === '/') {
            $etapa = 'visita'; // Home
        } elseif (str_starts_with($path, 'minisseries/') && strlen($path) > strlen('minisseries/')) {
            $etapa = 'visualizou'; // Página de um curso específico
        } elseif ($path === 'minisseries') {
            $etapa = 'visita'; // Catálogo
        } elseif ($path === 'checkout') {
            $etapa = 'checkout'; // Entrou no checkout
        } elseif ($path === 'comprarealizada') {
            $etapa = 'converteu'; // Conversão confirmada
        }

        if ($etapa) {
            FunnelService::registrar($etapa, null, $sessionId);
        }

        return $response;
    }
}
