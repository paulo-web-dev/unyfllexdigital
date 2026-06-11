<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use Illuminate\Http\Request;

class PropostaController extends Controller
{
    // ── Formulário de geração ─────────────────────────────────────────────
    public function form()
    {
        // Lista de minisséries disponíveis para montar a proposta
        $cursos = Classes::where('express', '1')
            ->where('status', 'able')
            ->orderBy('title')
            ->get(['id', 'title', 'valor', 'workload']);

        return view('pages.admin.proposta-form', compact('cursos'));
    }

    // ── Gera a proposta (página A4 para imprimir) ─────────────────────────
    public function gerar(Request $request)
    {
        $data = $request->validate([
            'cliente_nome'     => ['nullable', 'string', 'max:200'],
            'cliente_orgao'    => ['nullable', 'string', 'max:200'],
            'num_alunos'       => ['required', 'integer', 'min:1', 'max:100000'],
            'cursos'           => ['required', 'array', 'min:1'],
            'cursos.*'         => ['integer'],
            'preco_cheio'      => ['required', 'numeric', 'min:0'],
            'preco_final'      => ['required', 'numeric', 'min:0'],
            'validade_dias'    => ['nullable', 'integer', 'min:1', 'max:365'],
            'observacoes'      => ['nullable', 'string', 'max:2000'],
            'parcelas'         => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        // Busca os cursos selecionados
        $cursos = Classes::whereIn('id', $data['cursos'])
            ->get(['id', 'title', 'valor', 'workload']);

        $numAlunos  = (int) $data['num_alunos'];
        $precoCheio = (float) $data['preco_cheio'];
        $precoFinal = (float) $data['preco_final'];

        // Totais considerando o número de alunos
        $totalCheio   = $precoCheio * $numAlunos;
        $totalFinal   = $precoFinal * $numAlunos;
        $economia     = $totalCheio - $totalFinal;
        $descontoPct  = $totalCheio > 0 ? round(($economia / $totalCheio) * 100) : 0;

        $validadeDias = (int) ($data['validade_dias'] ?? 7);
        $parcelas     = (int) ($data['parcelas'] ?? 1);
        $valorParcela = $parcelas > 0 ? $totalFinal / $parcelas : $totalFinal;

        $proposta = [
            'numero'        => 'PROP-' . now()->format('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'data'          => now()->format('d/m/Y'),
            'validade'      => now()->addDays($validadeDias)->format('d/m/Y'),
            'cliente_nome'  => $data['cliente_nome']  ?? '',
            'cliente_orgao' => $data['cliente_orgao'] ?? '',
            'vendedor'      => auth()->user()->name,
            'num_alunos'    => $numAlunos,
            'cursos'        => $cursos,
            'preco_cheio'   => $precoCheio,
            'preco_final'   => $precoFinal,
            'total_cheio'   => $totalCheio,
            'total_final'   => $totalFinal,
            'economia'      => $economia,
            'desconto_pct'  => $descontoPct,
            'parcelas'      => $parcelas,
            'valor_parcela' => $valorParcela,
            'observacoes'   => $data['observacoes'] ?? '',
        ];

        return view('pages.admin.proposta-pdf', compact('proposta'));
    }
}
