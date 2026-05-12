@extends('layouts.admin')
@section('title', 'Analytics em tempo real')
@section('section', 'Visão Geral')

@section('content')
<div class="page">

  <div class="page-header">
    <div>
      <h1 class="page-title">Analytics em tempo real</h1>
      <p class="page-subtitle">Acessos, retenção e comportamento · atualiza a cada 2s</p>
    </div>
    <div class="page-actions">
      <span class="live-pill"><span class="lp-dot"></span> live</span>
      <button class="btn">Hoje</button>
      <button class="btn">Exportar</button>
    </div>
  </div>

  <div class="kpi-row cols-5">
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Online agora</span></div><div class="kpi-value" id="online-an">312</div><div class="kpi-delta positive">ao vivo</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Acessos hoje</span></div><div class="kpi-value">8.420</div><div class="kpi-delta positive">↑ 11,4% vs. ontem</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Tempo médio</span></div><div class="kpi-value">14m 22s</div><div class="kpi-delta positive">↑ 3,8% por sessão</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Retenção 30d</span></div><div class="kpi-value">74%</div><div class="kpi-delta positive">↑ 2,1%</div></div>
    <div class="kpi-card kpi-gold"><div class="kpi-top"><span class="kpi-label">Conclusão cápsulas</span></div><div class="kpi-value" style="color:var(--gold-400);">68%</div><div class="kpi-delta positive">↑ 4,2%</div></div>
  </div>

  <div class="admin-grid-3" style="margin-top:14px;">

    {{-- Top cápsulas --}}
    <div class="card" style="padding:0;">
      <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
        <h3 style="margin:0;font-size:15px;">Cápsulas mais assistidas</h3>
        <div style="font-size:11.5px;color:var(--fg-4);margin-top:2px;">Top 5 · hoje</div>
      </div>
      @foreach([
        ['1.2 Mapeamento da frota',      'Frotas Públicas com IA',   '2.841'],
        ['2.3 Manutenção preditiva',     'Frotas Públicas com IA',   '2.104'],
        ['1.1 Pregão eletrônico',        'Pregão Eletrônico',         '1.822'],
        ['3.4 Lei 14.133 na prática',    'Contratos Adm.',            '1.640'],
        ['1.3 Inventário público',       'Patrimônio Público',        '1.488'],
      ] as $i => [$titulo, $curso, $views])
        <div style="padding:10px 20px;border-top:1px solid var(--line-1);display:flex;align-items:center;gap:10px;">
          <div style="width:24px;height:24px;border-radius:6px;background:rgba(0,163,255,0.10);color:var(--brand-300);display:grid;place-items:center;font-family:var(--font-mono);font-size:11px;font-weight:600;">{{ $i+1 }}</div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:13px;color:var(--fg-1);font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $titulo }}</div>
            <div style="font-size:11px;color:var(--fg-4);">{{ $curso }}</div>
          </div>
          <span style="font-family:var(--font-mono);font-size:12px;color:var(--brand-300);font-weight:600;">{{ $views }}</span>
        </div>
      @endforeach
    </div>

    {{-- Alunos mais ativos --}}
    <div class="card" style="padding:0;">
      <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
        <h3 style="margin:0;font-size:15px;">Alunos mais ativos</h3>
        <div style="font-size:11.5px;color:var(--fg-4);margin-top:2px;">Sequência atual · 7 dias</div>
      </div>
      {{-- TODO: loop real com $alunosAtivos --}}
      <div style="padding:40px;text-align:center;color:var(--fg-4);font-size:13px;">
        Conecte a query de alunos mais ativos.
      </div>
    </div>

    {{-- Localização --}}
    <div class="card" style="padding:0;">
      <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
        <h3 style="margin:0;font-size:15px;">Acessos por estado</h3>
        <div style="font-size:11.5px;color:var(--fg-4);margin-top:2px;">Agora</div>
      </div>
      @foreach([
        ['SP','São Paulo',       1842, 32],
        ['RJ','Rio de Janeiro',   984, 17],
        ['MG','Minas Gerais',     748, 13],
        ['DF','Distrito Federal', 612, 11],
        ['BA','Bahia',            428,  7],
      ] as [$uf, $nome, $views, $pct])
        <div style="padding:10px 20px;border-top:1px solid var(--line-1);">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
            <span style="display:flex;align-items:center;gap:8px;font-size:13px;">
              <span style="width:24px;padding:2px 4px;text-align:center;border-radius:4px;background:rgba(0,163,255,0.10);color:var(--brand-300);font-family:var(--font-mono);font-size:10px;font-weight:700;">{{ $uf }}</span>
              <span style="color:var(--fg-2);">{{ $nome }}</span>
            </span>
            <span style="font-family:var(--font-mono);font-size:12px;color:var(--fg-1);font-weight:600;">{{ number_format($views,0,',','.') }}</span>
          </div>
          <div style="height:4px;background:rgba(255,255,255,0.05);border-radius:99px;">
            <div style="height:100%;width:{{ $pct*3 }}%;background:linear-gradient(90deg,#00C2FF,#0072FF);border-radius:99px;"></div>
          </div>
        </div>
      @endforeach
    </div>

  </div>

</div>
@endsection

@push('scripts')
<script>
  const el = document.getElementById('online-an');
  if (el) {
    let v = 312;
    setInterval(() => {
      v = Math.max(280, Math.min(360, v + Math.round((Math.random()-.5)*10)));
      el.textContent = v;
    }, 1800);
  }
</script>
@endpush
