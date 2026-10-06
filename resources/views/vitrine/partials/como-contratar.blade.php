<section class="vt-secao" id="como-contratar" aria-labelledby="contratar-titulo">
  <div class="vt-wrap">
    <p class="vt-eyebrow">Contratação pública</p>
    <h2 id="contratar-titulo" class="vt-h2">Como contratar pelo seu órgão</h2>

    <ol class="vt-passos">
      @foreach(config('assinatura_vitrine.passos') as $i => $passo)
        <li class="vt-passo">
          <span class="vt-passo-num">{{ $i + 1 }}</span>
          <i data-lucide="{{ $passo['icone'] }}" class="vt-passo-ico"></i>
          <h3>{{ $passo['titulo'] }}</h3>
          <p>{{ $passo['texto'] }}</p>
        </li>
      @endforeach
    </ol>

    <div class="vt-centro">
      <x-vitrine.whatsapp class="vt-btn vt-btn-primary" content-name="Como contratar: Solicitar proposta">
        <i data-lucide="message-circle"></i>Solicitar proposta pelo WhatsApp
      </x-vitrine.whatsapp>
      <x-vitrine.selos :itens="['empenho', 'nf', 'mec']" class="vt-selos--centro" />
    </div>
  </div>
</section>
