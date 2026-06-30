@php
  $miniUrl = (isset($category) && $category && $category->minisserie_url)
      ? $category->minisserie_url
      : url('/minisseries');
@endphp
<div class="mini-cta">
  <div class="mini-cta-inner">
    <div class="k">Unyflex Digital</div>
    <h3>Conheça a minissérie completa disponível na Unyflex Digital.</h3>
    <p>Cápsulas de 10 a 20 minutos, direto ao ponto, para você aplicar na sua rotina já no dia seguinte.</p>
    <a href="{{ $miniUrl }}" class="btn-mini">Ver minissérie <i data-lucide="arrow-right"></i></a>
  </div>
</div>
