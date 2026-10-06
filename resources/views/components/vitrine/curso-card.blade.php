{{--
  Card de curso da vitrine, gerado (sem capa): degradê da categoria + textura + ícone em marca d'água.
  `curso` = item de AssinaturaVitrineService::cards(). O card inteiro leva a "Ver curso"
  (página da categoria, na âncora do card; `link` vazio = âncora na própria página).
  Conversão: um único CTA "Assinar" no WhatsApp, com o nome do curso na mensagem.
--}}
@props(['curso', 'link' => null])
@php
    $c = $curso;
    $ancora = 'curso-' . $c['tipo'] . '-' . $c['id'];
    $icones = config('assinatura_vitrine.icones_categoria', []);
    $slugIcone = $c['tipo'] === 'modular' ? config('assinatura_vitrine.categoria_apostilas.slug') : ($c['categorias'][0] ?? '');
    $icone = $icones[$slugIcone] ?? 'book-open';
    $badge = config('assinatura_vitrine.badges.' . $c['tipo'], $c['tipo_label']);
    $detalhe = match ($c['tipo']) {
        'modular' => 'Apostila e materiais',
        'livre'   => $c['paineis'] . ' cursos · ' . $c['aulas'] . ' aulas',
        default   => $c['aulas'] . ' ' . ($c['aulas'] === 1 ? 'aula' : 'aulas'),
    };
@endphp
<article {{ $attributes->merge(['class' => 'vt-card vt-card--' . $c['tipo']]) }} id="{{ $ancora }}" style="{{ $c['estilo'] }}">
  <a href="{{ $link }}#{{ $ancora }}" class="vt-card-alvo" aria-label="Ver curso: {{ $c['titulo'] }}"></a>
  <i data-lucide="{{ $icone }}" class="vt-card-marca" aria-hidden="true"></i>

  <span class="vt-card-tipo">{{ $badge }}</span>

  <div class="vt-card-corpo">
    @if($c['turma'])
      <span class="vt-card-turma" title="{{ $c['turma'] }}">{{ $c['turma'] }}</span>
    @endif
    <h3 class="vt-card-titulo" title="{{ $c['titulo_principal'] }}">{{ $c['titulo_principal'] }}</h3>
  </div>

  <div class="vt-card-rodape">
    <span class="vt-card-meta"><i data-lucide="play-circle"></i><span>{{ $detalhe }}</span></span>
    <x-vitrine.whatsapp class="vt-card-assinar" :mensagem="\App\Services\AssinaturaVitrineService::mensagem('curso', ['curso' => $c['titulo']])"
        :content-name="'Curso: ' . \Illuminate\Support\Str::limit($c['titulo'], 80)">
      <i data-lucide="message-circle"></i>Assinar
    </x-vitrine.whatsapp>
  </div>
</article>
