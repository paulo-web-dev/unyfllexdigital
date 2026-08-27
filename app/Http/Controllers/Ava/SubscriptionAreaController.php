<?php

namespace App\Http\Controllers\Ava;

use App\Http\Controllers\Controller;
use App\Services\AssinanteCatalogoService;
use Illuminate\Http\Request;

class SubscriptionAreaController extends Controller
{
    /**
     * Catálogo do assinante. Unidade: painel (minisséries e gravados) e curso (modulares).
     * Filtros, busca, ordenação e paginação são server-side — ver AssinanteCatalogoService.
     */
    public function home(Request $request, AssinanteCatalogoService $catalogo)
    {
        $filtros = $catalogo->filtros($request);

        $itens = $catalogo->listar($filtros, auth()->id(), auth()->user()->student_id);
        $meta  = $catalogo->meta();

        return view('assinante.home', compact('itens', 'filtros', 'meta'));
    }
}
