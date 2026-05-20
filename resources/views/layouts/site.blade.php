<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#00A3FF">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  {{-- SEO --}}
  <title>@yield('meta_title', 'Unyflex Digital — Treinamentos para Servidores Públicos')</title>
  <meta name="description" content="@yield('meta_description', 'Miniséries de aprendizado rápido para servidores municipais, gestores, pregoeiros e auditores. Reconhecidos pelo MEC via Faculdade Unypublica.')">
  <meta name="keywords" content="@yield('meta_keywords', 'servidores públicos, treinamento, pregão eletrônico, lei 14133, gestão pública, capacitação')">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="{{ url()->current() }}">

  {{-- Open Graph --}}
  <meta property="og:type" content="website">
  <meta property="og:title" content="@yield('og_title', 'Unyflex Digital — Treinamentos High-Performance para o Setor Público')">
  <meta property="og:description" content="@yield('og_description', 'Miniséries de 10 a 20 min que transformam conhecimento em aplicação imediata. Instituição reconhecida pelo MEC.')">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta property="og:image" content="{{ asset('img/og-image.png') }}">
  <meta property="og:site_name" content="Unyflex Digital">
  <meta property="og:locale" content="pt_BR">

  {{-- Twitter Card --}}
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="@yield('og_title', 'Unyflex Digital')">
  <meta name="twitter:description" content="@yield('og_description', 'Treinamentos de alta performance para servidores públicos.')">
  <meta name="twitter:image" content="{{ asset('img/og-image.png') }}">

  {{-- Fonts --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

  {{-- Bootstrap 5 --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

  {{-- Design System --}}
  <link rel="stylesheet" href="{{ asset('css/colors_and_type.css') }}">
  <link rel="stylesheet" href="{{ asset('css/site.css') }}">

  {{-- Bootstrap + Brand override --}}
  <style>
    :root {
      --bs-primary: var(--brand-500);
      --bs-primary-rgb: 0,163,255;
      --bs-body-bg: var(--bg-0);
      --bs-body-color: var(--fg-2);
      --bs-border-color: var(--line-2);
      --bs-border-radius: var(--r-md);
      --bs-font-sans-serif: var(--font-body);
    }
  </style>

  @stack('styles')
</head>
<body class="has-urgency-bar">

  {{-- Barra de urgência --}}
  <div class="urgency-bar">
    <span>🔥 Oferta expira em breve — </span>
    <strong>R$ 1.990 por apenas R$ 998</strong>
    <span> — </span>
    <a href="{{ route('checkout') }}">Garantir minha vaga</a>
    <button class="urgency-bar-dismiss" style="background:none;border:none;color:#fff;margin-left:16px;cursor:pointer;font-size:16px;">×</button>
  </div>

  {{-- Navbar --}}
  @include('partials.navbar')

  {{-- Mobile menu --}}
  @include('partials.mobile-menu')

  {{-- Popup de conversão --}}
  @include('partials.popup')

  {{-- Conteúdo da página --}}
  @yield('content')

  {{-- Footer --}}
  @include('partials.footer')

  {{-- Bootstrap JS --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  {{-- Lucide Icons --}}
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

  {{-- App JS --}}
  <script src="{{ asset('js/app.js') }}"></script>

  @stack('scripts')
</body>
</html>
