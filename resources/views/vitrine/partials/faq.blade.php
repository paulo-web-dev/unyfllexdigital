<section class="vt-secao vt-secao--alt" id="faq" aria-labelledby="faq-titulo">
  <div class="vt-wrap vt-wrap--estreito">
    <p class="vt-eyebrow">Dúvidas do gestor</p>
    <h2 id="faq-titulo" class="vt-h2">Perguntas frequentes</h2>

    <div class="vt-faq">
      @foreach(config('assinatura_vitrine.faq') as $item)
        <details class="vt-faq-item" @if($loop->first) open @endif>
          <summary>{{ $item['p'] }}<i data-lucide="chevron-down"></i></summary>
          <p>{{ $item['r'] }}</p>
        </details>
      @endforeach
    </div>
  </div>
</section>
