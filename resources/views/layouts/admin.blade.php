<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=1280">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Painel') — Unyflex Admin</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="{{ asset('css/unyflex/colors_and_type.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin.css') }}">

  <style>
    /* ── Busca admin ──────────────────────────────────────── */
    .admin-search-wrap { position: relative; flex: 1; max-width: 480px; }

    #admin-search-dropdown {
      display: none;
      position: absolute;
      top: calc(100% + 8px);
      left: 0;
      right: 0;
      background: var(--bg-2);
      border: 1px solid var(--line-2);
      border-radius: 14px;
      box-shadow: 0 24px 48px rgba(0,0,0,0.55);
      z-index: 99999;
      overflow: hidden;
      max-height: 440px;
      overflow-y: auto;
    }
    #admin-search-dropdown.open { display: block; }

    .asd-section {
      padding: 8px 14px 4px;
      font-size: 10px;
      font-weight: 700;
      letter-spacing: 0.14em;
      text-transform: uppercase;
      color: var(--fg-4);
      border-top: 1px solid var(--line-1);
    }
    .asd-section:first-child { border-top: none; }

    .asd-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px 14px;
      text-decoration: none;
      color: inherit;
      transition: background 0.15s;
      cursor: pointer;
    }
    .asd-item:hover { background: rgba(0,163,255,0.07); }

    .asd-icon {
      width: 32px; height: 32px;
      border-radius: 9px;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
    }
    .asd-icon.aluno    { background: rgba(0,163,255,0.12); color: var(--brand-300); }
    .asd-icon.matricula{ background: rgba(43,217,161,0.12); color: #6FE6BD; }
    .asd-icon.curso    { background: rgba(232,183,101,0.12); color: var(--gold-400); }

    .asd-title {
      font-size: 13px;
      font-weight: 500;
      color: var(--fg-1);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .asd-sub {
      font-size: 11px;
      color: var(--fg-4);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .asd-meta {
      margin-left: auto;
      font-size: 10px;
      color: var(--fg-4);
      white-space: nowrap;
      flex-shrink: 0;
    }
    .asd-empty {
      padding: 24px 14px;
      text-align: center;
      color: var(--fg-4);
      font-size: 13px;
    }
    .asd-loading {
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
    .asd-spinner {
      width: 13px; height: 13px;
      border: 2px solid rgba(0,163,255,0.2);
      border-top-color: var(--brand-400);
      border-radius: 50%;
      animation: spin 0.7s linear infinite;
    }
    mark {
      background: rgba(0,163,255,0.22);
      color: var(--brand-200);
      border-radius: 2px;
      padding: 0 2px;
    }
  </style>

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

    <div class="sidebar-section-label">Visão Geral</div>
    <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
      <span>Dashboard</span>
    </a>

    {{-- Analytics: apenas super admin --}}
    @can('admin.analytics')
    <a href="{{ route('admin.analytics') }}" class="nav-item {{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
      <span>Analytics</span>
      <span class="nav-badge" style="background:rgba(43,217,161,0.15);color:#6FE6BD;">LIVE</span>
    </a>
    <a href="{{ route('admin.referral') }}" class="nav-item {{ request()->routeIs('admin.referral') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
      </svg>
      <span>Links Referral</span>
    </a>
    <a href="{{ route('admin.leads-guia') }}"
    class="nav-item {{ request()->routeIs('admin.leads-guia') ? 'active' : '' }}">
   <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
     <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
     <polyline points="14 2 14 8 20 8"/>
     <line x1="16" y1="13" x2="8" y2="13"/>
     <line x1="16" y1="17" x2="8" y2="17"/>
     <polyline points="10 9 9 9 8 9"/>
   </svg>
   <span>Leads Isca</span>
 </a>
    <a href="{{ route('admin.funil') }}" class="nav-item {{ request()->routeIs('admin.funil') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
        stroke-linecap="round" stroke-linejoin="round">
       <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
     </svg>
    <span>Funil</span>
   </a>
   <a href="{{ route('admin.proposta') }}"
   class="nav-item {{ request()->routeIs('admin.proposta') ? 'active' : '' }}">
  <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
    <polyline points="14 2 14 8 20 8"/>
    <line x1="16" y1="13" x2="8" y2="13"/>
    <line x1="16" y1="17" x2="8" y2="17"/>
    <polyline points="10 9 9 9 8 9"/>
  </svg>
  <span>Gerar Proposta</span>
</a>
    @endcan

    <div class="sidebar-section-label">Operacional</div>

    {{-- Alunos: super admin e comercial --}}
    @can('admin.alunos')
    <a href="{{ route('admin.alunos') }}" class="nav-item {{ request()->routeIs('admin.alunos*') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      <span>Alunos</span>
    </a>
    @endcan
    @can('admin.matriculas')
    <a href="{{ route('admin.meu-link') }}"
       class="nav-item {{ request()->routeIs('admin.meu-link') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
      </svg>
      <span>Meu Link</span>
    </a>
    @endcan
    {{-- Matrículas: super admin e comercial --}}
    @can('admin.matriculas')
    <a href="{{ route('admin.matriculas') }}" class="nav-item {{ request()->routeIs('admin.matriculas*') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
      <span>Matrículas</span>
    </a>
    @endcan

    {{-- Cursos / Materiais: apenas super admin --}}
    @can('admin.cursos')
    <a href="{{ route('admin.cursos') }}" class="nav-item {{ request()->routeIs('admin.cursos*') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"/><line x1="7" y1="2" x2="7" y2="22"/><line x1="17" y1="2" x2="17" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/></svg>
      <span>Cursos</span>
    </a>
    <a href="{{ route('admin.materiais') }}" class="nav-item {{ request()->routeIs('admin.materiais*') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      <span>Materiais</span>
    </a>
    <a href="{{ route('admin.vendas') }}" class="nav-item {{ request()->routeIs('admin.vendas') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      <span>Vendas</span>
    </a>
    <a href="{{ route('admin.cupons') }}" class="nav-item {{ request()->routeIs('admin.cupons') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
      <span>Cupons</span>
    </a>
    @endcan

    <a href="{{ route('admin.certif') }}" class="nav-item {{ request()->routeIs('admin.certif') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
      <span>Certificados</span>
    </a>

    {{-- Financeiro / Relatórios: apenas super admin --}}
    @can('admin.financeiro')
    <div class="sidebar-section-label">Financeiro</div>
    <a href="{{ route('admin.financeiro') }}" class="nav-item {{ request()->routeIs('admin.financeiro') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
      <span>Financeiro</span>
    </a>
    @endcan

    @can('admin.relatorios')
    <a href="{{ route('admin.relatorios') }}" class="nav-item {{ request()->routeIs('admin.relatorios') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
      <span>Relatórios</span>
    </a>
    @endcan

    {{-- Sistema: apenas super admin --}}
    @can('admin.super')
    <a href="{{ route('admin.referral') }}" class="nav-item {{ request()->routeIs('admin.referral') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
      </svg>
      <span>Links Referral</span>
    </a>
     <a href="{{ route('admin.funil') }}" class="nav-item {{ request()->routeIs('admin.funil') ? 'active' : '' }}">
          <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
            stroke-linecap="round" stroke-linejoin="round">
           <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
         </svg>
        <span>Funil</span>
       </a>
    
    <div class="sidebar-section-label">Sistema</div>
    <a href="{{ route('admin.suporte') }}"   class="nav-item {{ request()->routeIs('admin.suporte')   ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
      <span>Suporte</span>
    </a>
    <a href="{{ route('admin.equipe') }}"    class="nav-item {{ request()->routeIs('admin.equipe')    ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      <span>Equipe</span>
    </a>
    <a href="{{ route('admin.permissoes') }}" class="nav-item {{ request()->routeIs('admin.permissoes') ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      <span>Permissões</span>
    </a>
    <a href="{{ route('admin.logs') }}"      class="nav-item {{ request()->routeIs('admin.logs')      ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
      <span>Logs</span>
    </a>
    <a href="{{ route('admin.integ') }}"     class="nav-item {{ request()->routeIs('admin.integ')     ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
      <span>Integrações</span>
    </a>
    <a href="{{ route('admin.config') }}"    class="nav-item {{ request()->routeIs('admin.config')    ? 'active' : '' }}">
      <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
      <span>Configurações</span>
    </a>
    @endcan

    {{-- Badge de role do usuário logado --}}
    @if(auth()->user()->power == 13)
    <div style="margin:12px 16px 0;padding:10px 14px;background:rgba(232,183,101,0.08);border:1px solid rgba(232,183,101,0.25);border-radius:var(--r-md);">
      <div style="font-size:10px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--gold-400);margin-bottom:3px;">Acesso Comercial</div>
      <div style="font-size:11px;color:var(--fg-4);">Carteira: <strong style="color:var(--fg-2);">{{ auth()->user()->name }}</strong></div>
    </div>
    @endif

    <div class="sidebar-spacer"></div>

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

      {{-- ── Busca global com dropdown ─────────────────────────── --}}
      <div class="admin-search-wrap">
        <div class="search" style="position:relative;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
               stroke-linecap="round" stroke-linejoin="round"
               style="width:14px;height:14px;color:var(--fg-4);flex-shrink:0;">
            <circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/>
          </svg>
          <input type="search"
                 id="admin-search-input"
                 placeholder="Buscar alunos, cursos, matrículas…"
                 autocomplete="off">
          <span class="kbd" id="admin-search-kbd">⌘K</span>
        </div>

        <div id="admin-search-dropdown">
          {{-- preenchido pelo JS --}}
        </div>
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

        <form action="{{ route('logout') }}" method="POST" style="display:inline;">
          @csrf
          <button type="submit" class="icon-btn" title="Sair">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" style="width:17px;height:17px;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          </button>
        </form>
      </div>
    </div>

    <div class="page-wrap">
      @yield('content')
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<script>if(window.lucide) lucide.createIcons();</script>

{{-- ── Busca global admin ────────────────────────────────────────── --}}
<script>
(function () {
  const input    = document.getElementById('admin-search-input');
  const dropdown = document.getElementById('admin-search-dropdown');
  const kbd      = document.getElementById('admin-search-kbd');
  const CSRF     = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  let   timer    = null;
  let   lastQ    = '';

  const LABELS = { aluno: 'Alunos', matricula: 'Matrículas', curso: 'Minisséries' };
  const COLORS = { aluno: 'aluno', matricula: 'matricula', curso: 'curso' };

  // Highlight da query
  function hl(text, q) {
    if (!text || !q) return text ?? '';
    const esc = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    return String(text).replace(new RegExp(esc, 'gi'), m => `<mark>${m}</mark>`);
  }

  // Ícone SVG inline por nome
  const icons = {
    'user'     : '<svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:1.75;stroke-linecap:round;stroke-linejoin:round;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
    'file-text': '<svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:1.75;stroke-linecap:round;stroke-linejoin:round;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
    'film'     : '<svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:1.75;stroke-linecap:round;stroke-linejoin:round;"><rect x="2" y="2" width="20" height="20" rx="2.18"/><line x1="7" y1="2" x2="7" y2="22"/><line x1="17" y1="2" x2="17" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/></svg>',
  };

  function renderDropdown(data) {
    const { results, query } = data;

    if (!results.length) {
      dropdown.innerHTML = `<div class="asd-empty">Nenhum resultado para "<strong>${query}</strong>"</div>`;
      return;
    }

    // Agrupa por tipo
    const grupos = {};
    results.forEach(r => {
      if (!grupos[r.tipo]) grupos[r.tipo] = [];
      grupos[r.tipo].push(r);
    });

    let html = '';
    Object.entries(grupos).forEach(([tipo, items]) => {
      html += `<div class="asd-section">${LABELS[tipo] ?? tipo}</div>`;
      html += items.map(r => `
        <a href="${r.url}" class="asd-item">
          <div class="asd-icon ${COLORS[r.tipo] ?? ''}">${icons[r.icone] ?? ''}</div>
          <div style="flex:1;min-width:0;">
            <div class="asd-title">${hl(r.titulo, query)}</div>
            ${r.sub ? `<div class="asd-sub">${hl(r.sub, query)}</div>` : ''}
          </div>
          ${r.meta ? `<span class="asd-meta">${r.meta}</span>` : ''}
        </a>
      `).join('');
    });

    dropdown.innerHTML = html;
  }

  async function buscar(q) {
    if (q === lastQ) return;
    lastQ = q;

    if (q.length < 2) { fechar(); return; }

    dropdown.innerHTML = `<div class="asd-loading"><div class="asd-spinner"></div>Buscando…</div>`;
    abrir();

    try {
      const res  = await fetch(`{{ route('admin.busca') }}?q=${encodeURIComponent(q)}`, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
      });
      const data = await res.json();
      renderDropdown(data);
    } catch (e) {
      dropdown.innerHTML = `<div class="asd-empty">Erro ao buscar.</div>`;
    }
  }

  function abrir() { dropdown.classList.add('open'); kbd.style.display = 'none'; }
  function fechar() { dropdown.classList.remove('open'); lastQ = ''; kbd.style.display = ''; }

  // Debounce 280ms
  input.addEventListener('input', function () {
    clearTimeout(timer);
    const q = this.value.trim();
    if (q.length < 2) { fechar(); return; }
    timer = setTimeout(() => buscar(q), 280);
  });

  input.addEventListener('focus', function () {
    if (this.value.trim().length >= 2) buscar(this.value.trim());
  });

  // Navegar com teclado
  input.addEventListener('keydown', function (e) {
    const items  = [...dropdown.querySelectorAll('.asd-item')];
    const active = document.activeElement;

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      const idx = items.indexOf(active);
      (idx < 0 ? items[0] : items[idx + 1] ?? items[0])?.focus();
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      const idx = items.indexOf(active);
      (idx <= 0 ? input : items[idx - 1])?.focus();
    } else if (e.key === 'Escape') {
      fechar(); input.blur();
    }
  });

  // ⌘K / Ctrl+K abre a busca
  document.addEventListener('keydown', function (e) {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
      e.preventDefault();
      input.focus();
      input.select();
    }
  });

  // Fecha ao clicar fora
  document.addEventListener('click', function (e) {
    if (!input.closest('.admin-search-wrap').contains(e.target)) fechar();
  });
})();
</script>

@stack('scripts')
</body>
</html>
