@extends('layouts.site')
@section('meta_title', 'Seu guia esta liberado — Unyflex Digital')
@section('meta_description', 'Baixe o guia das contratacoes publicas pela Lei 14.133.')

@push('styles')
<meta name="robots" content="noindex">
{{-- META PIXEL — evento de conversao (Lead). Descomente junto com o bloco base. --}}
{{-- <script> if (typeof fbq !== 'undefined') { fbq('track','Lead'); } </script> --}}
<style>
  .ty-card{background:radial-gradient(70% 120% at 90% 0%, rgba(0,163,255,0.18), transparent 60%),linear-gradient(160deg,#0F1726,#050A18);border:1px solid var(--line-2);border-radius:var(--r-xl);padding:34px;box-shadow:var(--shadow-lg);text-align:center}
  .ty-check{width:74px;height:74px;border-radius:50%;background:rgba(0,200,120,0.16);border:2px solid var(--success);display:flex;align-items:center;justify-content:center;margin:0 auto 20px}
  .ty-check svg{width:38px;height:38px;color:var(--success)}
  .ty-dl{display:flex;gap:16px;align-items:center;justify-content:center;flex-wrap:wrap;text-align:left;margin:24px 0 4px}
  .ty-book{flex:none;width:56px;height:72px;border-radius:5px;background:linear-gradient(160deg,#0072FF,#00A3FF);box-shadow:0 8px 20px -8px rgba(0,163,255,.6);position:relative;border-left:4px solid rgba(0,0,0,.25)}
  .ty-book::before{content:"14.133";position:absolute;bottom:8px;left:0;right:0;text-align:center;font-family:var(--font-display);font-weight:800;font-size:10px;color:rgba(255,255,255,.95)}
  .ty-book::after{content:"";position:absolute;top:10px;left:9px;right:9px;height:3px;background:rgba(255,255,255,.55);border-radius:2px;box-shadow:0 7px 0 rgba(255,255,255,.35),0 14px 0 rgba(255,255,255,.25)}
  .ty-book-ttl b{font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;display:block}
  .ty-book-ttl span{font-size:13px;color:var(--fg-3)}
  .ty-obs{font-size:13px;color:var(--fg-3);margin-top:14px}
  .ty-oferta{background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-xl);padding:30px;text-align:center;margin-top:22px}
  .ty-pills{display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin:14px 0 22px}
  .ty-pill{background:var(--bg-3);border:1px solid var(--line-2);border-radius:var(--r-pill);padding:7px 14px;font-size:13px;font-weight:500;color:var(--fg-2)}
  .btn-wpp{display:inline-flex;align-items:center;gap:10px;background:#1ebe5d;color:#fff;font-family:var(--font-display);font-weight:700;font-size:16px;padding:14px 28px;border-radius:14px;transition:.18s;box-shadow:0 12px 26px -12px rgba(30,190,93,.7)}
  .btn-wpp:hover{background:#17a350;transform:translateY(-1px);color:#fff}
  .btn-wpp svg{width:20px;height:20px}
</style>
@endpush

@section('content')
<div style="padding-top:128px;padding-bottom:90px;">
  <div class="container" style="max-width:760px;">

    <div class="ty-card aos-fade">
      <div class="ty-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M20 6L9 17l-5-5"/></svg></div>
      <h1 class="section-title" style="font-size:clamp(26px,3.5vw,38px);">Pronto{{ $nome ? ', '.\Illuminate\Support\Str::of($nome)->explode(' ')->first() : '' }}! Seu guia esta liberado.</h1>
      <p style="font-size:16px;color:var(--fg-3);line-height:1.6;max-width:48ch;margin:10px auto 0;">Tambem enviamos o link para o seu e-mail. Se preferir, baixe agora mesmo:</p>

      <div class="ty-dl">
        <div class="ty-book" aria-hidden="true"></div>
        <div class="ty-book-ttl">
          <b>Risco Zero nas Contratacoes Publicas</b>
          <span>O caminho completo da demanda ao contrato · PDF</span>
        </div>
      </div>

      <a class="btn-ux btn-ux-primary btn-ux-lg" style="margin-top:14px;justify-content:center;" href="{{ route('guia.download') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="width:18px;height:18px;"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
        Baixar o guia em PDF
      </a>
      <p class="ty-obs">Nao iniciou? <a href="{{ route('guia.download') }}" style="color:#00A3FF;font-weight:600;">Clique aqui</a>.</p>
    </div>

    <div class="ty-oferta aos-fade">
      <div class="offer-badge">Proximo passo</div>
      <h2 style="font-family:var(--font-display);font-weight:800;font-size:24px;color:#fff;letter-spacing:-.02em;margin:14px 0 10px;">Quer dominar cada etapa na pratica?</h2>
      <p style="color:var(--fg-3);max-width:52ch;margin:0 auto;font-size:15px;">As <b style="color:#fff;">Minisseries Digitais</b> da Unyflex aprofundam exatamente o que o guia apresenta — em capacitacoes rapidas, praticas e com certificado.</p>
      <div class="ty-pills">
        <span class="ty-pill">ETP na pratica</span>
        <span class="ty-pill">Termo de Referencia</span>
        <span class="ty-pill">Pesquisa de Precos</span>
        <span class="ty-pill">Gestao de Contratos</span>
      </div>
      @php
        // Mostra o botao do catalogo so se existir uma rota publica conhecida (evita erro).
        $verRota = collect(['minisseries','cursos.index','agendados','curso.index'])
                   ->first(fn ($n) => \Illuminate\Support\Facades\Route::has($n));
      @endphp
      <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
        @if($verRota)
          <a href="{{ route($verRota) }}" class="btn-ux btn-ux-ghost btn-ux-lg">Ver as minisseries</a>
        @endif
        <a class="btn-wpp" href="https://wa.me/{{ $whatsapp }}?text={{ $whatsMsg }}" target="_blank" rel="noopener">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 018.413 3.488 11.824 11.824 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 001.51 5.26l-.999 3.648 3.477-.711z"/></svg>
          Falar no WhatsApp
        </a>
      </div>
    </div>

  </div>
</div>
@endsection
