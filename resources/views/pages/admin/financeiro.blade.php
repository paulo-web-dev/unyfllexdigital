@extends('layouts.admin')
@section('title', 'Financeiro')
@section('section', 'Financeiro')

@section('content')
<div class="page">

  <div class="page-header">
    <div>
      <h1 class="page-title">Financeiro</h1>
      <p class="page-subtitle">Receita, vendas e conciliação · minisséries express</p>
    </div>
    <div class="page-actions">
      {{-- Seletor de mês --}}
      <form method="GET" action="{{ route('admin.financeiro') }}" style="display:flex;gap:8px;align-items:center;">
        <select name="mes" class="filter-select" style="height:36px;" onchange="this.form.submit()">
          @foreach($mesesDisponiveis as $m)
            <option value="{{ $m['value'] }}" {{ $mes === $m['value'] ? 'selected':'' }}>{{ $m['label'] }}</option>
          @endforeach
        </select>
      </form>
      <a href="{{ route('admin.matriculas') }}" class="btn" style="text-decoration:none;">Ver matrículas</a>
    </div>
  </div>

  {{-- KPIs --}}
  <div class="kpi-row cols-5">
    <div class="kpi-card kpi-gold">
      <div class="kpi-top"><span class="kpi-label">Receita confirmada</span></div>
      <div class="kpi-value" style="color:var(--gold-400);">R$ {{ number_format($kpis['receitaBruta'],2,',','.') }}</div>
      <div class="kpi-delta {{ $kpis['receitaMes'] >= $kpis['receitaMesAnt'] ? 'positive' : 'negative' }}">
        {{ $kpis['varReceita'] >= 0 ? '↑':'↓' }} {{ number_format(abs($kpis['varReceita']),1) }}% vs mês ant.
      </div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Vendas confirmadas</span></div>
      <div class="kpi-value">{{ $kpis['qtdVendas'] }}</div>
      <div class="kpi-delta positive">matrículas pagas</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Ticket médio</span></div>
      <div class="kpi-value">R$ {{ number_format($kpis['ticketMedio'],2,',','.') }}</div>
      <div class="kpi-delta neutral">por matrícula</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Receita pendente</span></div>
      <div class="kpi-value">R$ {{ number_format($kpis['receitaPendente'],2,',','.') }}</div>
      <div class="kpi-delta negative">a confirmar</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Descontos concedidos</span></div>
      <div class="kpi-value">R$ {{ number_format($kpis['totalDesconto'],2,',','.') }}</div>
      <div class="kpi-delta neutral">{{ $kpis['qtdCanceladas'] }} canceladas</div>
    </div>
  </div>

  {{-- Gráfico + por forma --}}
  <div class="admin-grid-2-1" style="margin-top:18px;">

    {{-- Gráfico de receita por dia --}}
    <div class="card" style="padding:0;">
      <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
        <h3 style="margin:0;font-size:15px;">Receita por dia</h3>
        <div style="font-size:11.5px;color:var(--fg-4);margin-top:2px;">Matrículas confirmadas · {{ \Carbon\Carbon::parse($mes.'-01')->isoFormat('MMMM [de] YYYY') }}</div>
      </div>
      <div style="padding:16px 20px 20px;">
        <canvas id="chart-receita" height="110"></canvas>
      </div>
    </div>

    {{-- Por forma de pagamento --}}
    <div class="card" style="padding:0;">
      <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
        <h3 style="margin:0;font-size:15px;">Por forma de pagamento</h3>
      </div>
      @forelse($porForma as $f)
        @php $max = $porForma->max('total'); @endphp
        <div style="padding:10px 20px;border-top:1px solid var(--line-1);">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;">
            <span style="font-size:13px;color:var(--fg-1);">{{ $f->payment_method ?: 'Não informado' }}</span>
            <div style="text-align:right;">
              <div style="font-family:var(--font-mono);font-size:12px;color:var(--brand-300);font-weight:600;">R$ {{ number_format($f->total,0,',','.') }}</div>
              <div style="font-size:10px;color:var(--fg-4);">{{ $f->qtd }} venda(s)</div>
            </div>
          </div>
          <div style="height:4px;background:rgba(255,255,255,0.05);border-radius:2px;overflow:hidden;">
            <div style="height:100%;width:{{ $max > 0 ? round(($f->total/$max)*100) : 0 }}%;background:var(--grad-brand);"></div>
          </div>
        </div>
      @empty
        <div style="padding:32px;text-align:center;color:var(--fg-4);font-size:13px;">Nenhuma venda neste período.</div>
      @endforelse
    </div>

  </div>

  {{-- Por minissérie --}}
  @if($porCurso->isNotEmpty())
  <div class="card" style="padding:0;margin-top:18px;">
    <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
      <h3 style="margin:0;font-size:15px;">Receita por minissérie</h3>
    </div>
    <div class="tbl-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>Minissérie</th>
            <th style="text-align:center;">Matrículas</th>
            <th style="text-align:right;">Receita</th>
            <th style="text-align:right;">Ticket médio</th>
            <th style="width:180px;"></th>
          </tr>
        </thead>
        <tbody>
          @php $maxR = $porCurso->max('total'); @endphp
          @foreach($porCurso as $c)
            <tr>
              <td style="font-size:13px;font-weight:500;color:var(--fg-1);">{{ optional($c->classes)->title ?? "Curso #{$c->classes_id}" }}</td>
              <td style="text-align:center;font-family:var(--font-mono);">{{ $c->qtd }}</td>
              <td style="text-align:right;font-family:var(--font-mono);color:var(--fg-1);font-weight:600;">R$ {{ number_format($c->total,0,',','.') }}</td>
              <td style="text-align:right;font-family:var(--font-mono);color:var(--fg-3);">R$ {{ number_format($c->qtd > 0 ? $c->total/$c->qtd : 0,0,',','.') }}</td>
              <td>
                <div style="height:4px;background:rgba(255,255,255,0.05);border-radius:2px;overflow:hidden;">
                  <div style="height:100%;width:{{ $maxR > 0 ? round(($c->total/$maxR)*100) : 0 }}%;background:var(--grad-brand);"></div>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endif

  {{-- Transações --}}
  <div class="card" style="padding:0;margin-top:18px;">
    <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
      <h3 style="margin:0;font-size:15px;">Todas as transações do período</h3>
    </div>
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
            <th>Data</th>
          </tr>
        </thead>
        <tbody>
          @forelse($transacoes as $t)
            @php
              $sc = ['checked'=>['Confirmada','success'],'not_checked'=>['Pendente','warn'],'canceled'=>['Cancelada','danger'],'scheduled_billing'=>['Agendada','neutral']][$t->status] ?? [$t->status,'neutral'];
            @endphp
            <tr>
              <td><span style="font-family:var(--font-mono);font-size:11px;color:var(--fg-4);">#{{ $t->id }}</span></td>
              <td>
                <div style="font-size:13px;font-weight:500;color:var(--fg-1);">{{ optional($t->student)->name ?? '—' }}</div>
                <div style="font-size:11px;color:var(--fg-4);">{{ optional($t->student)->email }}</div>
              </td>
              <td style="font-size:13px;color:var(--fg-2);max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ optional($t->classes)->title ?? '—' }}</td>
              <td><span class="badge {{ $sc[1] }}">{{ $sc[0] }}</span></td>
              <td style="font-size:12px;color:var(--fg-2);">{{ $t->payment_method ?: '—' }}</td>
              <td style="text-align:right;font-family:var(--font-mono);font-size:13px;font-weight:600;">R$ {{ number_format($t->final_value,2,',','.') }}</td>
              <td style="font-size:12px;color:var(--fg-3);">{{ optional($t->created_at)->format('d/m/Y') }}</td>
            </tr>
          @empty
            <tr><td colspan="7" style="text-align:center;color:var(--fg-4);padding:32px;font-size:13px;">Nenhuma transação neste período.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div style="padding:10px 16px;border-top:1px solid var(--line-2);">
      {{ $transacoes->links() }}
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('chart-receita'), {
  type: 'bar',
  data: {
    labels: {!! json_encode($diasLabels) !!},
    datasets: [
      {
        label: 'Receita (R$)',
        data: {!! json_encode($diasReceita) !!},
        backgroundColor: 'rgba(43,217,161,0.45)',
        borderColor: 'rgba(43,217,161,0.9)',
        borderWidth: 1,
        borderRadius: 5,
        yAxisID: 'y',
      },
      {
        label: 'Vendas',
        data: {!! json_encode($diasQtd) !!},
        type: 'line',
        borderColor: 'rgba(0,163,255,0.8)',
        backgroundColor: 'transparent',
        pointBackgroundColor: 'rgba(0,163,255,1)',
        tension: 0.4,
        yAxisID: 'y2',
      }
    ]
  },
  options: {
    responsive: true,
    interaction: { mode: 'index', intersect: false },
    plugins: { legend: { labels: { color: '#8899AA', font: { size: 11 } } } },
    scales: {
      x:  { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#8899AA', font: { size: 10 } } },
      y:  { position: 'left',  grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#8899AA', font: { size: 10 }, callback: v => 'R$ '+v.toLocaleString('pt-BR') }, beginAtZero: true },
      y2: { position: 'right', grid: { drawOnChartArea: false }, ticks: { color: '#8899AA', font: { size: 10 }, stepSize: 1 }, beginAtZero: true }
    }
  }
});
</script>
@endpush
