@extends('layouts.admin')
@section('title', 'Funil de Conversão')
@section('section', 'Analytics')

@section('content')
<div class="page">

  <div class="page-header">
    <div>
      <h1 class="page-title">Funil de Conversão</h1>
      <p class="page-subtitle">Rastreamento completo da jornada do visitante até a compra</p>
    </div>
    <div class="page-actions">
      <form method="GET" action="{{ route('admin.funil') }}" style="display:flex;gap:8px;align-items:center;">
        <select name="periodo" class="filter-select" style="height:36px;" onchange="this.form.submit()">
          @foreach([7=>'Últimos 7 dias',15=>'Últimos 15 dias',30=>'Últimos 30 dias',60=>'Últimos 60 dias',90=>'Últimos 90 dias'] as $v=>$l)
            <option value="{{ $v }}" {{ $periodo==$v?'selected':'' }}>{{ $l }}</option>
          @endforeach
        </select>
        <select name="origem" class="filter-select" style="height:36px;" onchange="this.form.submit()">
          <option value="todos" {{ $origemFiltro==='todos'?'selected':'' }}>Todas origens</option>
          <option value="organico" {{ $origemFiltro==='organico'?'selected':'' }}>Orgânico</option>
          <option value="referral" {{ $origemFiltro==='referral'?'selected':'' }}>Referral</option>
        </select>
      </form>
    </div>
  </div>

  {{-- ══ FUNIL VISUAL ════════════════════════════════════════════════════ --}}
  <div class="card" style="padding:28px;margin-bottom:18px;">
    <div style="font-size:11px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--fg-4);margin-bottom:24px;">
      Funil completo · {{ $periodo }} dias
    </div>
    <div style="display:flex;flex-direction:column;gap:6px;">
      @php
        $cores = ['#0072FF','#0098FF','#00B4FF','#00C9A7','#00D68F','#6FE6BD'];
        $maxEtapa = $funil->max('total') ?: 1;
      @endphp
      @foreach($funil as $i => $etapa)
        @php
          $pct       = round(($etapa->total / $maxEtapa) * 100);
          $pctAnterior = $i > 0 ? ($funil[$i-1]->total > 0 ? round(($etapa->total / $funil[$i-1]->total) * 100, 1) : 0) : 100;
          $abandono  = $i > 0 ? ($funil[$i-1]->total - $etapa->total) : 0;
          $cor       = $cores[$i] ?? '#0072FF';
          $labels    = ['visita'=>'Visitou o site','visualizou'=>'Visualizou minisérie','carrinho'=>'Adicionou ao carrinho','checkout'=>'Entrou no checkout','pagamento'=>'Iniciou pagamento','converteu'=>'Compra finalizada'];
          $icones    = ['visita'=>'eye','visualizou'=>'play-circle','carrinho'=>'shopping-cart','checkout'=>'credit-card','pagamento'=>'zap','converteu'=>'check-circle'];
        @endphp
        <div style="position:relative;">

          {{-- Abandono entre etapas --}}
          @if($i > 0 && $abandono > 0)
          <div style="display:flex;align-items:center;gap:8px;padding:4px 0 4px 20px;margin-bottom:2px;">
            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="#ff6b6b" stroke-width="2" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
            <span style="font-size:11px;color:#ff6b6b;">{{ number_format($abandono,0,',','.') }} abandonaram ({{ 100-$pctAnterior }}%)</span>
          </div>
          @endif

          <div style="display:grid;grid-template-columns:180px 1fr 80px 80px;align-items:center;gap:12px;">
            {{-- Label --}}
            <div style="display:flex;align-items:center;gap:8px;">
              <i data-lucide="{{ $icones[$etapa->etapa] ?? 'circle' }}" style="width:14px;height:14px;stroke:{{ $cor }};fill:none;stroke-width:1.75;flex-shrink:0;"></i>
              <span style="font-size:13px;color:var(--fg-2);">{{ $labels[$etapa->etapa] ?? $etapa->etapa }}</span>
            </div>

            {{-- Barra --}}
            <div style="height:36px;background:rgba(255,255,255,0.04);border-radius:6px;overflow:hidden;position:relative;">
              <div style="height:100%;width:{{ $pct }}%;background:{{ $cor }};border-radius:6px;opacity:0.85;transition:width 0.6s ease;display:flex;align-items:center;padding-left:12px;">
                @if($pct > 15)
                <span style="font-size:12px;font-weight:700;color:#fff;">{{ number_format($etapa->total,0,',','.') }}</span>
                @endif
              </div>
              @if($pct <= 15)
              <span style="position:absolute;left:calc({{ $pct }}% + 8px);top:50%;transform:translateY(-50%);font-size:12px;font-weight:700;color:var(--fg-1);">{{ number_format($etapa->total,0,',','.') }}</span>
              @endif
            </div>

            {{-- % do total --}}
            <div style="text-align:right;font-family:var(--font-mono);font-size:12px;color:var(--fg-3);">{{ $pct }}%</div>

            {{-- % da etapa anterior --}}
            <div style="text-align:right;font-family:var(--font-mono);font-size:12px;color:{{ $i===0?'var(--fg-4)':($pctAnterior>=50?'var(--success)':'var(--warning)') }};">
              {{ $i === 0 ? '—' : $pctAnterior.'%' }}
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <div style="display:flex;justify-content:flex-end;gap:24px;margin-top:16px;padding-top:12px;border-top:1px solid var(--line-1);">
      <div style="font-size:11px;color:var(--fg-4);">
        % do total de visitantes
      </div>
      <div style="font-size:11px;color:var(--fg-4);">
        % da etapa anterior
      </div>
    </div>
  </div>

  {{-- ══ KPIs ════════════════════════════════════════════════════════════ --}}
  <div class="kpi-row" style="grid-template-columns:repeat(5,1fr);margin-bottom:18px;">
    @php
      $visitas     = $funil->firstWhere('etapa','visita')?->total      ?? 0;
      $carrinhos   = $funil->firstWhere('etapa','carrinho')?->total    ?? 0;
      $checkouts   = $funil->firstWhere('etapa','checkout')?->total    ?? 0;
      $convertidos = $funil->firstWhere('etapa','converteu')?->total   ?? 0;
      $txCarrinho  = $visitas    > 0 ? round(($carrinhos   / $visitas)    * 100, 1) : 0;
      $txCheckout  = $carrinhos  > 0 ? round(($checkouts   / $carrinhos)  * 100, 1) : 0;
      $txConversao = $visitas    > 0 ? round(($convertidos / $visitas)    * 100, 1) : 0;
    @endphp
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Visitantes</span></div>
      <div class="kpi-value">{{ number_format($visitas,0,',','.') }}</div>
      <div class="kpi-delta neutral">sessões únicas</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Adicionaram carrinho</span></div>
      <div class="kpi-value">{{ number_format($carrinhos,0,',','.') }}</div>
      <div class="kpi-delta {{ $txCarrinho>=5?'positive':'neutral' }}">{{ $txCarrinho }}% dos visitantes</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Chegaram ao checkout</span></div>
      <div class="kpi-value">{{ number_format($checkouts,0,',','.') }}</div>
      <div class="kpi-delta {{ $txCheckout>=50?'positive':'neutral' }}">{{ $txCheckout }}% dos carrinhos</div>
    </div>
    <div class="kpi-card kpi-gold">
      <div class="kpi-top"><span class="kpi-label">Converteram</span></div>
      <div class="kpi-value" style="color:var(--gold-400);">{{ number_format($convertidos,0,',','.') }}</div>
      <div class="kpi-delta positive">compras finalizadas</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Taxa de conversão</span></div>
      <div class="kpi-value">{{ $txConversao }}%</div>
      <div class="kpi-delta neutral">visita → compra</div>
    </div>
  </div>

  {{-- ══ GRÁFICOS ════════════════════════════════════════════════════════ --}}
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px;">

    {{-- Eventos por dia --}}
    <div class="card" style="padding:0;">
      <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
        <h3 style="margin:0;font-size:15px;">Eventos por dia</h3>
        <div style="font-size:11.5px;color:var(--fg-4);margin-top:2px;">Todas as etapas · {{ $periodo }} dias</div>
      </div>
      <div style="padding:16px 20px 20px;"><canvas id="chart-dias" height="160"></canvas></div>
    </div>

    {{-- Orgânico vs Referral --}}
    <div class="card" style="padding:0;">
      <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
        <h3 style="margin:0;font-size:15px;">Origem dos visitantes</h3>
        <div style="font-size:11.5px;color:var(--fg-4);margin-top:2px;">Orgânico vs Referral por etapa</div>
      </div>
      <div style="padding:16px 20px 20px;"><canvas id="chart-origem" height="160"></canvas></div>
    </div>

  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px;">

    {{-- Top miniséries adicionadas ao carrinho --}}
    <div class="card" style="padding:0;">
      <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
        <h3 style="margin:0;font-size:15px;">Top miniséries no carrinho</h3>
        <div style="font-size:11.5px;color:var(--fg-4);margin-top:2px;">Mais adicionadas sem converter</div>
      </div>
      <div style="padding:0;">
        @forelse($topCarrinho as $i => $item)
          @php
            $nome = \App\Models\Classes::find($item->classes_id)?->title ?? "Minisérie #{$item->classes_id}";
          @endphp
          <div style="display:flex;align-items:center;gap:12px;padding:10px 20px;border-top:1px solid var(--line-1);">
            <span style="font-family:var(--font-mono);font-size:11px;color:var(--fg-4);width:20px;">{{ $i+1 }}</span>
            <div style="flex:1;font-size:13px;color:var(--fg-1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $nome }}</div>
            <span class="badge neutral">{{ $item->total }} add</span>
          </div>
        @empty
          <div style="padding:24px;text-align:center;color:var(--fg-4);font-size:13px;">Nenhum dado ainda</div>
        @endforelse
      </div>
    </div>

    {{-- Top cidades --}}
    <div class="card" style="padding:0;">
      <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
        <h3 style="margin:0;font-size:15px;">Top cidades</h3>
        <div style="font-size:11.5px;color:var(--fg-4);margin-top:2px;">Origem geográfica dos visitantes</div>
      </div>
      <div style="padding:0;">
        @forelse($topCidades as $i => $c)
          <div style="display:flex;align-items:center;gap:12px;padding:10px 20px;border-top:1px solid var(--line-1);">
            <span style="font-family:var(--font-mono);font-size:11px;color:var(--fg-4);width:20px;">{{ $i+1 }}</span>
            <div style="flex:1;">
              <div style="font-size:13px;color:var(--fg-1);">{{ $c->cidade ?? 'Desconhecida' }}</div>
              <div style="font-size:11px;color:var(--fg-4);">{{ $c->estado ?? '—' }} · {{ $c->pais ?? '—' }}</div>
            </div>
            <span class="badge neutral">{{ number_format($c->total,0,',','.') }}</span>
          </div>
        @empty
          <div style="padding:24px;text-align:center;color:var(--fg-4);font-size:13px;">Nenhum dado ainda</div>
        @endforelse
      </div>
    </div>

  </div>

  {{-- ══ ABANDONO POR REFERRAL ══════════════════════════════════════════ --}}
  <div class="card" style="padding:0;margin-bottom:18px;">
    <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
      <h3 style="margin:0;font-size:15px;">Funil por origem</h3>
      <div style="font-size:11.5px;color:var(--fg-4);margin-top:2px;">Comparação orgânico vs cada vendedor</div>
    </div>
    <div class="tbl-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>Origem / Vendedor</th>
            <th style="text-align:center;">Visitantes</th>
            <th style="text-align:center;">Carrinho</th>
            <th style="text-align:center;">Checkout</th>
            <th style="text-align:center;">Converteu</th>
            <th style="text-align:center;">Taxa conv.</th>
          </tr>
        </thead>
        <tbody>
          @forelse($funilPorOrigem as $orig)
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:8px;">
                  @if($orig->origem === 'organico')
                    <span style="width:8px;height:8px;border-radius:50%;background:var(--brand-300);display:inline-block;"></span>
                    <span style="font-size:13px;color:var(--fg-1);">Orgânico</span>
                  @else
                    <span style="width:8px;height:8px;border-radius:50%;background:var(--gold-400);display:inline-block;"></span>
                    <span style="font-size:13px;color:var(--gold-400);">{{ $orig->referral ?? 'Referral' }}</span>
                  @endif
                </div>
              </td>
              <td style="text-align:center;font-family:var(--font-mono);font-size:13px;">{{ number_format($orig->visitas,0,',','.') }}</td>
              <td style="text-align:center;font-family:var(--font-mono);font-size:13px;">{{ number_format($orig->carrinhos,0,',','.') }}</td>
              <td style="text-align:center;font-family:var(--font-mono);font-size:13px;">{{ number_format($orig->checkouts,0,',','.') }}</td>
              <td style="text-align:center;">
                @if($orig->convertidos > 0)
                  <span class="badge success">{{ $orig->convertidos }}</span>
                @else
                  <span style="font-size:12px;color:var(--fg-4);">—</span>
                @endif
              </td>
              <td style="text-align:center;font-family:var(--font-mono);font-size:13px;color:{{ $orig->taxa>=2?'var(--success)':'var(--fg-3)' }};">
                {{ $orig->taxa }}%
              </td>
            </tr>
          @empty
            <tr><td colspan="6" style="text-align:center;color:var(--fg-4);padding:32px;">Nenhum dado no período</td></tr>
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
const gc = 'rgba(255,255,255,0.04)';
const tc = '#8899AA';
const tf = { size: 10 };

// ── Eventos por dia ────────────────────────────────────────────────────────
new Chart(document.getElementById('chart-dias'), {
  type: 'line',
  data: {
    labels: {!! json_encode($grafDiasLabels) !!},
    datasets: [
      { label:'Visitas',    data:{!! json_encode($grafDias['visita']    ?? []) !!}, borderColor:'#0072FF', backgroundColor:'rgba(0,114,255,0.08)', fill:true, tension:0.4, pointRadius:2 },
      { label:'Carrinho',   data:{!! json_encode($grafDias['carrinho']  ?? []) !!}, borderColor:'#00C9A7', backgroundColor:'rgba(0,201,167,0.08)', fill:true, tension:0.4, pointRadius:2 },
      { label:'Checkout',   data:{!! json_encode($grafDias['checkout']  ?? []) !!}, borderColor:'#FFB547', backgroundColor:'rgba(255,181,71,0.08)', fill:true, tension:0.4, pointRadius:2 },
      { label:'Converteu',  data:{!! json_encode($grafDias['converteu'] ?? []) !!}, borderColor:'#6FE6BD', backgroundColor:'rgba(111,230,189,0.1)', fill:true, tension:0.4, pointRadius:3, borderWidth:2 },
    ]
  },
  options: {
    responsive:true,
    interaction:{ mode:'index', intersect:false },
    plugins:{ legend:{ labels:{ color:tc, font:tf, boxWidth:12 } } },
    scales:{
      x:{ grid:{color:gc}, ticks:{color:tc,font:tf,maxTicksLimit:12} },
      y:{ grid:{color:gc}, ticks:{color:tc,font:tf,stepSize:1}, beginAtZero:true }
    }
  }
});

// ── Orgânico vs Referral por etapa ────────────────────────────────────────
new Chart(document.getElementById('chart-origem'), {
  type: 'bar',
  data: {
    labels: ['Visita','Visualizou','Carrinho','Checkout','Pagamento','Converteu'],
    datasets: [
      { label:'Orgânico', data:{!! json_encode($grafOrigem['organico'] ?? [0,0,0,0,0,0]) !!}, backgroundColor:'rgba(0,114,255,0.6)', borderRadius:4 },
      { label:'Referral', data:{!! json_encode($grafOrigem['referral'] ?? [0,0,0,0,0,0]) !!}, backgroundColor:'rgba(232,183,101,0.7)', borderRadius:4 },
    ]
  },
  options: {
    responsive:true,
    plugins:{ legend:{ labels:{ color:tc, font:tf, boxWidth:12 } } },
    scales:{
      x:{ grid:{color:gc}, ticks:{color:tc,font:tf}, stacked:false },
      y:{ grid:{color:gc}, ticks:{color:tc,font:tf}, beginAtZero:true }
    }
  }
});
</script>
@endpush
