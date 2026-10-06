{{--
  Card de curso da vitrine. `curso` = item de AssinaturaVitrineService::cards().
  "Ver curso" leva à página da categoria (âncora do card); a conversão é sempre o WhatsApp.
--}}
@props(['curso', 'link' => null])
@php
    $c = $curso;
    $ancora = 'curso-' . $c['tipo'] . '-' . $c['id'];
    $detalhe = $c['tipo'] === 'modular'
        ? 'Apostila e materiais'
        : ($c['tipo'] === 'livre'
            ? $c['paineis'] . ' cursos · ' . $c['aulas'] . ' aulas'
            : $c['aulas'] . ' ' . ($c['aulas'] === 1 ? 'aula' : 'aulas'));
@endphp
<article {{ $attributes->merge(['class' => 'vt-card']) }} id="{{ $ancora }}">
  <div class="vt-card-capa" style="{{ $c['estilo'] }}">
    @if($c['capa'])
      <img src="{{ $c['capa'] }}" alt="" loading="lazy" decoding="async" onerror="this.remove()">
    @endif
    <span class="vt-card-tipo">{{ $c['tipo_label'] }}</span>
  </div>
  <div class="vt-card-corpo">
    <span class="vt-card-cat">{{ $c['categoria'] }}</span>
    <h3 class="vt-card-titulo" title="{{ $c['titulo'] }}">{{ $c['titulo'] }}</h3>
    <span class="vt-card-meta"><i data-lucide="play-circle"></i>{{ $detalhe }}</span>
  </div>
  <div class="vt-card-acoes">
    @if($link)
      <a href="{{ $link }}#{{ $ancora }}" class="vt-card-link">Ver curso</a>
    @endif
    <x-vitrine.whatsapp class="vt-card-wa" :mensagem="\App\Services\AssinaturaVitrineService::mensagem('curso', ['curso' => $c['titulo']])"
        :content-name="'Curso: ' . \Illuminate\Support\Str::limit($c['titulo'], 80)" aria-label="Quero para minha equipe">
      <i data-lucide="message-circle"></i><span>Quero para minha equipe</span>
    </x-vitrine.whatsapp>
  </div>
</article>
