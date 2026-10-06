@php
  $vitrineCfg = config('assinatura_vitrine');
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#05080F">

  {{-- Google Tag Manager (container sem Meta Pixel — ver config assinatura_vitrine.gtm) --}}
  @if($vitrineCfg['gtm'])
  <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{{ $vitrineCfg['gtm'] }}');</script>
  @endif

  {{-- Google Ads --}}
  @if($vitrineCfg['google_ads'])
  <script async src="https://www.googletagmanager.com/gtag/js?id={{ $vitrineCfg['google_ads'] }}"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '{{ $vitrineCfg['google_ads'] }}');
  </script>
  @endif

  {{-- Meta Pixel (único pixel da vitrine) --}}
  <script>
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
    n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
    document,'script','https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '{{ $vitrineCfg['meta_pixel'] }}');
    fbq('track', 'PageView');
  </script>
  <noscript><img height="1" width="1" style="display:none" alt=""
    src="https://www.facebook.com/tr?id={{ $vitrineCfg['meta_pixel'] }}&ev=PageView&noscript=1"></noscript>

  <title>@yield('meta_title', 'Assinatura Premium Unyflex — Capacitação para órgãos públicos')</title>
  <meta name="description" content="@yield('meta_description', 'Capacitação contínua para servidores de câmaras e prefeituras em uma única contratação. Aceita nota de empenho.')">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="{{ url()->current() }}">

  <meta property="og:type" content="website">
  <meta property="og:title" content="@yield('meta_title', 'Assinatura Premium Unyflex')">
  <meta property="og:description" content="@yield('meta_description', 'Capacitação contínua para toda a sua equipe, em uma única contratação.')">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta property="og:image" content="{{ asset('img/logo-unyflex.png') }}">
  <meta property="og:site_name" content="Unyflex Digital">
  <meta property="og:locale" content="pt_BR">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="{{ asset('css/colors_and_type.css') }}">
  <link rel="stylesheet" href="{{ asset('css/vitrine.css') }}">
  @stack('styles')
</head>
<body class="vt">
  @if($vitrineCfg['gtm'])
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $vitrineCfg['gtm'] }}"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
  @endif

  @include('vitrine.partials.nav')

  <main>
    @yield('content')
  </main>

  @include('vitrine.partials.footer')

  {{-- WhatsApp flutuante --}}
  <x-vitrine.whatsapp class="vt-wa-float" content-name="Botão flutuante" aria-label="Falar com um consultor no WhatsApp">
    <i data-lucide="message-circle"></i>
  </x-vitrine.whatsapp>

  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
  <script src="{{ asset('js/vitrine.js') }}"></script>
  @stack('scripts')
</body>
</html>
