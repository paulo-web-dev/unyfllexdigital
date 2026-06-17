<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeadGuia;
use Illuminate\Http\Request;

/**
 * Leads do Guia de Licitacoes — integrado ao painel admin existente.
 * As rotas ficam dentro do grupo admin (middleware 'auth' + 'admin'),
 * por isso este controller NAO tem login proprio.
 */
class LeadsGuiaController extends Controller
{
    private function filtrar(Request $request)
    {
        $status = $request->query('status', 'todos'); // todos | novos | contatados | baixaram
        $busca  = trim((string) $request->query('busca', ''));
        $origem = trim((string) $request->query('origem', ''));

        $query = LeadGuia::query();

        if ($status === 'novos')      $query->where('contatado', false);
        if ($status === 'contatados') $query->where('contatado', true);
        if ($status === 'baixaram')   $query->where('baixou', true);

        if ($busca !== '') {
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                  ->orWhere('email', 'like', "%{$busca}%")
                  ->orWhere('cidade', 'like', "%{$busca}%")
                  ->orWhere('cargo', 'like', "%{$busca}%");
            });
        }

        if ($origem !== '') {
            $query->where(function ($q) use ($origem) {
                $q->where('utm_campaign', 'like', "%{$origem}%")
                  ->orWhere('utm_source', 'like', "%{$origem}%");
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $hoje  = now()->startOfDay();
        $stats = [
            'total'      => LeadGuia::count(),
            'hoje'       => LeadGuia::where('created_at', '>=', $hoje)->count(),
            'semana'     => LeadGuia::where('created_at', '>=', now()->subDays(7))->count(),
            'novos'      => LeadGuia::where('contatado', false)->count(),
            'contatados' => LeadGuia::where('contatado', true)->count(),
            'baixaram'   => LeadGuia::where('baixou', true)->count(),
        ];

        $leads = $this->filtrar($request)
                      ->orderByDesc('created_at')
                      ->paginate(50)
                      ->appends($request->query());

        return view('admin.leads-guia', [
            'leads'  => $leads,
            'stats'  => $stats,
            'status' => $request->query('status', 'todos'),
            'busca'  => trim((string) $request->query('busca', '')),
            'origem' => trim((string) $request->query('origem', '')),
        ]);
    }

    public function toggleContatado(Request $request, $id)
    {
        $lead = LeadGuia::findOrFail($id);
        $lead->contatado    = ! $lead->contatado;
        $lead->contatado_em = $lead->contatado ? now() : null;
        $lead->save();

        return back();
    }

    public function salvarObservacao(Request $request, $id)
    {
        $request->validate(['observacoes' => ['nullable', 'string', 'max:5000']]);

        $lead = LeadGuia::findOrFail($id);
        $lead->observacoes = $request->input('observacoes');
        $lead->save();

        return back();
    }

    public function exportar(Request $request)
    {
        $leads   = $this->filtrar($request)->orderByDesc('created_at')->get();
        $arquivo = 'leads-guia-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($leads) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM p/ Excel reconhecer UTF-8
            fputcsv($out, [
                'ID', 'Nome', 'E-mail', 'WhatsApp', 'Cidade', 'Cargo',
                'Origem', 'Campanha (utm)', 'Fonte (utm)',
                'Contatado', 'Contatado em', 'Baixou', 'Baixado em',
                'Observacoes', 'Cadastrado em',
            ], ';');

            foreach ($leads as $l) {
                fputcsv($out, [
                    $l->id, $l->nome, $l->email, $l->whatsapp, $l->cidade, $l->cargo,
                    $l->origem, $l->utm_campaign, $l->utm_source,
                    $l->contatado ? 'Sim' : 'Nao',
                    optional($l->contatado_em)->format('d/m/Y H:i'),
                    $l->baixou ? 'Sim' : 'Nao',
                    optional($l->baixado_em)->format('d/m/Y H:i'),
                    $l->observacoes,
                    optional($l->created_at)->format('d/m/Y H:i'),
                ], ';');
            }
            fclose($out);
        }, $arquivo, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
