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
        <a href="#minisseries-section" class="btn-ux btn-ux-primary btn-ux-lg">
          <i data-lucide="zap" style="width:16px;height:16px;fill:currentColor;stroke:none;"></i>
          Explorar Minisséries — R$ 998
        </a>  
      </div>
    </div>

    {{-- Spotlight --}}
    <div class="aos-fade" style="background:radial-gradient(60% 110% at 90% 50%, rgba(0,163,255,0.22), transparent 60%),linear-gradient(120deg,#0F1726,#050A18);border:1px solid var(--line-2);border-radius:var(--r-xl);padding:36px 40px;margin-bottom:36px;display:grid;grid-template-columns:1fr 200px;gap:28px;align-items:center;box-shadow:var(--shadow-lg);">
      <div>
        <div class="offer-badge" style="margin-bottom:12px;">Lançamento desta semana</div>
        <h2 style="font-family:var(--font-display);font-weight:800;font-size:clamp(22px,2.5vw,30px);color:#fff;letter-spacing:-0.02em;margin-bottom:10px;">Assessoria de Imprensa Com Mídias Sociais</h2>
        <p style="color:var(--fg-3);margin-bottom:18px;font-size:15px;max-width:480px;">Aprenda a transformar a comunicação pública em autoridade digital, com 6 cápsulas rápidas e estratégias práticas para criar conteúdos, fortalecer a imagem institucional e engajar a população nas redes sociais.</p>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
          <a href="{{ route('curso.show', 'Assessoria-de-Imprensa-Com-Midias-Sociais-Janeiro-express') }}" class="btn-ux btn-ux-primary">
            <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><polygon points="6 4 20 12 6 20 6 4"/></svg>
            Começar agora
          </a>
          <a href="#" class="btn-ux btn-ux-ghost">Adicionar ao Carrinho</a>
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
     {{-- Filtros --}}
<div class="d-flex flex-wrap gap-2 align-items-center mb-4" id="minisseries-section" > 

  @foreach([
      'todos' => 'Todos',
      'minisserie' => 'Minisséries'
  ] as $val => $label)

      <button class="filter-chip {{ $val === 'todos' ? 'active' : '' }}"
              data-filter="{{ $val }}"
              style="font-size:13px;font-weight:500;color:var(--fg-2);background:var(--bg-2);border:1px solid var(--line-2);padding:9px 16px;border-radius:var(--r-pill);cursor:pointer;transition:all 0.2s;">
          {{ $label }}
      </button>

  @endforeach

  <span style="margin-left:auto;font-size:13px;color:var(--fg-3);">
      {{ $classes->count() }} cursos
  </span>

</div>

{{-- Grid de cursos --}}
<div class="row g-4" id="coursesGrid">

  @foreach($classes as $curso)

      <div class="col-lg-4 col-md-6 course-col"
           data-category="minisserie">

          <a href="{{ route('curso.show', $curso->slug) }}"
             class="course-card"
             data-category="minisserie"
             style="display:flex;text-decoration:none;color:inherit;">

              <div class="course-card-thumb course-thumb-t"
                   style="
                      background-image:url('https://unyflex.com.br/storage/cursos/banner/{{$curso->photo}}');
                      background-size:cover;
                      background-position:center;
                      background-repeat:no-repeat;
                   ">

                  @if($curso->badge)
                      <span class="course-card-badge novo">
                          NOVO
                      </span>
                  @endif

                  <span class="course-card-duration">
                      {{$curso->workload}}H
                  </span>

                  <div class="course-card-play">
                      <div style="width:52px;height:52px;border-radius:50%;background:rgba(255,255,255,0.95);color:#0072FF;display:flex;align-items:center;justify-content:center;box-shadow:0 12px 40px -8px rgba(0,163,255,0.6);">
                          <svg viewBox="0 0 24 24"
                               style="width:20px;height:20px;fill:currentColor;margin-left:2px;">
                              <polygon points="6 4 20 12 6 20 6 4"/>
                          </svg>
                      </div>
                  </div>

              </div>

              <div class="course-card-body">

                  <div class="course-eyebrow">
                      MINISSÉRIE · 70 CÁPSULAS
                  </div>

                  <div class="course-title">
                      {{ $curso->title }}
                  </div>

                  <button class="course-card-cta">
                      Acessar!
                      <i data-lucide="arrow-right"
                         style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;">
                      </i>
                  </button>

              </div>

          </a>

      </div>

  @endforeach

</div>
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
