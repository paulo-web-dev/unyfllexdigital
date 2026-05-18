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
      <button class="btn">
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Exportar
      </button>
      <a href="{{ route('admin.alunos.create') }}" class="btn btn-primary" style="text-decoration:none;">
        <svg class="ico" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12 5v14M5 12h14"/></svg>
        Novo aluno
      </a>
    </div>
  </div>

  @if(session('success'))
    <div style="padding:12px 16px;background:rgba(43,217,161,0.10);border:1px solid rgba(43,217,161,0.35);border-radius:var(--r-md);color:#6FE6BD;font-size:13px;font-weight:500;margin-bottom:20px;">
      ✓ {{ session('success') }}
    </div>
  @endif

  {{-- KPIs --}}
  <div class="kpi-row">
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Total de alunos</span></div><div class="kpi-value">{{ number_format($kpis['totalAlunos'],0,',','.') }}</div><div class="kpi-delta positive">cadastrados</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Novos hoje</span></div><div class="kpi-value">{{ $kpis['novosHoje'] }}</div><div class="kpi-delta {{ $kpis['novosHoje'] > 0 ? 'positive' : 'neutral' }}">{{ $kpis['novosSemana'] }} esta semana</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Com matrícula</span></div><div class="kpi-value">{{ number_format($kpis['totalMatriculas'],0,',','.') }}</div><div class="kpi-delta positive">em minisséries</div></div>
    <div class="kpi-card kpi-gold"><div class="kpi-top"><span class="kpi-label">Assistindo</span></div><div class="kpi-value" style="color:var(--gold-400);">{{ number_format($kpis['alunosAtivos'],0,',','.') }}</div><div class="kpi-delta positive">já assistiram</div></div>
  </div>

  {{-- Tabela --}}
  <div class="card" style="padding:0;">
    <form method="GET" action="{{ route('admin.alunos') }}">
      <div class="filter-bar" style="padding:12px 16px;">
        <div class="search-mini">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:14px;height:14px;"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>
          <input type="search" name="q" value="{{ $busca }}" placeholder="Buscar por nome, e-mail ou CPF…" autocomplete="off">
        </div>
        <select name="ordem" class="filter-select" onchange="this.form.submit()">
          <option value="recentes" {{ $ordem === 'recentes' ? 'selected':'' }}>Mais recentes</option>
          <option value="nome"     {{ $ordem === 'nome'     ? 'selected':'' }}>Nome A→Z</option>
        </select>
        <button type="submit" class="btn btn-sm" style="font-size:12px;">Filtrar</button>
        @if($busca || $ordem !== 'recentes')
          <a href="{{ route('admin.alunos') }}" class="btn btn-sm" style="font-size:12px;">✕</a>
        @endif
        <div style="flex:1;"></div>
        <span style="font-size:12px;color:var(--fg-4);">{{ $alunos->total() }} {{ $alunos->total() === 1 ? 'aluno' : 'alunos' }}</span>
      </div>
    </form>

    <div class="tbl-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>Aluno</th>
            <th>Setor / Função</th>
            <th>Matrículas</th>
            <th>Cápsulas assistidas</th>
            <th>Cadastro</th>
            <th>Power</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($alunos as $aluno)
            @php
              $matriculas = $matriculasPorAluno[$aluno->student_id] ?? 0;
              $capsulas   = $capsulasPorUser[$aluno->id] ?? 0;
              $iniciais   = strtoupper(substr($aluno->name,0,1).(strpos($aluno->name,' ')!==false?substr(strrchr($aluno->name,' '),1,1):''));
            @endphp
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:10px;">
                  <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#00C2FF,#0072FF);color:#061224;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">{{ $iniciais }}</div>
                  <div style="min-width:0;">
                    <div style="font-size:13px;font-weight:500;color:var(--fg-1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px;">{{ $aluno->name }}</div>
                    <div style="font-size:11px;color:var(--fg-4);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px;">{{ $aluno->email }}</div>
                  </div>
                </div>
              </td>
              <td>
                <div style="font-size:12px;color:var(--fg-2);">{{ $aluno->funcao ?: '—' }}</div>
                @if($aluno->setor)<div style="font-size:11px;color:var(--fg-4);">{{ $aluno->setor }}</div>@endif
              </td>
              <td>
                @if($matriculas > 0)
                  <span style="display:inline-flex;align-items:center;padding:3px 8px;border-radius:999px;background:rgba(0,163,255,0.10);color:var(--brand-300);font-size:11px;font-weight:600;border:1px solid rgba(0,163,255,0.25);">
                    {{ $matriculas }} {{ $matriculas === 1 ? 'curso' : 'cursos' }}
                  </span>
                @else
                  <span style="font-size:12px;color:var(--fg-4);">—</span>
                @endif
              </td>
              <td>
                <div style="display:flex;align-items:center;gap:8px;">
                  <span style="font-family:var(--font-mono);font-size:13px;color:{{ $capsulas > 0 ? 'var(--fg-1)' : 'var(--fg-4)' }};">{{ $capsulas > 0 ? number_format($capsulas,0,',','.') : '—' }}</span>
                  @if($capsulas > 0)
                    <div style="width:48px;height:3px;background:rgba(255,255,255,0.07);border-radius:2px;overflow:hidden;">
                      <div style="height:100%;width:{{ min(100,($capsulas/50)*100) }}%;background:var(--grad-brand);"></div>
                    </div>
                  @endif
                </div>
              </td>
              <td>
                <div style="font-size:12px;color:var(--fg-2);">{{ optional($aluno->created_at)->format('d/m/Y') ?? '—' }}</div>
                <div style="font-size:11px;color:var(--fg-4);">{{ optional($aluno->created_at)->diffForHumans() ?? '' }}</div>
              </td>
              <td>
                <span style="font-family:var(--font-mono);font-size:11px;padding:2px 8px;border-radius:999px;background:{{ ($aluno->power??0)>10?'rgba(232,183,101,0.12)':'rgba(255,255,255,0.05)' }};color:{{ ($aluno->power??0)>10?'var(--gold-400)':'var(--fg-4)' }};border:1px solid {{ ($aluno->power??0)>10?'rgba(232,183,101,0.25)':'var(--line-1)' }};">
                  {{ $aluno->power ?? 0 }}
                </span>
              </td>
              <td style="text-align:right;">
                <div style="display:flex;gap:6px;justify-content:flex-end;">
                  <a href="{{ route('admin.matriculas.create', ['student_id' => $aluno->student_id]) }}"
                     class="btn btn-sm" style="font-size:11px;padding:5px 10px;text-decoration:none;"
                     title="Matricular">
                    <svg viewBox="0 0 24 24" style="width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2;"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
                  </a>
                  <a href="{{ route('admin.alunos.edit', $aluno->id) }}"
                     class="btn btn-sm" style="font-size:11px;padding:5px 10px;text-decoration:none;">
                    Editar
                  </a>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" style="text-align:center;color:var(--fg-4);padding:48px;font-size:13px;">
                @if($busca) Nenhum aluno encontrado para "<strong>{{ $busca }}</strong>".
                @else Nenhum aluno cadastrado ainda.
                @endif
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div style="padding:12px 16px;display:flex;justify-content:space-between;align-items:center;border-top:1px solid var(--line-2);font-size:12px;color:var(--fg-4);">
      <span>@if($alunos->total() > 0){{ $alunos->firstItem() }}–{{ $alunos->lastItem() }} de {{ number_format($alunos->total(),0,',','.') }}@endif</span>
      {{ $alunos->links() }}
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
