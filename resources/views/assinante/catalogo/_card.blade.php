{{-- Card procedural do catálogo do assinante. Sem imagem: identidade visual vem da categoria. --}}
@php
  use App\Services\AssinanteCatalogoService as Cat;
  $v = $item->visual;
@endphp
<a href="{{ $item->url }}"
   class="as-card as-card--{{ $item->tipo }}"
   data-pattern="{{ $v['pattern'] }}"
   style="{{ Cat::estiloVisual($v) }}"
   title="{{ $item->titulo }}">

  <div class="as-card__art">
    <div class="as-card__topo">
      <span class="as-badge as-badge--{{ $item->tipo }}">{{ $item->tipo_label }}</span>
      @if($item->concluido)
        <span class="as-badge as-badge--concluido">Concluído</span>
      @elseif($item->assistido)
        <span class="as-badge as-badge--visto">Assistido</span>
      @endif
    </div>

    @if($item->tipo === 'livre')
      {{-- Card da TURMA inteira (regra por turma: algum painel com mais de 1 aula). --}}
      <p class="as-card__turma">{{ $item->painel_label }} · {{ $item->aulas }} {{ $item->aulas === 1 ? 'aula' : 'aulas' }}</p>
      <h3 class="as-card__titulo">{{ $item->titulo }}</h3>
    @elseif($item->tipo !== 'modular')
      <p class="as-card__turma">{{ $item->turma }}</p>
      <h3 class="as-card__titulo">{{ $item->painel_label }}</h3>
    @else
      <p class="as-card__turma">Apostilas e Materiais Pós-Graduação</p>
      <h3 class="as-card__titulo">{{ $item->titulo }}</h3>
    @endif
  </div>

  @if($item->tipo !== 'modular' && $item->aulas > 0)
    <div class="as-card__prog"><i style="width:{{ min(100, (int) round($item->vistos / $item->aulas * 100)) }}%"></i></div>
  @endif

  <div class="as-card__meta">
    <span class="as-card__cat"><i></i><span>{{ $item->categoria }}</span></span>
    @if($item->tipo === 'modular')
      <span class="as-card__aulas">Resumo · Cartões · Prova</span>
    @elseif($item->tipo === 'livre')
      <span class="as-card__aulas">{{ $item->paineis }} {{ $item->paineis === 1 ? 'curso' : 'cursos' }} · {{ $item->aulas }} {{ $item->aulas === 1 ? 'aula' : 'aulas' }}</span>
    @else
      <span class="as-card__aulas">{{ $item->aulas }} {{ $item->aulas === 1 ? 'aula' : 'aulas' }}</span>
    @endif
  </div>
</a>
