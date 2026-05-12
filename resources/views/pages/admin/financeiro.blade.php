@extends('layouts.admin')
@section('title', 'Financeiro')
@section('section', 'Financeiro')

@section('content')
<div class="page">

  <div class="page-header">
    <div>
      <h1 class="page-title">Financeiro</h1>
      <p class="page-subtitle">MRR, conciliação e fluxo de caixa · integração ASAAS, PIX e Cartão</p>
    </div>
    <div class="page-actions">
      <button class="btn">Maio · 2026</button>
      <button class="btn">Emitir notas</button>
      <button class="btn btn-primary">Conciliar</button>
    </div>
  </div>

  <div class="kpi-row cols-5">
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">MRR</span></div><div class="kpi-value">R$ 284.500</div><div class="kpi-delta positive">↑ 12,7% mês</div></div>
    <div class="kpi-card kpi-gold"><div class="kpi-top"><span class="kpi-label">ARR</span></div><div class="kpi-value" style="color:var(--gold-400);">R$ 3,41M</div><div class="kpi-delta positive">↑ 14,2% proj.</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Lucro líquido</span></div><div class="kpi-value">R$ 186.200</div><div class="kpi-delta positive">↑ 8,1% mês</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">LTV</span></div><div class="kpi-value">R$ 1.842</div><div class="kpi-delta positive">↑ 6,1%</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">CAC</span></div><div class="kpi-value">R$ 184</div><div class="kpi-delta positive">ROI 10x</div></div>
  </div>
  <div class="kpi-row cols-5">
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Inadimplência</span></div><div class="kpi-value">2,4%</div><div class="kpi-delta positive">↓ 0,6%</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Conversão</span></div><div class="kpi-value">5,9%</div><div class="kpi-delta positive">↑ 1,4% checkout</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Chargeback</span></div><div class="kpi-value">0,18%</div><div class="kpi-delta positive">ótimo</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Reembolsos</span></div><div class="kpi-value">R$ 8.420</div><div class="kpi-delta negative">↑ 2,2% mês</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Ticket médio</span></div><div class="kpi-value">R$ 487</div><div class="kpi-delta negative">↓ 1,8%</div></div>
  </div>

  <div class="admin-grid-2-1" style="margin-top:14px;">

    {{-- Transações recentes --}}
    <div class="card" style="padding:0;">
      <div style="padding:16px 20px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--line-2);">
        <div><h3 style="margin:0;font-size:15px;">Transações recentes</h3><div style="font-size:11.5px;color:var(--fg-4);">Últimas movimentações</div></div>
        <button class="btn btn-sm">Ver tudo</button>
      </div>
      <table class="tbl">
        <thead><tr><th>Cliente</th><th>Curso</th><th>Forma</th><th>Status</th><th style="text-align:right;">Valor</th></tr></thead>
        <tbody>
          {{-- TODO: loop real de vendas --}}
          <tr><td colspan="5" style="text-align:center;color:var(--fg-4);padding:32px;font-size:13px;">Conecte o model de vendas/transações.</td></tr>
        </tbody>
      </table>
    </div>

    {{-- Saúde do gateway --}}
    <div class="card">
      <div class="card-h"><div><h3>Saúde do gateway</h3><div class="sub">Live · ASAAS</div></div><span class="live-pill"><span class="lp-dot"></span></span></div>
      <div style="display:flex;flex-direction:column;gap:12px;">
        @foreach([
          ['Aprovação PIX',            '98,7%', 'success', '+0,3pp'],
          ['Aprovação cartão',         '87,4%', 'success', '+1,1pp'],
          ['Tempo médio confirmação',  '1m 24s','success', '-12s'],
          ['Webhooks com falha',       '0,4%',  'warn',    'estável'],
          ['Reservas em análise',      '12',    'neutral', 'fila'],
        ] as [$label, $valor, $status, $delta])
          <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;background:rgba(255,255,255,0.02);border:1px solid var(--line-1);border-radius:10px;">
            <div>
              <div style="font-size:11.5px;color:var(--fg-4);text-transform:uppercase;letter-spacing:0.08em;font-weight:600;">{{ $label }}</div>
              <div style="font-size:14px;font-family:var(--font-display);font-weight:700;color:var(--fg-1);margin-top:3px;">{{ $valor }}</div>
            </div>
            <span class="badge {{ $status }}">{{ $delta }}</span>
          </div>
        @endforeach
      </div>
    </div>
  </div>

</div>
@endsection
