@extends('layouts.admin')
@section('title', 'Matrículas')
@section('section', 'Operacional')

@section('content')
<div class="page">

  {{-- ══ Cabeçalho ══════════════════════════════════════════════════════ --}}
  <div class="page-header">
    <div>
      <h1 class="page-title">Matrículas</h1>
      <p class="page-subtitle">
        Minisséries express · {{ number_format($kpis['totalGeral'], 0, ',', '.') }} matrículas no total
      </p>
    </div>
    <div class="page-actions">
      <button class="btn">
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
        </svg>
        Exportar
      </button>
      <button class="btn btn-primary">
        <svg class="ico" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12 5v14M5 12h14"/></svg>
        Nova matrícula
      </button>
    </div>
  </div>

  {{-- ══ KPIs ════════════════════════════════════════════════════════════ --}}
  <div class="kpi-row cols-5">
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Total geral</span></div>
      <div class="kpi-value">{{ number_format($kpis['totalGeral'], 0, ',', '.') }}</div>
      <div class="kpi-delta positive">matrículas express</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Novas hoje</span></div>
      <div class="kpi-value">{{ $kpis['totalHoje'] }}</div>
      <div class="kpi-delta {{ $kpis['totalHoje'] > 0 ? 'positive' : 'neutral' }}">
        {{ today()->format('d/m/Y') }}
      </div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Confirmadas</span></div>
      <div class="kpi-value">{{ number_format($kpis['totalChecked'], 0, ',', '.') }}</div>
      <div class="kpi-delta positive">status checked</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Pendentes</span></div>
      <div class="kpi-value">{{ number_format($kpis['totalPending'], 0, ',', '.') }}</div>
      <div class="kpi-delta {{ $kpis['totalPending'] > 0 ? 'negative' : 'positive' }}">
        not_checked
      </div>
    </div>
    <div class="kpi-card kpi-gold">
      <div class="kpi-top"><span class="kpi-label">Receita confirmada</span></div>
      <div class="kpi-value" style="color:var(--gold-400);">
        R$ {{ number_format($kpis['receitaTotal'], 0, ',', '.') }}
      </div>
      <div class="kpi-delta neutral">
        R$ {{ number_format($kpis['receitaPending'], 0, ',', '.') }} pendente
      </div>
    </div>
  </div>

  {{-- ══ Card da tabela ══════════════════════════════════════════════════ --}}
  <div class="card" style="padding:0;">

    {{-- Abas de status --}}
    <div style="display:flex;gap:0;border-bottom:1px solid var(--line-2);padding:0 16px;overflow-x:auto;">
      @foreach([
        ['todas',            'Todas',        $kpis['totalGeral']],
        ['checked',          'Confirmadas',  $kpis['totalChecked']],
        ['not_checked',      'Pendentes',    $kpis['totalPending']],
        ['scheduled_billing','Agendadas',    $kpis['totalSched']],
      ] as [$val, $label, $cnt])
        <a href="{{ route('admin.matriculas', array_merge(request()->except('status','page'), ['status' => $val])) }}"
           style="display:inline-flex;align-items:center;gap:6px;padding:12px 14px;font-size:12px;font-weight:600;text-decoration:none;border-bottom:2px solid {{ $status === $val ? 'var(--brand-400)' : 'transparent' }};color:{{ $status === $val ? '#fff' : 'var(--fg-3)' }};white-space:nowrap;transition:color .15s;">
          {{ $label }}
          <span style="padding:1px 6px;border-radius:999px;font-size:10px;background:{{ $status === $val ? 'rgba(0,163,255,0.15)' : 'rgba(255,255,255,0.05)' }};color:{{ $status === $val ? 'var(--brand-300)' : 'var(--fg-4)' }};">
            {{ number_format($cnt, 0, ',', '.') }}
          </span>
        </a>
      @endforeach
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('admin.matriculas') }}">
      <input type="hidden" name="status" value="{{ $status }}">
      <div class="filter-bar" style="padding:10px 16px;">
        <div class="search-mini">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:14px;height:14px;">
            <circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/>
          </svg>
          <input type="search" name="q" value="{{ $busca }}" placeholder="Buscar por aluno ou curso…" autocomplete="off">
        </div>

        <select name="forma" class="filter-select" onchange="this.form.submit()">
          <option value="">Forma: todas</option>
          @foreach(['PIX','Cartão','Boleto','Gratuito','Cortesia'] as $f)
            <option value="{{ $f }}" {{ $forma === $f ? 'selected' : '' }}>{{ $f }}</option>
          @endforeach
        </select>

        <button type="submit" class="btn btn-sm" style="font-size:12px;">Filtrar</button>

        @if($busca || $forma)
          <a href="{{ route('admin.matriculas', ['status' => $status]) }}" class="btn btn-sm" style="font-size:12px;">✕ Limpar</a>
        @endif

        <div style="flex:1;"></div>
        <span style="font-size:12px;color:var(--fg-4);">
          {{ $matriculas->total() }} {{ $matriculas->total() === 1 ? 'matrícula' : 'matrículas' }}
        </span>
      </div>
    </form>

    {{-- Tabela --}}
    <div class="tbl-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>ID</th>
            <th>Aluno</th>
            <th>Minissérie</th>
            <th>Status</th>
            <th>Forma</th>
            <th style="text-align:right;">Valor</th>
            <th>Vigência</th>
            <th>Criada</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($matriculas as $m)
            @php
              $aluno  = $m->aluno;
              $classe = $m->classes;

              $iniciais = $aluno
                ? strtoupper(substr($aluno->name, 0, 1) . (strpos($aluno->name,' ') !== false ? substr(strrchr($aluno->name,' '),1,1) : ''))
                : '?';

              $statusConfig = [
                'checked'          => ['label' => 'Confirmada', 'class' => 'success'],
                'not_checked'      => ['label' => 'Pendente',   'class' => 'warn'],
                'scheduled_billing'=> ['label' => 'Agendada',   'class' => 'neutral'],
                'canceled'         => ['label' => 'Cancelada',  'class' => 'danger'],
              ];
              $sc = $statusConfig[$m->status] ?? ['label' => $m->status, 'class' => 'neutral'];
            @endphp
            <tr>
              {{-- ID --}}
              <td>
                <span style="font-family:var(--font-mono);font-size:11px;color:var(--fg-4);">
                  #{{ $m->id }}
                </span>
              </td>

              {{-- Aluno --}}
              <td>
                <div style="display:flex;align-items:center;gap:9px;">
                  <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#00C2FF,#0072FF);color:#061224;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    {{ $iniciais }}
                  </div>
                  <div style="min-width:0;">
                    <div style="font-size:12.5px;font-weight:500;color:var(--fg-1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:160px;">
                      {{ $aluno->name ?? '—' }}
                    </div>
                    <div style="font-size:11px;color:var(--fg-4);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:160px;">
                      {{ $aluno->email ?? '' }}
                    </div>
                  </div>
                </div>
              </td>

              {{-- Minissérie --}}
              <td>
                <div style="font-size:12.5px;color:var(--fg-1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px;">
                  {{ optional($classe)->title ?? '—' }}
                </div>
                @if($m->plano)
                  <div style="font-size:11px;color:var(--fg-4);">{{ $m->plano }}</div>
                @endif
              </td>

              {{-- Status --}}
              <td>
                <span class="badge {{ $sc['class'] }}">{{ $sc['label'] }}</span>
              </td>

              {{-- Forma de pagamento --}}
              <td>
                <span style="font-size:12px;color:var(--fg-2);">
                  {{ $m->payment_method ?: '—' }}
                </span>
              </td>

              {{-- Valor --}}
              <td style="text-align:right;">
                <div style="font-family:var(--font-mono);font-size:13px;color:var(--fg-1);font-weight:600;">
                  @if($m->final_value > 0)
                    R$ {{ number_format($m->final_value, 2, ',', '.') }}
                  @elseif($m->value > 0)
                    R$ {{ number_format($m->value, 2, ',', '.') }}
                  @else
                    <span style="color:var(--fg-4);">Gratuito</span>
                  @endif
                </div>
                @if($m->discount > 0)
                  <div style="font-size:10px;color:var(--fg-4);">
                    desc. R$ {{ number_format($m->discount, 2, ',', '.') }}
                  </div>
                @endif
              </td>

              {{-- Vigência --}}
              <td>
                @if($m->start_date && $m->end_date)
                  <div style="font-size:12px;color:var(--fg-2);">
                    {{ \Carbon\Carbon::parse($m->start_date)->format('d/m/Y') }}
                  </div>
                  <div style="font-size:11px;color:var(--fg-4);">
                    até {{ \Carbon\Carbon::parse($m->end_date)->format('d/m/Y') }}
                  </div>
                @else
                  <span style="font-size:12px;color:var(--fg-4);">—</span>
                @endif
              </td>

              {{-- Criada em --}}
              <td>
                <div style="font-size:12px;color:var(--fg-2);">
                  {{ optional($m->created_at)->format('d/m/Y') ?? '—' }}
                </div>
                <div style="font-size:11px;color:var(--fg-4);">
                  {{ optional($m->created_at)->diffForHumans() ?? '' }}
                </div>
              </td>

              {{-- Ações --}}
              <td style="text-align:right;">
                <button class="btn btn-sm" style="font-size:11px;padding:5px 10px;">
                  Ver
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" style="text-align:center;color:var(--fg-4);padding:48px;font-size:13px;">
                @if($busca)
                  Nenhuma matrícula encontrada para "<strong>{{ $busca }}</strong>".
                @else
                  Nenhuma matrícula para o filtro selecionado.
                @endif
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Paginação --}}
    <div style="padding:12px 16px;display:flex;justify-content:space-between;align-items:center;border-top:1px solid var(--line-2);font-size:12px;color:var(--fg-4);">
      <span>
        @if($matriculas->total() > 0)
          {{ $matriculas->firstItem() }}–{{ $matriculas->lastItem() }}
          de {{ number_format($matriculas->total(), 0, ',', '.') }}
        @endif
      </span>
      {{ $matriculas->links() }}
    </div>

  </div>

</div>
@endsection

@push('styles')
<style>
nav[role="navigation"] { display:inline-flex;gap:4px;align-items:center; }
nav[role="navigation"] a,
nav[role="navigation"] span {
  display:inline-flex;align-items:center;justify-content:center;
  min-width:28px;height:28px;padding:0 8px;
  border-radius:7px;font-size:12px;font-weight:500;
  border:1px solid var(--line-2);background:var(--bg-2);
  color:var(--fg-3);text-decoration:none;transition:all .15s;
}
nav[role="navigation"] a:hover { background:var(--bg-3);color:#fff; }
nav[role="navigation"] [aria-current] {
  background:rgba(0,163,255,0.15);border-color:rgba(0,163,255,0.4);color:var(--brand-200);
}
</style>
@endpush
