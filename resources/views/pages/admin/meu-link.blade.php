@extends('layouts.admin')
@section('title', 'Meu Link de Divulgação')
@section('section', 'Comercial')

@section('content')
<div class="page">

  <div class="page-header">
    <div>
      <h1 class="page-title">Meu Link de Divulgação</h1>
      <p class="page-subtitle">Compartilhe e acompanhe suas conversões em tempo real</p>
    </div>
  </div>

  {{-- KPIs --}}
  <div class="kpi-row" style="grid-template-columns:repeat(5,1fr);">
    <div class="kpi-card kpi-gold">
      <div class="kpi-top"><span class="kpi-label">Receita total</span></div>
      <div class="kpi-value" style="color:var(--gold-400);">R$ {{ number_format($receitaTotal,0,',','.') }}</div>
      <div class="kpi-delta positive">acumulado</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Receita este mês</span></div>
      <div class="kpi-value">R$ {{ number_format($receitaMes,0,',','.') }}</div>
      <div class="kpi-delta positive">confirmado</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Conversões</span></div>
      <div class="kpi-value">{{ $totalConversoes }}</div>
      <div class="kpi-delta positive">matrículas</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Cliques 30d</span></div>
      <div class="kpi-value">{{ number_format($cliques30d,0,',','.') }}</div>
      <div class="kpi-delta neutral">{{ $cliquesHoje }} hoje</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Taxa de conversão</span></div>
      <div class="kpi-value">{{ $taxaConversao }}%</div>
      <div class="kpi-delta neutral">cliques → matrícula</div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 340px;gap:20px;margin-top:20px;align-items:start;">

    {{-- Coluna principal --}}
    <div style="display:flex;flex-direction:column;gap:18px;">

      {{-- Links --}}
      <div class="card" style="padding:0;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
          <h3 style="margin:0;font-size:15px;">Seus links de divulgação</h3>
          <div style="font-size:12px;color:var(--fg-4);margin-top:2px;">Copie e compartilhe — qualquer compra feita via estes links é registrada na sua carteira</div>
        </div>
        <div style="padding:20px;display:flex;flex-direction:column;gap:14px;">

          {{-- Link principal --}}
          <div>
            <div style="font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--fg-4);margin-bottom:8px;">Link principal (página inicial)</div>
            <div style="display:flex;gap:8px;align-items:center;">
              <div style="flex:1;padding:10px 14px;background:var(--bg-1);border:1px solid var(--line-2);border-radius:var(--r-sm);font-family:var(--font-mono);font-size:12px;color:var(--brand-200);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                {{ $linkBase }}
              </div>
              <button onclick="copiarLink('{{ $linkBase }}', this)"
                      class="btn btn-primary" style="padding:10px 16px;font-size:12px;flex-shrink:0;">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                Copiar
              </button>
            </div>
          </div>

          {{-- Link catálogo --}}
          <div>
            <div style="font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--fg-4);margin-bottom:8px;">Link direto para o catálogo de miniséries</div>
            <div style="display:flex;gap:8px;align-items:center;">
              <div style="flex:1;padding:10px 14px;background:var(--bg-1);border:1px solid var(--line-2);border-radius:var(--r-sm);font-family:var(--font-mono);font-size:12px;color:var(--brand-200);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                {{ $linkCursos }}
              </div>
              <button onclick="copiarLink('{{ $linkCursos }}', this)"
                      class="btn btn-primary" style="padding:10px 16px;font-size:12px;flex-shrink:0;">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                Copiar
              </button>
            </div>
          </div>

          {{-- Alerta sobre o cookie --}}
          <div style="padding:12px 14px;background:rgba(0,163,255,0.06);border:1px solid rgba(0,163,255,0.2);border-radius:var(--r-md);font-size:12px;color:var(--fg-3);">
            <strong style="color:#fff;">Como funciona:</strong> Quando alguém acessa o site pelo seu link, um cookie de <strong style="color:#fff;">30 dias</strong> é gravado no navegador. Se essa pessoa comprar qualquer minisérie dentro desse prazo — mesmo sem clicar no link de novo — a venda é registrada na sua carteira automaticamente.
          </div>
        </div>
      </div>

      {{-- Gráfico de cliques --}}
      <div class="card" style="padding:0;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
          <h3 style="margin:0;font-size:15px;">Cliques — últimos 30 dias</h3>
        </div>
        <div style="padding:16px 20px 20px;">
          <canvas id="chart-cliques" height="100"></canvas>
        </div>
      </div>

      {{-- Últimas conversões --}}
      <div class="card" style="padding:0;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
          <h3 style="margin:0;font-size:15px;">Últimas conversões</h3>
          <div style="font-size:12px;color:var(--fg-4);margin-top:2px;">Matrículas registradas na sua carteira</div>
        </div>
        @forelse($ultimasConversoes as $e)
          @php
            $nome = optional($e->student)->name ?? "Aluno #{$e->student_id}";
            $ini  = strtoupper(substr($nome,0,1));
            $sc   = ['checked'=>['Confirmada','success'],'not_checked'=>['Pendente','warn'],'canceled'=>['Cancelada','danger'],'scheduled_billing'=>['Agendada','neutral']][$e->status] ?? [$e->status,'neutral'];
          @endphp
          <div style="display:flex;align-items:center;gap:12px;padding:10px 20px;border-top:1px solid var(--line-1);">
            <div style="width:32px;height:32px;border-radius:50%;background:var(--grad-brand);color:#061224;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">{{ $ini }}</div>
            <div style="flex:1;min-width:0;">
              <div style="font-size:13px;font-weight:500;color:var(--fg-1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $nome }}</div>
              <div style="font-size:11px;color:var(--fg-4);">{{ optional($e->classes)->title ?? '—' }}</div>
            </div>
            <span class="badge {{ $sc[1] }}">{{ $sc[0] }}</span>
            <div style="text-align:right;flex-shrink:0;">
              <div style="font-family:var(--font-mono);font-size:13px;font-weight:600;color:var(--fg-1);">R$ {{ number_format($e->final_value,0,',','.') }}</div>
              <div style="font-size:10px;color:var(--fg-4);">{{ optional($e->created_at)->format('d/m/Y') }}</div>
            </div>
          </div>
        @empty
          <div style="padding:40px;text-align:center;color:var(--fg-4);font-size:13px;">
            Nenhuma conversão ainda. Compartilhe seu link!
          </div>
        @endforelse
      </div>

    </div>

    {{-- Sidebar: QR Code --}}
    <div style="display:flex;flex-direction:column;gap:14px;position:sticky;top:80px;">

      {{-- QR Code --}}
      <div class="card" style="padding:20px;text-align:center;">
        <div style="font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--fg-4);margin-bottom:14px;">QR Code do seu link</div>
        <div id="qrcode-container" style="display:flex;justify-content:center;margin-bottom:14px;">
          <canvas id="qrcode-canvas" width="180" height="180" style="border-radius:12px;"></canvas>
        </div>
        <div style="font-size:11px;color:var(--fg-4);margin-bottom:14px;">Imprima e distribua em eventos, cursos e reuniões</div>
        <button onclick="baixarQR()" class="btn btn-ghost" style="width:100%;justify-content:center;font-size:12px;">
          <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Baixar QR Code (PNG)
        </button>
      </div>

      {{-- Stats rápidas --}}
      <div class="card" style="padding:16px 18px;">
        <div style="font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--fg-4);margin-bottom:12px;">Estatísticas</div>
        @foreach([
          ['Hoje',      $cliquesHoje, 'cliques'],
          ['7 dias',    $cliques7d,   'cliques'],
          ['30 dias',   $cliques30d,  'cliques'],
          ['Total',     $totalCliques,'cliques acumulados'],
        ] as [$label, $val, $sub])
          <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--line-1);">
            <span style="font-size:12px;color:var(--fg-3);">{{ $label }}</span>
            <div style="text-align:right;">
              <div style="font-family:var(--font-mono);font-size:14px;color:#fff;font-weight:600;">{{ number_format($val,0,',','.') }}</div>
              <div style="font-size:10px;color:var(--fg-4);">{{ $sub }}</div>
            </div>
          </div>
        @endforeach
      </div>

      {{-- Seu token --}}
      <div class="card" style="padding:14px 18px;">
        <div style="font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--fg-4);margin-bottom:6px;">Seu identificador</div>
        <div style="font-family:var(--font-mono);font-size:13px;color:var(--brand-200);padding:8px 10px;background:var(--bg-1);border-radius:8px;text-align:center;">
          ?ref={{ $token }}
        </div>
        <div style="font-size:11px;color:var(--fg-4);margin-top:6px;text-align:center;">Aparece em qualquer URL do site</div>
      </div>

    </div>
  </div>

</div>

{{-- Toast de cópia --}}
<div id="copy-toast"
     style="display:none;position:fixed;bottom:24px;right:24px;background:rgba(43,217,161,0.15);border:1px solid rgba(43,217,161,0.4);color:#6FE6BD;padding:12px 20px;border-radius:12px;font-size:13px;font-weight:600;z-index:9999;box-shadow:0 8px 24px rgba(0,0,0,0.4);">
  ✓ Link copiado!
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>

// ── Gráfico de cliques ────────────────────────────────────────────────────
new Chart(document.getElementById('chart-cliques'), {
  type: 'bar',
  data: {
    labels: {!! json_encode($grafLabels) !!},
    datasets: [{
      label: 'Cliques',
      data: {!! json_encode($grafValores) !!},
      backgroundColor: 'rgba(0,163,255,0.45)',
      borderColor: 'rgba(0,163,255,0.9)',
      borderWidth: 1,
      borderRadius: 5,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#8899AA', font: { size: 10 }, maxTicksLimit: 10 } },
      y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#8899AA', font: { size: 10 }, stepSize: 1 }, beginAtZero: true }
    }
  }
});

// ── QR Code ───────────────────────────────────────────────────────────────
const qrLink = @json($linkBase);
QRCode.toCanvas(document.getElementById('qrcode-canvas'), qrLink, {
  width: 180,
  color: { dark: '#ffffff', light: '#0D1526' },
  margin: 2,
}, function (err) { if (err) console.error(err); });

function baixarQR() {
  QRCode.toDataURL(qrLink, { width: 400, color: { dark: '#ffffff', light: '#0D1526' }, margin: 2 }, function(err, url) {
    if (err) return;
    const a = document.createElement('a');
    a.href     = url;
    a.download = 'meu-link-unyflex.png';
    a.click();
  });
}

// ── Copiar link ───────────────────────────────────────────────────────────
let toastTimer;
function copiarLink(url, btn) {
  navigator.clipboard.writeText(url).then(() => {
    const orig = btn.innerHTML;
    btn.textContent = '✓ Copiado!';
    setTimeout(() => btn.innerHTML = orig, 2000);

    const toast = document.getElementById('copy-toast');
    toast.style.display = 'block';
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.style.display = 'none', 2500);
  });
}
</script>
@endpush
