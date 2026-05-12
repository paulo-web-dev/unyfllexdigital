<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=1280">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Painel') — Unyflex Admin</title>

  {{-- Bootstrap 5 --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

  {{-- Design Tokens --}}
  <link rel="stylesheet" href="{{ asset('css/unyflex/colors_and_type.css') }}">

  {{-- Estilos do Admin Panel --}}
  <link rel="stylesheet" href="{{ asset('css/admin.css') }}">

  @stack('styles')
</head>
<body>

<div class="app" id="admin-app">

  {{-- ═══════════════════════ SIDEBAR ═══════════════════════ --}}
  <aside class="sidebar">
    <div class="sidebar-brand">
      <img src="{{ asset('img/logo-unyflex.png') }}" alt="Unyflex">
      <div class="name">UNYFLEX <em>DIGITAL</em></div>
    </div>

    {{-- Visão Geral --}}
    <div class="sidebar-section-label">Visão Geral</div>
    <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
      <span>Dashboard</span>
    </a>
    <a href="{{ route('admin.analytics') }}" class="nav-item {{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
      <span>Analytics</span>
      <span class="nav-badge" style="background:rgba(43,217,161,0.15);color:#6FE6BD;">LIVE</span>
    </a>

    {{-- Operacional --}}
    <div class="sidebar-section-label">Operacional</div>
    <a href="{{ route('admin.alunos') }}" class="nav-item {{ request()->routeIs('admin.alunos') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      <span>Alunos</span>
      <span class="nav-badge">12.8k</span>
    </a>
    <a href="{{ route('admin.matriculas') }}" class="nav-item {{ request()->routeIs('admin.matriculas') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
      <span>Matrículas</span>
      <span class="nav-badge">47</span>
    </a>
    <a href="{{ route('admin.cursos') }}" class="nav-item {{ request()->routeIs('admin.cursos') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"/><line x1="7" y1="2" x2="7" y2="22"/><line x1="17" y1="2" x2="17" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/></svg>
      <span>Cursos</span>
    </a>
    <a href="{{ route('admin.vendas') }}" class="nav-item {{ request()->routeIs('admin.vendas') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      <span>Vendas</span>
    </a>
    <a href="{{ route('admin.cupons') }}" class="nav-item {{ request()->routeIs('admin.cupons') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
      <span>Cupons</span>
    </a>
    <a href="{{ route('admin.certif') }}" class="nav-item {{ request()->routeIs('admin.certif') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
      <span>Certificados</span>
    </a>

    {{-- Financeiro --}}
    <div class="sidebar-section-label">Financeiro</div>
    <a href="{{ route('admin.financeiro') }}" class="nav-item {{ request()->routeIs('admin.financeiro') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
      <span>Financeiro</span>
    </a>
    <a href="{{ route('admin.relatorios') }}" class="nav-item {{ request()->routeIs('admin.relatorios') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
      <span>Relatórios</span>
    </a>

    {{-- Sistema --}}
    <div class="sidebar-section-label">Sistema</div>
    <a href="{{ route('admin.suporte') }}" class="nav-item {{ request()->routeIs('admin.suporte') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
      <span>Suporte</span>
      <span class="nav-badge">8</span>
    </a>
    <a href="{{ route('admin.equipe') }}" class="nav-item {{ request()->routeIs('admin.equipe') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      <span>Equipe</span>
    </a>
    <a href="{{ route('admin.permissoes') }}" class="nav-item {{ request()->routeIs('admin.permissoes') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      <span>Permissões</span>
    </a>
    <a href="{{ route('admin.logs') }}" class="nav-item {{ request()->routeIs('admin.logs') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
      <span>Logs</span>
    </a>
    <a href="{{ route('admin.integ') }}" class="nav-item {{ request()->routeIs('admin.integ') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
      <span>Integrações</span>
    </a>
    <a href="{{ route('admin.config') }}" class="nav-item {{ request()->routeIs('admin.config') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
      <span>Configurações</span>
    </a>

    <div class="sidebar-spacer"></div>

    {{-- Rodapé da sidebar --}}
    <div class="sidebar-foot">
      <div class="sidebar-status">
        <span class="dot"></span>
        <span class="label">
          <div style="font-size:11px;font-weight:600;color:var(--fg-1);">Sistema operacional</div>
          <div style="font-size:10px;color:var(--fg-4);margin-top:1px;">99.98% uptime</div>
        </span>
      </div>
    </div>
  </aside>

  {{-- ═══════════════════════ MAIN ═══════════════════════ --}}
  <div class="main-wrap">

    {{-- Topbar --}}
    <div class="topbar">
      <div class="crumbs">
        <span>@yield('section', 'Admin')</span>
        <span class="sep">/</span>
        <strong>@yield('title', 'Painel')</strong>
      </div>

      <div class="search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;color:var(--fg-4);flex-shrink:0;"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>
        <input type="search" placeholder="Buscar alunos, cursos, matrículas…">
        <span class="kbd">⌘K</span>
      </div>

      <div class="topbar-actions">
        <a href="{{ route('dashboard') }}" class="icon-btn" title="Ir para AVA" style="text-decoration:none;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" style="width:17px;height:17px;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        </a>
        <button class="icon-btn" title="Notificações">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" style="width:17px;height:17px;"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span class="badge-dot"></span>
        </button>

        <div class="profile">
          <span class="avatar">{{ auth()->user()->initials ?? 'AD' }}</span>
          <div class="meta">
            <span class="nm">{{ auth()->user()->first_name ?? 'Admin' }}</span>
            <span class="rl">Power {{ auth()->user()->power ?? '' }}</span>
          </div>
        </div>

        {{-- Logout --}}
        <form action="{{ route('logout') }}" method="POST" style="display:inline;">
          @csrf
          <button type="submit" class="icon-btn" title="Sair">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" style="width:17px;height:17px;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          </button>
        </form>
      </div>
    </div>

    {{-- Conteúdo --}}
    <div class="page-wrap">
      @yield('content')
    </div>

  </div>{{-- /.main-wrap --}}

</div>{{-- /.app --}}

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<script>if(window.lucide) lucide.createIcons();</script>
@stack('scripts')
</body>
</html>
