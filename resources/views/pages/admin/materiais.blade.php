@extends('layouts.admin')
@section('title', 'Materiais')
@section('section', 'Cursos')

@section('content')
<div class="page">

  <div class="page-header">
    <div>
      <h1 class="page-title">Materiais de Apoio</h1>
      <p class="page-subtitle">PDFs, mapas mentais e podcasts vinculados às temporadas</p>
    </div>
    <div class="page-actions">
      <a href="{{ route('admin.materiais.create') }}" class="btn btn-primary" style="text-decoration:none;">
        <svg class="ico" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12 5v14M5 12h14"/></svg>
        Novo material
      </a>
    </div>
  </div>

  {{-- Flash --}}
  @if(session('success'))
    <div style="padding:12px 16px;background:rgba(43,217,161,0.10);border:1px solid rgba(43,217,161,0.35);border-radius:var(--r-md);color:#6FE6BD;font-size:13px;font-weight:500;margin-bottom:20px;">
      ✓ {{ session('success') }}
    </div>
  @endif

  {{-- KPIs --}}
  <div class="kpi-row">
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Total</span></div><div class="kpi-value">{{ $kpis['total'] }}</div><div class="kpi-delta neutral">materiais</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">PDFs</span></div><div class="kpi-value">{{ $kpis['pdfs'] }}</div><div class="kpi-delta neutral">📄</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Podcasts</span></div><div class="kpi-value">{{ $kpis['podcasts'] }}</div><div class="kpi-delta neutral">🎧</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Ativos</span></div><div class="kpi-value">{{ $kpis['ativos'] }}</div><div class="kpi-delta positive">publicados</div></div>
  </div>

  {{-- Tabela --}}
  <div class="card" style="padding:0;">
    <form method="GET" action="{{ route('admin.materiais') }}">
      <div class="filter-bar" style="padding:10px 16px;">
        <div class="search-mini">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:14px;height:14px;"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>
          <input type="search" name="q" value="{{ $busca }}" placeholder="Buscar por nome ou arquivo…" autocomplete="off">
        </div>
        <select name="tipo" class="filter-select" onchange="this.form.submit()">
          <option value="">Tipo: todos</option>
          <option value="PDF"     {{ $tipo === 'PDF'     ? 'selected' : '' }}>PDF</option>
          <option value="PODCAST" {{ $tipo === 'PODCAST' ? 'selected' : '' }}>Podcast</option>
        </select>
        <button type="submit" class="btn btn-sm" style="font-size:12px;">Filtrar</button>
        @if($busca || $tipo)
          <a href="{{ route('admin.materiais') }}" class="btn btn-sm" style="font-size:12px;">✕</a>
        @endif
        <div style="flex:1;"></div>
        <span style="font-size:12px;color:var(--fg-4);">{{ $materiais->total() }} materiais</span>
      </div>
    </form>

    <div class="tbl-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Arquivo</th>
            <th>Tipo</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($materiais as $mat)
            <tr>
              <td><span style="font-family:var(--font-mono);font-size:11px;color:var(--fg-4);">#{{ $mat->id }}</span></td>
              <td>
                <div style="display:flex;align-items:center;gap:10px;">
                  <span style="font-size:18px;">{{ ['PDF'=>'📄','PODCAST'=>'🎧'][$mat->type] ?? '📁' }}</span>
                  <span style="font-size:13px;font-weight:500;color:var(--fg-1);">{{ $mat->name }}</span>
                </div>
              </td>
              <td>
                <a href="https://unygov.com.br/storage/materials/{{ $mat->file_name }}"
                   target="_blank"
                   style="font-family:var(--font-mono);font-size:11px;color:var(--brand-300);text-decoration:none;">
                  {{ $mat->file_name }}
                  <svg viewBox="0 0 24 24" style="width:10px;height:10px;stroke:currentColor;fill:none;stroke-width:2;display:inline;"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                </a>
              </td>
              <td>
                <span class="badge {{ $mat->type === 'PDF' ? 'gold' : 'brand' }}">{{ $mat->type }}</span>
              </td>
              <td>
                <span class="badge {{ $mat->status === 'able' ? 'success' : 'neutral' }}">
                  {{ $mat->status === 'able' ? 'Ativo' : 'Inativo' }}
                </span>
              </td>
              <td style="text-align:right;">
                <div style="display:flex;gap:6px;justify-content:flex-end;">
                  <a href="{{ route('admin.materiais.edit', $mat->id) }}"
                     class="btn btn-sm" style="font-size:11px;padding:5px 10px;text-decoration:none;">
                    Editar
                  </a>
                  <form action="{{ route('admin.materiais.destroy', $mat->id) }}" method="POST"
                        onsubmit="return confirm('Remover este material?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm"
                            style="font-size:11px;padding:5px 10px;background:rgba(255,92,122,0.10);border-color:rgba(255,92,122,0.3);color:#FF5C7A;">
                      Excluir
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" style="text-align:center;color:var(--fg-4);padding:48px;font-size:13px;">
                @if($busca || $tipo) Nenhum material encontrado para este filtro.
                @else Nenhum material cadastrado ainda.
                @endif
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div style="padding:12px 16px;display:flex;justify-content:space-between;align-items:center;border-top:1px solid var(--line-2);font-size:12px;color:var(--fg-4);">
      <span>@if($materiais->total() > 0){{ $materiais->firstItem() }}–{{ $materiais->lastItem() }} de {{ $materiais->total() }}@endif</span>
      {{ $materiais->links() }}
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
nav[role="navigation"] { display:inline-flex;gap:4px;align-items:center; }
nav[role="navigation"] a,nav[role="navigation"] span { display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;padding:0 8px;border-radius:7px;font-size:12px;font-weight:500;border:1px solid var(--line-2);background:var(--bg-2);color:var(--fg-3);text-decoration:none;transition:all .15s; }
nav[role="navigation"] a:hover { background:var(--bg-3);color:#fff; }
nav[role="navigation"] [aria-current] { background:rgba(0,163,255,0.15);border-color:rgba(0,163,255,0.4);color:var(--brand-200); }
</style>
@endpush
