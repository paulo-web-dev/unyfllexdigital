<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=AW-18192995141"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'AW-18192995141');
  </script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#00A3FF">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- SEO --}}
  <title>@yield('meta_title', 'Blog — Unyflex Digital')</title>
  <meta name="description" content="@yield('meta_description', 'Conteúdo prático sobre licitações, Lei 14.133, controle interno, LGPD e gestão pública para servidores municipais.')">
  <meta name="keywords" content="@yield('meta_keywords', 'licitações, lei 14133, gestão pública, servidores públicos, pregão eletrônico')">
  <meta name="robots" content="@yield('robots', 'index, follow')">
  <link rel="canonical" href="@yield('canonical', url()->current())">

  {{-- Open Graph --}}
  <meta property="og:type" content="@yield('og_type', 'website')">
  <meta property="og:title" content="@yield('og_title', 'Blog — Unyflex Digital')">
  <meta property="og:description" content="@yield('og_description', 'Conteúdo prático para servidores públicos.')">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta property="og:image" content="@yield('og_image', asset('img/og-image.png'))">
  <meta property="og:site_name" content="Unyflex Digital">
  <meta property="og:locale" content="pt_BR">
  @hasSection('article_published')
  <meta property="article:published_time" content="@yield('article_published')">
  @endif
  @hasSection('article_modified')
  <meta property="article:modified_time" content="@yield('article_modified')">
  @endif

  {{-- Twitter Card --}}
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="@yield('og_title', 'Unyflex Digital')">
  <meta name="twitter:description" content="@yield('og_description', 'Treinamentos de alta performance para servidores públicos.')">
  <meta name="twitter:image" content="@yield('og_image', asset('img/og-image.png'))">

  {{-- Schema.org / JSON-LD --}}
  @stack('schema')

  {{-- Fonts --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

  {{-- Bootstrap 5 --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

  {{-- Design System --}}
  <link rel="stylesheet" href="{{ asset('css/colors_and_type.css') }}">
  <link rel="stylesheet" href="{{ asset('css/site.css') }}">

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

  {{-- Facebook Pixel --}}
  <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '1614520382942883');
    fbq('track', 'PageView');
  </script>
  <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id=1614520382942883&ev=PageView&noscript=1"/></noscript>

  @stack('styles')
</head>
<body>
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-K7L7LBLS"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>

  @include('partials.navbar')
  @include('partials.mobile-menu')

  @yield('content')

  @include('partials.footer')

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
  <script src="{{ asset('js/app.js') }}"></script>
  <script>if (window.lucide) lucide.createIcons();</script>

  @stack('scripts')
</body>
</html>
