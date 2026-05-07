<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Unyflex Digital')</title>

    {{-- Bootstrap CSS via CDN (antes dos tokens) --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    {{-- Design System: tokens + componentes AVA --}}
    <link rel="stylesheet" href="{{ asset('css/unyflex/colors_and_type.css') }}">
    <link rel="stylesheet" href="{{ asset('css/unyflex/ava.css') }}">

    {{-- Bootstrap override (variáveis Unyflex sobre BS5) --}}
    <style>
        :root {
            --bs-primary:            var(--brand-500);
            --bs-primary-rgb:        0, 163, 255;
            --bs-body-bg:            var(--bg-0);
            --bs-body-color:         var(--fg-2);
            --bs-border-color:       var(--line-2);
            --bs-border-radius:      var(--r-md);
            --bs-border-radius-sm:   var(--r-sm);
            --bs-border-radius-lg:   var(--r-lg);
            --bs-link-color:         var(--brand-300);
            --bs-link-hover-color:   var(--brand-200);
            --bs-font-sans-serif:    var(--font-body);
            --bs-font-monospace:     var(--font-mono);
        }
    </style>

    @stack('styles')
</head>
<body>

<div class="app">

    {{-- ═══════════════════════════════ SIDEBAR ═══════════════════════════════ --}}
    <aside class="sidebar">

        {{-- Marca --}}
        <div class="brand">
            <div class="brand-mark">
                <img src="{{ asset('img/logo-unyflex.png') }}" alt="Unyflex">
            </div>
            <div class="brand-name">UNYFLEX <em>DIGITAL</em></div>
        </div>

        {{-- Grupo de navegação --}}
        <div class="nav-group-label">Navegação</div>

        <a href="{{ route('dashboard') }}"
           class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i data-lucide="layout-dashboard" class="ico"></i>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('cursos') }}"
           class="nav-item {{ request()->routeIs('cursos*') ? 'active' : '' }}">
            <i data-lucide="film" class="ico"></i>
            <span>Minisséries</span>
        </a>

        <a href="{{ route('perfil') }}"
           class="nav-item {{ request()->routeIs('perfil') ? 'active' : '' }}">
            <i data-lucide="user" class="ico"></i>
            <span>Perfil</span>
        </a>

        {{-- Espaçador --}}
        <div class="nav-spacer"></div>

        {{-- Card da trilha --}}
        <div style="
            padding: 14px;
            background: linear-gradient(160deg, rgba(0,163,255,0.10), transparent);
            border: 1px solid rgba(0,163,255,0.22);
            border-radius: 14px;
            margin-bottom: 10px;
        ">
            <div class="eyebrow" style="font-size:11px;">SUA TRILHA</div>
            <div style="color:#fff; font-size:13px; font-weight:600; margin-top:6px; line-height:1.35;">
                4 cápsulas para concluir a Temporada 1
            </div>
            <div style="height:4px; background:rgba(255,255,255,0.08); border-radius:2px; overflow:hidden; margin-top:10px;">
                <div style="height:100%; width:67%; background:linear-gradient(90deg,#00C2FF,#0072FF);"></div>
            </div>
        </div>

        {{-- Sair --}}
        <a href="{{ route('logout') }}"
           class="nav-item"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i data-lucide="log-out" class="ico"></i>
            <span>Sair</span>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>

    </aside>
    {{-- /sidebar --}}

    {{-- ═══════════════════════════════ MAIN ═══════════════════════════════════ --}}
    <div class="main">

        {{-- Topbar --}}
        <header class="topbar">
            <div class="search">
                <svg class="ico-s" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="7"/>
                    <line x1="21" y1="21" x2="16.5" y2="16.5"/>
                </svg>
                <input type="search" placeholder="Buscar minisséries, cápsulas, materiais…">
            </div>

            <div class="right">
                {{-- Mensagens --}}
                <button class="icon-btn" title="Mensagens">
                    <i data-lucide="message-square" class="ico"></i>
                </button>

                {{-- Notificações --}}
                <button class="icon-btn" title="Notificações">
                    <i data-lucide="bell" class="ico"></i>
                    <span class="dot"></span>
                </button>

                {{-- Chip do usuário --}}
                <div class="user-chip">
                    <div class="avatar">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}{{ strtoupper(substr(explode(' ', auth()->user()->name ?? 'X')[1] ?? 'X', 0, 1)) }}
                    </div>
                    <div>
                        <div class="name">{{ auth()->user()->name ?? 'Usuário' }}</div>
                        <div class="role">{{ auth()->user()->role ?? 'Servidor público' }}</div>
                    </div>
                </div>
            </div>
        </header>
        {{-- /topbar --}}

        {{-- Conteúdo da página --}}
        @yield('content')

    </div>
    {{-- /main --}}

</div>
{{-- /app --}}

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- Lucide Icons via CDN --}}
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<script>lucide.createIcons();</script>

@stack('scripts')
</body>
</html>
