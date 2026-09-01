<!DOCTYPE html>
{{-- Certificado do painel (12h) — página standalone de impressão. O PDF é gerado
     pelo próprio navegador (Imprimir → Salvar como PDF); sem dependência nova. --}}
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Certificado — {{ $titulo }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --azul: #0088F4;          /* --as-blue (identidade Unyflex) */
      --azul-2: #58b6ff;
      --navy: #050d1a;
      --tinta: #101826;
      --suave: #5a6b80;
    }
    * { box-sizing: border-box; margin: 0; }
    body { background: #1a2433; font-family: 'Inter', system-ui, sans-serif; color: var(--tinta); }

    .folha {
      width: 1122px; height: 793px;           /* A4 paisagem @96dpi */
      margin: 32px auto; padding: 26px;
      background: var(--navy);
      display: flex;
    }
    .quadro {
      flex: 1; background: #fff; border: 2px solid var(--azul);
      padding: 58px 84px; display: flex; flex-direction: column; text-align: center;
      position: relative; overflow: hidden;
    }
    .quadro::after {  /* filete decorativo no rodapé, cores da marca */
      content: ''; position: absolute; left: 0; right: 0; bottom: 0; height: 10px;
      background: linear-gradient(90deg, var(--azul), var(--azul-2));
    }

    .marca { display: flex; align-items: center; justify-content: center; gap: 14px; }
    .marca img { height: 46px; }
    .marca small { font-size: 11px; letter-spacing: .14em; text-transform: uppercase; color: var(--suave); }

    h1 {
      font-family: 'Sora', sans-serif; font-weight: 800; font-size: 40px;
      letter-spacing: .28em; color: var(--navy); margin-top: 40px;
    }
    .certifica { margin-top: 34px; font-size: 15px; color: var(--suave); }
    .aluno {
      font-family: 'Sora', sans-serif; font-weight: 700; font-size: 34px; color: var(--azul);
      margin: 14px auto 0; padding-bottom: 10px; max-width: 760px;
      border-bottom: 1px solid #d7e3f2;
    }
    .curso { margin-top: 26px; font-size: 15px; color: var(--suave); }
    .curso strong {
      display: block; margin-top: 8px; font-family: 'Sora', sans-serif;
      font-weight: 600; font-size: 21px; color: var(--tinta); line-height: 1.4;
    }
    .detalhes { margin-top: auto; display: flex; justify-content: center; gap: 64px; }
    .detalhes div { font-size: 13px; color: var(--suave); }
    .detalhes strong { display: block; font-family: 'Sora', sans-serif; font-size: 18px; color: var(--navy); margin-top: 4px; }
    .rodape { margin-top: 30px; padding-bottom: 16px; font-size: 11px; color: var(--suave); }

    .acoes { max-width: 1122px; margin: 0 auto 40px; display: flex; gap: 12px; justify-content: center; }
    .acoes a, .acoes button {
      font: 600 14px 'Inter', sans-serif; padding: 11px 22px; border-radius: 999px;
      cursor: pointer; text-decoration: none; border: 1px solid transparent;
    }
    .acoes button { background: var(--azul); color: #fff; }
    .acoes a { background: transparent; color: #cfe3ff; border-color: #3b4c66; }

    @media print {
      @page { size: A4 landscape; margin: 0; }
      body { background: #fff; }
      .folha { width: 100%; height: 100vh; margin: 0; padding: 18px; }
      .acoes { display: none; }
    }
  </style>
</head>
<body>

  <div class="folha">
    <div class="quadro">
      <div class="marca">
        <img src="{{ asset('img/logo-unyflex.png') }}" alt="Unyflex Digital">
        <small>by Faculdade Unypublica · Reconhecida MEC</small>
      </div>

      <h1>CERTIFICADO</h1>

      <p class="certifica">Certificamos que</p>
      <p class="aluno">{{ $aluno }}</p>

      <p class="curso">
        concluiu, na Assinatura Unyflex Digital, o curso
        <strong>{{ $titulo }}</strong>
      </p>

      <div class="detalhes">
        <div>Carga horária<strong>{{ $horas }} horas</strong></div>
        <div>Concluído em<strong>{{ $concluidoEm->format('d/m/Y') }}</strong></div>
      </div>

      <p class="rodape">
        Unyflex Digital — digital.unyflex.com.br
        @if($token) · Código de autenticidade: {{ $token }} @endif
      </p>
    </div>
  </div>

  <div class="acoes">
    <button type="button" onclick="window.print()">Baixar PDF / Imprimir</button>
    <a href="{{ $urlVoltar }}">Voltar ao curso</a>
  </div>

</body>
</html>
