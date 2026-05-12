@extends('layouts.admin')
@section('title', 'Alunos')
@section('section', 'Operacional')

@section('content')
<div class="page">

  <div class="page-header">
    <div>
      <h1 class="page-title">Alunos</h1>
      <p class="page-subtitle">Base completa · CRM com filtros e segmentação</p>
    </div>
    <div class="page-actions">
      <button class="btn"><svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg> Importar CSV</button>
      <button class="btn"><svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Exportar</button>
      <button class="btn btn-primary"><svg class="ico" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12 5v14M5 12h14"/></svg> Novo aluno</button>
    </div>
  </div>

  {{-- KPIs --}}
  <div class="kpi-row">
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Total de alunos</span></div><div class="kpi-value">12.847</div><div class="kpi-delta positive">↑ 8,4% vs. mês ant.</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Novos hoje</span></div><div class="kpi-value">34</div><div class="kpi-delta positive">↑ 12,0% vs. ontem</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Inadimplentes</span></div><div class="kpi-value">218</div><div class="kpi-delta negative">↑ 4,2% 30 dias</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Bloqueados</span></div><div class="kpi-value">12</div><div class="kpi-delta neutral">ativos</div></div>
  </div>

  {{-- Tabela --}}
  <div class="card" style="padding:0;">
    <div class="filter-bar">
      <div class="search-mini">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:14px;height:14px;"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>
        <input type="search" placeholder="Buscar por nome, e-mail ou cidade…">
      </div>
      <select class="filter-select"><option>Status: todos</option><option>Ativo</option><option>Pendente</option><option>Inadimplente</option><option>Cancelado</option></select>
      <div style="flex:1;"></div>
      {{-- TODO: {{ $alunos->total() }} resultados --}}
      <span style="font-size:12px;color:var(--fg-4);">— resultados</span>
    </div>

    <div class="tbl-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>Aluno</th>
            <th>Status</th>
            <th>Cidade</th>
            <th>Cursos</th>
            <th>Último acesso</th>
            <th style="text-align:right;">Total comprado</th>
            <th>Origem</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          {{--
            TODO: substituir pelo loop real:
            @foreach($alunos as $aluno)
              <tr>
                <td>{{ $aluno->name }}</td>
                ...
              </tr>
            @endforeach
          --}}
          <tr>
            <td colspan="8" style="text-align:center;color:var(--fg-4);padding:40px;font-size:13px;">
              Conecte o model <code>User</code> para listar os alunos aqui.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div style="padding:10px 14px;display:flex;justify-content:space-between;align-items:center;border-top:1px solid var(--line-2);font-size:12px;color:var(--fg-4);">
      <span>{{-- {{ $alunos->firstItem() }}–{{ $alunos->lastItem() }} de {{ $alunos->total() }} --}}</span>
      {{-- {{ $alunos->links() }} --}}
    </div>
  </div>

</div>
@endsection
