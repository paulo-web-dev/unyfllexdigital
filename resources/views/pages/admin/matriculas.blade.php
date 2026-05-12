@extends('layouts.admin')
@section('title', 'Matrículas')
@section('section', 'Operacional')

@section('content')
<div class="page">

  <div class="page-header">
    <div>
      <h1 class="page-title">Matrículas</h1>
      <p class="page-subtitle">Gestão de acessos · 47 pendentes hoje</p>
    </div>
    <div class="page-actions">
      <button class="btn"><svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Exportar</button>
      <button class="btn btn-primary"><svg class="ico" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12 5v14M5 12h14"/></svg> Nova matrícula</button>
    </div>
  </div>

  <div class="kpi-row">
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Aprovadas hoje</span></div><div class="kpi-value">47</div><div class="kpi-delta positive">↑ 4,2% vs. ontem</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Pendentes</span></div><div class="kpi-value">84</div><div class="kpi-delta neutral">aguardando</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Inadimplentes</span></div><div class="kpi-value">218</div><div class="kpi-delta negative">ação requerida</div></div>
    <div class="kpi-card kpi-gold"><div class="kpi-top"><span class="kpi-label">Receita pendente</span></div><div class="kpi-value" style="color:var(--gold-400);">R$ 82.340</div><div class="kpi-delta neutral">recuperar</div></div>
  </div>

  <div class="card" style="padding:0;">

    {{-- Abas de status --}}
    <div class="tabs" style="padding:0 16px;border-bottom:1px solid var(--line-2);margin-bottom:0;">
      @foreach(['Todas','Ativas','Pendentes','Inadimplentes','Vencidas','Canceladas'] as $tab)
        <div class="tab {{ $loop->first ? 'active' : '' }}">{{ $tab }}</div>
      @endforeach
    </div>

    <div class="filter-bar">
      <div class="search-mini">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:14px;height:14px;"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>
        <input type="search" placeholder="Buscar por aluno ou curso…">
      </div>
      <select class="filter-select"><option>Forma: todas</option><option>PIX</option><option>Cartão</option><option>Boleto</option></select>
      <div style="flex:1;"></div>
    </div>

    <div class="tbl-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>ID</th>
            <th>Aluno</th>
            <th>Curso</th>
            <th>Status</th>
            <th>Forma</th>
            <th style="text-align:right;">Valor</th>
            <th>Vencimento</th>
            <th>Criada</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          {{--
            TODO: @foreach($matriculas as $m)
            <tr>
              <td>#{{ $m->id }}</td>
              <td>{{ $m->user->name ?? '' }}</td>
              <td>{{ $m->curso->title ?? '' }}</td>
              <td><span class="badge {{ $m->status }}">{{ $m->status }}</span></td>
              ...
            </tr>
            @endforeach
          --}}
          <tr>
            <td colspan="9" style="text-align:center;color:var(--fg-4);padding:40px;font-size:13px;">
              Conecte o model <code>Matricula</code> para listar aqui.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection
