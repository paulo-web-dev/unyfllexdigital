{{-- ===========================================================================
     >>> AJUSTE 1 LINHA: troque 'layouts.admin' pelo layout que seu painel usa
     (o mesmo que o dashboard estende). Ex.: 'admin.layout', 'layouts.painel'...
     O conteudo vai dentro de @section('content') (mesmo padrao do site).
=========================================================================== --}}
@extends('layouts.admin')
@section('title', 'Leads — Guia de Licitacoes')

@section('content')
<div class="lg-wrap">
  <style>
    .lg-wrap{
      --lg-blue:#3B82F6;
      --lg-green:#22C55E;
      --lg-ink:#E8EDF4;
      --lg-mut:#8A97A8;
      --lg-line:#2A3340;
      --lg-bg:#0F1620;
      --lg-bg-soft:#161D29;
      --lg-bg-hover:#1B2330;
      font-family:system-ui,Segoe UI,Roboto,sans-serif
    }
    .lg-wrap *{box-sizing:border-box}
    .lg-head{display:flex;flex-wrap:wrap;gap:12px;justify-content:space-between;align-items:center;margin-bottom:18px}
    .lg-head h1{font-size:22px;font-weight:800;color:var(--lg-ink);margin:0}
    .lg-head p{margin:2px 0 0;font-size:13px;color:var(--lg-mut)}
    .lg-kpis{display:grid;grid-template-columns:repeat(6,1fr);gap:12px;margin-bottom:18px}
    .lg-kpi{background:var(--lg-bg-soft);border:1px solid var(--lg-line);border-radius:14px;padding:14px 16px}
    .lg-kpi b{display:block;font-size:26px;font-weight:800;line-height:1.1;color:var(--lg-ink)}
    .lg-kpi span{font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:var(--lg-mut)}
    .lg-card{background:var(--lg-bg-soft);border:1px solid var(--lg-line);border-radius:16px;overflow:hidden}
    .lg-filtros{display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;padding:16px}
    .lg-filtros label{display:block;font-size:12px;font-weight:600;color:var(--lg-ink);margin-bottom:4px}
    .lg-filtros input,.lg-filtros select{border:1px solid var(--lg-line);border-radius:9px;padding:9px 11px;font-size:14px;color:var(--lg-ink);background:var(--lg-bg)}
    .lg-filtros input::placeholder{color:var(--lg-mut)}
    .lg-filtros input:focus,.lg-filtros select:focus{outline:none;border-color:var(--lg-blue);box-shadow:0 0 0 3px rgba(59,130,246,.18)}
    .lg-btn{border:none;cursor:pointer;border-radius:9px;padding:10px 16px;font-size:13px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
    .lg-btn-blue{background:var(--lg-blue);color:#fff}
    .lg-btn-green{background:var(--lg-green);color:#fff}
    .lg-btn-out{background:var(--lg-bg);border:1px solid var(--lg-line);color:var(--lg-ink)}
    .lg-btn-xs{padding:5px 10px;font-size:12px}
    .lg-toolbar{display:flex;justify-content:space-between;align-items:center;padding:0 4px 8px}
    .lg-toolbar small{color:var(--lg-mut)}
    table.lg-tbl{width:100%;border-collapse:collapse;font-size:13.5px;color:var(--lg-ink)}
    table.lg-tbl th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:var(--lg-mut);padding:11px 14px;border-bottom:1px solid var(--lg-line);background:var(--lg-bg);white-space:nowrap}
    table.lg-tbl td{padding:12px 14px;border-bottom:1px solid var(--lg-line);vertical-align:middle}
    table.lg-tbl tr:hover td{background:var(--lg-bg-hover)}
    .lg-nome{font-weight:600;color:var(--lg-ink)}
    .lg-sub{font-size:12px;color:var(--lg-mut)}
    .lg-sub a{color:var(--lg-mut);text-decoration:none}
    .lg-wpp{color:#34D399!important;font-weight:600}
    .lg-badge{display:inline-block;font-size:11px;font-weight:600;padding:3px 9px;border-radius:999px;margin-right:4px}
    .lg-b-novo{background:rgba(234,179,8,.15);color:#EAB308}
    .lg-b-cont{background:rgba(34,197,94,.15);color:#4ADE80}
    .lg-b-baix{background:rgba(59,130,246,.15);color:#60A5FA}
    .lg-acoes{white-space:nowrap;text-align:right}
    .lg-obs{margin-top:6px}
    .lg-obs summary{cursor:pointer;font-size:12px;color:var(--lg-blue);list-style:none}
    .lg-obs textarea{width:100%;border:1px solid var(--lg-line);border-radius:8px;padding:8px;font-size:13px;margin-top:6px;font-family:inherit;background:var(--lg-bg);color:var(--lg-ink)}
    .lg-pag{display:flex;justify-content:space-between;align-items:center;padding:14px;font-size:13px;color:var(--lg-mut)}
    .lg-empty{text-align:center;color:var(--lg-mut);padding:40px}
    .d-inline{display:inline}
    @media(max-width:900px){.lg-kpis{grid-template-columns:repeat(3,1fr)}}
    @media(max-width:560px){.lg-kpis{grid-template-columns:repeat(2,1fr)}}
  </style>

  <div class="lg-head">
    <div>
      <h1>Leads — Guia de Licitacoes</h1>
      <p>Captura da LP <code>/guia-licitacoes</code> · campanha de ads</p>
    </div>
    <a href="{{ route('admin.leads-guia.export', request()->query()) }}" class="lg-btn lg-btn-green">Exportar CSV</a>
  </div>

  {{-- KPIs --}}
  <div class="lg-kpis">
    <div class="lg-kpi"><b>{{ $stats['total'] }}</b><span>Total</span></div>
    <div class="lg-kpi"><b style="color:#1D6FF2">{{ $stats['hoje'] }}</b><span>Hoje</span></div>
    <div class="lg-kpi"><b>{{ $stats['semana'] }}</b><span>7 dias</span></div>
    <div class="lg-kpi"><b style="color:#b07800">{{ $stats['novos'] }}</b><span>A contatar</span></div>
    <div class="lg-kpi"><b style="color:#0c6b44">{{ $stats['contatados'] }}</b><span>Contatados</span></div>
    <div class="lg-kpi"><b style="color:#155ad1">{{ $stats['baixaram'] }}</b><span>Baixaram</span></div>
  </div>

  <div class="lg-card" style="margin-bottom:14px">
    <form method="GET" action="{{ route('admin.leads-guia') }}" class="lg-filtros">
      <div style="flex:1;min-width:200px">
        <label>Buscar</label>
        <input type="text" name="busca" value="{{ $busca }}" placeholder="Nome, e-mail, cidade ou cargo" style="width:100%">
      </div>
      <div>
        <label>Status</label>
        <select name="status">
          <option value="todos"      @selected($status==='todos')>Todos</option>
          <option value="novos"      @selected($status==='novos')>A contatar</option>
          <option value="contatados" @selected($status==='contatados')>Contatados</option>
          <option value="baixaram"   @selected($status==='baixaram')>Que baixaram</option>
        </select>
      </div>
      <div>
        <label>Campanha / Fonte (utm)</label>
        <input type="text" name="origem" value="{{ $origem }}" placeholder="meta, google...">
      </div>
      <button class="lg-btn lg-btn-blue">Filtrar</button>
      <a href="{{ route('admin.leads-guia') }}" class="lg-btn lg-btn-out">Limpar</a>
    </form>
  </div>

  <div class="lg-toolbar">
    <small>Mostrando {{ $leads->firstItem() ?? 0 }}–{{ $leads->lastItem() ?? 0 }} de {{ $leads->total() }} leads</small>
  </div>

  <div class="lg-card">
    <div style="overflow-x:auto">
      <table class="lg-tbl">
        <thead>
          <tr>
            <th>Lead</th><th>Cidade / Cargo</th><th>Origem</th><th>Data</th><th>Status</th><th class="lg-acoes">Acoes</th>
          </tr>
        </thead>
        <tbody>
          @forelse($leads as $lead)
            <tr>
              <td>
                <div class="lg-nome">{{ $lead->nome }}</div>
                <div class="lg-sub">
                  <a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a> ·
                  <a class="lg-wpp" href="{{ $lead->whatsappLink() }}" target="_blank" rel="noopener">{{ $lead->whatsapp }}</a>
                </div>
                <details class="lg-obs">
                  <summary>{{ $lead->observacoes ? 'Editar anotacao' : 'Anotar' }}</summary>
                  <form method="POST" action="{{ route('admin.leads-guia.note', $lead->id) }}">
                    @csrf
                    <textarea name="observacoes" rows="2" placeholder="Ex.: ligado dia 10, retornar semana que vem...">{{ $lead->observacoes }}</textarea>
                    <button class="lg-btn lg-btn-blue lg-btn-xs" style="margin-top:6px">Salvar</button>
                  </form>
                </details>
              </td>
              <td>{{ $lead->cidade }}<div class="lg-sub">{{ $lead->cargo }}</div></td>
              <td><span class="lg-sub">{{ $lead->utm_campaign ?: ($lead->utm_source ?: $lead->origem) }}</span></td>
              <td class="lg-sub">{{ optional($lead->created_at)->format('d/m/Y') }}<br>{{ optional($lead->created_at)->format('H:i') }}</td>
              <td>
                @if($lead->contatado)<span class="lg-badge lg-b-cont">Contatado</span>@else<span class="lg-badge lg-b-novo">A contatar</span>@endif
                @if($lead->baixou)<span class="lg-badge lg-b-baix">Baixou</span>@endif
              </td>
              <td class="lg-acoes">
                <a class="lg-btn lg-btn-out lg-btn-xs lg-wpp" href="{{ $lead->whatsappLink() }}" target="_blank" rel="noopener">WhatsApp</a>
                <form method="POST" action="{{ route('admin.leads-guia.toggle', $lead->id) }}" class="d-inline">
                  @csrf
                  <button class="lg-btn lg-btn-xs {{ $lead->contatado ? 'lg-btn-out' : 'lg-btn-green' }}">
                    {{ $lead->contatado ? 'Reabrir' : 'Marcar contatado' }}
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="lg-empty">Nenhum lead encontrado com esses filtros.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($leads->hasPages())
      <div class="lg-pag">
        <span>Pagina {{ $leads->currentPage() }} de {{ $leads->lastPage() }}</span>
        <span>
          @if($leads->onFirstPage())
            <span class="lg-btn lg-btn-out lg-btn-xs" style="opacity:.5">Anterior</span>
          @else
            <a class="lg-btn lg-btn-out lg-btn-xs" href="{{ $leads->previousPageUrl() }}">Anterior</a>
          @endif
          @if($leads->hasMorePages())
            <a class="lg-btn lg-btn-out lg-btn-xs" href="{{ $leads->nextPageUrl() }}">Proxima</a>
          @else
            <span class="lg-btn lg-btn-out lg-btn-xs" style="opacity:.5">Proxima</span>
          @endif
        </span>
      </div>
    @endif
  </div>

</div>
@endsection
