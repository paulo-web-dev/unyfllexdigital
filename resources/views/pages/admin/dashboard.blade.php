@extends('layouts.admin')
@section('title', 'Painel administrativo')
@section('section', 'Visão Geral')

@section('content')
<div class="page">

  <div class="page-header">
    <div>
      <h1 class="page-title">Olá, {{ auth()->user()->first_name }} 👋</h1>
      <p class="page-subtitle">Resumo da operação · {{ now()->isoFormat('D [de] MMMM [de] YYYY') }}</p>
    </div>
    <div class="page-actions">
      <span class="live-pill"><span class="lp-dot"></span> ao vivo</span>
      <a href="{{ route('admin.matriculas.create') }}" class="btn btn-primary" style="text-decoration:none;">
        <svg class="ico" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12 5v14M5 12h14"/></svg>
        Nova matrícula
      </a>
    </div>
  </div>

  {{-- KPIs linha 1 --}}
  <div class="kpi-row cols-5">
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Alunos ativos</span></div>
      <div class="kpi-value">{{ number_format($kpis['totalAlunos'],0,',','.') }}</div>
      <div class="kpi-delta positive">+{{ $kpis['alunosMes'] }} este mês</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Matrículas hoje</span></div>
      <div class="kpi-value">{{ $kpis['matriculasHoje'] }}</div>
      <div class="kpi-delta {{ $kpis['matriculasHoje'] >= $kpis['matriculasOntem'] ? 'positive' : 'negative' }}">
        {{ $kpis['matriculasOntem'] }} ontem
      </div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Receita do mês</span></div>
      <div class="kpi-value">R$ {{ number_format($kpis['receitaMes'],0,',','.') }}</div>
      <div class="kpi-delta {{ $kpis['receitaMes'] >= $kpis['receitaAnt'] ? 'positive' : 'negative' }}">
        @php $var = $kpis['receitaAnt'] > 0 ? (($kpis['receitaMes']-$kpis['receitaAnt'])/$kpis['receitaAnt']*100) : 0; @endphp
        {{ $var >= 0 ? '↑' : '↓' }} {{ number_format(abs($var),1) }}% vs mês ant.
      </div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Usuários ativos 7d</span></div>
      <div class="kpi-value">{{ number_format($kpis['usuariosAtivos'],0,',','.') }}</div>
      <div class="kpi-delta positive">assistindo conteúdo</div>
    </div>
    <div class="kpi-card kpi-gold">
      <div class="kpi-top"><span class="kpi-label">Receita total</span></div>
      <div class="kpi-value" style="color:var(--gold-400);">R$ {{ number_format($kpis['receitaTotal'],0,',','.') }}</div>
      <div class="kpi-delta neutral">acumulado</div>
    </div>
  </div>

  {{-- KPIs linha 2 --}}
  <div class="kpi-row cols-5">
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Novos hoje</span></div><div class="kpi-value">{{ $kpis['alunosHoje'] }}</div><div class="kpi-delta positive">alunos</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Total matrículas</span></div><div class="kpi-value">{{ number_format($kpis['totalMatriculas'],0,',','.') }}</div><div class="kpi-delta neutral">acumulado</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Inadimplentes</span></div><div class="kpi-value">{{ $kpis['inadimplentes'] }}</div><div class="kpi-delta {{ $kpis['inadimplentes'] > 0 ? 'negative' : 'positive' }}">R$ {{ number_format($kpis['pendentes'],0,',','.') }} pend.</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Cápsulas assistidas</span></div><div class="kpi-value">{{ number_format($kpis['capsulasMes'],0,',','.') }}</div><div class="kpi-delta positive">este mês</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Conclusão média</span></div><div class="kpi-value">{{ $kpis['progressoMedio'] }}%</div><div class="kpi-delta neutral">por aluno</div></div>
  </div>

  {{-- Gráfico de matrículas + últimas vendas --}}
  <div class="admin-grid-3-1" style="margin-top:18px;">

    <div style="display:flex;flex-direction:column;gap:18px;">

      {{-- Gráfico barras --}}
      <div class="card" style="padding:0;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
          <h3 style="margin:0;font-size:15px;">Matrículas — últimos 14 dias</h3>
        </div>
        <div style="padding:16px 20px 20px;">
          <canvas id="chart-matriculas" height="100"></canvas>
        </div>
      </div>

      {{-- Top minisséries --}}
      <div class="card" style="padding:0;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
          <h3 style="margin:0;font-size:15px;">Top minisséries por matrículas</h3>
        </div>
        @forelse($topCursos as $tc)
          @php $max = $topCursos->max('total'); @endphp
          <div style="padding:10px 20px;border-top:1px solid var(--line-1);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;">
              <span style="font-size:13px;color:var(--fg-1);font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:320px;">
                {{ optional($tc->classes)->title ?? "Curso #{$tc->classes_id}" }}
              </span>
              <span style="font-family:var(--font-mono);font-size:12px;color:var(--brand-300);font-weight:600;flex-shrink:0;">{{ $tc->total }}</span>
            </div>
            <div style="height:4px;background:rgba(255,255,255,0.05);border-radius:2px;overflow:hidden;">
              <div style="height:100%;width:{{ $max > 0 ? round(($tc->total/$max)*100) : 0 }}%;background:var(--grad-brand);"></div>
            </div>
          </div>
        @empty
          <div style="padding:32px;text-align:center;color:var(--fg-4);font-size:13px;">Nenhuma matrícula ainda.</div>
        @endforelse
      </div>

    </div>

    {{-- Últimas vendas --}}
    <div class="card" style="padding:0;">
      <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);display:flex;justify-content:space-between;align-items:center;">
        <div><h3 style="margin:0;font-size:15px;">Últimas vendas</h3><div style="font-size:11.5px;color:var(--fg-4);margin-top:2px;">Confirmadas</div></div>
        <span class="live-pill"><span class="lp-dot"></span></span>
      </div>
      @forelse($ultimasVendas as $v)
        @php
          $nome = optional($v->student)->name ?? "Aluno #{$v->student_id}";
          $ini  = strtoupper(substr($nome,0,1));
        @endphp
        <div style="display:flex;align-items:center;gap:10px;padding:10px 20px;border-top:1px solid var(--line-1);">
          <div class="avatar-sm">{{ $ini }}</div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:13px;color:var(--fg-1);font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $nome }}</div>
            <div style="font-size:11px;color:var(--fg-4);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ optional($v->classes)->title ?? '—' }}</div>
          </div>
          <div style="text-align:right;flex-shrink:0;">
            <div style="font-family:var(--font-mono);font-size:13px;color:var(--fg-1);font-weight:600;">R$ {{ number_format($v->final_value,0,',','.') }}</div>
            <div style="font-size:10.5px;color:var(--fg-4);">{{ optional($v->created_at)->diffForHumans() }}</div>
          </div>
        </div>
      @empty
        <div style="padding:40px;text-align:center;color:var(--fg-4);font-size:13px;">Nenhuma venda confirmada ainda.</div>
      @endforelse
      <div style="padding:10px 20px;border-top:1px solid var(--line-1);">
        <a href="{{ route('admin.matriculas') }}" style="font-size:12px;color:var(--brand-300);text-decoration:none;">Ver todas as matrículas →</a>
      </div>
    </div>

  </div>
</div>
{{-- ══ Ranking de carteiras — apenas super admin ══════════════════════ --}}
@can('admin.super')
@if($rankingCarteiras->isNotEmpty())
<div style="margin-top:18px;">
  <div class="card" style="padding:0;">
    <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
      <div>
        <h3 style="margin:0;font-size:15px;">Ranking de vendedores</h3>
        <div style="font-size:11.5px;color:var(--fg-4);margin-top:2px;">Faturamento por carteira · acumulado</div>
      </div>
      <div style="display:flex;gap:20px;font-size:12px;color:var(--fg-4);">
        <span>{{ $rankingCarteiras->count() }} vendedor(es)</span>
        <span>Total: <strong style="color:#fff;">R$ {{ number_format($rankingCarteiras->sum('receita'),2,',','.') }}</strong></span>
      </div>
    </div>

    <div class="tbl-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th style="width:36px;">#</th>
            <th>Vendedor / Carteira</th>
            <th style="text-align:center;">Matrículas</th>
            <th style="text-align:center;">Confirmadas</th>
            <th style="text-align:center;">Pendentes</th>
            <th style="text-align:right;">A receber</th>
            <th style="text-align:right;">Faturado</th>
            <th style="text-align:right;">Última venda</th>
            <th style="width:160px;"></th>
          </tr>
        </thead>
        <tbody>
          @php
            $maxReceita = $rankingCarteiras->max('receita');
          @endphp
          @foreach($rankingCarteiras as $i => $carteira)
            @php
              $pct      = $maxReceita > 0 ? round(($carteira->receita / $maxReceita) * 100) : 0;
              $iniciais = strtoupper(substr($carteira->wallet, 0, 2));
              $isTop    = $i === 0;
            @endphp
            <tr>
              {{-- Posição --}}
              <td>
                @if($i === 0)
                  <span style="font-size:16px;" title="1º lugar">🥇</span>
                @elseif($i === 1)
                  <span style="font-size:16px;" title="2º lugar">🥈</span>
                @elseif($i === 2)
                  <span style="font-size:16px;" title="3º lugar">🥉</span>
                @else
                  <span style="font-family:var(--font-mono);font-size:12px;color:var(--fg-4);">{{ $i + 1 }}</span>
                @endif
              </td>

              {{-- Vendedor --}}
              <td>
                <div style="display:flex;align-items:center;gap:10px;">
                  <div style="width:32px;height:32px;border-radius:50%;background:{{ $isTop ? 'linear-gradient(135deg,#E8B765,#C9921A)' : 'var(--grad-brand)' }};color:{{ $isTop ? '#061224' : '#061224' }};font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    {{ $iniciais }}
                  </div>
                  <div>
                    <div style="font-size:13px;font-weight:600;color:{{ $isTop ? 'var(--gold-400)' : 'var(--fg-1)' }};">
                      {{ $carteira->wallet }}
                      @if($isTop)
                        <span style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--gold-400);margin-left:6px;">TOP</span>
                      @endif
                    </div>
                  </div>
                </div>
              </td>

              {{-- Total matrículas --}}
              <td style="text-align:center;font-family:var(--font-mono);font-size:13px;">
                {{ number_format($carteira->total_matriculas, 0, ',', '.') }}
              </td>

              {{-- Confirmadas --}}
              <td style="text-align:center;">
                <span class="badge success">{{ $carteira->confirmadas }}</span>
              </td>

              {{-- Pendentes --}}
              <td style="text-align:center;">
                @if($carteira->pendentes > 0)
                  <span class="badge warn">{{ $carteira->pendentes }}</span>
                @else
                  <span style="font-size:12px;color:var(--fg-4);">—</span>
                @endif
              </td>

              {{-- A receber --}}
              <td style="text-align:right;font-family:var(--font-mono);font-size:12px;color:var(--fg-3);">
                @if($carteira->a_receber > 0)
                  R$ {{ number_format($carteira->a_receber, 0, ',', '.') }}
                @else
                  <span style="color:var(--fg-4);">—</span>
                @endif
              </td>

              {{-- Faturado --}}
              <td style="text-align:right;">
                <div style="font-family:var(--font-mono);font-size:14px;font-weight:700;color:{{ $isTop ? 'var(--gold-400)' : 'var(--fg-1)' }};">
                  R$ {{ number_format($carteira->receita, 0, ',', '.') }}
                </div>
              </td>

              {{-- Última venda --}}
              <td style="text-align:right;font-size:11px;color:var(--fg-4);">
                {{ $carteira->ultima_venda ? \Carbon\Carbon::parse($carteira->ultima_venda)->diffForHumans() : '—' }}
              </td>

              {{-- Barra de progresso --}}
              <td>
                <div style="height:6px;background:rgba(255,255,255,0.05);border-radius:3px;overflow:hidden;">
                  <div style="height:100%;width:{{ $pct }}%;background:{{ $isTop ? 'linear-gradient(90deg,#E8B765,#F0C97A)' : 'var(--grad-brand)' }};border-radius:3px;transition:width 0.6s ease;"></div>
                </div>
                <div style="font-size:10px;color:var(--fg-4);text-align:right;margin-top:3px;">{{ $pct }}%</div>
              </td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr style="border-top:2px solid var(--line-2);">
            <td colspan="2" style="padding:12px 16px;font-size:12px;font-weight:700;color:var(--fg-3);">TOTAL GERAL</td>
            <td style="text-align:center;font-family:var(--font-mono);font-weight:700;font-size:13px;">{{ number_format($rankingCarteiras->sum('total_matriculas'),0,',','.') }}</td>
            <td style="text-align:center;"><span class="badge success">{{ number_format($rankingCarteiras->sum('confirmadas'),0,',','.') }}</span></td>
            <td style="text-align:center;"><span class="badge warn">{{ number_format($rankingCarteiras->sum('pendentes'),0,',','.') }}</span></td>
            <td style="text-align:right;font-family:var(--font-mono);font-size:12px;color:var(--fg-3);">R$ {{ number_format($rankingCarteiras->sum('a_receber'),0,',','.') }}</td>
            <td style="text-align:right;font-family:var(--font-mono);font-size:15px;font-weight:800;color:var(--gold-400);">R$ {{ number_format($rankingCarteiras->sum('receita'),0,',','.') }}</td>
            <td colspan="2"></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>
@endif
@endcan

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('chart-matriculas'), {
  type: 'bar',
  data: {
    labels: {!! json_encode($labels) !!},
    datasets: [{
      label: 'Matrículas',
      data: {!! json_encode($valores) !!},
      backgroundColor: 'rgba(0,163,255,0.5)',
      borderColor: 'rgba(0,163,255,0.9)',
      borderWidth: 1,
      borderRadius: 6,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#8899AA', font: { size: 11 } } },
      y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#8899AA', font: { size: 11 }, stepSize: 1 }, beginAtZero: true }
    }
  }
});
</script>
@endpush
