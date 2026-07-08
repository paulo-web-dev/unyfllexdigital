<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Assinatura — Unyflex Digital')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/unyflex/colors_and_type.css') }}">
    <link rel="stylesheet" href="{{ asset('css/unyflex/ava.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ava-extras.css') }}">
    <style>
        .assinante-badge{display:inline-block;font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#0a2540;background:linear-gradient(90deg,#00a3ff,#5ad1ff);padding:2px 8px;border-radius:20px;margin-left:6px;}
    </style>
    @stack('styles')
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-mark"><i data-lucide="crown"></i></div>
            <div class="brand-name">UNYFLEX <em>DIGITAL</em></div>
        </div>

        <div class="nav-group-label">Assinatura</div>

        <a href="{{ route('assinante.home') }}" class="nav-item {{ request()->routeIs('assinante.home') ? 'active' : '' }}">
            <i data-lucide="layout-grid" class="ico"></i>
            <span>Catálogo</span>
        </a>
        <a href="{{ route('perfil') }}" class="nav-item {{ request()->routeIs('perfil*') ? 'active' : '' }}">
            <i data-lucide="user" class="ico"></i>
            <span>Meu perfil</span>
        </a>

        <div class="nav-spacer"></div>

        <a href="{{ route('logout') }}" class="nav-item"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i data-lucide="log-out" class="ico"></i>
            <span>Sair</span>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
    </aside>

    <div class="main">
        <header class="topbar">
            <div><strong>@yield('section', 'Assinatura')</strong><span class="assinante-badge">Assinante</span></div>
            <div>@yield('topbar')</div>
        </header>

        <div class="scroll">
            @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<script>lucide.createIcons();</script>
@stack('scripts')
</body>
</html>
