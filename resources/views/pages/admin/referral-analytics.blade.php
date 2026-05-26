@extends('layouts.admin')
@section('title', 'Analytics de Referral')
@section('section', 'Visão Geral')

@section('content')
<div class="page">

  <div class="page-header">
    <div>
      <h1 class="page-title">Analytics de Referral</h1>
      <p class="page-subtitle">Cliques, conversões e receita por vendedor</p>
    </div>
    <div class="page-actions">
      {{-- Seletor de período --}}
      <form method="GET" action="{{ route('admin.referral') }}" style="display:flex;gap:8px;align-items:center;">
        <select name="periodo" class="filter-select" style="height:36px;" onchange="this.form.submit()">
          @foreach([7=>'Últimos 7 dias', 15=>'Últimos 15 dias', 30=>'Últimos 30 dias', 60=>'Últimos 60 dias', 90=>'Últimos 90 dias'] as $v => $l)
            <option value="{{ $v }}" {{ $periodo == $v ? 'selected':'' }}>{{ $l }}</option>
          @endforeach
        </select>
      </form>
    </div>
  </div>

  {{-- KPIs --}}
  <div class="kpi-row" style="grid-template-columns:repeat(6,1fr);">
    <div class="kpi-card kpi-gold">
      <div class="kpi-top"><span class="kpi-label">Receita via referral</span></div>
      <div class="kpi-value" style="color:var(--gold-400);">R$ {{ number_format($kpis['receitaReferral'],0,',','.') }}</div>
      <div class="kpi-delta positive">confirmado</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Conversões</span></div>
      <div class="kpi-value">{{ $kpis['totalConversoes'] }}</div>
      <div class="kpi-delta positive">matrículas</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Cliques no período</span></div>
      <div class="kpi-value">{{ number_format($kpis['cliquesNoPeriodo'],0,',','.') }}</div>
      <div class="kpi-delta neutral">{{ $periodo }} dias</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Cliques hoje</span></div>
      <div class="kpi-value">{{ $kpis['cliquesHoje'] }}</div>
      <div class="kpi-delta neutral">acessos</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Vendedores ativos</span></div>
      <div class="kpi-value">{{ $kpis['totalVendedores'] }}</div>
      <div class="kpi-delta neutral">com link usado</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Total cliques</span></div>
      <div class="kpi-value">{{ number_format($kpis['totalCliquesGeral'],0,',','.') }}</div>
      <div class="kpi-delta neutral">acumulado</div>
    </div>
  </div>

  {{-- Gráfico de cliques por dia --}}
  <div class="card" style="padding:0;margin-top:18px;">
    <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);display:flex;align-items:center;justify-content:space-between;">
      <div>
        <h3 style="margin:0;font-size:15px;">Cliques por dia</h3>
        <div style="font-size:11.5px;color:var(--fg-4);margin-top:2px;">Todos os vendedores · últimos {{ $periodo }} dias</div>
      </div>
    </div>
    <div style="padding:16px 20px 20px;">
      <canvas id="chart-dias" height="90"></canvas>
    </div>
  </div>

  {{-- Gráficos lado a lado: Top vendedores + Cliques por hora --}}
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-top:18px;">

    {{-- Top 5 vendedores --}}
    <div class="card" style="padding:0;">
      <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
        <h3 style="margin:0;font-size:15px;">Top 5 vendedores por cliques</h3>
        <div style="font-size:11.5px;color:var(--fg-4);margin-top:2px;">No período selecionado</div>
      </div>
      <div style="padding:16px 20px 20px;">
        <canvas id="chart-top5" height="160"></canvas>
      </div>
    </div>

    {{-- Cliques por hora --}}
    <div class="card" style="padding:0;">
      <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
        <h3 style="margin:0;font-size:15px;">Distribuição por hora do dia</h3>
        <div style="font-size:11.5px;color:var(--fg-4);margin-top:2px;">Quando os links são mais acessados</div>
      </div>
      <div style="padding:16px 20px 20px;">
        <canvas id="chart-horas" height="160"></canvas>
      </div>
    </div>

  </div>

  {{-- Ranking completo --}}
  <div class="card" style="padding:0;margin-top:18px;">
    <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
      <h3 style="margin:0;font-size:15px;">Ranking completo de vendedores</h3>
      <div style="font-size:11.5px;color:var(--fg-4);margin-top:2px;">Cliques, conversões e receita no período</div>
    </div>
    <div class="tbl-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th style="width:40px;">#</th>
            <th>Vendedor</th>
            <th style="text-align:center;">Cliques</th>
            <th style="text-align:center;">Conversões</th>
            <th style="text-align:center;">Taxa</th>
            <th style="text-align:right;">Receita</th>
            <th style="width:160px;">Engajamento</th>
          </tr>
        </thead>
        <tbody>
          @php $maxCliques = $rankingVendedores->max('cliques') ?: 1; @endphp
          @forelse($rankingVendedores as $i => $v)
            @php
              $ini  = strtoupper(substr($v->token, 0, 2));
              $pct  = round(($v->cliques / $maxCliques) * 100);
              $isTop = $i === 0;
            @endphp
            <tr>
              <td>
                @if($i===0) <span style="font-size:16px;">🥇</span>
                @elseif($i===1) <span style="font-size:16px;">🥈</span>
                @elseif($i===2) <span style="font-size:16px;">🥉</span>
                @else <span style="font-family:var(--font-mono);font-size:12px;color:var(--fg-4);">{{ $i+1 }}</span>
                @endif
              </td>
              <td>
                <div style="display:flex;align-items:center;gap:10px;">
                  <div style="width:30px;height:30px;border-radius:50%;background:{{ $isTop ? 'linear-gradient(135deg,#E8B765,#C9921A)' : 'var(--grad-brand)' }};color:#061224;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">{{ $ini }}</div>
                  <span style="font-size:13px;font-weight:{{ $isTop ? '700':'500' }};color:{{ $isTop ? 'var(--gold-400)':'var(--fg-1)' }};">{{ $v->token }}</span>
                </div>
              </td>
              <td style="text-align:center;font-family:var(--font-mono);font-size:13px;">{{ number_format($v->cliques,0,',','.') }}</td>
              <td style="text-align:center;">
                @if($v->conversoes > 0)
                  <span class="badge success">{{ $v->conversoes }}</span>
                @else
                  <span style="font-size:12px;color:var(--fg-4);">—</span>
                @endif
              </td>
              <td style="text-align:center;">
                <span style="font-size:13px;font-family:var(--font-mono);color:{{ $v->taxa > 5 ? 'var(--success)' : 'var(--fg-3)' }};">
                  {{ $v->taxa }}%
                </span>
              </td>
              <td style="text-align:right;font-family:var(--font-mono);font-size:13px;font-weight:600;color:{{ $isTop ? 'var(--gold-400)':'var(--fg-1)' }};">
                @if($v->receita > 0) R$ {{ number_format($v->receita,0,',','.') }}
                @else <span style="color:var(--fg-4);">—</span>
                @endif
              </td>
              <td>
                <div style="height:6px;background:rgba(255,255,255,0.05);border-radius:3px;overflow:hidden;">
                  <div style="height:100%;width:{{ $pct }}%;background:{{ $isTop ? 'linear-gradient(90deg,#E8B765,#F0C97A)' : 'var(--grad-brand)' }};border-radius:3px;"></div>
                </div>
                <div style="font-size:10px;color:var(--fg-4);margin-top:2px;">{{ $pct }}% do líder</div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" style="text-align:center;color:var(--fg-4);padding:40px;font-size:13px;">
                Nenhum clique registrado no período.
              </td>
            </tr>
          @endforelse
        </tbody>
        @if($rankingVendedores->isNotEmpty())
        <tfoot>
          <tr style="border-top:2px solid var(--line-2);">
            <td colspan="2" style="padding:12px 16px;font-size:12px;font-weight:700;color:var(--fg-3);">TOTAL</td>
            <td style="text-align:center;font-family:var(--font-mono);font-weight:700;">{{ number_format($rankingVendedores->sum('cliques'),0,',','.') }}</td>
            <td style="text-align:center;"><span class="badge success">{{ $rankingVendedores->sum('conversoes') }}</span></td>
            <td style="text-align:center;">
              @php
                $totalC = $rankingVendedores->sum('cliques');
                $totalV = $rankingVendedores->sum('conversoes');
                $taxaG  = $totalC > 0 ? round(($totalV / $totalC) * 100, 1) : 0;
              @endphp
              <span style="font-family:var(--font-mono);font-size:13px;color:var(--fg-3);">{{ $taxaG }}%</span>
            </td>
            <td style="text-align:right;font-family:var(--font-mono);font-size:15px;font-weight:800;color:var(--gold-400);">
              R$ {{ number_format($rankingVendedores->sum('receita'),0,',','.') }}
            </td>
            <td></td>
          </tr>
        </tfoot>
        @endif
      </table>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const gridColor = 'rgba(255,255,255,0.04)';
const tickColor = '#8899AA';
const tickFont  = { size: 10 };

// ── Cliques por dia ────────────────────────────────────────────────────────
new Chart(document.getElementById('chart-dias'), {
  type: 'line',
  data: {
    labels: {!! json_encode($grafDiasLabels) !!},
    datasets: [{
      label: 'Cliques',
      data: {!! json_encode($grafDiasCliques) !!},
      borderColor: 'rgba(0,163,255,0.9)',
      backgroundColor: 'rgba(0,163,255,0.08)',
      fill: true,
      tension: 0.4,
      pointRadius: 3,
      pointBackgroundColor: 'rgba(0,163,255,1)',
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { color: gridColor }, ticks: { color: tickColor, font: tickFont, maxTicksLimit: 15 } },
      y: { grid: { color: gridColor }, ticks: { color: tickColor, font: tickFont, stepSize: 1 }, beginAtZero: true }
    }
  }
});

// ── Top 5 vendedores ───────────────────────────────────────────────────────
new Chart(document.getElementById('chart-top5'), {
  type: 'bar',
  data: {
    labels: {!! json_encode($grafTop5Labels) !!},
    datasets: [{
      label: 'Cliques',
      data: {!! json_encode($grafTop5Cliques) !!},
      backgroundColor: [
        'rgba(232,183,101,0.7)',
        'rgba(0,163,255,0.6)',
        'rgba(43,217,161,0.6)',
        'rgba(0,163,255,0.4)',
        'rgba(43,217,161,0.4)',
      ],
      borderRadius: 6,
    }]
  },
  options: {
    responsive: true,
    indexAxis: 'y',
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { color: gridColor }, ticks: { color: tickColor, font: tickFont }, beginAtZero: true },
      y: { grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 11 } } }
    }
  }
});

// ── Cliques por hora ───────────────────────────────────────────────────────
new Chart(document.getElementById('chart-horas'), {
  type: 'bar',
  data: {
    labels: {!! json_encode($grafHorasLabels) !!},
    datasets: [{
      label: 'Cliques',
      data: {!! json_encode($grafHorasCliques) !!},
      backgroundColor: 'rgba(43,217,161,0.5)',
      borderColor: 'rgba(43,217,161,0.9)',
      borderWidth: 1,
      borderRadius: 4,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 9 } } },
      y: { grid: { color: gridColor }, ticks: { color: tickColor, font: tickFont, stepSize: 1 }, beginAtZero: true }
    }
  }
});
</script>
@endpush
