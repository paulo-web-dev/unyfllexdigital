{{-- Faixa de CTA final. Variáveis opcionais: $titulo, $texto, $botao, $mensagem, $contentName. --}}
<section class="vt-cta-final">
  <div class="vt-wrap vt-cta-final-in">
    <h2 class="vt-h2">{{ $titulo ?? 'Capacite toda a sua equipe em uma única contratação' }}</h2>
    <p class="vt-lead">{{ $texto ?? 'Fale com um consultor e receba a proposta com a documentação para o seu órgão.' }}</p>
    <x-vitrine.whatsapp class="vt-btn vt-btn-primary vt-btn-lg" :mensagem="$mensagem ?? null" :content-name="$contentName ?? 'CTA final'">
      <i data-lucide="message-circle"></i>{{ $botao ?? 'Falar com um consultor' }}
    </x-vitrine.whatsapp>
    <x-vitrine.selos :itens="['empenho', 'nf', 'carga']" class="vt-selos--centro" />
  </div>
</section>
