@extends('layouts.admin')
@section('title', 'Inbox WhatsApp')

@section('content')
<div class="container-fluid py-3">

  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
      <h1 class="h4 mb-1">Inbox WhatsApp</h1>
      <p class="text-muted small mb-0">
        Instância de teste, modo sombra. O atendimento de produção continua no Chatwoot.
      </p>
    </div>
    <span class="badge bg-secondary">Atualiza só ao recarregar a página</span>
  </div>

  @if ($gruposOcultos > 0)
    {{-- Grupos ficam FORA do MVP na exibição, mas são capturados e gravados.
         Mostrar a contagem é o que distingue "oculto por decisão" de
         "não capturado" para quem confere o modo sombra. --}}
    <div class="alert alert-light border small py-2">
      <strong>{{ $gruposOcultos }}</strong> conversa(s) de grupo capturada(s) e não exibida(s) —
      grupos estão fora do MVP na tela, mas são persistidos integralmente.
    </div>
  @endif

  @if ($conversas->isEmpty())
    <div class="alert alert-info">
      Nenhuma conversa 1:1 ainda. Mande uma mensagem para o número de teste.
    </div>
  @else
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th scope="col">Contato</th>
            <th scope="col">Telefone</th>
            <th scope="col">Última mensagem</th>
            <th scope="col" class="text-end">&nbsp;</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($conversas as $conversa)
            <tr>
              <td>
                {{-- "Não identificado" é o estado COMUM, não a exceção: o teto
                     de cobertura medido na Fatia 0 é 28,8% na fonte principal
                     do funil. A tela precisa ficar apresentável assim. --}}
                {{ $conversa->nome_exibicao ?: 'Não identificado' }}
              </td>
              <td><code>{{ $conversa->chat_phone ?: '—' }}</code></td>
              <td class="text-muted small">
                {{ $conversa->ultima_mensagem_em?->format('d/m/Y H:i') ?: 'sem data' }}
              </td>
              <td class="text-end">
                <a href="{{ route('admin.whatsapp.thread', $conversa) }}" class="btn btn-sm btn-outline-primary">
                  Abrir
                </a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{ $conversas->links() }}
  @endif

</div>
@endsection
