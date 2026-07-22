@extends('layouts.admin')
@section('title', 'Conversa — Inbox WhatsApp')

@section('content')
<div class="container-fluid py-3">

  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
      <a href="{{ route('admin.whatsapp.index') }}" class="small text-decoration-none">&larr; Voltar</a>
      <h1 class="h5 mb-0 mt-1">{{ $conversa->nome_exibicao ?: 'Não identificado' }}</h1>
      <code class="small text-muted">{{ $conversa->chat_phone ?: '—' }}</code>
    </div>
    <span class="badge bg-secondary">Somente leitura</span>
  </div>

  {{-- Sem painel lateral de CRM: identificar o contato é Fatia 4, e ela está
       travada até a Q8c fechar a cobertura em linhas. --}}

  @if ($mensagens->isEmpty())
    <div class="alert alert-info">Nenhuma mensagem nesta conversa.</div>
  @else
    <div class="d-flex flex-column gap-2">
      @foreach ($mensagens as $mensagem)
        <div class="d-flex {{ $mensagem->from_me ? 'justify-content-end' : 'justify-content-start' }}">
          <div class="p-2 px-3 rounded-3 {{ $mensagem->from_me ? 'bg-primary text-white' : 'bg-light border' }}"
               style="max-width:min(680px,80%)">
            @if ($mensagem->texto)
              <div style="white-space:pre-wrap;word-break:break-word">{{ $mensagem->texto }}</div>
            @else
              {{-- Mídia é Fatia 7. Até lá, o tipo é tudo que se pode dizer
                   honestamente sobre uma mensagem sem texto. --}}
              <em class="small">[{{ $mensagem->tipo ?: 'sem conteúdo de texto' }}]</em>
            @endif
            <div class="small mt-1 {{ $mensagem->from_me ? 'text-white-50' : 'text-muted' }}">
              {{ $mensagem->enviada_em?->format('d/m/Y H:i') ?: 'sem data' }}
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @endif

</div>
@endsection
