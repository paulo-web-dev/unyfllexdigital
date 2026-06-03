@extends('layouts.site')

@section('meta_title', $curso->title . ' — Unyflex Digital')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($curso->info ?? $curso->subtitle ?? 'Minisérie prática para servidores públicos.'), 155))

@section('content')

@php
  $waBase = 'https://api.whatsapp.com/send/?phone=554188980259&type=phone_number&app_absent=0';
  $waCurso = $waBase . '&text=' . rawurlencode('Olá! Tenho interesse na minisérie "' . $curso->title . '". Pode me ajudar?');
  $waIcon = '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>';
  $thumb = 'https://unyflex.com.br/storage/cursos/banner/' . $curso->photo;
  $preco = $curso->price ?? 998;
@endphp

{{-- ================================================================
     HERO DO CURSO
     ================================================================ --}}
<section class="hero-section" id="curso-hero" style="padding-top:60px;">
  <div class="container">

    {{-- Breadcrumb --}}
    <div class="aos-fade" style="margin-bottom:24px;">
      <a href="{{ route('cursos') }}" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--fg-4);text-decoration:none;">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        Voltar ao catálogo
      </a>
    </div>

    <div class="row align-items-center g-5">

      {{-- Texto --}}
      <div class="col-lg-6">
        <div class="hero-eyebrow aos-fade">
          <span class="dot"></span>
          <span>Minisérie · Certificado MEC incluso</span>
        </div>

        <h1 class="hero-title aos-fade aos-delay-1" style="font-size:clamp(28px,4vw,44px);">
          {{ $curso->title }}
        </h1>

        @if($curso->subtitle)
        <p class="hero-subtitle aos-fade aos-delay-2">{{ $curso->subtitle }}</p>
        @endif

        {{-- Métricas rápidas --}}
        <div class="aos-fade aos-delay-3" style="display:flex;gap:24px;flex-wrap:wrap;margin:24px 0;">
          <div style="display:flex;align-items:center;gap:8px;">
            <i data-lucide="layers" style="width:18px;height:18px;stroke:var(--brand-300);fill:none;stroke-width:1.75;"></i>
            <div>
              <div style="font-size:18px;font-weight:800;color:#fff;font-family:var(--font-display);">{{ $totalTemporadas }}</div>
              <div style="font-size:11px;color:var(--fg-4);">temporadas</div>
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:8px;">
            <i data-lucide="play-circle" style="width:18px;height:18px;stroke:var(--brand-300);fill:none;stroke-width:1.75;"></i>
            <div>
              <div style="font-size:18px;font-weight:800;color:#fff;font-family:var(--font-display);">{{ $totalVideos }}</div>
              <div style="font-size:11px;color:var(--fg-4);">cápsulas</div>
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:8px;">
            <i data-lucide="file-text" style="width:18px;height:18px;stroke:var(--brand-300);fill:none;stroke-width:1.75;"></i>
            <div>
              <div style="font-size:18px;font-weight:800;color:#fff;font-family:var(--font-display);">{{ $totalMateriais }}</div>
              <div style="font-size:11px;color:var(--fg-4);">materiais</div>
            </div>
          </div>
          @if($curso->workload)
          <div style="display:flex;align-items:center;gap:8px;">
            <i data-lucide="clock" style="width:18px;height:18px;stroke:var(--brand-300);fill:none;stroke-width:1.75;"></i>
            <div>
              <div style="font-size:18px;font-weight:800;color:#fff;font-family:var(--font-display);">{{ $curso->workload }}h</div>
              <div style="font-size:11px;color:var(--fg-4);">de conteúdo</div>
            </div>
          </div>
          @endif
        </div>

        {{-- Preço --}}
        <div class="aos-fade aos-delay-3" style="background:rgba(0,163,255,0.06);border:1px solid rgba(0,163,255,0.2);border-radius:var(--r-lg);padding:18px 22px;margin-bottom:24px;display:inline-block;">
          <div style="font-size:12px;color:var(--fg-4);text-decoration:line-through;">De R$ 1.990</div>
          <div style="display:flex;align-items:baseline;gap:6px;">
            <span style="font-family:var(--font-display);font-weight:800;font-size:22px;color:#fff;">10x</span>
            <span style="font-family:var(--font-display);font-weight:800;font-size:34px;color:#fff;line-height:1;">R$ 98</span>
            <span style="font-size:13px;color:var(--fg-3);">ou R$ {{ number_format($preco,0,',','.') }} à vista</span>
          </div>
        </div>

        {{-- CTAs --}}
        <div class="hero-cta-group aos-fade aos-delay-4">
          <button class="btn-ux btn-ux-primary btn-ux-lg btn-add-to-cart"
                  data-course-id="{{ $curso->id }}"
                  data-course-title="{{ $curso->title }}"
                  data-course-price="{{ $preco }}"
                  data-course-thumb="{{ $thumb }}"
                  data-course-slug="{{ $curso->slug }}">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
              <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
            <span class="btn-cart-label">Adicionar ao carrinho</span>
          </button>
          <a href="{{ $waCurso }}" target="_blank" class="btn-ux btn-ux-lg" style="background:#25D366;color:#fff;border:none;">
            {!! $waIcon !!}
            Tirar dúvidas
          </a>
        </div>
      </div>

      {{-- Imagem/thumb --}}
      <div class="col-lg-6 aos-fade aos-delay-2">
        <div style="position:relative;border-radius:16px;overflow:hidden;border:1px solid rgba(59,130,246,0.25);box-shadow:0 0 40px rgba(59,130,246,0.12);aspect-ratio:16/9;background-image:url('{{ $thumb }}');background-size:cover;background-position:center;">
          <div style="position:absolute;inset:0;background:linear-gradient(135deg,rgba(5,10,30,0.3),rgba(10,20,50,0.15));"></div>
          @if($curso->novidade)
          <span style="position:absolute;top:16px;left:16px;background:var(--grad-brand);color:#061224;font-size:11px;font-weight:800;letter-spacing:0.08em;text-transform:uppercase;padding:4px 12px;border-radius:999px;">Novidade</span>
          @endif
        </div>
      </div>

    </div>
  </div>
</section>

{{-- ================================================================
     SOBRE / DESCRIÇÃO
     ================================================================ --}}
@if($curso->info)
<section class="section-py" style="background:var(--bg-1);border-top:1px solid var(--line-1);border-bottom:1px solid var(--line-1);">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8 aos-fade">
        <div class="section-eyebrow">Sobre esta minisérie</div>
        <div style="font-size:16px;color:var(--fg-2);line-height:1.75;">
          {!! html_entity_decode($curso->info, ENT_QUOTES | ENT_HTML5, 'UTF-8') !!}
        </div>
      </div>
    </div>
  </div>
</section>
@endif

{{-- ================================================================
     CONTEÚDO PROGRAMÁTICO — temporadas e cápsulas
     ================================================================ --}}
<section class="section-py" id="conteudo">
  <div class="container">
    <div class="text-center mb-5 aos-fade">
      <div class="section-eyebrow">Conteúdo programático</div>
      <h2 class="section-title">O que você vai<br><span class="text-brand-gradient">aprender nesta minisérie</span></h2>
      <p class="section-subtitle mx-auto">{{ $totalTemporadas }} temporadas · {{ $totalVideos }} cápsulas · {{ $totalMateriais }} materiais de apoio</p>
    </div>

    <div class="row justify-content-center">
      <div class="col-lg-9">
        @foreach($panels as $panel)
        @php $pNum = $loop->iteration; @endphp
        <div class="aos-fade" style="margin-bottom:14px;">
          <div class="lp-accordion-header" onclick="toggleAccordion(this)"
               style="background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-lg);padding:18px 22px;cursor:pointer;display:flex;align-items:center;gap:14px;transition:border-color 0.2s;">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(0,163,255,0.10);border:1px solid rgba(0,163,255,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <span style="font-family:var(--font-display);font-weight:800;font-size:16px;color:var(--brand-300);">{{ $pNum }}</span>
            </div>
            <div style="flex:1;min-width:0;">
              <div style="font-size:10px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--brand-400);margin-bottom:2px;">Temporada {{ $pNum }}</div>
              <div style="font-size:15px;font-weight:700;color:#fff;">{{ $panel->title }}</div>
              <div style="font-size:12px;color:var(--fg-4);margin-top:2px;">
                {{ $panel->video_lesson->count() }} cápsulas
                @if($panel->material->count() > 0) · {{ $panel->material->count() }} materiais @endif
              </div>
            </div>
            <i data-lucide="chevron-down" class="lp-chevron" style="width:20px;height:20px;stroke:var(--fg-4);fill:none;stroke-width:2;flex-shrink:0;transition:transform 0.25s;"></i>
          </div>

          <div class="lp-accordion-body" style="display:none;padding:8px 0 0;">
            @foreach($panel->video_lesson as $video)
            @php $vNum = $loop->iteration; @endphp
            <div style="display:flex;align-items:center;gap:12px;padding:12px 22px;border-bottom:1px solid var(--line-1);">
              <div style="width:28px;height:28px;border-radius:50%;border:1.5px solid var(--line-2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg viewBox="0 0 24 24" style="width:11px;height:11px;fill:var(--brand-300);"><polygon points="6 4 20 12 6 20 6 4"/></svg>
              </div>
              <div style="flex:1;min-width:0;">
                <div style="font-size:14px;color:var(--fg-2);">{{ $pNum }}.{{ $vNum }} {{ $video->titulo }}</div>
              </div>
              <span style="font-size:11px;color:var(--fg-4);flex-shrink:0;">~12 min</span>
            </div>
            @endforeach

            @if($panel->material->count() > 0)
            <div style="padding:14px 22px;background:rgba(0,163,255,0.03);">
              <div style="font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--fg-4);margin-bottom:8px;">Materiais de apoio</div>
              <div style="display:flex;flex-wrap:wrap;gap:8px;">
                @foreach($panel->material as $mat)
                <span style="display:inline-flex;align-items:center;gap:6px;background:var(--bg-1);border:1px solid var(--line-2);border-radius:8px;padding:5px 10px;font-size:12px;color:var(--fg-2);">
                  <i data-lucide="{{ $mat->type === 'PODCAST' ? 'headphones' : 'file-text' }}" style="width:13px;height:13px;stroke:var(--brand-300);fill:none;stroke-width:1.75;"></i>
                  {{ $mat->name ?? $mat->file_name }}
                </span>
                @endforeach
              </div>
            </div>
            @endif
          </div>
        </div>
        @endforeach
      </div>
    </div>

    {{-- CTA após conteúdo --}}
    <div class="text-center mt-5 aos-fade">
      <button class="btn-ux btn-ux-primary btn-ux-lg btn-add-to-cart"
              data-course-id="{{ $curso->id }}"
              data-course-title="{{ $curso->title }}"
              data-course-price="{{ $preco }}"
              data-course-thumb="{{ $thumb }}"
              data-course-slug="{{ $curso->slug }}">
        <i data-lucide="zap" style="width:18px;height:18px;fill:currentColor;stroke:none;"></i>
        <span class="btn-cart-label">Quero acesso — 10x R$ 98</span>
      </button>
    </div>
  </div>
</section>

{{-- ================================================================
     INCLUSO + GARANTIAS
     ================================================================ --}}
<section class="section-py" style="background:var(--bg-1);border-top:1px solid var(--line-1);border-bottom:1px solid var(--line-1);">
  <div class="container">
    <div class="row g-4 align-items-center">
      <div class="col-lg-6 aos-fade">
        <div class="section-eyebrow">O que está incluso</div>
        <h2 class="section-title" style="font-size:clamp(24px,3vw,34px);">Tudo o que você recebe ao se matricular</h2>
        <div style="display:flex;flex-direction:column;gap:12px;margin-top:24px;">
          @foreach([
            'Acesso por 12 meses a todas as cápsulas',
            'Versão em podcast de cada aula',
            'Materiais, modelos e checklists para download',
            'Certificado reconhecido pelo MEC',
            'Suporte pedagógico durante o acesso',
            'Garantia incondicional de 7 dias',
          ] as $item)
          <div style="display:flex;align-items:center;gap:12px;font-size:15px;color:var(--fg-2);">
            <i data-lucide="check-circle" style="width:18px;height:18px;stroke:var(--success);fill:none;stroke-width:1.75;flex-shrink:0;"></i>
            {{ $item }}
          </div>
          @endforeach
        </div>
      </div>
      <div class="col-lg-5 offset-lg-1 aos-fade aos-delay-2">
        <div style="background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-xl);padding:32px;text-align:center;">
          <div style="font-size:12px;color:var(--fg-4);text-decoration:line-through;margin-bottom:6px;">De R$ 1.990,00</div>
          <div style="display:flex;align-items:baseline;justify-content:center;gap:4px;margin-bottom:6px;">
            <span style="font-family:var(--font-display);font-weight:800;font-size:26px;color:#fff;">10x</span>
            <span style="font-family:var(--font-display);font-weight:800;font-size:44px;color:#fff;line-height:1;">R$ 98</span>
          </div>
          <div style="font-size:13px;color:var(--fg-3);margin-bottom:24px;">ou R$ {{ number_format($preco,0,',','.') }} à vista · 12 meses de acesso</div>

          <button class="btn-ux btn-ux-primary btn-ux-lg btn-add-to-cart" style="width:100%;justify-content:center;margin-bottom:12px;"
                  data-course-id="{{ $curso->id }}"
                  data-course-title="{{ $curso->title }}"
                  data-course-price="{{ $preco }}"
                  data-course-thumb="{{ $thumb }}"
                  data-course-slug="{{ $curso->slug }}">
            <i data-lucide="zap" style="width:16px;height:16px;fill:currentColor;stroke:none;"></i>
            <span class="btn-cart-label">Adicionar ao carrinho</span>
          </button>
          <a href="{{ $waCurso }}" target="_blank" class="btn-ux btn-ux-ghost btn-ux-sm" style="width:100%;justify-content:center;color:#25D366;border-color:rgba(37,211,102,0.4);">
            {!! $waIcon !!}
            Tirar dúvidas no WhatsApp
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ================================================================
     CTA FINAL
     ================================================================ --}}
<section class="final-cta-section">
  <div class="container position-relative">
    <div class="row justify-content-center">
      <div class="col-lg-8 text-center aos-fade">
        <div class="hero-eyebrow" style="justify-content:center;">
          <span class="dot"></span>
          <span>Acesso imediato · Certificado MEC · Garantia de 7 dias</span>
        </div>
        <h2 class="section-title" style="font-size:clamp(28px,4vw,46px);margin-bottom:16px;">
          Comece <span class="text-brand-gradient">{{ \Illuminate\Support\Str::limit($curso->title, 40) }}</span> ainda hoje
        </h2>
        <p style="font-size:17px;color:var(--fg-3);line-height:1.65;margin-bottom:32px;max-width:540px;margin-left:auto;margin-right:auto;">
          {{ $totalVideos }} cápsulas práticas, certificado MEC e garantia de 7 dias. Acesso liberado em até 5 minutos.
        </p>

        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
          <button class="btn-ux btn-ux-primary btn-ux-lg btn-add-to-cart"
                  data-course-id="{{ $curso->id }}"
                  data-course-title="{{ $curso->title }}"
                  data-course-price="{{ $preco }}"
                  data-course-thumb="{{ $thumb }}"
                  data-course-slug="{{ $curso->slug }}">
            <i data-lucide="zap" style="width:18px;height:18px;fill:currentColor;stroke:none;"></i>
            <span class="btn-cart-label">Garantir meu acesso</span>
          </button>
          <a href="{{ $waCurso }}" target="_blank" class="btn-ux btn-ux-lg" style="background:#25D366;color:#fff;border:none;">
            {!! $waIcon !!}
            Falar no WhatsApp
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- Botão flutuante WhatsApp --}}
<a href="{{ $waCurso }}" target="_blank" class="wa-float" aria-label="Fale conosco no WhatsApp">
  <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
  <span class="wa-label">Tirar dúvidas</span>
</a>

{{-- Toast --}}
<div class="cart-toast" id="cartToast" role="status" aria-live="polite">
  <div class="cart-toast-icon">
    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
  </div>
  <div class="cart-toast-body">
    <div class="cart-toast-title">Adicionado ao carrinho!</div>
    <div class="cart-toast-sub" id="cartToastSub">Minisérie adicionada com sucesso.</div>
  </div>
  <a href="{{ route('checkout') }}" class="cart-toast-action">Ver carrinho →</a>
</div>

@push('styles')
<style>
@keyframes waPulse { 0%{box-shadow:0 0 0 0 rgba(37,211,102,.55)}70%{box-shadow:0 0 0 16px rgba(37,211,102,0)}100%{box-shadow:0 0 0 0 rgba(37,211,102,0)} }
.wa-float{position:fixed;bottom:24px;right:24px;z-index:9990;display:flex;align-items:center;gap:10px;background:#25D366;color:#fff;padding:14px 20px 14px 16px;border-radius:999px;text-decoration:none;font-weight:700;font-size:14px;box-shadow:0 10px 30px -6px rgba(37,211,102,.5);animation:waPulse 2.4s infinite;transition:transform .2s;}
.wa-float:hover{transform:scale(1.05);color:#fff;}
@media(max-width:600px){ .wa-float{padding:14px;} .wa-float .wa-label{display:none;} }
.lp-accordion-header:hover{border-color:rgba(0,163,255,0.4)!important;}
.lp-chevron.open{transform:rotate(180deg);}
</style>
@endpush

@push('scripts')
<script>
function toggleAccordion(header) {
  const body = header.nextElementSibling;
  const chev = header.querySelector('.lp-chevron');
  const open = body.style.display !== 'none';
  body.style.display = open ? 'none' : 'block';
  chev.classList.toggle('open', !open);
}

document.addEventListener('DOMContentLoaded', function () {
  // Abre a primeira temporada por padrão
  const first = document.querySelector('.lp-accordion-header');
  if (first) toggleAccordion(first);

  const cart = (window.UnyCart ? UnyCart.getCart() : []);

  document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
    if (cart.find(i => String(i.id) === String(btn.dataset.courseId))) setInCart(btn);

    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const item = {
        id:    this.dataset.courseId,
        title: this.dataset.courseTitle,
        price: parseFloat(this.dataset.coursePrice) || 998,
        thumb: this.dataset.courseThumb,
        slug:  this.dataset.courseSlug,
      };
      const result = UnyCart.addItem(item);
      if (result.added) {
        if (typeof registrarFunil === 'function') registrarFunil('carrinho', parseInt(item.id));
        document.querySelectorAll('.btn-add-to-cart').forEach(b => {
          if (String(b.dataset.courseId) === String(item.id)) setInCart(b);
        });
        showCartToast(item.title);
      } else {
        window.location.href = '{{ route('checkout') }}';
      }
    });
  });

  // Registra visualização no funil
  if (typeof registrarFunil === 'function') registrarFunil('visualizou', {{ $curso->id }});

  function setInCart(btn) {
    btn.classList.add('in-cart');
    const lbl = btn.querySelector('.btn-cart-label');
    if (lbl) lbl.textContent = 'No carrinho ✓';
  }

  let toastTimer;
  function showCartToast(title) {
    const toast = document.getElementById('cartToast');
    const sub   = document.getElementById('cartToastSub');
    if (!toast) return;
    sub.textContent = title;
    toast.classList.add('visible');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('visible'), 4000);
  }
});
</script>
@endpush

@endsection
