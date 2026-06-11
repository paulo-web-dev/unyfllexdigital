<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Proposta {{ $proposta['numero'] }} — Unyflex Digital</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }

  :root{
    --brand:#0072FF;
    --brand-2:#00A3FF;
    --ink:#0f1b2d;
    --ink-soft:#475569;
    --line:#e2e8f0;
    --green:#16a34a;
    --red:#dc2626;
    --bg-soft:#f8fafc;
  }

  body{
    font-family:'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
    background:#5b6577;
    color:var(--ink);
    padding:24px 0;
    -webkit-print-color-adjust:exact;
    print-color-adjust:exact;
  }

  .page{
    width:210mm;
    min-height:297mm;
    margin:0 auto;
    background:#fff;
    box-shadow:0 8px 40px rgba(0,0,0,.3);
    padding:0;
    position:relative;
    overflow:hidden;
  }

  /* ── Barra de ações (some na impressão) ── */
  .toolbar{
    width:210mm;
    margin:0 auto 16px;
    display:flex;
    gap:10px;
    justify-content:flex-end;
  }
  .btn-print{
    background:var(--brand);
    color:#fff;
    border:none;
    padding:12px 22px;
    border-radius:8px;
    font-size:14px;
    font-weight:700;
    cursor:pointer;
    display:inline-flex;
    align-items:center;
    gap:8px;
    box-shadow:0 4px 14px rgba(0,114,255,.4);
  }
  .btn-print:hover{ background:#005fd6; }
  .btn-back{
    background:#fff;
    color:var(--ink);
    border:1px solid var(--line);
    padding:12px 22px;
    border-radius:8px;
    font-size:14px;
    font-weight:600;
    cursor:pointer;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
  }

  /* ── Header ── */
  .header{
    background:linear-gradient(135deg, var(--brand), var(--brand-2));
    color:#fff;
    padding:32px 40px;
    position:relative;
    overflow:hidden;
  }
  .header::after{
    content:'';
    position:absolute;
    right:-60px; top:-60px;
    width:200px; height:200px;
    background:rgba(255,255,255,.08);
    border-radius:50%;
  }
  .header-top{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    position:relative;
    z-index:1;
  }
  .logo{
    font-size:22px;
    font-weight:800;
    letter-spacing:-.02em;
  }
  .logo span{ opacity:.85; font-weight:400; }
  .badge-prop{
    text-align:right;
    font-size:12px;
  }
  .badge-prop .num{
    font-size:15px;
    font-weight:700;
    margin-bottom:2px;
  }
  .header h1{
    font-size:26px;
    font-weight:800;
    margin-top:24px;
    position:relative;
    z-index:1;
  }
  .header .sub{
    font-size:13px;
    opacity:.9;
    margin-top:4px;
    position:relative;
    z-index:1;
  }

  /* ── Corpo ── */
  .body{ padding:32px 40px; }

  .meta-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:0;
    border:1px solid var(--line);
    border-radius:10px;
    overflow:hidden;
    margin-bottom:28px;
  }
  .meta-cell{ padding:14px 18px; }
  .meta-cell + .meta-cell{ border-left:1px solid var(--line); }
  .meta-row{ border-top:1px solid var(--line); }
  .meta-label{
    font-size:10px;
    font-weight:700;
    letter-spacing:.1em;
    text-transform:uppercase;
    color:var(--ink-soft);
    margin-bottom:3px;
  }
  .meta-value{ font-size:14px; font-weight:600; color:var(--ink); }

  .section-title{
    font-size:13px;
    font-weight:800;
    letter-spacing:.06em;
    text-transform:uppercase;
    color:var(--brand);
    margin-bottom:14px;
    display:flex;
    align-items:center;
    gap:8px;
  }
  .section-title::before{
    content:'';
    width:4px; height:16px;
    background:var(--brand);
    border-radius:2px;
  }

  /* ── Tabela de cursos ── */
  .cursos-table{
    width:100%;
    border-collapse:collapse;
    margin-bottom:28px;
  }
  .cursos-table th{
    background:var(--bg-soft);
    font-size:10px;
    font-weight:700;
    letter-spacing:.08em;
    text-transform:uppercase;
    color:var(--ink-soft);
    text-align:left;
    padding:10px 14px;
    border-bottom:2px solid var(--line);
  }
  .cursos-table td{
    padding:12px 14px;
    font-size:13px;
    border-bottom:1px solid var(--line);
    color:var(--ink);
  }
  .cursos-table .c-num{
    width:32px;
    color:var(--brand);
    font-weight:700;
  }
  .cursos-table .c-hours{
    text-align:right;
    color:var(--ink-soft);
    white-space:nowrap;
  }

  /* ── Box de valores ── */
  .pricing{
    background:var(--bg-soft);
    border:1px solid var(--line);
    border-radius:12px;
    padding:24px 28px;
    margin-bottom:24px;
  }
  .price-line{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:8px 0;
  }
  .price-line + .price-line{ border-top:1px solid var(--line); }
  .price-label{ font-size:13px; color:var(--ink-soft); }
  .price-label small{ display:block; font-size:11px; color:#94a3b8; }

  .price-de{
    font-size:18px;
    font-weight:700;
    color:var(--red);
    text-decoration:line-through;
  }
  .price-por{
    font-size:30px;
    font-weight:800;
    color:var(--green);
  }
  .price-economia{
    font-size:14px;
    font-weight:700;
    color:var(--green);
  }
  .desconto-badge{
    display:inline-block;
    background:var(--green);
    color:#fff;
    font-size:12px;
    font-weight:700;
    padding:3px 10px;
    border-radius:999px;
    margin-left:8px;
  }

  .parcelas-box{
    background:linear-gradient(135deg, rgba(0,114,255,.08), rgba(0,163,255,.04));
    border:1px solid rgba(0,114,255,.2);
    border-radius:10px;
    padding:16px 20px;
    text-align:center;
    margin-top:16px;
  }
  .parcelas-box .big{
    font-size:24px;
    font-weight:800;
    color:var(--brand);
  }
  .parcelas-box small{ font-size:12px; color:var(--ink-soft); }

  /* ── Incluso ── */
  .incluso-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:8px 20px;
    margin-bottom:24px;
  }
  .incluso-item{
    display:flex;
    align-items:center;
    gap:8px;
    font-size:13px;
    color:var(--ink);
  }
  .check{
    width:18px; height:18px;
    background:var(--green);
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-shrink:0;
  }
  .check svg{ width:11px; height:11px; }

  .obs{
    background:#fffbeb;
    border:1px solid #fde68a;
    border-radius:10px;
    padding:14px 18px;
    font-size:13px;
    color:#78350f;
    line-height:1.55;
    margin-bottom:24px;
  }

  /* ── Footer ── */
  .footer{
    border-top:2px solid var(--line);
    padding:20px 40px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    font-size:11px;
    color:var(--ink-soft);
  }
  .footer .validade{
    background:#fef2f2;
    border:1px solid #fecaca;
    color:var(--red);
    padding:6px 14px;
    border-radius:8px;
    font-weight:700;
    font-size:12px;
  }

  /* ── Impressão ── */
  @media print{
    body{ background:#fff; padding:0; }
    .toolbar{ display:none !important; }
    .page{ box-shadow:none; width:100%; min-height:auto; }
    @page{ size:A4; margin:0; }
  }
</style>
</head>
<body>

  {{-- Barra de ações --}}
  <div class="toolbar">
    <a href="{{ route('admin.proposta') }}" class="btn-back">← Nova proposta</a>
    <button class="btn-print" onclick="window.print()">
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
      Imprimir / Salvar PDF
    </button>
  </div>

  <div class="page">

    {{-- HEADER --}}
    <div class="header">
      <div class="header-top">
        <div class="logo">UNYFLEX <span>DIGITAL</span></div>
        <div class="badge-prop">
          <div class="num">{{ $proposta['numero'] }}</div>
          <div>Emitida em {{ $proposta['data'] }}</div>
        </div>
      </div>
      <h1>Proposta Comercial</h1>
      <div class="sub">Capacitação prática para servidores públicos · Certificado reconhecido pelo MEC</div>
    </div>

    {{-- CORPO --}}
    <div class="body">

      {{-- Dados --}}
      <div class="meta-grid">
        <div class="meta-cell">
          <div class="meta-label">Cliente</div>
          <div class="meta-value">{{ $proposta['cliente_nome'] ?: '—' }}</div>
        </div>
        <div class="meta-cell">
          <div class="meta-label">Órgão / Entidade</div>
          <div class="meta-value">{{ $proposta['cliente_orgao'] ?: '—' }}</div>
        </div>
        <div class="meta-cell meta-row">
          <div class="meta-label">Consultor responsável</div>
          <div class="meta-value">{{ $proposta['vendedor'] }}</div>
        </div>
        <div class="meta-cell meta-row">
          <div class="meta-label">Número de alunos</div>
          <div class="meta-value">{{ $proposta['num_alunos'] }} {{ $proposta['num_alunos'] > 1 ? 'alunos' : 'aluno' }}</div>
        </div>
      </div>

      {{-- Cursos --}}
      <div class="section-title">Minisséries inclusas</div>
      <table class="cursos-table">
        <thead>
          <tr>
            <th class="c-num">#</th>
            <th>Minisérie</th>
            <th class="c-hours">Carga horária</th>
          </tr>
        </thead>
        <tbody>
          @foreach($proposta['cursos'] as $i => $curso)
          <tr>
            <td class="c-num">{{ $i + 1 }}</td>
            <td>{{ $curso->title }}</td>
            <td class="c-hours">{{ $curso->workload }}h</td>
          </tr>
          @endforeach
        </tbody>
      </table>

      {{-- Valores --}}
      <div class="section-title">Investimento</div>
      <div class="pricing">
        <div class="price-line">
          <div class="price-label">
            Valor por aluno
            <small>{{ $proposta['num_alunos'] }} {{ $proposta['num_alunos'] > 1 ? 'alunos' : 'aluno' }}</small>
          </div>
          <div style="text-align:right;">
            <span class="price-de">R$ {{ number_format($proposta['preco_cheio'], 2, ',', '.') }}</span>
            &nbsp;→&nbsp;
            <span style="font-size:16px;font-weight:700;color:var(--green);">R$ {{ number_format($proposta['preco_final'], 2, ',', '.') }}</span>
          </div>
        </div>

        <div class="price-line">
          <div class="price-label">Valor total <small>De:</small></div>
          <div class="price-de">R$ {{ number_format($proposta['total_cheio'], 2, ',', '.') }}</div>
        </div>

        <div class="price-line">
          <div class="price-label">
            <strong style="color:var(--ink);font-size:15px;">Valor total da proposta</strong>
            <small>Por: condição especial</small>
          </div>
          <div>
            <span class="price-por">R$ {{ number_format($proposta['total_final'], 2, ',', '.') }}</span>
            @if($proposta['desconto_pct'] > 0)
            <span class="desconto-badge">-{{ $proposta['desconto_pct'] }}%</span>
            @endif
          </div>
        </div>

        @if($proposta['economia'] > 0)
        <div class="price-line">
          <div class="price-label">Sua economia</div>
          <div class="price-economia">R$ {{ number_format($proposta['economia'], 2, ',', '.') }}</div>
        </div>
        @endif

        @if($proposta['parcelas'] > 1)
        <div class="parcelas-box">
          <div class="big">{{ $proposta['parcelas'] }}x de R$ {{ number_format($proposta['valor_parcela'], 2, ',', '.') }}</div>
          <small>sem juros no cartão · ou R$ {{ number_format($proposta['total_final'], 2, ',', '.') }} à vista</small>
        </div>
        @endif
      </div>

      {{-- Incluso --}}
      <div class="section-title">O que está incluso</div>
      <div class="incluso-grid">
        @foreach([
          'Acesso por 12 meses a todas as cápsulas',
          'Certificado reconhecido pelo MEC',
          'Versão em podcast de cada aula',
          'Materiais, modelos e checklists',
          'Suporte pedagógico durante o acesso',
          'Emissão de nota fiscal',
        ] as $item)
        <div class="incluso-item">
          <span class="check">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          </span>
          {{ $item }}
        </div>
        @endforeach
      </div>

      {{-- Observações --}}
      @if($proposta['observacoes'])
      <div class="obs">
        <strong>Observações:</strong> {{ $proposta['observacoes'] }}
      </div>
      @endif

    </div>

    {{-- FOOTER --}}
    <div class="footer">
      <div>
        <strong>Unyflex Digital</strong> · Faculdade Unypublica · Reconhecido pelo MEC<br>
        WhatsApp: (41) 8898-0259 · unyflex.com.br
      </div>
      <div class="validade">Válida até {{ $proposta['validade'] }}</div>
    </div>

  </div>

  <script>
    // Atalho: dispara impressão se vier com ?print=1
    if (new URLSearchParams(window.location.search).get('print') === '1') {
      window.addEventListener('load', () => setTimeout(() => window.print(), 400));
    }
  </script>

</body>
</html>
