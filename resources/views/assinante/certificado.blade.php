<!DOCTYPE html>
{{-- Certificado do painel — página standalone de impressão (A4 paisagem). O PDF é
     gerado pelo próprio navegador (Imprimir → Salvar como PDF); sem dependência nova
     além do gerador de QR via CDN (sem ele, o quadro do QR simplesmente não aparece). --}}
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Certificado — {{ $titulo }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --navy: #01050E;          /* identidade Unyflex */
      --azul: #0088F4;
      --azul-2: #4DB3FF;
      --cinza: #EEF2F7;         /* cinza claro */
      --tinta: #0F172A;
      --suave: #5B6B82;
      --linha: #D9E2EE;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { background: #0B1426; font-family: 'Inter', system-ui, sans-serif; color: var(--tinta); }

    /* ── Folha A4 paisagem (297 × 210 mm) ─────────────────────────────── */
    .folha {
      width: 297mm; height: 210mm; margin: 24px auto 0;
      background: #fff; display: flex; overflow: hidden; position: relative;
      box-shadow: 0 24px 60px rgba(0,0,0,.45);
      -webkit-print-color-adjust: exact; print-color-adjust: exact;
    }

    /* ── Faixa institucional (esquerda) ───────────────────────────────── */
    .faixa {
      width: 84mm; height: 100%; background: var(--navy); color: #fff;
      padding: 14mm 10mm 12mm; display: flex; flex-direction: column; position: relative; overflow: hidden;
    }
    .faixa::before {   /* malha sutil */
      content: ''; position: absolute; inset: 0; opacity: .35;
      background-image: linear-gradient(rgba(77,179,255,.10) 1px, transparent 1px), linear-gradient(90deg, rgba(77,179,255,.10) 1px, transparent 1px);
      background-size: 9mm 9mm;
    }
    .faixa::after {    /* brilho azul no topo */
      content: ''; position: absolute; left: -30mm; top: -40mm; width: 120mm; height: 120mm; border-radius: 50%;
      background: radial-gradient(circle, rgba(0,136,244,.35), transparent 62%);
    }
    .faixa > * { position: relative; z-index: 1; }
    .faixa__logo { width: 34mm; height: 34mm; border-radius: 50%; display: block; }
    .faixa__inst { margin-top: 8mm; }
    .faixa__inst b { display: block; font-family: 'Sora', sans-serif; font-weight: 800; font-size: 13pt; letter-spacing: .02em; }
    .faixa__inst b em { font-style: normal; color: var(--azul-2); }
    .faixa__inst span { display: block; margin-top: 2mm; font-size: 8.2pt; line-height: 1.5; color: #B7C4D6; }
    .faixa__inst span strong { color: #fff; font-weight: 600; }

    .faixa__aut { margin-top: auto; }
    .faixa__aut small { display: block; font-size: 7pt; letter-spacing: .18em; text-transform: uppercase; color: var(--azul-2); font-weight: 700; }
    .faixa__qr { margin-top: 3mm; width: 26mm; height: 26mm; background: #fff; padding: 1.6mm; border-radius: 2mm; }
    .faixa__qr svg { width: 100%; height: 100%; display: block; }
    .faixa__qr:empty { display: none; }
    .faixa__cod { margin-top: 3mm; font-family: ui-monospace, 'Cascadia Mono', Consolas, monospace; font-size: 6.9pt; letter-spacing: 0; color: #E6EEF8; word-break: break-all; line-height: 1.45; }
    .faixa__url { margin-top: 1.5mm; font-size: 7.4pt; color: #B7C4D6; line-height: 1.45; word-break: break-all; }
    .faixa__num { margin-top: 4mm; padding-top: 3mm; border-top: 1px solid rgba(255,255,255,.14); font-size: 7.6pt; color: #B7C4D6; }
    .faixa__num b { color: #fff; font-weight: 600; }

    /* ── Corpo (direita) ──────────────────────────────────────────────── */
    .corpo { flex: 1; padding: 14mm 16mm 12mm 16mm; display: flex; flex-direction: column; position: relative; }
    .corpo::before {   /* filete superior nas cores da marca */
      content: ''; position: absolute; left: 0; right: 0; top: 0; height: 2.2mm;
      background: linear-gradient(90deg, var(--azul), var(--azul-2) 60%, var(--cinza));
    }
    .eyebrow { display: flex; justify-content: space-between; align-items: center; gap: 8mm; }
    .eyebrow span { font-size: 7.6pt; letter-spacing: .2em; text-transform: uppercase; color: var(--azul); font-weight: 700; }
    .eyebrow em { font-style: normal; font-size: 7.6pt; color: var(--suave); background: var(--cinza); padding: 1.2mm 3mm; border-radius: 999px; white-space: nowrap; }

    h1 { margin-top: 5mm; font-family: 'Sora', sans-serif; font-weight: 800; font-size: 30pt; letter-spacing: .12em; color: var(--navy); line-height: 1; }
    h1 small { display: block; margin-top: 1.5mm; font-size: 9.5pt; letter-spacing: .16em; font-weight: 600; color: var(--suave); }

    .miolo { flex: 1; display: flex; flex-direction: column; justify-content: center; padding: 5mm 0 4mm; }
    .certifica { font-size: 10.5pt; color: var(--suave); }
    .aluno {
      margin-top: 2mm; font-family: 'Sora', sans-serif; font-weight: 700; font-size: 24pt; color: var(--azul);
      line-height: 1.15; letter-spacing: -.01em;
    }
    .cpf { margin-top: 1.2mm; font-size: 9pt; color: var(--suave); }
    .texto { margin-top: 5mm; font-size: 10.5pt; line-height: 1.6; color: var(--tinta); max-width: 165mm; }
    .texto strong { font-family: 'Sora', sans-serif; font-weight: 700; color: var(--navy); }

    .dados { margin-top: 7mm; display: grid; grid-template-columns: repeat(4, 1fr); gap: 3mm; }
    .dado { background: var(--cinza); border-radius: 2.5mm; padding: 3mm 4mm; }
    .dado small { display: block; font-size: 6.8pt; letter-spacing: .14em; text-transform: uppercase; color: var(--suave); font-weight: 700; }
    .dado b { display: block; margin-top: 1mm; font-family: 'Sora', sans-serif; font-weight: 700; font-size: 11.5pt; color: var(--navy); }

    .conteudo { margin-top: 6mm; }
    .conteudo small { display: block; font-size: 6.8pt; letter-spacing: .14em; text-transform: uppercase; color: var(--suave); font-weight: 700; }
    .conteudo ol { margin-top: 1.5mm; columns: 2; column-gap: 8mm; list-style: none; counter-reset: aula; }
    .conteudo li { font-size: 8.4pt; line-height: 1.45; color: var(--tinta); break-inside: avoid; padding-left: 5mm; position: relative; counter-increment: aula; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .conteudo li::before { content: counter(aula, decimal-leading-zero); position: absolute; left: 0; color: var(--azul); font-weight: 700; font-size: 7.4pt; top: .3mm; }
    .conteudo li.mais { color: var(--suave); font-style: italic; }

    .rodape { margin-top: 0; display: flex; align-items: flex-end; justify-content: space-between; gap: 10mm; padding-top: 4mm; }
    .assinatura { text-align: center; min-width: 62mm; }
    .assinatura i { display: block; height: 0; border-top: 1px solid var(--navy); margin-bottom: 1.6mm; }
    .assinatura b { display: block; font-size: 8.4pt; color: var(--navy); font-weight: 700; }
    .assinatura span { display: block; font-size: 7.4pt; color: var(--suave); }
    .legal { font-size: 6.9pt; line-height: 1.5; color: var(--suave); max-width: 108mm; }

    /* ── Ações (só na tela) ───────────────────────────────────────────── */
    .acoes { width: 297mm; margin: 14px auto 40px; display: flex; gap: 12px; justify-content: center; }
    .acoes a, .acoes button {
      font: 600 14px 'Inter', sans-serif; padding: 11px 22px; border-radius: 999px;
      cursor: pointer; text-decoration: none; border: 1px solid transparent;
    }
    .acoes button { background: var(--azul); color: #fff; }
    .acoes a { background: transparent; color: #cfe3ff; border-color: #3b4c66; }

    @media print {
      @page { size: A4 landscape; margin: 0; }
      html, body { background: #fff; width: 297mm; height: 210mm; overflow: hidden; }
      .folha { margin: 0; box-shadow: none; page-break-after: avoid; page-break-inside: avoid; }
      .acoes { display: none; }
    }
  </style>
</head>
<body>

  <div class="folha">

    <aside class="faixa">
      <img class="faixa__logo" src="{{ asset('img/logo-unyflex.png') }}" alt="Unyflex Digital">
      <div class="faixa__inst">
        <b>UNYFLEX <em>DIGITAL</em></b>
        <span>Plataforma de capacitação para o setor público da <strong>Faculdade Unypública</strong>, instituição de ensino superior reconhecida pelo MEC.</span>
      </div>

      <div class="faixa__aut">
        <small>Autenticidade</small>
        @if($token)
          <div class="faixa__qr" id="qr" data-url="{{ $urlValidacao }}"></div>
          <div class="faixa__cod">{{ $token }}</div>
          <div class="faixa__url">{{ preg_replace('#^https?://#', '', $urlValidacao) }}</div>
        @else
          <div class="faixa__url">Código de autenticidade indisponível nesta emissão.</div>
        @endif
        <div class="faixa__num">
          @if($numero) Certificado nº <b>{{ $numero }}</b> · @endif
          Emitido em <b>{{ $emitidoEm->format('d/m/Y') }}</b>
        </div>
      </div>
    </aside>

    <main class="corpo">
      <div class="eyebrow">
        <span>Certificado de conclusão</span>
        <em>{{ $tipoTurma }} · Educação a distância</em>
      </div>

      <h1>CERTIFICADO<small>Curso de capacitação profissional</small></h1>

      <div class="miolo">
      <p class="certifica">A Unyflex Digital certifica que</p>
      <p class="aluno">{{ $aluno }}</p>
      @if($cpf)<p class="cpf">CPF {{ $cpf }}</p>@endif

      <p class="texto">
        concluiu com aproveitamento o curso <strong>{{ $titulo }}</strong>,
        ofertado pela Unyflex Digital na modalidade de educação a distância,
        com carga horária de <strong>{{ $horas }} horas</strong>, tendo sido aprovado(a) na avaliação final.
      </p>

      <div class="dados">
        <div class="dado"><small>Carga horária</small><b>{{ $horas }} horas</b></div>
        <div class="dado"><small>Conclusão</small><b>{{ $concluidoEm->format('d/m/Y') }}</b></div>
        <div class="dado"><small>Avaliação</small><b>Aprovado(a)</b></div>
        <div class="dado"><small>Modalidade</small><b>EAD</b></div>
      </div>

      @if($aulas)
        <div class="conteudo">
          <small>Conteúdo programático</small>
          <ol>
            @foreach(array_slice($aulas, 0, 8) as $aula)
              <li title="{{ $aula }}">{{ $aula }}</li>
            @endforeach
            @if(count($aulas) > 8)
              <li class="mais">e mais {{ count($aulas) - 8 }} {{ count($aulas) - 8 === 1 ? 'aula' : 'aulas' }}</li>
            @endif
          </ol>
        </div>
      @endif
      </div>

      <div class="rodape">
        <div class="legal">
          Certificado de curso livre de qualificação profissional, nos termos do art. 42 da
          Lei nº 9.394/1996 (LDB) e do Decreto nº 5.154/2004. A autenticidade deste documento
          pode ser conferida pelo código e pelo endereço indicados ao lado.
        </div>
        <div class="assinatura">
          <i></i>
          <b>Faculdade Unypública</b>
          <span>Coordenação Acadêmica · Unyflex Digital</span>
        </div>
      </div>
    </main>

  </div>

  <div class="acoes">
    <button type="button" onclick="window.print()">Baixar PDF / Imprimir</button>
    <a href="{{ route('assinante.certificados') }}">Meus certificados</a>
    <a href="{{ $urlVoltar }}">Voltar ao curso</a>
  </div>

  @if($token)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>
    <script>
      (function () {
        var el = document.getElementById('qr');
        if (!el || typeof qrcode !== 'function') return;
        try {
          var qr = qrcode(0, 'M');
          qr.addData(el.dataset.url);
          qr.make();
          el.innerHTML = qr.createSvgTag({ cellSize: 2, margin: 0, scalable: true });
        } catch (e) { /* sem QR: o código e a URL continuam impressos */ }
      })();
    </script>
  @endif

</body>
</html>
