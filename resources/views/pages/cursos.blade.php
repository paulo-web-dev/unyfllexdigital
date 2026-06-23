@extends('layouts.site')
@section('meta_title', 'Minisséries — Unyflex Digital')
@section('meta_description', 'Catálogo completo de minisséries para servidores públicos. 26 minisséries, 184+ cápsulas de 10 a 20 minutos.')

@section('content')

<div style="padding-top:112px; padding-bottom:80px;">
  <div class="container">

    {{-- Cabeçalho --}}
    <div class="row justify-content-between align-items-end mb-5 aos-fade">
      <div class="col-lg-7">
        <div class="section-eyebrow">Catálogo completo</div>
        <h1 class="section-title" style="font-size:clamp(32px,4vw,50px);">Minisséries</h1>
        <p style="font-size:16px;color:var(--fg-3);line-height:1.65;max-width:500px;">
          Cápsulas de 10 a 20 minutos pensadas para servidores municipais aplicarem o conteúdo na rotina logo após assistir.
        </p>
      </div>
      <div class="col-lg-auto text-lg-end mt-3 mt-lg-0">
        <a href="{{ route('checkout') }}" class="btn-ux btn-ux-primary btn-ux-lg">
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
          <button class="btn-ux btn-ux-ghost btn-add-to-cart"
                  data-course-id="spotlight"
                  data-course-title="Assessoria de Imprensa Com Mídias Sociais"
                  data-course-price="998"
                  data-course-thumb="{{ asset('img/logo-unyflex.png') }}"
                  data-course-slug="Assessoria-de-Imprensa-Com-Midias-Sociais-Janeiro-express">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
              <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
            <span class="btn-cart-label">Adicionar ao Carrinho</span>
          </button>
        </div>
      </div>
      <div style="display:flex;align-items:center;justify-content:center;">
        <div style="width:160px;height:160px;border-radius:50%;background:#000;box-shadow:0 0 60px -10px rgba(0,163,255,0.6),0 0 0 1px rgba(0,163,255,0.3);overflow:hidden;">
          <img src="{{ asset('img/logo-unyflex.png') }}" alt="Unyflex" style="width:100%;height:100%;object-fit:cover;">
        </div>
      </div>
    </div>

    {{-- Filtros + contagem --}}
    <div class="d-flex flex-wrap gap-2 align-items-center mb-4" id="minisseries-section">

      @foreach(['todos' => 'Todos', 'minisserie' => 'Minisséries'] as $val => $label)
        <button class="filter-chip {{ $val === 'todos' ? 'active' : '' }}"
                data-filter="{{ $val }}"
                style="font-size:13px;font-weight:500;color:var(--fg-2);background:var(--bg-2);border:1px solid var(--line-2);padding:9px 16px;border-radius:var(--r-pill);cursor:pointer;transition:all 0.2s;">
          {{ $label }}
        </button>
      @endforeach

      <span style="margin-left:auto;font-size:13px;color:var(--fg-3);">
        {{ $classes->count() }} {{ $classes->count() === 1 ? 'curso' : 'cursos' }}
      </span>

    </div>

    {{-- Grid de cursos --}}
    <div class="row g-4" id="coursesGrid">

      @foreach($classes as $curso)
        <div class="col-lg-4 col-md-6 course-col" data-category="minisserie">

          <div class="course-card" style="display:flex;flex-direction:column;">

            {{-- Thumb --}}
            <a href="{{ route('curso.show', $curso->slug) }}" style="display:block;text-decoration:none;">
              <div class="course-card-thumb course-thumb-t"
                   style="background-image:url('https://unyflex.com.br/storage/cursos/banner/{{ $curso->photo }}');background-size:cover;background-position:center;background-repeat:no-repeat;">

                @if($curso->novidade)
                  <span class="course-card-badge novo">NOVO</span>
                @endif

                @if($curso->workload)
                  <span class="course-card-duration">{{ $curso->workload }}</span>
                @endif

                <div class="course-card-play">
                  <div style="width:52px;height:52px;border-radius:50%;background:rgba(255,255,255,0.95);color:#0072FF;display:flex;align-items:center;justify-content:center;box-shadow:0 12px 40px -8px rgba(0,163,255,0.6);">
                    <svg viewBox="0 0 24 24" style="width:20px;height:20px;fill:currentColor;margin-left:2px;">
                      <polygon points="6 4 20 12 6 20 6 4"/>
                    </svg>
                  </div>
                </div>
              </div>
            </a>

            {{-- Corpo --}}
            <div class="course-card-body" style="flex:1;display:flex;flex-direction:column;">

              <div class="course-eyebrow">MINISSÉRIE</div>

              <a href="{{ route('curso.show', $curso->slug) }}" style="text-decoration:none;color:inherit;">
                <div class="course-title">{{ $curso->title }}</div>
              </a>

              {{-- Botões --}}
              <div style="display:flex;gap:8px;margin-top:auto;padding-top:14px;">

                <a href="{{ route('curso.show', $curso->slug) }}"
                   class="btn-ux btn-ux-ghost btn-ux-sm"
                   style="flex:0 0 auto;">
                  Ver detalhes
                </a>

                <button
                  class="btn-ux btn-ux-primary btn-ux-sm btn-add-to-cart"
                  style="flex:1;justify-content:center;"
                  data-course-id="{{ $curso->id }}"
                  data-course-title="{{ $curso->title }}"
                  data-course-price="{{ $curso->valor ?? 998 }}"
                  data-course-thumb="https://unyflex.com.br/storage/cursos/banner/{{ $curso->photo }}"
                  data-course-slug="{{ $curso->slug }}"
                  aria-label="Adicionar {{ $curso->title }} ao carrinho">

                  <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                  </svg>
                  <span class="btn-cart-label">Adicionar</span>
                </button>

              </div>
            </div>
          </div>
        </div>
      @endforeach

    </div>

  </div>
</div>

{{-- Toast --}}
<div class="cart-toast" id="cartToast" role="status" aria-live="polite">
  <div class="cart-toast-icon">
    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <polyline points="20 6 9 17 4 12"/>
    </svg>
  </div>
  <div class="cart-toast-body">
    <div class="cart-toast-title">Adicionado ao carrinho!</div>
    <div class="cart-toast-sub" id="cartToastSub">Minisérie adicionada com sucesso.</div>
  </div>
  <a href="{{ route('checkout') }}" class="cart-toast-action">Ver carrinho →</a>
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

.btn-add-to-cart.in-cart {
  background: rgba(0,200,120,0.15) !important;
  border-color: rgba(0,200,120,0.4) !important;
  color: var(--success) !important;
  pointer-events: none;
}
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
  
    // Marca botões já no carrinho ao carregar a página
    const cart = UnyCart.getCart();
  
    document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
      const id = String(btn.dataset.courseId);
  
      if (cart.find(i => String(i.id) === id)) {
        setInCart(btn);
      }
    });
  
    // Listener nos botões
    document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
  
      btn.addEventListener('click', function (e) {
        e.preventDefault();
  
        const item = {
          id:    this.dataset.courseId,
          title: this.dataset.courseTitle,
          price: parseFloat(this.dataset.coursePrice) || 0,
          thumb: this.dataset.courseThumb,
          slug:  this.dataset.courseSlug,
        };
  
        const result = UnyCart.addItem(item);
  
        // Adicionou ou já existia → vai para checkout
        if (result.added) {
          setInCart(this);
        }
  
        window.location.href = '/checkout';
      });
  
    });
  
    function setInCart(btn) {
      btn.classList.add('in-cart');
  
      const label = btn.querySelector('.btn-cart-label');
  
      if (label) {
        label.textContent = 'No carrinho';
      }
  
      btn.setAttribute(
        'aria-label',
        btn.dataset.courseTitle + ' — já no carrinho'
      );
    }
  
    // Toast
    let toastTimer;
  
    function showCartToast(title) {
      const toast = document.getElementById('cartToast');
      const sub   = document.getElementById('cartToastSub');
  
      if (!toast || !sub) return;
  
      sub.textContent = title;
  
      toast.classList.add('visible');
  
      clearTimeout(toastTimer);
  
      toastTimer = setTimeout(() => {
        toast.classList.remove('visible');
      }, 4000);
    }
  
    // Atualização do carrinho
    document.addEventListener('cart:updated', function (e) {
  
      const ids = e.detail.cart.map(i => String(i.id));
  
      document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
  
        if (!ids.includes(String(btn.dataset.courseId))) {
  
          btn.classList.remove('in-cart');
  
          const label = btn.querySelector('.btn-cart-label');
  
          if (label) {
            label.textContent = 'Adicionar';
          }
        }
  
      });
  
    });
  
  });
  </script>
@endpush

@endsection