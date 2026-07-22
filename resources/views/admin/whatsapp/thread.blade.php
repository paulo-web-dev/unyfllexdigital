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
    {{-- Continua sem responder: a única escrita desta tela é a atribuição, e
         ela não sai do nosso banco. --}}
    <span class="badge bg-secondary">Sem envio — modo sombra</span>
  </div>

  {{-- Sem painel lateral de CRM: identificar o contato é Fatia 4, e ela está
       travada até a Q9 fechar a cobertura em linhas. --}}

  @if (session('sucesso'))
    <div class="alert alert-success py-2 small">{{ session('sucesso') }}</div>
  @endif
  @if (session('aviso'))
    <div class="alert alert-warning py-2 small">{{ session('aviso') }}</div>
  @endif
  @error('atendente_id')
    <div class="alert alert-danger py-2 small">{{ $message }}</div>
  @enderror

  {{-- Atribuição (Fatia 7). Atendente é usuário nosso, com power 13
       (Comercial). O lead_assignedAttendant_id da Uazapi é ignorado — não
       lemos e não escrevemos (regra de ouro 5). --}}
  <div class="card mb-3">
    <div class="card-body py-2">
      <form method="POST" action="{{ route('admin.whatsapp.atribuir', $conversa) }}"
            class="row g-2 align-items-center">
        @csrf
        {{-- Quem era o atendente quando esta tela carregou. O controller
             recusa a gravação se mudou nesse meio-tempo, em vez de passar por
             cima do trabalho de outra pessoa. --}}
        <input type="hidden" name="atendente_atual_id" value="{{ $conversa->atendente_id }}">

        <div class="col-auto"><label class="col-form-label small text-muted" for="atendente_id">Atendente</label></div>
        <div class="col-auto">
          <select name="atendente_id" id="atendente_id" class="form-select form-select-sm">
            <option value="">— sem atendente —</option>
            @foreach ($atendentes as $atendente)
              <option value="{{ $atendente->id }}" @selected($conversa->atendente_id === $atendente->id)>
                {{ $atendente->name }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-sm btn-primary">Salvar</button>
        </div>
        <div class="col-auto small text-muted">
          @if ($conversa->atendente_id && !$conversa->atendente)
            {{-- Sem FK no banco (0005): usuário removido deixa id órfão.
                 Dizer isso é melhor que mostrar um campo vazio. --}}
            Atendente removido (id {{ $conversa->atendente_id }})
          @elseif ($conversa->atendente)
            Atribuída em {{ $conversa->atribuida_em?->format('d/m/Y H:i') ?: '—' }}
          @else
            Não atribuída
          @endif
        </div>
      </form>
    </div>
  </div>

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
