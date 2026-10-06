{{-- Faixa horizontal de cursos de uma categoria. $faixa = {categoria, itens}. --}}
<section class="vt-faixa" aria-labelledby="faixa-{{ $faixa->categoria->slug }}">
  <div class="vt-wrap">
    <div class="vt-faixa-topo">
      <h2 id="faixa-{{ $faixa->categoria->slug }}" class="vt-h3">
        <i data-lucide="{{ $faixa->categoria->icone }}"></i>{{ $faixa->categoria->titulo }}
        <span class="vt-faixa-qtd">{{ $faixa->categoria->cursos }} cursos</span>
      </h2>
      <div class="vt-faixa-nav">
        <a href="{{ $faixa->categoria->url }}" class="vt-faixa-todos">Ver todos</a>
        <button type="button" class="vt-seta" data-vt-scroll="-1" aria-label="Anterior"><i data-lucide="chevron-left"></i></button>
        <button type="button" class="vt-seta" data-vt-scroll="1" aria-label="Próximo"><i data-lucide="chevron-right"></i></button>
      </div>
    </div>
  </div>
  <div class="vt-trilho" data-vt-trilho>
    @foreach($faixa->itens as $curso)
      <x-vitrine.curso-card :curso="$curso" :link="$faixa->categoria->url" />
    @endforeach
  </div>
</section>
