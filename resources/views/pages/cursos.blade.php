@extends('layouts.site')
@section('meta_title', 'Miniséries — Unyflex Digital')
@section('meta_description', 'Catálogo completo de miniséries para servidores públicos. 26 miniséries, 184+ cápsulas de 10 a 20 minutos.')

@section('content')

<div style="padding-top:112px; padding-bottom:80px;">
  <div class="container">

    {{-- Cabeçalho --}}
    <div class="row justify-content-between align-items-end mb-5 aos-fade">
      <div class="col-lg-7">
        <div class="section-eyebrow">Catálogo completo</div>
        <h1 class="section-title" style="font-size:clamp(32px,4vw,50px);">Miniséries</h1>
        <p style="font-size:16px;color:var(--fg-3);line-height:1.65;max-width:500px;">
          Cápsulas de 10 a 20 minutos pensadas para servidores municipais aplicarem o conteúdo na rotina logo após assistir.
        </p>
      </div>
      <div class="col-lg-auto text-lg-end mt-3 mt-lg-0">
        <a href="{{ route('checkout') }}" class="btn-ux btn-ux-primary btn-ux-lg">
          <i data-lucide="zap" style="width:16px;height:16px;fill:currentColor;stroke:none;"></i>
          Acessar tudo — R$ 998
        </a>
      </div>
    </div>

    {{-- Spotlight --}}
    <div class="aos-fade" style="background:radial-gradient(60% 110% at 90% 50%, rgba(0,163,255,0.22), transparent 60%),linear-gradient(120deg,#0F1726,#050A18);border:1px solid var(--line-2);border-radius:var(--r-xl);padding:36px 40px;margin-bottom:36px;display:grid;grid-template-columns:1fr 200px;gap:28px;align-items:center;box-shadow:var(--shadow-lg);">
      <div>
        <div class="offer-badge" style="margin-bottom:12px;">Lançamento desta semana</div>
        <h2 style="font-family:var(--font-display);font-weight:800;font-size:clamp(22px,2.5vw,30px);color:#fff;letter-spacing:-0.02em;margin-bottom:10px;">Auditoria contínua com dashboards</h2>
        <p style="color:var(--fg-3);margin-bottom:18px;font-size:15px;max-width:480px;">Aprenda a montar indicadores que apontam riscos antes que virem problema, com 6 cápsulas curtas e um dashboard pronto para clonar.</p>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
          <a href="{{ route('curso.show', 'auditoria-dashboards') }}" class="btn-ux btn-ux-primary">
            <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><polygon points="6 4 20 12 6 20 6 4"/></svg>
            Começar agora
          </a>
          <a href="#" class="btn-ux btn-ux-ghost">Ver ementa</a>
        </div>
      </div>
      <div style="display:flex;align-items:center;justify-content:center;">
        <div style="width:160px;height:160px;border-radius:50%;background:#000;box-shadow:0 0 60px -10px rgba(0,163,255,0.6),0 0 0 1px rgba(0,163,255,0.3);overflow:hidden;">
          <img src="{{ asset('img/logo-unyflex.png') }}" alt="Unyflex" style="width:100%;height:100%;object-fit:cover;">
        </div>
      </div>
    </div>

    {{-- Filtros --}}
    <div class="filter-chip-group aos-fade" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:28px;">
      @foreach(['todos'=>'Todos','em-andamento'=>'Em andamento','pregao'=>'Pregão','patrimonio'=>'Patrimônio','contratos'=>'Contratos','lgpd'=>'LGPD','ia'=>'I.A. aplicada'] as $val => $label)
      <button class="filter-chip {{ $val === 'todos' ? 'active' : '' }}" data-filter="{{ $val }}" style="font-size:13px;font-weight:500;color:var(--fg-2);background:var(--bg-2);border:1px solid var(--line-2);padding:9px 16px;border-radius:var(--r-pill);cursor:pointer;transition:all 0.2s;">
        {{ $label }}
      </button>
      @endforeach
      <span style="margin-left:auto;font-size:13px;color:var(--fg-3);">26 miniséries · 184 cápsulas</span>
    </div>

    {{-- Grid de cursos --}}
    <div class="row g-4" id="coursesGrid">
      @foreach([
        ['tone'=>1,'badge'=>'EM ANDAMENTO','dur'=>'2h 48min','eyebrow'=>'MINISSÉRIE · 12 CÁPSULAS','title'=>'Patrimônio e Frotas Públicas com I.A.','desc'=>'Levantamento, auditoria e controle de bens patrimoniais com apoio de inteligência artificial.','progress'=>42,'cat'=>'patrimonio','slug'=>'patrimonio-frotas-ia'],
        ['tone'=>2,'badge'=>'EM ANDAMENTO','dur'=>'1h 52min','eyebrow'=>'MINISSÉRIE · 8 CÁPSULAS','title'=>'Lei 14.133 na prática','desc'=>'Como aplicar a Nova Lei de Licitações nos pregões eletrônicos do dia a dia da prefeitura.','progress'=>18,'cat'=>'pregao','slug'=>'lei-14133-pratica'],
        ['tone'=>3,'badge'=>'NOVO','dur'=>'1h 22min','eyebrow'=>'MINISSÉRIE · 6 CÁPSULAS','title'=>'Auditoria contínua com dashboards','desc'=>'Indicadores que apontam riscos antes que virem problema.','progress'=>null,'cta'=>'Acessar','cat'=>'ia','slug'=>'auditoria-dashboards'],
        ['tone'=>4,'badge'=>null,'dur'=>'2h 10min','eyebrow'=>'MINISSÉRIE · 9 CÁPSULAS','title'=>'Gestão de contratos públicos','desc'=>'Do recebimento à fiscalização contínua, passando por aditivos.','progress'=>null,'cta'=>'Acessar','cat'=>'contratos','slug'=>'gestao-contratos'],
        ['tone'=>1,'badge'=>null,'dur'=>'58min','eyebrow'=>'CÁPSULA AVULSA','title'=>'Como redigir um Termo de Referência','desc'=>'Modelo comentado + checklist final para usar imediatamente.','progress'=>null,'cta'=>'Acessar','cat'=>'pregao','slug'=>'termo-referencia'],
        ['tone'=>2,'badge'=>null,'dur'=>'46min','eyebrow'=>'CÁPSULA AVULSA','title'=>'LGPD para servidores municipais','desc'=>'Aplicação prática nos processos administrativos da prefeitura.','progress'=>null,'cta'=>'Acessar','cat'=>'lgpd','slug'=>'lgpd-servidores'],
        ['tone'=>3,'badge'=>null,'dur'=>'3h 04min','eyebrow'=>'MINISSÉRIE · 14 CÁPSULAS','title'=>'Pregão eletrônico avançado','desc'=>'Estratégias de condução, análise e diligências bem documentadas.','progress'=>null,'cta'=>'Acessar','cat'=>'pregao','slug'=>'pregao-eletronico-avancado'],
        ['tone'=>4,'badge'=>null,'dur'=>'1h 18min','eyebrow'=>'MINISSÉRIE · 5 CÁPSULAS','title'=>'Pesquisa de preços com inteligência','desc'=>'Como construir preços de referência defensáveis em 4 fontes.','progress'=>null,'cta'=>'Acessar','cat'=>'pregao','slug'=>'pesquisa-precos'],
      ] as $i => $c)
      <div class="col-lg-3 col-md-6 course-col" data-category="{{ $c['cat'] }}">
        <a href="{{ route('curso.show', $c['slug']) }}" class="course-card" data-category="{{ $c['cat'] }}" style="text-decoration:none;color:inherit;">
          <div class="course-card-thumb course-thumb-t{{ $c['tone'] }}">
            @if(!empty($c['badge']))<span class="course-card-badge {{ $c['badge']==='NOVO'?'badge-novo':'' }}">{{ $c['badge'] }}</span>@endif
            <span class="course-card-duration">{{ $c['dur'] }}</span>
          </div>
          <div class="course-card-body">
            <div class="course-eyebrow">{{ $c['eyebrow'] }}</div>
            <div class="course-title">{{ $c['title'] }}</div>
            <p class="course-desc">{{ $c['desc'] }}</p>
            @if(!is_null($c['progress'] ?? null))
              <div class="course-progress-bar"><div class="course-progress-fill" style="width:{{ $c['progress'] }}%;"></div></div>
              <div class="course-progress-text"><span>Progresso</span><span class="pct">{{ $c['progress'] }}%</span></div>
            @else
              <button class="course-card-cta">{{ $c['cta'] ?? 'Acessar' }} <i data-lucide="arrow-right" style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2;"></i></button>
            @endif
          </div>
        </a>
      </div>
      @endforeach
    </div>

  </div>
</div>

@push('styles')
<style>
.filter-chip.active {
  background: rgba(0,163,255,0.12);
  border-color: rgba(0,163,255,0.45);
  color: var(--brand-200);
  box-shadow: 0 0 14px -4px rgba(0,163,255,0.45);
}
.filter-chip:hover { background: var(--bg-3); color: #fff; }
</style>
@endpush

@endsection
