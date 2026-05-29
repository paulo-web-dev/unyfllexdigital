<?php

namespace App\Http\Controllers;

use App\Services\FunnelService;
use Illuminate\Http\Request;

/**
 * Recebe eventos do funil disparados pelo JavaScript do frontend.
 * Etapas registradas via JS: carrinho, pagamento
 */
class FunnelController extends Controller
{
    public function registrar(Request $request)
    {
        $etapa     = $request->input('etapa');
        $classesId = $request->input('classes_id');

        $etapasPermitidas = ['carrinho', 'pagamento', 'visualizou'];

        if (!in_array($etapa, $etapasPermitidas)) {
            return response()->json(['ok' => false], 422);
        }

        FunnelService::registrar(
            etapa:     $etapa,
            classesId: $classesId ? (int) $classesId : null,
        );

        return response()->json(['ok' => true]);
    }
}
