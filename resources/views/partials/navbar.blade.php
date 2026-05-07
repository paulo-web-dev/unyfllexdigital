<nav class="site-navbar" id="siteNavbar">
  <a href="{{ route('home') }}" class="navbar-logo">
    <div class="navbar-logo-mark">
      <img src="{{ asset('img/logo-unyflex.png') }}" alt="Unyflex Digital">
    </div>
    <div>
      <div class="navbar-brand-text">UNYFLEX <span>DIGITAL</span></div>
      <span class="navbar-brand-sub">Faculdade Unypublica · Reconhecida MEC</span>
    </div>
  </a>

  <ul class="navbar-links">
    <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Início</a></li>
    <li><a href="{{ route('cursos') }}" class="{{ request()->routeIs('cursos*') ? 'active' : '' }}">Minisséries</a></li>
    <li><a href="{{ route('sobre') }}" class="{{ request()->routeIs('sobre') ? 'active' : '' }}">Sobre</a></li>
    <li><a href="{{ route('contato') }}" class="{{ request()->routeIs('contato') ? 'active' : '' }}">Contato</a></li>
  </ul>

  <div class="navbar-cta">
    <a href="{{ route('login') }}" class="btn-ux btn-ux-ghost btn-ux-sm">Entrar</a>
    <a href="{{ route('checkout') }}" class="btn-ux btn-ux-primary btn-ux-sm">Garantir vaga</a>
  </div>

  <button class="navbar-toggle" id="navbarToggle" aria-label="Menu">
    <span></span>
    <span></span>
    <span></span>
  </button>
</nav>
