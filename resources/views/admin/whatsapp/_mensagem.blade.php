{{-- Balão de uma mensagem. Renderizado em DOIS caminhos: o @foreach da thread
     e o delta do polling (WhatsappInboxController::mensagens()).

     ESTÁ EM BLADE, E NÃO EM JS, DE PROPÓSITO. O texto vem de estranhos no
     WhatsApp; aqui ele é escapado por padrão. Montar o balão em JS colocaria
     conteúdo de terceiro perto de um innerHTML, e bastaria uma desatenção.

     Os data-* não são enfeite: data-enviada-em é o que permite ao JS INSERIR a
     mensagem na posição cronológica certa. O delta é ordenado por id (ordem de
     inserção), então uma entrega atrasada pode pertencer ao meio da thread. --}}
<div class="d-flex {{ $mensagem->from_me ? 'justify-content-end' : 'justify-content-start' }}"
     data-msg-id="{{ $mensagem->id }}"
     data-enviada-em="{{ $mensagem->enviada_em?->toIso8601String() }}">
  <div class="p-2 px-3 rounded-3 {{ $mensagem->from_me ? 'bg-primary text-white' : 'bg-light border' }}"
       style="max-width:min(680px,80%)">
    @if ($mensagem->texto)
      <div style="white-space:pre-wrap;word-break:break-word">{{ $mensagem->texto }}</div>
    @else
      {{-- Mídia é fatia posterior. Até lá, o tipo é tudo que se pode dizer
           honestamente sobre uma mensagem sem texto. --}}
      <em class="small">[{{ $mensagem->tipo ?: 'sem conteúdo de texto' }}]</em>
    @endif
    <div class="small mt-1 {{ $mensagem->from_me ? 'text-white-50' : 'text-muted' }}">
      {{ $mensagem->enviada_em?->format('d/m/Y H:i') ?: 'sem data' }}
    </div>
  </div>
</div>
