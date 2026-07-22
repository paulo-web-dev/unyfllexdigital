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
    <span class="badge bg-secondary" id="lista-estado">Avisa quando houver novidade</span>
  </div>

  {{-- Banner do polling (Fatia 5). A TABELA NÃO É RECONSTRUÍDA EM JS: o botão
       recarrega a página, então paginação e filtro continuam valendo sem caso
       especial. Reconciliar linhas, ordem e filtro no cliente é onde a versão
       "linhas ao vivo" ficaria cara — e ninguém opera esta tela ainda. --}}
  {{-- Só `d-none` aqui. Pôr `d-flex` junto não esconderia nada: no Bootstrap 5
       `.d-flex` é declarado DEPOIS de `.d-none`, mesma especificidade, então
       vence. O `d-flex` entra pelo JS na hora de mostrar. --}}
  <div class="alert alert-primary py-2 small d-none justify-content-between align-items-center"
       id="novidades-banner"
       data-desde="{{ $desde }}"
       data-cursor="{{ $cursor }}"
       data-url="{{ route('admin.whatsapp.novidades') }}">
    <span id="novidades-texto"></span>
    <button type="button" class="btn btn-sm btn-primary" id="btn-atualizar-lista">Atualizar</button>
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

  {{-- Filtro por atendente (Fatia 7). Whitelist de três valores; o controller
       ignora qualquer outra coisa que venha na URL. --}}
  <div class="btn-group btn-group-sm mb-3" role="group" aria-label="Filtro por atendente">
    @foreach (['todos' => 'Todas', 'meus' => 'Minhas', 'sem' => 'Sem atendente'] as $valor => $rotulo)
      <a href="{{ route('admin.whatsapp.index', ['atendente' => $valor]) }}"
         class="btn {{ $filtro === $valor ? 'btn-primary' : 'btn-outline-primary' }}">{{ $rotulo }}</a>
    @endforeach
  </div>

  @if ($conversas->isEmpty())
    <div class="alert alert-info">
      @if ($filtro === 'meus')
        Nenhuma conversa atribuída a você.
      @elseif ($filtro === 'sem')
        Nenhuma conversa sem atendente.
      @else
        Nenhuma conversa 1:1 ainda. Mande uma mensagem para o número de teste.
      @endif
    </div>
  @else
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th scope="col">Contato</th>
            <th scope="col">Telefone</th>
            <th scope="col">Atendente</th>
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
              <td class="small">
                @if ($conversa->atendente)
                  {{ $conversa->atendente->name }}
                @elseif ($conversa->atendente_id)
                  {{-- Id órfão: sem FK no banco (0005), por decisão. --}}
                  <span class="text-danger">Atendente removido</span>
                @else
                  {{-- Estado normal, como "Não identificado": conversa nova
                       chega sem dono. --}}
                  <span class="text-muted">Não atribuída</span>
                @endif
              </td>
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

@push('scripts')
<script>
// ══════════════════════════════════════════════════════════════════════════
// POLLING DA LISTA — Fatia 5. Só o banner: conta quantas conversas mudaram
// desde o carregamento e oferece recarregar.
// ══════════════════════════════════════════════════════════════════════════
(function () {
  const banner = document.getElementById('novidades-banner');
  if (!banner) return;

  const CSRF   = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  const texto  = document.getElementById('novidades-texto');
  const estado = document.getElementById('lista-estado');
  const botao  = document.getElementById('btn-atualizar-lista');

  // Marco do SERVIDOR, gravado no render. Nunca Date.now(): relógio de máquina
  // de escritório erra minutos, e o banner ficaria preso em "nunca" ou em
  // "sempre".
  const DESDE  = banner.dataset.desde;
  // Marco por id de mensagem. Só o tempo não basta: mensagem atrasada não
  // atualiza a conversa, então o updated_at dela não sobe. Ver novidades().
  const CURSOR = banner.dataset.cursor || '0';
  const URL    = banner.dataset.url;

  let falhas = 0;
  let timer  = null;

  function stopPolling(motivo) {
    if (timer) { clearInterval(timer); timer = null; }
    if (estado && motivo) estado.textContent = motivo;
  }

  // location.reload() preserva a querystring — filtro e página atuais seguem.
  botao?.addEventListener('click', function () { window.location.reload(); });

  async function verificar() {
    if (document.hidden) return;

    try {
      const res = await fetch(URL + '?desde=' + encodeURIComponent(DESDE) + '&depois_de=' + CURSOR, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
      });

      if (res.status === 401 || res.status === 403 || res.status === 419) {
        stopPolling('Sessão expirada — recarregue a página');
        return;
      }

      if (!res.ok) {
        if (++falhas >= 3) stopPolling('Atualização automática pausada');
        return;
      }

      const data = await res.json();
      falhas = 0;

      const n = Number(data.novidades ?? 0);
      if (n > 0) {
        texto.textContent = n === 1
          ? '1 conversa com novidade'
          : n + ' conversas com novidade';
        banner.classList.remove('d-none');
        banner.classList.add('d-flex');
      }
    } catch (e) {
      if (++falhas >= 3) stopPolling('Atualização automática pausada');
    }
  }

  timer = setInterval(verificar, 5000);
})();
</script>
@endpush
