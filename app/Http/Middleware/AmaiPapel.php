<?php

namespace App\Http\Middleware;

use App\Models\AmaiVinculo;
use App\Services\AmaiService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protege a área de gestão AMAI (/amai/*).
 * Uso: ->middleware('amai.papel:master') ou 'amai.papel:master,ponto_focal'.
 *
 * - Tabela ainda não instalada: página "indisponível" (200), sem erro.
 * - Sem vínculo ativo ou papel fora da lista: 404 (não revela a existência da área).
 * Independe de users.power e dos middlewares do admin da Unyflex.
 */
class AmaiPapel
{
    public function __construct(private AmaiService $amai) {}

    public function handle(Request $request, Closure $next, string ...$papeis): Response
    {
        if (! AmaiService::instalado()) {
            return response()->view('amai.indisponivel', [], 200);
        }

        $vinculo = $this->amai->vinculoDe($request->user());
        $papeis  = $papeis ?: [AmaiVinculo::MASTER, AmaiVinculo::PONTO_FOCAL];

        if (! $vinculo || ! in_array($vinculo->papel, $papeis, true)) {
            abort(404);
        }

        $request->attributes->set('amaiVinculo', $vinculo);
        view()->share('amaiVinculo', $vinculo);

        return $next($request);
    }
}
