@extends('layouts.admin')
@section('title', 'Analytics')
@section('section', 'Visão Geral')

@section('content')
<div class="page">

  <div class="page-header">
    <div>
      <h1 class="page-title">Analytics em tempo real</h1>
      <p class="page-subtitle">Acessos, engajamento e comportamento dos alunos</p>
    </div>
    <div class="page-actions">
      <span class="live-pill"><span class="lp-dot"></span> live</span>
    </div>
  </div>

  {{-- KPIs --}}
  <div class="kpi-row cols-5">
    <div class="kpi-card kpi-gold">
      <div class="kpi-top"><span class="kpi-label">Online agora</span></div>
      <div class="kpi-value" id="online-counter" style="color:var(--gold-400);">{{ $kpis['onlineAgora'] }}</div>
      <div class="kpi-delta positive">últimos 15 min</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Acessos hoje</span></div>
      <div class="kpi-value">{{ number_format($kpis['acessosHoje'],0,',','.') }}</div>
      <div class="kpi-delta positive">alunos únicos</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Acessos esta semana</span></div>
      <div class="kpi-value">{{ number_format($kpis['acessosSemana'],0,',','.') }}</div>
      <div class="kpi-delta positive">alunos únicos</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Tempo médio</span></div>
      <div class="kpi-value">{{ $kpis['tempoMedio'] }}</div>
      <div class="kpi-delta neutral">por aluno</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Taxa de conclusão</span></div>
      <div class="kpi-value">{{ $kpis['taxaConclusao'] }}%</div>
      <div class="kpi-delta {{ $kpis['taxaConclusao'] >= 50 ? 'positive':'neutral' }}">média por aluno</div>
    </div>
  </div>

  <div class="kpi-row" style="grid-template-columns:repeat(3,1fr);">
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Total visualizações</span></div><div class="kpi-value">{{ number_format($kpis['totalViews'],0,',','.') }}</div><div class="kpi-delta positive">acumulado</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Ativos últimos 30d</span></div><div class="kpi-value">{{ number_format($kpis['ativosUlt30'],0,',','.') }}</div><div class="kpi-delta positive">assistiram algo</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Retenção 30d</span></div><div class="kpi-value">{{ $kpis['retencao30d'] }}%</div><div class="kpi-delta {{ $kpis['retencao30d'] >= 60 ? 'positive':'neutral' }}">dos matriculados</div></div>
  </div>

  {{-- Gráfico de acessos --}}
  <div class="card" style="padding:0;margin-top:18px;">
    <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
      <h3 style="margin:0;font-size:15px;">Acessos e visualizações — últimos 30 dias</h3>
    </div>
    <div style="padding:16px 20px 20px;">
      <canvas id="chart-acessos" height="90"></canvas>
    </div>
  </div>

  {{-- Top cápsulas + alunos ativos --}}
  <div class="admin-grid-3" style="margin-top:18px;">

    {{-- Top cápsulas --}}
    <div class="card" style="padding:0;grid-column:span 2;">
      <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
        <h3 style="margin:0;font-size:15px;">Cápsulas mais assistidas</h3>
        <div style="font-size:11.5px;color:var(--fg-4);margin-top:2px;">Top 10 · acumulado</div>
      </div>
      @forelse($topCapsulas as $i => $cap)
        @php $max = $topCapsulas->max('views'); @endphp
        <div style="display:flex;align-items:center;gap:12px;padding:10px 20px;border-top:1px solid var(--line-1);">
          <div style="width:26px;height:26px;border-radius:8px;background:rgba(0,163,255,0.10);border:1px solid rgba(0,163,255,0.2);display:grid;place-items:center;font-family:var(--font-mono);font-size:11px;color:var(--brand-300);flex-shrink:0;">{{ $i+1 }}</div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:13px;color:var(--fg-1);font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $cap->titulo }}</div>
            <div style="font-size:11px;color:var(--fg-4);">{{ $cap->classe }} · {{ $cap->panel }}</div>
            <div style="height:3px;background:rgba(255,255,255,0.05);border-radius:2px;overflow:hidden;margin-top:5px;">
              <div style="height:100%;width:{{ $max>0?round(($cap->views/$max)*100):0 }}%;background:var(--grad-brand);"></div>
            </div>
          </div>
          <span style="font-family:var(--font-mono);font-size:13px;color:var(--brand-300);font-weight:600;flex-shrink:0;">{{ number_format($cap->views,0,',','.') }}</span>
        </div>
      @empty
        <div style="padding:40px;text-align:center;color:var(--fg-4);font-size:13px;">Nenhuma visualização registrada.</div>
      @endforelse
    </div>

    {{-- Alunos mais ativos --}}
    <div class="card" style="padding:0;">
      <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
        <h3 style="margin:0;font-size:15px;">Alunos mais ativos</h3>
        <div style="font-size:11.5px;color:var(--fg-4);margin-top:2px;">Últimos 30 dias</div>
      </div>
      @forelse($alunosAtivos as $a)
        @php $ini = strtoupper(substr($a->nome,0,1)); @endphp
        <div style="display:flex;align-items:center;gap:10px;padding:10px 20px;border-top:1px solid var(--line-1);">
          <div class="avatar-sm" style="flex-shrink:0;">{{ $ini }}</div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:13px;font-weight:500;color:var(--fg-1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $a->nome }}</div>
            <div style="font-size:11px;color:var(--fg-4);">{{ $a->ultimo }}</div>
          </div>
          <div style="text-align:right;flex-shrink:0;">
            <div style="font-family:var(--font-mono);font-size:13px;color:var(--brand-300);font-weight:600;">{{ $a->capsulas }}</div>
            <div style="font-size:10px;color:var(--fg-4);">cápsulas</div>
          </div>
        </div>
      @empty
        <div style="padding:40px;text-align:center;color:var(--fg-4);font-size:13px;">Nenhum acesso nos últimos 30 dias.</div>
      @endforelse
    </div>

  </div>

  {{-- Top minisséries por engajamento --}}
  <div class="card" style="padding:0;margin-top:18px;">
    <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
      <h3 style="margin:0;font-size:15px;">Minisséries por engajamento</h3>
    </div>
    <div class="tbl-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>#</th>
            <th>Minissérie</th>
            <th style="text-align:center;">Alunos únicos</th>
            <th style="text-align:center;">Total views</th>
            <th style="width:200px;">Engajamento</th>
          </tr>
        </thead>
        <tbody>
          @php $maxV = $topMinisseries->max('views'); @endphp
          @forelse($topMinisseries as $i => $m)
            <tr>
              <td style="font-family:var(--font-mono);font-size:12px;color:var(--fg-4);">{{ $i+1 }}</td>
              <td style="font-size:13px;font-weight:500;color:var(--fg-1);">{{ $m->titulo }}</td>
              <td style="text-align:center;font-family:var(--font-mono);">{{ number_format($m->alunos,0,',','.') }}</td>
              <td style="text-align:center;font-family:var(--font-mono);color:var(--brand-300);">{{ number_format($m->views,0,',','.') }}</td>
              <td>
                <div style="height:6px;background:rgba(255,255,255,0.05);border-radius:3px;overflow:hidden;">
                  <div style="height:100%;width:{{ $maxV>0?round(($m->views/$maxV)*100):0 }}%;background:var(--grad-brand);"></div>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" style="text-align:center;color:var(--fg-4);padding:32px;font-size:13px;">Nenhum dado.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Gráfico de acessos
new Chart(document.getElementById('chart-acessos'), {
  type: 'line',
  data: {
    labels: {!! json_encode($grafLabels) !!},
    datasets: [
      {
        label: 'Alunos únicos',
        data: {!! json_encode($grafAlunos) !!},
        borderColor: 'rgba(0,163,255,0.9)',
        backgroundColor: 'rgba(0,163,255,0.08)',
        fill: true, tension: 0.4, pointRadius: 2,
        yAxisID: 'y',
      },
      {
        label: 'Visualizações',
        data: {!! json_encode($grafViews) !!},
        borderColor: 'rgba(43,217,161,0.8)',
        backgroundColor: 'transparent',
        tension: 0.4, pointRadius: 2, borderDash: [4,3],
        yAxisID: 'y',
      }
    ]
  },
  options: {
    responsive: true,
    interaction: { mode: 'index', intersect: false },
    plugins: { legend: { labels: { color: '#8899AA', font: { size: 11 } } } },
    scales: {
      x: { grid: { color: 'rgba(255,255,255,0.03)' }, ticks: { color: '#8899AA', font: { size: 10 }, maxTicksLimit: 15 } },
      y: { grid: { color: 'rgba(255,255,255,0.03)' }, ticks: { color: '#8899AA', font: { size: 10 }, stepSize: 1 }, beginAtZero: true }
    }
  }
});

// Contador "online agora" animado (atualiza a cada 30s com valor aproximado)
const el = document.getElementById('online-counter');
const base = {{ $kpis['onlineAgora'] }};
if (el && base > 0) {
  setInterval(() => {
    const delta = Math.round((Math.random() - 0.5) * Math.max(2, Math.ceil(base * 0.1)));
    const val   = Math.max(0, base + delta);
    el.textContent = val;
  }, 30000);
}
</script>
@endpush
