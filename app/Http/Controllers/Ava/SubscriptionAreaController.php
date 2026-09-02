<?php

namespace App\Http\Controllers\Ava;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Panel;
use App\Models\PanelCertificate;
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
        $meta = $catalogo->meta();

        return view('assinante.home', compact('itens', 'filtros', 'meta'));
    }

    /**
     * "Meus certificados": todos os certificados emitidos pelo aluno (panel_certificates),
     * cada um abrindo a página de impressão do player (que revalida os critérios).
     * Sem a tabela (database/panel_certificates.sql), a lista fica vazia — sem erro.
     */
    public function certificados()
    {
        $user = auth()->user();

        try {
            $certs = PanelCertificate::where('student_id', $user->student_id)
                ->orderByDesc('concluido_em')->orderByDesc('id')
                ->get();
        } catch (\Illuminate\Database\QueryException $e) {
            $certs = collect();
        }

        $paineis = $certs->isEmpty()
            ? collect()
            : Panel::whereIn('id', $certs->pluck('panel_id'))->get()->keyBy('id');
        $turmas = $paineis->isEmpty()
            ? collect()
            : Classes::whereIn('id', $paineis->pluck('classes_id'))->get()->keyBy('id');

        $certificados = $certs->map(function (PanelCertificate $c) use ($paineis, $turmas) {
            $painel = $paineis[$c->panel_id] ?? null;
            $turma = $painel ? ($turmas[$painel->classes_id] ?? null) : null;

            return (object) [
                'id' => $c->id,
                'numero' => str_pad((string) $c->id, 6, '0', STR_PAD_LEFT),
                'titulo' => $c->titulo,
                'horas' => (int) $c->horas,
                'concluidoEm' => $c->concluido_em,
                'emitidoEm' => $c->created_at,
                'token' => $c->token,
                'tipo' => $turma ? ((string) $turma->express === '1' ? 'minisserie' : 'gravado') : null,
                'url' => $turma ? route('player.certificado', [$turma->slug, $painel->id]) : null,
                'urlValidar' => route('certificado.validar.token', $c->token),
            ];
        });

        return view('assinante.certificados', compact('certificados'));
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

        // Usuário AMAI removido pelo ponto focal: variante sem WhatsApp comercial.
        $amaiRemovido = null;
        if (\App\Services\AmaiService::instalado()) {
            $amaiRemovido = \App\Models\AmaiVinculo::where('user_id', $user->id)
                ->whereNotNull('removed_at')->with('pontoFocal')->first();
        }

        $mensagem = sprintf(
            'Olá! Sou %s (%s). Minha assinatura Unyflex Digital (plano %s) venceu em %s e quero renovar.',
            $user->name,
            $user->email,
            $ultima?->plano ?: 'assinatura',
            $ultima?->end_date?->format('d/m/Y') ?: '—'
        );

        $whatsapp = 'https://wa.me/'.config('assinante.whatsapp_comercial')
            .'?'.http_build_query([
                'text' => $mensagem,
                'utm_source' => 'area-assinante',
                'utm_medium' => 'expirada',
                'utm_campaign' => 'renovacao',
            ]);

        $email = config('assinante.email_comercial');
        $mailto = 'mailto:'.$email.'?'.http_build_query([
            'subject' => 'Renovação da assinatura Unyflex Digital',
            'body' => $mensagem,
        ]);

        return view('assinante.expirada', [
            'ultima' => $ultima,
            'meta' => $meta,
            'whatsapp' => $whatsapp,
            'mailto' => $mailto,
            'email' => $email,
            'amaiRemovido' => $amaiRemovido,
        ]);
    }
}
