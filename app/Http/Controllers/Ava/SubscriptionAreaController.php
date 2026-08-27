<?php

namespace App\Http\Controllers\Ava;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
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

    /**
     * Tela "assinatura expirada": para quem já assinou, não tem assinatura vigente
     * nem minissérie comprada. Demais usuários são devolvidos à sua home.
     * Renovação é comercial (não há renovação no checkout).
     */
    public function expirada(AssinanteCatalogoService $catalogo)
    {
        $user = auth()->user();

        if (! $user->assinaturaExpiradaSemAcesso()) {
            return redirect()->to($user->rotaHome());
        }

        $ultima = Subscription::where('student_id', $user->student_id)
            ->orderByDesc('end_date')->orderByDesc('id')
            ->first();

        $meta = $catalogo->meta();

        $mensagem = sprintf(
            "Olá! Sou %s (%s). Minha assinatura Unyflex Digital (plano %s) venceu em %s e quero renovar.",
            $user->name,
            $user->email,
            $ultima?->plano ?: 'assinatura',
            $ultima?->end_date?->format('d/m/Y') ?: '—'
        );

        $whatsapp = 'https://wa.me/' . config('assinante.whatsapp_comercial')
            . '?' . http_build_query([
                'text'         => $mensagem,
                'utm_source'   => 'area-assinante',
                'utm_medium'   => 'expirada',
                'utm_campaign' => 'renovacao',
            ]);

        $email = config('assinante.email_comercial');
        $mailto = 'mailto:' . $email . '?' . http_build_query([
            'subject' => 'Renovação da assinatura Unyflex Digital',
            'body'    => $mensagem,
        ]);

        return view('assinante.expirada', [
            'ultima'   => $ultima,
            'meta'     => $meta,
            'whatsapp' => $whatsapp,
            'mailto'   => $mailto,
            'email'    => $email,
        ]);
    }
}
