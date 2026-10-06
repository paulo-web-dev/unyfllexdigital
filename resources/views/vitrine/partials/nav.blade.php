<header class="vt-nav" id="topo">
  <div class="vt-wrap vt-nav-in">
    <a href="{{ route('assinatura.home') }}" class="vt-logo" aria-label="Unyflex — Assinatura Premium">
      <img src="{{ asset('img/logo-unyflex.png') }}" alt="">
      <span class="vt-logo-txt">UNYFLEX <em>PREMIUM</em></span>
    </a>

    <button class="vt-nav-toggle" type="button" aria-label="Abrir menu" aria-expanded="false" aria-controls="vt-menu" data-vt-menu>
      <i data-lucide="menu"></i>
    </button>

    <nav class="vt-menu" id="vt-menu" aria-label="Menu principal">
      <a href="{{ route('assinatura.home') }}" class="{{ request()->routeIs('assinatura.home') ? 'ativo' : '' }}">Início</a>
      <a href="{{ route('assinatura.categorias') }}" class="{{ request()->routeIs('assinatura.categoria*') ? 'ativo' : '' }}">Cursos e categorias</a>
      <a href="{{ route('assinatura.planos') }}" class="{{ request()->routeIs('assinatura.planos') ? 'ativo' : '' }}">Planos</a>
      <a href="{{ route('assinatura.planos') }}#como-contratar">Como contratar</a>
      <a href="{{ route('login') }}" class="vt-menu-entrar"><i data-lucide="log-in"></i>Entrar</a>
      <x-vitrine.whatsapp class="vt-btn vt-btn-primary vt-btn-sm" content-name="Menu: Solicitar proposta">
        <i data-lucide="message-circle"></i>Solicitar proposta
      </x-vitrine.whatsapp>
    </nav>
  </div>
</header>
