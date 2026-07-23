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
    {{-- O badge reflete o PORTÃO de envio (Fatia 6), que segue fechado por
         padrão. Modo sombra: o atendimento de produção continua no Chatwoot. O
         polling da Fatia 5 não muda isso — ele só lê. --}}
    <div class="text-end">
      @if ($envioHabilitado)
        <span class="badge bg-success">Envio habilitado</span>
      @else
        <span class="badge bg-secondary">Envio desabilitado — modo sombra</span>
      @endif
      <div class="small text-muted mt-1" id="polling-estado">Atualizando automaticamente</div>
    </div>
  </div>

  @include('admin.whatsapp._crm', ['contato' => $contato])

  @if (session('sucesso'))
    <div class="alert alert-success py-2 small">{{ session('sucesso') }}</div>
  @endif
  @if (session('aviso'))
    <div class="alert alert-warning py-2 small">{{ session('aviso') }}</div>
  @endif
  @if (session('erro'))
    <div class="alert alert-danger py-2 small">{{ session('erro') }}</div>
  @endif
  @error('atendente_id')
    <div class="alert alert-danger py-2 small">{{ $message }}</div>
  @enderror

  {{-- Aviso do polling quando a atribuição muda em outra sessão. Ele AVISA e
       pede recarregar — o formulário abaixo não é sincronizado sozinho, ver o
       comentário no <input hidden>. --}}
  <div class="alert alert-warning py-2 small d-none" id="aviso-atribuicao"></div>

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
             cima do trabalho de outra pessoa.

             O POLLING DA FATIA 5 NÃO ATUALIZA ESTE CAMPO, e isso é a decisão
             central daquela fatia. Ele existe para dizer "era isto que eu via
             quando decidi". Se o polling o sincronizasse, o <select>
             desatualizado de alguém passaria por cima da atribuição de outra
             pessoa SEM o aviso — a atualização automática teria piorado a
             corrida que esta guarda tenta estreitar. --}}
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
        {{-- Rótulo vem do model (rotuloAtribuicao()), não montado aqui: o
             polling renderiza o MESMO texto, e duas versões divergiriam na
             primeira mudança. Inclui id órfão ("Atendente removido"), que é
             estado previsto por não haver FK no 0005. --}}
        <div class="col-auto small text-muted" id="rotulo-atribuicao">{{ $conversa->rotuloAtribuicao() }}</div>
      </form>
    </div>
  </div>

  {{-- O container existe SEMPRE, mesmo sem mensagem: é nele que o polling
       insere, e "conversa vazia agora" é justamente o caso em que a primeira
       mensagem precisa aparecer sozinha. --}}
  <div class="alert alert-info {{ $mensagens->isEmpty() ? '' : 'd-none' }}" id="thread-vazia">
    Nenhuma mensagem nesta conversa.
  </div>

  <div class="d-flex flex-column gap-2"
       id="thread-mensagens"
       data-cursor="{{ $cursor }}"
       data-assinatura="{{ $conversa->assinaturaAtribuicao() }}"
       data-url="{{ route('admin.whatsapp.mensagens', $conversa) }}">
    @foreach ($mensagens as $mensagem)
      @include('admin.whatsapp._mensagem', ['mensagem' => $mensagem])
    @endforeach
  </div>

  {{-- Aparece só quando chega mensagem e a pessoa NÃO está no fim da página.
       Rolar a tela de quem está lendo histórico é o jeito clássico de tornar a
       atualização automática irritante. --}}
  <div class="position-sticky bottom-0 text-center py-2 d-none" id="novas-abaixo">
    <button type="button" class="btn btn-sm btn-primary shadow" id="btn-novas-abaixo"></button>
  </div>

  {{-- Envio (Fatia 6). O PORTÃO é quem decide se algo sai, não este formulário:
       com o portão fechado (default) o campo vem desabilitado — afordância
       honesta, não um botão que finge funcionar e falha calado. A trava real é
       o provedor (LogicException), que barra até um POST forjado com o campo
       reabilitado no DevTools. O balão da resposta, quando o portão abrir, será
       renderizado pelo servidor (from_me) como qualquer outro. --}}
  <div class="card mt-3">
    <div class="card-body py-2">
      @unless ($envioHabilitado)
        <div class="alert alert-secondary py-2 small mb-2">
          <strong>Envio desabilitado (Fatia 6).</strong> O campo está aqui, mas
          nada é enviado até o checkpoint liberar o portão
          (<code>UAZAPI_ENVIO_HABILITADO</code>). Modo sombra: o atendimento de
          produção segue no Chatwoot.
        </div>
      @endunless
      <form method="POST" action="{{ route('admin.whatsapp.enviar', $conversa) }}">
        @csrf
        <label class="form-label small text-muted mb-1" for="texto">Responder</label>
        <textarea name="texto" id="texto" rows="2" maxlength="4096"
                  class="form-control @error('texto') is-invalid @enderror"
                  placeholder="{{ $envioHabilitado ? 'Escreva uma mensagem…' : 'Envio desabilitado — Fatia 6' }}"
                  @disabled(! $envioHabilitado)>{{ old('texto') }}</textarea>
        @error('texto')
          <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        <div class="text-end mt-2">
          <button type="submit" class="btn btn-sm btn-primary" @disabled(! $envioHabilitado)>
            Enviar
          </button>
        </div>
      </form>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
// ══════════════════════════════════════════════════════════════════════════
// POLLING DA THREAD — Fatia 5. Padrão do checkout.blade.php: fetch com
// X-CSRF-TOKEN + Accept, e stopPolling() explícito.
//
// NOTA: o layout admin também declara uma const CSRF, mas dentro de uma IIFE
// própria — ela não é global, então esta lê a meta tag de novo.
// ══════════════════════════════════════════════════════════════════════════
(function () {
  const cont = document.getElementById('thread-mensagens');
  if (!cont) return;

  const CSRF     = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  const URL_BASE = cont.dataset.url;
  const vazia    = document.getElementById('thread-vazia');
  const rotulo   = document.getElementById('rotulo-atribuicao');
  const aviso    = document.getElementById('aviso-atribuicao');
  const estado   = document.getElementById('polling-estado');
  const barra    = document.getElementById('novas-abaixo');
  const btnNovas = document.getElementById('btn-novas-abaixo');

  let cursor     = Number(cont.dataset.cursor || 0);
  let assinatura = cont.dataset.assinatura || '';
  let falhas     = 0;
  let naoLidas   = 0;
  let timer      = null;

  function stopPolling(motivo) {
    if (timer) { clearInterval(timer); timer = null; }
    if (estado && motivo) estado.textContent = motivo;
  }

  function noFim() {
    return (window.innerHeight + window.scrollY) >= (document.body.offsetHeight - 120);
  }

  // Insere na POSIÇÃO cronológica, não no fim. O delta vem ordenado por id
  // (ordem de inserção), então uma entrega atrasada pode ter enviada_em mais
  // antiga que balões já na tela. Sem data → fim, igual ao ORDER BY do
  // servidor (enviada_em IS NULL por último).
  function instante(el) {
    const bruto = el.dataset.enviadaEm || '';
    const t = bruto ? Date.parse(bruto) : NaN;
    return isNaN(t) ? null : t;
  }

  function inserir(el) {
    // Comparação como DATA, não como string: ISO com fusos diferentes ordena
    // errado lexicograficamente, e não vale apostar que o fuso do servidor
    // nunca muda.
    const quando = instante(el);
    if (quando !== null) {
      for (const atual of cont.children) {
        const dele = instante(atual);
        if (dele !== null && dele > quando) { cont.insertBefore(el, atual); return; }
      }
    }
    cont.appendChild(el);
  }

  function mostrarBarra() {
    if (!barra || !btnNovas) return;
    btnNovas.textContent = naoLidas === 1
      ? '1 nova mensagem ↓'
      : naoLidas + ' novas mensagens ↓';
    barra.classList.remove('d-none');
  }

  btnNovas?.addEventListener('click', function () {
    window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
    naoLidas = 0;
    barra.classList.add('d-none');
  });

  async function verificar() {
    // Aba de fundo não gasta consulta. O cursor é absoluto, então o primeiro
    // tick visível recupera tudo de uma vez.
    if (document.hidden) return;

    try {
      const res = await fetch(URL_BASE + '?depois_de=' + cursor, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
      });

      // Sessão expirada não pode virar uma requisição a cada 5s contra a tela
      // de login.
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

      const estavaNoFim = noFim();

      (data.mensagens ?? []).forEach(function (html) {
        const molde = document.createElement('template');
        molde.innerHTML = html.trim();
        const el = molde.content.firstElementChild;
        if (!el) return;
        // Guarda de idempotência na tela: se por qualquer motivo o mesmo id
        // voltar, não duplica o balão.
        if (cont.querySelector('[data-msg-id="' + el.dataset.msgId + '"]')) return;
        inserir(el);
        naoLidas++;
      });

      if ((data.mensagens ?? []).length > 0) {
        vazia?.classList.add('d-none');
        if (estavaNoFim) {
          window.scrollTo({ top: document.body.scrollHeight });
          naoLidas = 0;
        } else {
          mostrarBarra();
        }
      }

      cursor = Number(data.cursor ?? cursor);

      // ATRIBUIÇÃO: atualiza o rótulo visível e AVISA. Não toca no <select>
      // nem no campo escondido atendente_atual_id — sincronizá-lo desarmaria a
      // guarda de sobrescrita da Fatia 7. O payload nem traz o id, justamente
      // para que isso não seja possível por descuido.
      if (data.atribuicao && data.atribuicao.assinatura !== assinatura) {
        assinatura = data.atribuicao.assinatura;
        if (rotulo) rotulo.textContent = data.atribuicao.rotulo;
        if (aviso) {
          aviso.textContent = 'A atribuição mudou em outra sessão: ' + data.atribuicao.rotulo
            + '. Recarregue antes de salvar, ou sua alteração será recusada.';
          aviso.classList.remove('d-none');
        }
      }
    } catch (e) {
      if (++falhas >= 3) stopPolling('Atualização automática pausada');
    }
  }

  timer = setInterval(verificar, 5000);
})();
</script>
@endpush
