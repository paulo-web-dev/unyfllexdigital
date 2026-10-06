{{--
  Mini-calculadora (lógica em public/js/vitrine.js). Preços e mensagens vêm do config via
  data-vt-calc (JSON de AssinaturaVitrineService::dadosCalculadora()). Nada fixo no JS.
--}}
<section class="vt-secao vt-secao--alt" id="calculadora" aria-labelledby="calc-titulo">
  <div class="vt-wrap">
    <p class="vt-eyebrow">Simulador</p>
    <h2 id="calc-titulo" class="vt-h2">Quanto o seu órgão economiza?</h2>
    <p class="vt-lead">Informe quantos servidores serão capacitados e quantos cursos cada um faria no ano.</p>

    <div class="vt-calc" data-vt-calc='@json($calculadora)'>
      <div class="vt-calc-campos">
        <label class="vt-campo">
          <span>Servidores</span>
          <div class="vt-stepper">
            <button type="button" data-passo="-1" data-alvo="servidores" aria-label="Menos um servidor">−</button>
            <input type="number" name="servidores" min="1" max="500" value="5" inputmode="numeric">
            <button type="button" data-passo="1" data-alvo="servidores" aria-label="Mais um servidor">+</button>
          </div>
        </label>
        <label class="vt-campo">
          <span>Cursos por servidor no ano</span>
          <div class="vt-stepper">
            <button type="button" data-passo="-1" data-alvo="cursos" aria-label="Menos um curso">−</button>
            <input type="number" name="cursos" min="1" max="100" value="3" inputmode="numeric">
            <button type="button" data-passo="1" data-alvo="cursos" aria-label="Mais um curso">+</button>
          </div>
        </label>
      </div>

      <div class="vt-calc-res" aria-live="polite">
        <div class="vt-calc-linha vt-calc-linha--off">
          <span>Com transmissões avulsas</span>
          <strong data-out="avulsa">—</strong>
          <small data-out="avulsa-det"></small>
        </div>
        <div class="vt-calc-linha vt-calc-linha--on">
          <span>Com a Assinatura Premium</span>
          <strong data-out="assinatura">—</strong>
          <small data-out="combinacao"></small>
        </div>
        <div class="vt-calc-economia">
          <span data-out="economia-rotulo">Economia no ano</span>
          <strong data-out="economia">—</strong>
        </div>
        <p class="vt-calc-aviso" data-out="upsell" hidden></p>
        <p class="vt-calc-aviso vt-calc-aviso--sob" data-out="sob-medida" hidden></p>
        <a class="vt-btn vt-btn-primary vt-btn-bloco" href="#" target="_blank" rel="noopener"
           data-out="cta" data-vt-contact="Calculadora">
          <i data-lucide="message-circle"></i><span>Receber proposta com esta simulação</span>
        </a>
      </div>
    </div>
    <p class="vt-nota">Simulação com {{ \App\Services\AssinaturaVitrineService::brl($calculadora['avulsa']) }} por servidor, por curso, na transmissão avulsa, e a combinação mais barata de planos para o número de servidores. Valores anuais, à vista.</p>
  </div>
</section>
