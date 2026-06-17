@extends('layouts.site')
@section('meta_title', 'Guia Gratuito: Contratacoes Publicas pela Lei 14.133/2021 — Unyflex Digital')
@section('meta_description', 'Baixe gratis o roteiro completo das contratacoes publicas: as 12 etapas da demanda ao contrato, com checklists e 8 documentos modelo prontos para o seu orgao.')

@push('styles')
<meta property="og:type" content="website">
<meta property="og:title" content="Da demanda ao contrato sem medo de errar na Lei 14.133">
<meta property="og:description" content="Roteiro completo das contratacoes publicas, com checklists e modelos prontos. Download gratuito.">
<style>
  .lp-hp{position:absolute!important;left:-9999px!important;top:-9999px!important;height:0;width:0;opacity:0}

  /* Hero */
  .lp-hero{display:grid;grid-template-columns:1fr 440px;gap:44px;align-items:start}
  .lp-benef{list-style:none;padding:0;margin:22px 0 0;display:grid;gap:11px}
  .lp-benef li{display:flex;gap:11px;align-items:flex-start;font-size:15px;color:var(--fg-2);line-height:1.5}
  .lp-benef li svg{flex:none;width:20px;height:20px;color:#00A3FF;margin-top:2px}
  .lp-trust{display:flex;flex-wrap:wrap;gap:8px 20px;margin-top:26px;padding-top:20px;border-top:1px solid var(--line-2);font-size:13px;color:var(--fg-3)}
  .lp-trust b{color:#fff;font-family:var(--font-display);font-weight:700}

  /* Enfase no "Gratuito" */
  .lp-eyebrow-row{display:flex;flex-wrap:wrap;align-items:center;gap:10px}
  .lp-free-pill{display:inline-flex;align-items:center;gap:7px;background:linear-gradient(135deg,#13955F,#0b8a52);color:#fff;font-family:var(--font-display);font-weight:800;font-size:13px;letter-spacing:.02em;text-transform:uppercase;padding:8px 15px;border-radius:999px;box-shadow:0 8px 22px -8px rgba(19,149,95,.75)}
  .lp-free-pill::before{content:"";width:7px;height:7px;border-radius:50%;background:#7dffba;box-shadow:0 0 0 3px rgba(125,255,186,.25)}
  .lp-free-sticker{position:absolute;top:-16px;right:-12px;width:76px;height:76px;border-radius:50%;background:linear-gradient(135deg,#13955F,#0b8a52);color:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;font-family:var(--font-display);font-weight:800;font-size:19px;line-height:1;transform:rotate(8deg);box-shadow:0 12px 28px -8px rgba(19,149,95,.85),0 0 0 4px rgba(11,138,82,.25);z-index:3}
  .lp-free-sticker small{font-size:8.5px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;margin-top:3px;opacity:.95}

  /* Form card */
  .lp-formcard{position:relative;background:radial-gradient(70% 120% at 90% 0%, rgba(0,163,255,0.18), transparent 60%),linear-gradient(160deg,#0F1726,#050A18);border:1px solid var(--line-2);border-radius:var(--r-xl);padding:26px;box-shadow:var(--shadow-lg);top:96px}
  .lp-formcard h3{font-family:var(--font-display);font-weight:800;font-size:20px;color:#fff;letter-spacing:-0.02em;margin:12px 0 3px}
  .lp-lead{font-size:13.5px;color:var(--fg-3);margin-bottom:18px}
  .lp-field{margin-bottom:13px}
  .lp-field label{display:block;font-size:13px;font-weight:600;color:var(--fg-2);margin-bottom:6px}
  .lp-field input{width:100%;background:var(--bg-2);border:1px solid var(--line-2);border-radius:12px;padding:13px 14px;color:#fff;font-size:15px;font-family:inherit;transition:.18s}
  .lp-field input::placeholder{color:var(--fg-3)}
  .lp-field input:focus{outline:none;border-color:rgba(0,163,255,0.55);box-shadow:0 0 0 3px rgba(0,163,255,0.18);background:var(--bg-3)}
  .lp-err{display:block;color:#ff6b6b;font-size:12.5px;margin-top:5px}
  .lp-submit{width:100%;justify-content:center;margin-top:6px}
  .lp-micro{text-align:center;font-size:12.5px;color:var(--fg-3);margin-top:11px;display:flex;align-items:center;justify-content:center;gap:6px}
  .lp-micro svg{width:14px;height:14px;color:var(--success)}
  .lp-lgpd{font-size:11.5px;color:var(--fg-3);text-align:center;margin-top:9px;line-height:1.45;opacity:.85}
  .lp-alert{background:rgba(255,80,80,0.1);border:1px solid rgba(255,80,80,0.35);color:#ff8a8a;border-radius:12px;padding:10px 13px;font-size:13px;margin-bottom:14px}

  /* Secoes */
  .lp-sec{padding:64px 0;border-top:1px solid var(--line-2)}
  .lp-pain{background:var(--bg-2);border:1px solid var(--line-2);border-left:3px solid #00A3FF;border-radius:14px;padding:18px 20px;display:flex;gap:12px;align-items:flex-start;height:100%}
  .lp-pain svg{flex:none;width:20px;height:20px;color:#00A3FF;margin-top:2px}
  .lp-pain p{margin:0;font-size:14.5px;color:var(--fg-2)}
  .lp-fecho{margin-top:28px;background:radial-gradient(60% 120% at 90% 50%, rgba(0,163,255,0.18), transparent 60%),linear-gradient(120deg,#0F1726,#050A18);border:1px solid var(--line-2);border-radius:var(--r-xl);padding:22px 26px;font-size:16px;color:#eaf2ff;display:flex;gap:15px;align-items:center;box-shadow:var(--shadow-lg)}
  .lp-fecho svg{flex:none;width:30px;height:30px;color:#00A3FF}

  /* Trilha */
  .lp-step{background:var(--bg-2);border:1px solid var(--line-2);border-radius:16px;padding:20px;height:100%;transition:.2s}
  .lp-step:hover{border-color:rgba(0,163,255,0.4);transform:translateY(-3px);box-shadow:var(--shadow-lg)}
  .lp-step-num{font-family:var(--font-display);font-weight:800;font-size:14px;width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#0072FF,#00A3FF);color:#fff;display:flex;align-items:center;justify-content:center;margin-bottom:13px}
  .lp-step h3{font-family:var(--font-display);font-size:16px;font-weight:700;color:#fff;margin-bottom:5px;letter-spacing:-0.01em}
  .lp-step p{font-size:13.5px;color:var(--fg-3);line-height:1.55;margin:0}
  .lp-modelos{margin-top:26px;background:var(--bg-2);border:1px solid rgba(0,163,255,0.3);border-radius:16px;padding:20px 24px;display:flex;gap:15px;align-items:center}
  .lp-modelos svg{flex:none;width:30px;height:30px;color:#00A3FF}
  .lp-modelos p{margin:0;font-size:15px;color:var(--fg-2)}
  .lp-modelos b{color:#fff}

  /* Para quem */
  .lp-quem{display:flex;gap:12px;align-items:center;background:var(--bg-2);border:1px solid var(--line-2);border-radius:14px;padding:16px 18px;height:100%}
  .lp-quem svg{flex:none;width:22px;height:22px;color:#00A3FF}
  .lp-quem span{font-size:14.5px;font-weight:500;color:var(--fg-2)}

  /* KPIs */
  .lp-kpi{background:var(--bg-2);border:1px solid var(--line-2);border-radius:16px;padding:26px 16px;text-align:center;height:100%}
  .lp-kpi b{display:block;font-family:var(--font-display);font-weight:800;font-size:30px;letter-spacing:-.02em;background:linear-gradient(135deg,#0072FF,#00A3FF);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
  .lp-kpi span{font-size:13px;color:var(--fg-3);margin-top:6px;display:block}

  /* CTA final */
  .lp-final{background:radial-gradient(60% 120% at 85% 30%, rgba(0,163,255,0.2), transparent 60%),linear-gradient(120deg,#0F1726,#050A18);border:1px solid var(--line-2);border-radius:var(--r-xl);padding:40px;box-shadow:var(--shadow-lg);display:grid;grid-template-columns:1fr 420px;gap:44px;align-items:center}
  .lp-final h2{font-family:var(--font-display);font-weight:800;font-size:clamp(24px,3vw,32px);color:#fff;letter-spacing:-.02em;margin-bottom:12px}

  @media(max-width:991px){
    .lp-hero{grid-template-columns:1fr;gap:28px}
    .lp-formcard{position:relative;top:0}
    .lp-final{grid-template-columns:1fr;gap:28px;padding:28px}
  }
</style>
@endpush

@section('content')
<div style="padding-top:112px;padding-bottom:0;">
  <div class="container">

    {{-- ===== HERO ===== --}}
    <div class="lp-hero aos-fade">
      <div>
        <div class="lp-eyebrow-row" style="margin-bottom:16px;">
          <span class="lp-free-pill">100% Gratuito</span>
          <span class="offer-badge">Atualizado 2026 · Decreto 12.807/2025</span>
        </div>
        <h1 class="section-title" style="font-size:clamp(30px,4.2vw,50px);">Da demanda ao contrato, <span style="color:#00A3FF;">sem medo de errar</span> na Lei 14.133</h1>
        <p style="font-size:16px;color:var(--fg-3);line-height:1.65;max-width:500px;margin-top:8px;">
          Baixe <b style="color:#fff;">gratuitamente</b> o roteiro completo das contratacoes publicas: as 12 etapas explicadas, checklists de conferencia e documentos modelo prontos para adaptar no seu orgao.
        </p>
        <ul class="lp-benef">
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg> Passo a passo do DFD ate a gestao do contrato</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg> Checklists para conferir cada fase antes de avancar</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg> 8 documentos modelo (ETP, Termo de Referencia, contrato...)</li>
        </ul>
        <div class="lp-trust">
          <span><b>+49.000</b> servidores capacitados</span>
          <span><b>Certificado</b> reconhecido pelo MEC</span>
          <span><b>Faculdade Unypublica</b></span>
        </div>
      </div>

      {{-- FORM (topo) --}}
      <div class="lp-formcard" id="form-topo">
        <span class="lp-free-sticker">R$ 0<small>100% gratis</small></span>
        <div class="offer-badge">Material gratuito · PDF</div>
        <h3>Receba o guia agora</h3>
        <p class="lp-lead">Preencha os dados e receba o guia no seu e-mail.</p>

        @if($errors->any())
          <div class="lp-alert">Confira os campos destacados para receber o guia.</div>
        @endif

        <form method="POST" action="{{ route('guia.store') }}" novalidate>
          @csrf
          @include('guia._campos', ['utm' => $utm])
          <button type="submit" class="btn-ux btn-ux-primary btn-ux-lg lp-submit">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="width:18px;height:18px;"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
            Quero o guia gratuito
          </button>
          <p class="lp-micro"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg> 100% gratuito · sem cartao · acesso imediato</p>
          <p class="lp-lgpd">Ao enviar, voce concorda em receber comunicacoes da Unyflex Digital. Seus dados estao protegidos conforme a LGPD.</p>
        </form>
      </div>
    </div>

    {{-- ===== DOR ===== --}}
    <div class="lp-sec aos-fade" style="margin-top:64px;">
      <div class="section-eyebrow">Por que isso importa</div>
      <h2 class="section-title" style="font-size:clamp(24px,3vw,34px);max-width:760px;">O erro quase nunca aparece na sessao de disputa. Ele nasce no planejamento.</h2>
      <p style="font-size:16px;color:var(--fg-3);line-height:1.65;max-width:660px;margin:14px 0 28px;">
        A maior parte das falhas apontadas pelos tribunais de contas acontece <b style="color:var(--fg-2)">antes</b> da licitacao: demanda mal definida, preco mal pesquisado, risco ignorado. E, quando algo da errado, e o agente que costuma responder.
      </p>
      <div class="row g-3">
        <div class="col-md-6"><div class="lp-pain"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg><p>Inseguranca para montar ETP, Termo de Referencia e pesquisa de precos?</p></div></div>
        <div class="col-md-6"><div class="lp-pain"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg><p>Receio de uma especificacao direcionada gerar impugnacao e atraso?</p></div></div>
        <div class="col-md-6"><div class="lp-pain"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg><p>Duvida sobre dispensa, inexigibilidade e os valores vigentes em 2026?</p></div></div>
        <div class="col-md-6"><div class="lp-pain"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg><p>Medo de uma falha no processo recair diretamente sobre voce?</p></div></div>
      </div>
      <div class="lp-fecho">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
        <div>Este guia organiza o caminho inteiro para voce decidir com base tecnica em cada etapa — e seguir o processo com seguranca.</div>
      </div>
    </div>

    {{-- ===== TRILHA ===== --}}
    <div class="lp-sec aos-fade">
      <div class="section-eyebrow">O que voce vai dominar</div>
      <h2 class="section-title" style="font-size:clamp(24px,3vw,34px);">O caminho completo, etapa por etapa</h2>
      <p style="font-size:16px;color:var(--fg-3);line-height:1.65;max-width:620px;margin:14px 0 30px;">Cada capitulo traz explicacao objetiva, checklist de conferencia e modelo pronto para adaptar.</p>
      <div class="row g-3">
        <div class="col-lg-4 col-md-6"><div class="lp-step"><div class="lp-step-num">01</div><h3>Identificacao da Necessidade</h3><p>Formalize a demanda (DFD) e vincule ao Plano de Contratacoes Anual.</p></div></div>
        <div class="col-lg-4 col-md-6"><div class="lp-step"><div class="lp-step-num">02</div><h3>Estudo Tecnico Preliminar</h3><p>Avalie alternativas e comprove a viabilidade da contratacao.</p></div></div>
        <div class="col-lg-4 col-md-6"><div class="lp-step"><div class="lp-step-num">03</div><h3>Gerenciamento de Riscos</h3><p>Monte a matriz de riscos com medidas preventivas e responsaveis.</p></div></div>
        <div class="col-lg-4 col-md-6"><div class="lp-step"><div class="lp-step-num">04</div><h3>Pesquisa de Precos</h3><p>Cesta com 3+ fontes oficiais (PNCP, Painel) e metodologia clara.</p></div></div>
        <div class="col-lg-4 col-md-6"><div class="lp-step"><div class="lp-step-num">05</div><h3>Termo de Referencia</h3><p>Especifique sem direcionamento, com modelo de gestao do contrato.</p></div></div>
        <div class="col-lg-4 col-md-6"><div class="lp-step"><div class="lp-step-num">06</div><h3>Modalidade e Julgamento</h3><p>Pregao, concorrencia, dispensa ou inexigibilidade: quando usar cada um.</p></div></div>
        <div class="col-lg-4 col-md-6"><div class="lp-step"><div class="lp-step-num">07</div><h3>Elaboracao do Edital</h3><p>A "lei interna" da licitacao, coerente com o TR e os anexos.</p></div></div>
        <div class="col-lg-4 col-md-6"><div class="lp-step"><div class="lp-step-num">08</div><h3>Fase Externa</h3><p>Publicacao, disputa, julgamento, habilitacao e fase recursal.</p></div></div>
        <div class="col-lg-4 col-md-6"><div class="lp-step"><div class="lp-step-num">09</div><h3>Homologacao e Adjudicacao</h3><p>Encerramento motivado pela autoridade competente.</p></div></div>
        <div class="col-lg-4 col-md-6"><div class="lp-step"><div class="lp-step-num">10</div><h3>Contrato Administrativo</h3><p>Gestao, fiscalizacao, aditivos, prorrogacoes e garantias.</p></div></div>
        <div class="col-lg-4 col-md-6"><div class="lp-step"><div class="lp-step-num">11</div><h3>Checklist Mestre</h3><p>Conferencia final de todas as etapas antes de avancar.</p></div></div>
        <div class="col-lg-4 col-md-6"><div class="lp-step"><div class="lp-step-num">12</div><h3>Documentos Modelo</h3><p>Estruturas prontas para adaptar a realidade do seu orgao.</p></div></div>
      </div>
      <div class="lp-modelos">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6M9 17h6"/></svg>
        <p><b>Inclui 8 documentos modelo:</b> DFD, ETP, Mapa de Riscos, Pesquisa de Precos, Termo de Referencia, Ata de Julgamento, Relatorio Final e Contrato Administrativo.</p>
      </div>
    </div>

    {{-- ===== PARA QUEM ===== --}}
    <div class="lp-sec aos-fade">
      <div class="section-eyebrow">Para quem e</div>
      <h2 class="section-title" style="font-size:clamp(24px,3vw,34px);">Feito para quem atua na linha de frente das contratacoes</h2>
      <div class="row g-3" style="margin-top:18px;">
        <div class="col-md-6"><div class="lp-quem"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg><span>Pregoeiros e agentes de contratacao</span></div></div>
        <div class="col-md-6"><div class="lp-quem"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg><span>Gestores e fiscais de contratos</span></div></div>
        <div class="col-md-6"><div class="lp-quem"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg><span>Equipes de licitacao e setores de compras</span></div></div>
        <div class="col-md-6"><div class="lp-quem"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg><span>Controladorias, procuradorias e juridicos</span></div></div>
        <div class="col-md-6"><div class="lp-quem"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg><span>Secretarios e servidores municipais</span></div></div>
        <div class="col-md-6"><div class="lp-quem"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg><span>Quem precisa comprovar capacitacao com certificado</span></div></div>
      </div>
    </div>

    {{-- ===== PROVA ===== --}}
    <div class="lp-sec aos-fade">
      <div class="section-eyebrow">Quem entrega este conteudo</div>
      <h2 class="section-title" style="font-size:clamp(24px,3vw,34px);">Unyflex Digital, referencia na qualificacao do setor publico</h2>
      <div class="row g-3" style="margin-top:20px;">
        <div class="col-6 col-md-3"><div class="lp-kpi"><b>+49 mil</b><span>servidores ja capacitados</span></div></div>
        <div class="col-6 col-md-3"><div class="lp-kpi"><b>14.133</b><span>conteudo 100% na nova lei</span></div></div>
        <div class="col-6 col-md-3"><div class="lp-kpi"><b>MEC</b><span>certificado reconhecido</span></div></div>
        <div class="col-6 col-md-3"><div class="lp-kpi"><b>Unypublica</b><span>respaldo institucional</span></div></div>
      </div>
    </div>

    {{-- ===== CTA FINAL ===== --}}
    <div style="padding:24px 0 80px;">
      <div class="lp-final aos-fade" id="baixar">
        <div>
          <div class="offer-badge" style="margin-bottom:12px;">Download gratuito</div>
          <h2>Baixe agora e tenha o roteiro sempre a mao</h2>
          <p style="font-size:16px;color:var(--fg-3);line-height:1.65;max-width:420px;">Um material de consulta para abrir sempre que precisar conferir uma etapa, um prazo ou um modelo.</p>
          <ul class="lp-benef">
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg> 12 etapas explicadas com linguagem direta</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg> Checklists prontos para imprimir e usar</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg> Valores de dispensa atualizados (2026)</li>
          </ul>
        </div>

        {{-- FORM (final) --}}
        <div id="form-final">
          <h3 style="font-family:var(--font-display);font-weight:800;font-size:20px;color:#fff;margin-bottom:3px;">Liberar meu guia gratuito</h3>
          <p class="lp-lead" style="margin-bottom:18px;">Preencha e receba o guia no seu e-mail.</p>

          @if($errors->any())
            <div class="lp-alert">Confira os campos destacados.</div>
          @endif

          <form method="POST" action="{{ route('guia.store') }}" novalidate>
            @csrf
            @include('guia._campos', ['utm' => $utm])
            <button type="submit" class="btn-ux btn-ux-primary btn-ux-lg lp-submit">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="width:18px;height:18px;"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
              Baixar guia gratuito
            </button>
            <p class="lp-micro"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg> 100% gratuito · sem cartao · acesso imediato</p>
            <p class="lp-lgpd">Dados protegidos conforme a LGPD.</p>
          </form>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
  document.querySelectorAll('.js-whats').forEach(function(el){
    el.addEventListener('input', function(){
      var v = el.value.replace(/\D/g,'').slice(0,11);
      if(v.length > 6){ el.value = '('+v.slice(0,2)+') '+v.slice(2,7)+'-'+v.slice(7); }
      else if(v.length > 2){ el.value = '('+v.slice(0,2)+') '+v.slice(2); }
      else if(v.length > 0){ el.value = '('+v; }
      else { el.value = ''; }
    });
  });
  document.querySelectorAll('form').forEach(function(f){
    if(!f.querySelector('.js-whats')) return;
    f.addEventListener('submit', function(){
      var b = f.querySelector('button[type=submit]');
      if(b){ setTimeout(function(){ b.disabled = true; b.style.opacity = .7; }, 10); }
    });
  });
</script>
@endpush