@extends('layouts.admin')
@section('title', 'Painel administrativo')
@section('section', 'Visão Geral')

@section('content')
<div class="page">

  <div class="page-header">
    <div>
      <h1 class="page-title">Olá, {{ auth()->user()->first_name }} 👋</h1>
      <p class="page-subtitle">Resumo da operação Unyflex Digital · {{ now()->isoFormat('D [de] MMMM [de] YYYY') }}</p>
    </div>
    <div class="page-actions">
      <span class="live-pill"><span class="lp-dot"></span> em tempo real</span>
      <button class="btn">
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Últimos 30 dias
      </button>
      <button class="btn">
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Exportar
      </button>
      <a href="{{ route('admin.matriculas') }}" class="btn btn-primary">
        <svg class="ico" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12 5v14M5 12h14"/></svg>
        Nova matrícula
      </a>
    </div>
  </div>

  {{-- KPIs linha 1 --}}
  <div class="kpi-row cols-5">
    <div class="kpi-card">
      <div class="kpi-top">
        <span class="kpi-label">Alunos ativos</span>
        <span class="kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
      </div>
      <div class="kpi-value">12.847</div>
      <div class="kpi-delta positive">↑ 8,4% vs. mês ant.</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Faturamento mensal</span><span class="kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></span></div>
      <div class="kpi-value">R$ 1.284.500</div>
      <div class="kpi-delta positive">↑ 12,7% MRR</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Matrículas hoje</span><span class="kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg></span></div>
      <div class="kpi-value">47</div>
      <div class="kpi-delta positive">↑ 4,2% vs. ontem</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Ticket médio</span><span class="kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg></span></div>
      <div class="kpi-value">R$ 487</div>
      <div class="kpi-delta negative">↓ 1,8% 30 dias</div>
    </div>
    <div class="kpi-card kpi-gold">
      <div class="kpi-top"><span class="kpi-label">LTV</span><span class="kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></span></div>
      <div class="kpi-value" style="color:var(--gold-400);">R$ 1.842</div>
      <div class="kpi-delta positive">↑ 6,1% 12 meses</div>
    </div>
  </div>

  {{-- KPIs linha 2 --}}
  <div class="kpi-row cols-5">
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Online agora</span></div><div class="kpi-value" id="online-counter">312</div><div class="kpi-delta positive">ao vivo</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Taxa conclusão</span></div><div class="kpi-value">68,4%</div><div class="kpi-delta positive">↑ 3,2% cápsulas</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Churn</span></div><div class="kpi-value">2,4%</div><div class="kpi-delta positive">↓ 0,6% mês</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Retenção 90d</span></div><div class="kpi-value">87%</div><div class="kpi-delta positive">↑ 1,4%</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Horas assistidas</span></div><div class="kpi-value">48,2k h</div><div class="kpi-delta positive">↑ 9,8% mês</div></div>
  </div>

  {{-- Grid principal --}}
  <div class="admin-grid-3-1" style="margin-top:18px;">

    {{-- Alertas --}}
    <div class="card" style="padding:0;">
      <div style="padding:16px 20px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--line-2);">
        <div><h3 style="margin:0;font-size:15px;">Alertas do sistema</h3><div style="font-size:11.5px;color:var(--fg-4);margin-top:2px;">Eventos críticos · 24h</div></div>
      </div>
      @foreach([
        ['warn',    'Inadimplência acima de 2%',       '218 alunos · ação recomendada',   'há 12 min'],
        ['success', '47 matrículas aprovadas hoje',     'Meta diária atingida',            'há 1h'],
        ['info',    'Novo curso publicado',             'Auditoria com Dashboards · 6 cap', 'há 3h'],
        ['danger',  'Webhook ASAAS com falha',          '3 retentativas · monitorando',    'há 4h'],
        ['success', 'Backup concluído',                 'Banco + storage · 2.4 GB',        'há 6h'],
      ] as [$tipo, $titulo, $sub, $quando])
        @php $colors = ['warn'=>'#FFB547','danger'=>'#FF5C7A','info'=>'#00A3FF','success'=>'#2BD9A1']; @endphp
        <div style="display:flex;align-items:flex-start;gap:10px;padding:10px 20px;border-top:1px solid var(--line-1);">
          <div style="width:28px;height:28px;border-radius:8px;background:{{ $colors[$tipo] }}22;color:{{ $colors[$tipo] }};display:grid;place-items:center;flex-shrink:0;font-size:14px;">●</div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:12.5px;color:var(--fg-1);font-weight:500;">{{ $titulo }}</div>
            <div style="font-size:11px;color:var(--fg-4);margin-top:2px;">{{ $sub }}</div>
          </div>
          <span style="font-size:10.5px;color:var(--fg-4);font-family:var(--font-mono);white-space:nowrap;">{{ $quando }}</span>
        </div>
      @endforeach
    </div>

    {{-- Últimas vendas --}}
    <div class="card" style="padding:0;">
      <div style="padding:16px 20px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--line-2);">
        <div><h3 style="margin:0;font-size:15px;">Últimas vendas</h3><div style="font-size:11.5px;color:var(--fg-4);margin-top:2px;">Aprovações em tempo real</div></div>
        <span class="live-pill"><span class="lp-dot"></span></span>
      </div>
      @foreach([
        ['Ana Lima',         'Pregão eletrônico',       497,  '2 min'],
        ['Carlos Mendes',    'Patrimônio com IA',        997,  '8 min'],
        ['Fernanda Rocha',   'Lei 14.133 na prática',    497,  '15 min'],
        ['Rodrigo Pinto',    'Gestão de Contratos',      697,  '28 min'],
        ['Juliana Saraiva',  'LGPD p/ Servidores',       297,  '41 min'],
      ] as [$nome, $curso, $valor, $ha])
        <div style="display:flex;align-items:center;gap:10px;padding:10px 20px;border-top:1px solid var(--line-1);">
          <div class="avatar-sm">{{ strtoupper(substr($nome,0,1)) }}</div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:13px;color:var(--fg-1);font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $nome }}</div>
            <div style="font-size:11.5px;color:var(--fg-4);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $curso }}</div>
          </div>
          <div style="text-align:right;">
            <div style="font-family:var(--font-mono);font-size:13px;color:var(--fg-1);font-weight:600;">R$ {{ number_format($valor,0,',','.') }}</div>
            <div style="font-size:10.5px;color:var(--fg-4);">há {{ $ha }}</div>
          </div>
        </div>
      @endforeach
    </div>

    {{-- Atalhos rápidos --}}
    <div class="card">
      <div class="card-h"><div><h3>Atalhos</h3><div class="sub">Ações frequentes</div></div></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
        @foreach([
          [route('admin.alunos'),    'Alunos',       '#00A3FF'],
          [route('admin.matriculas'),'Matrículas',   '#2BD9A1'],
          [route('admin.cursos'),    'Cursos',        '#E8B765'],
          [route('admin.financeiro'),'Financeiro',   '#A18CD1'],
          [route('admin.analytics'), 'Analytics',    '#00C2FF'],
          [route('admin.suporte'),   'Suporte',       '#FF8A9F'],
        ] as [$url, $label, $cor])
          <a href="{{ $url }}" class="btn" style="justify-content:center;background:{{ $cor }}14;border-color:{{ $cor }}30;color:{{ $cor }};text-decoration:none;">{{ $label }}</a>
        @endforeach
      </div>
    </div>

  </div>

</div>
@endsection

@push('scripts')
<script>
  // Contador "online agora" animado
  const el = document.getElementById('online-counter');
  if (el) {
    let v = 312;
    setInterval(() => {
      v = Math.max(280, Math.min(360, v + Math.round((Math.random()-.5)*8)));
      el.textContent = v;
    }, 2400);
  }
</script>
@endpush
