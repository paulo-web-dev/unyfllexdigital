<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex">
<title>Painel de Leads · Guia de Licitacoes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body{background:#f1f4f9;font-family:system-ui,sans-serif;color:#0A2540}
  .topnav{background:#0E2F4F;color:#fff;padding:14px 0}
  .topnav .brand{font-weight:800}
  .topnav .brand small{font-weight:600;font-size:.68rem;letter-spacing:.07em;text-transform:uppercase;color:#9fc0e6;display:block}
  .kpi{background:#fff;border-radius:14px;padding:16px 18px;height:100%;border:1px solid #e4ebf3}
  .kpi b{font-size:1.7rem;font-weight:800;display:block;line-height:1.1}
  .kpi span{font-size:.78rem;color:#5B6B7B;text-transform:uppercase;letter-spacing:.04em}
  .kpi.tot b{color:#0E2F4F}.kpi.hoje b{color:#1D6FF2}.kpi.sem b{color:#1B4D8F}
  .kpi.nov b{color:#F0A500}.kpi.cont b{color:#13955F}.kpi.baix b{color:#0c6b44}
  .card-box{background:#fff;border-radius:16px;border:1px solid #e4ebf3;overflow:hidden}
  table{font-size:.88rem}
  th{font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;color:#5B6B7B;white-space:nowrap}
  td{vertical-align:middle}
  .badge-novo{background:#fff4e0;color:#b07800}
  .badge-cont{background:#e7f4ec;color:#0c6b44}
  .badge-baix{background:#e8f0fe;color:#155ad1}
  .lead-nome{font-weight:600}
  .lead-sub{font-size:.78rem;color:#5B6B7B}
  .btn-xs{padding:3px 9px;font-size:.78rem}
  .wpp{color:#1ebe5d}
  a.muted{color:#5B6B7B;text-decoration:none}
  a.muted:hover{color:#1D6FF2}
  .filtros .form-control,.filtros .form-select{font-size:.88rem}
</style>
</head>
<body>

<div class="topnav">
  <div class="container d-flex justify-content-between align-items-center">
    <div class="brand">Unyflex Digital<small>Painel de Leads — Guia de Licitacoes</small></div>
    <form method="POST" action="{{ route('leads.logout') }}">@csrf
      <button class="btn btn-outline-light btn-sm">Sair</button>
    </form>
  </div>
</div>

<div class="container py-4">

  <!-- KPIs -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-2"><div class="kpi tot"><b>{{ $stats['total'] }}</b><span>Total</span></div></div>
    <div class="col-6 col-md-2"><div class="kpi hoje"><b>{{ $stats['hoje'] }}</b><span>Hoje</span></div></div>
    <div class="col-6 col-md-2"><div class="kpi sem"><b>{{ $stats['semana'] }}</b><span>7 dias</span></div></div>
    <div class="col-6 col-md-2"><div class="kpi nov"><b>{{ $stats['novos'] }}</b><span>A contatar</span></div></div>
    <div class="col-6 col-md-2"><div class="kpi cont"><b>{{ $stats['contatados'] }}</b><span>Contatados</span></div></div>
    <div class="col-6 col-md-2"><div class="kpi baix"><b>{{ $stats['baixaram'] }}</b><span>Baixaram</span></div></div>
  </div>

  <!-- Filtros -->
  <div class="card-box p-3 mb-3">
    <form method="GET" action="{{ route('leads.index') }}" class="row g-2 filtros align-items-end">
      <div class="col-md-4">
        <label class="form-label small mb-1 fw-semibold">Buscar</label>
        <input type="text" name="busca" value="{{ $busca }}" class="form-control" placeholder="Nome, e-mail, cidade ou cargo">
      </div>
      <div class="col-md-3">
        <label class="form-label small mb-1 fw-semibold">Status</label>
        <select name="status" class="form-select">
          <option value="todos"      @selected($status==='todos')>Todos</option>
          <option value="novos"      @selected($status==='novos')>A contatar</option>
          <option value="contatados" @selected($status==='contatados')>Contatados</option>
          <option value="baixaram"   @selected($status==='baixaram')>Que baixaram</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small mb-1 fw-semibold">Campanha / Fonte (utm)</label>
        <input type="text" name="origem" value="{{ $origem }}" class="form-control" placeholder="Ex.: meta, google...">
      </div>
      <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-primary flex-fill" style="background:#1D6FF2;border:none">Filtrar</button>
        <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary" title="Limpar">&times;</a>
      </div>
    </form>
  </div>

  <div class="d-flex justify-content-between align-items-center mb-2">
    <div class="text-muted small">Mostrando {{ $leads->firstItem() ?? 0 }}–{{ $leads->lastItem() ?? 0 }} de {{ $leads->total() }} leads</div>
    <a href="{{ route('leads.export', request()->query()) }}" class="btn btn-success btn-sm" style="background:#13955F;border:none">
      Exportar CSV
    </a>
  </div>

  <!-- Tabela -->
  <div class="card-box">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-3">Lead</th>
            <th>Cidade / Cargo</th>
            <th>Origem</th>
            <th>Data</th>
            <th>Status</th>
            <th class="text-end pe-3">Acoes</th>
          </tr>
        </thead>
        <tbody>
          @forelse($leads as $lead)
            <tr>
              <td class="ps-3">
                <div class="lead-nome">{{ $lead->nome }}</div>
                <div class="lead-sub">
                  <a class="muted" href="mailto:{{ $lead->email }}">{{ $lead->email }}</a><br>
                  <a class="wpp" href="{{ $lead->whatsappLink() }}" target="_blank" rel="noopener">{{ $lead->whatsapp }}</a>
                </div>
              </td>
              <td>
                <div>{{ $lead->cidade }}</div>
                <div class="lead-sub">{{ $lead->cargo }}</div>
              </td>
              <td>
                <span class="lead-sub">{{ $lead->utm_campaign ?: ($lead->utm_source ?: $lead->origem) }}</span>
              </td>
              <td><span class="lead-sub">{{ optional($lead->created_at)->format('d/m/Y') }}<br>{{ optional($lead->created_at)->format('H:i') }}</span></td>
              <td>
                @if($lead->contatado)
                  <span class="badge badge-cont">Contatado</span>
                @else
                  <span class="badge badge-novo">A contatar</span>
                @endif
                @if($lead->baixou)<span class="badge badge-baix">Baixou</span>@endif
              </td>
              <td class="text-end pe-3" style="white-space:nowrap">
                <a class="btn btn-outline-success btn-xs" href="{{ $lead->whatsappLink() }}" target="_blank" rel="noopener" title="WhatsApp">WhatsApp</a>
                <button class="btn btn-outline-secondary btn-xs btn-obs"
                        data-action="{{ route('leads.note', $lead->id) }}"
                        data-nome="{{ $lead->nome }}"
                        data-obs="{{ e($lead->observacoes) }}"
                        data-bs-toggle="modal" data-bs-target="#modalObs">Anotar</button>
                <form method="POST" action="{{ route('leads.toggle', $lead->id) }}" class="d-inline">
                  @csrf
                  <button class="btn btn-xs {{ $lead->contatado ? 'btn-outline-warning' : 'btn-success' }}" style="{{ $lead->contatado ? '' : 'background:#13955F;border:none' }}">
                    {{ $lead->contatado ? 'Reabrir' : 'Marcar contatado' }}
                  </button>
                </form>
              </td>
            </tr>
            @if($lead->observacoes)
              <tr class="table-light"><td colspan="6" class="ps-3 py-1 lead-sub"><b>Obs:</b> {{ $lead->observacoes }}</td></tr>
            @endif
          @empty
            <tr><td colspan="6" class="text-center text-muted py-5">Nenhum lead encontrado com esses filtros.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- Paginacao -->
  @if($leads->hasPages())
    <nav class="mt-3 d-flex justify-content-between align-items-center">
      <span class="text-muted small">Pagina {{ $leads->currentPage() }} de {{ $leads->lastPage() }}</span>
      <div class="btn-group">
        @if($leads->onFirstPage())
          <span class="btn btn-outline-secondary btn-sm disabled">Anterior</span>
        @else
          <a class="btn btn-outline-secondary btn-sm" href="{{ $leads->previousPageUrl() }}">Anterior</a>
        @endif
        @if($leads->hasMorePages())
          <a class="btn btn-outline-secondary btn-sm" href="{{ $leads->nextPageUrl() }}">Proxima</a>
        @else
          <span class="btn btn-outline-secondary btn-sm disabled">Proxima</span>
        @endif
      </div>
    </nav>
  @endif

</div>

<!-- Modal de observacao -->
<div class="modal fade" id="modalObs" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" id="formObs" class="modal-content">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title">Anotacao — <span id="obsNome"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <textarea name="observacoes" id="obsTexto" class="form-control" rows="5" placeholder="Ex.: ligado dia 10, pediu para retornar na proxima semana..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-primary" style="background:#1D6FF2;border:none">Salvar anotacao</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Preenche o modal de observacao com os dados do lead clicado
  document.querySelectorAll('.btn-obs').forEach(function(b){
    b.addEventListener('click', function(){
      document.getElementById('formObs').action = b.dataset.action;
      document.getElementById('obsNome').textContent = b.dataset.nome;
      document.getElementById('obsTexto').value = b.dataset.obs || '';
    });
  });
</script>
</body>
</html>
