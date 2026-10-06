{{--
  "Por dentro da plataforma": telas em moldura de navegador. Imagens de
  config('assinatura_vitrine.plataforma_imagens'); vazio = a seção não aparece.
  Celular: uma tela por vez + miniaturas. Desktop com exatamente 2 telas: lado a lado.
  Clique na tela abre o lightbox (vitrine.js).
--}}
@php
  $telas = collect(config('assinatura_vitrine.plataforma_imagens', []))->filter(fn ($t) => ! empty($t['url']))->values();
@endphp
@if($telas->isNotEmpty())
<section class="vt-secao vt-plat-secao" aria-labelledby="plat-titulo">
  <div class="vt-wrap">
    <p class="vt-eyebrow">Por dentro da plataforma</p>
    <h2 id="plat-titulo" class="vt-h2">Veja como o servidor estuda</h2>

    <div class="vt-plat {{ $telas->count() === 2 ? 'vt-plat--duas' : '' }}" data-vt-plat>
      <div class="vt-plat-telas">
        @foreach($telas as $i => $tela)
          <figure class="vt-plat-tela {{ $i === 0 ? 'ativa' : '' }}" data-indice="{{ $i }}">
            <div class="vt-moldura">
              <div class="vt-moldura-barra" aria-hidden="true">
                <span></span><span></span><span></span>
                <em>digital.unyflex.com.br</em>
              </div>
              <button type="button" class="vt-moldura-tela" data-vt-zoom="{{ $i }}" aria-label="Ampliar: {{ $tela['legenda'] }}">
                <img src="{{ $tela['url'] }}" alt="{{ $tela['legenda'] }}" loading="lazy" decoding="async"
                     width="{{ $tela['largura'] ?? 1365 }}" height="{{ $tela['altura'] ?? 600 }}">
              </button>
            </div>
            <figcaption>{{ $tela['legenda'] }}</figcaption>
          </figure>
        @endforeach
      </div>

      @if($telas->count() > 1)
        <div class="vt-plat-miniaturas" role="tablist" aria-label="Telas da plataforma">
          @foreach($telas as $i => $tela)
            <button type="button" class="vt-plat-mini {{ $i === 0 ? 'ativa' : '' }}" data-vt-tela="{{ $i }}"
                    role="tab" aria-selected="{{ $i === 0 ? 'true' : 'false' }}">
              <img src="{{ $tela['url'] }}" alt="" loading="lazy" decoding="async"
                   width="{{ $tela['largura'] ?? 1365 }}" height="{{ $tela['altura'] ?? 600 }}">
              <span>{{ $tela['legenda'] }}</span>
            </button>
          @endforeach
        </div>
      @endif
    </div>
  </div>
</section>
@endif
