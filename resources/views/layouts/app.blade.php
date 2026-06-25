<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Unyflex Digital')</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/unyflex/colors_and_type.css') }}">
    <link rel="stylesheet" href="{{ asset('css/unyflex/ava.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ava-extras.css') }}">

    <style>
        :root {
            --bs-primary:          var(--brand-500);
            --bs-primary-rgb:      0, 163, 255;
            --bs-body-bg:          var(--bg-0);
            --bs-body-color:       var(--fg-2);
            --bs-border-color:     var(--line-2);
            --bs-border-radius:    var(--r-md);
            --bs-border-radius-sm: var(--r-sm);
            --bs-border-radius-lg: var(--r-lg);
            --bs-link-color:       var(--brand-300);
            --bs-link-hover-color: var(--brand-200);
            --bs-font-sans-serif:  var(--font-body);
            --bs-font-monospace:   var(--font-mono);
        }

        /* ── Search dropdown ─────────────────────────────────────── */
        .search-wrap {
            position: relative;
            flex: 1;
            max-width: 520px;
        }
        .search-wrap input {
            width: 100%;
            z-index: 99999; 
        }
        #search-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            background: var(--bg-2);
            border: 1px solid var(--line-2);
            border-radius: var(--r-lg);
            box-shadow: 0 24px 48px rgba(0,0,0,0.5);
            z-index: 99999;
            overflow: hidden;
            max-height: 420px;
            overflow-y: auto;
        }
        #search-dropdown.open { display: block; }

        .sd-header {
            padding: 10px 16px 8px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--fg-4);
            border-bottom: 1px solid var(--line-1);
        }

        .sd-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            text-decoration: none;
            color: inherit;
            transition: background 0.15s;
            border-bottom: 1px solid rgba(255,255,255,0.03);
        }
        .sd-item:hover { background: rgba(0,163,255,0.07); }
        .sd-item:last-child { border-bottom: none; }

        .sd-thumb {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: var(--bg-3);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .sd-thumb img   { width: 100%; height: 100%; object-fit: cover; }
        .sd-thumb svg   { width: 16px; height: 16px; stroke: var(--fg-4); fill: none; stroke-width: 1.75; }

        .sd-type {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .sd-type.minisserie { color: var(--brand-300); }
        .sd-type.capsula    { color: var(--fg-4); }

        .sd-title {
            font-size: 13px;
            font-weight: 500;
            color: var(--fg-1);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sd-sub {
            font-size: 11px;
            color: var(--fg-4);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sd-check {
            margin-left: auto;
            flex-shrink: 0;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: rgba(43,217,161,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .sd-check svg { width: 10px; height: 10px; stroke: #2BD9A1; fill: none; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; }

        .sd-empty {
            padding: 24px 16px;
            text-align: center;
            color: var(--fg-4);
            font-size: 13px;
        }

        .sd-loading {
            padding: 16px;
            text-align: center;
            color: var(--fg-4);
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        @keyframes spin { to { transform: rotate(360deg); } }
        .sd-spinner {
            width: 14px; height: 14px;
            border: 2px solid rgba(0,163,255,0.2);
            border-top-color: var(--brand-400);
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }

        /* highlight da query no texto */
        mark {
            background: rgba(0,163,255,0.25);
            color: var(--brand-200);
            border-radius: 2px;
            padding: 0 2px;
        }
        .topbar {
    position: relative;
    z-index: 99999;
}
    </style>

    @stack('styles')
</head>
<body>

<div class="app">

    {{-- ════════════════════════ SIDEBAR ════════════════════════ --}}
    <aside class="sidebar">

        <div class="brand">
            <div class="brand-mark">
                <img src="{{ asset('img/logo-unyflex.png') }}" alt="Unyflex">
            </div>
            <div class="brand-name">UNYFLEX <em>DIGITAL</em></div>
        </div>

        <div class="nav-group-label">Navegação</div>

        <a href="{{ route('dashboard') }}"
           class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i data-lucide="layout-dashboard" class="ico"></i>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('ava.cursos') }}"
           class="nav-item {{ request()->routeIs('ava.cursos*') ? 'active' : '' }}">
            <i data-lucide="film" class="ico"></i>
            <span>Minisséries</span>
        </a>

        <a href="{{ route('ava.modulares') }}"
           class="nav-item {{ request()->routeIs('ava.modulares*') ? 'active' : '' }}">
            <i data-lucide="book-open" class="ico"></i>
            <span>Cursos Modulares</span>
        </a>

        <a href="{{ route('perfil') }}"
           class="nav-item {{ request()->routeIs('perfil*') ? 'active' : '' }}">
            <i data-lucide="user" class="ico"></i>
            <span>Perfil</span>
        </a>

        <div class="nav-spacer"></div>

        <div style="padding:14px;background:linear-gradient(160deg,rgba(0,163,255,0.10),transparent);border:1px solid rgba(0,163,255,0.22);border-radius:14px;margin-bottom:10px;"></div>

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

    {{-- ════════════════════════ MAIN ════════════════════════ --}}
    <div class="main">

        {{-- Topbar --}}
        <header class="topbar">

            {{-- ── Busca com dropdown ─────────────────────────────── --}}
            <div class="search-wrap">
                <div class="search">
                    <svg class="ico-s" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="7"/>
                        <line x1="21" y1="21" x2="16.5" y2="16.5"/>
                    </svg>
                    <input
                        type="search"
                        id="global-search"
                        placeholder="Buscar minisséries, cápsulas, materiais…"
                        autocomplete="off"
                    >
                </div>

                {{-- Dropdown de resultados --}}
                <div id="search-dropdown">
                    <div class="sd-loading">
                        <div class="sd-spinner"></div>
                        Buscando…
                    </div>
                </div>
            </div>

            <div class="right">
                <button class="icon-btn" title="Mensagens">
                    <i data-lucide="message-square" class="ico"></i>
                </button>
                <button class="icon-btn" title="Notificações">
                    <i data-lucide="bell" class="ico"></i>
                    <span class="dot"></span>
                </button>
                <div class="user-chip">
                    <div class="avatar">{{ auth()->user()->initials ?? 'U' }}</div>
                    <div>
                        <div class="name">{{ auth()->user()->name ?? 'Usuário' }}</div>
                        <div class="role">{{ auth()->user()->role ?? 'Servidor público' }}</div>
                    </div>
                </div>
            </div>
        </header>

        @yield('content')

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<script>lucide.createIcons();</script>

{{-- ── Busca em tempo real ──────────────────────────────────────── --}}
<script>
(function () {
    const input    = document.getElementById('global-search');
    const dropdown = document.getElementById('search-dropdown');
    const CSRF     = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    let   timer    = null;
    let   lastQ    = '';

    // Highlight da query no texto
    function hl(text, q) {
        if (!q) return text;
        const escaped = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        return text.replace(new RegExp(escaped, 'gi'), m => `<mark>${m}</mark>`);
    }

    // Ícone de filme (minissérie sem foto)
    const filmSvg = `<svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="2.18"/><line x1="7" y1="2" x2="7" y2="22"/><line x1="17" y1="2" x2="17" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/></svg>`;
    const playSvg = `<svg viewBox="0 0 24 24"><polygon points="6 4 20 12 6 20 6 4"/></svg>`;
    const checkSvg = `<svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>`;

    function renderResults(data) {
        const { results, query } = data;

        if (!results.length) {
            dropdown.innerHTML = `<div class="sd-empty">Nenhum resultado para "<strong>${query}</strong>"</div>`;
            return;
        }

        // Separa minisséries e cápsulas
        const series   = results.filter(r => r.type === 'minisserie');
        const capsulas = results.filter(r => r.type === 'capsula');

        let html = '';

        if (series.length) {
            html += `<div class="sd-header">Minisséries</div>`;
            html += series.map(r => `
                <a href="${r.url}" class="sd-item" onclick="fecharDropdown()">
                    <div class="sd-thumb">
                        ${r.photo
                            ? `<img src="${r.photo}" alt="">`
                            : filmSvg}
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div class="sd-type minisserie">Minissérie</div>
                        <div class="sd-title">${hl(r.titulo, query)}</div>
                        ${r.sub ? `<div class="sd-sub">${hl(r.sub, query)}</div>` : ''}
                    </div>
                </a>
            `).join('');
        }

        if (capsulas.length) {
            html += `<div class="sd-header">Cápsulas</div>`;
            html += capsulas.map(r => `
                <a href="${r.url}" class="sd-item" onclick="fecharDropdown()">
                    <div class="sd-thumb">${playSvg}</div>
                    <div style="flex:1;min-width:0;">
                        <div class="sd-type capsula">Cápsula</div>
                        <div class="sd-title">${hl(r.titulo, query)}</div>
                        <div class="sd-sub">${r.sub}</div>
                    </div>
                    ${r.visto
                        ? `<div class="sd-check" title="Já assistida">${checkSvg}</div>`
                        : ''}
                </a>
            `).join('');
        }

        dropdown.innerHTML = html;
    }

    async function buscar(q) {
        if (q === lastQ) return;
        lastQ = q;

        if (q.length < 2) {
            fecharDropdown();
            return;
        }

        // Mostra loading
        dropdown.innerHTML = `<div class="sd-loading"><div class="sd-spinner"></div>Buscando…</div>`;
        abrirDropdown();

        try {
            const res  = await fetch(`/busca?q=${encodeURIComponent(q)}`, {
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
            });
            const data = await res.json();
            renderResults(data);
        } catch (e) {
            dropdown.innerHTML = `<div class="sd-empty">Erro ao buscar. Tente novamente.</div>`;
        }
    }

    function abrirDropdown() { dropdown.classList.add('open'); }
    window.fecharDropdown = function () { dropdown.classList.remove('open'); lastQ = ''; };

    // Debounce de 280ms
    input.addEventListener('input', function () {
        clearTimeout(timer);
        const q = this.value.trim();
        if (q.length < 2) { fecharDropdown(); return; }
        timer = setTimeout(() => buscar(q), 280);
    });

    // Abre ao focar se já tiver texto
    input.addEventListener('focus', function () {
        if (this.value.trim().length >= 2) buscar(this.value.trim());
    });

    // Navegar com teclado
    input.addEventListener('keydown', function (e) {
        const items = dropdown.querySelectorAll('.sd-item');
        const active = dropdown.querySelector('.sd-item:focus');
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            (active ? active.nextElementSibling : items[0])?.focus();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            (active ? active.previousElementSibling : null)?.focus() ?? input.focus();
        } else if (e.key === 'Escape') {
            fecharDropdown();
            input.blur();
        }
    });

    // Fecha ao clicar fora
    document.addEventListener('click', function (e) {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            fecharDropdown();
        }
    });
})();
</script>

@stack('scripts')
</body>
</html>
