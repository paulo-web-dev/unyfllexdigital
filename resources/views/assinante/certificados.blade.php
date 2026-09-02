@extends('layouts.assinante')
@section('title', 'Meus certificados — Assinatura Unyflex')
@section('section', 'Meus certificados')

@push('styles')
<style>
  .as-certs { display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:16px; }
  .as-certcard { display:flex; flex-direction:column; gap:12px; }
  .as-certcard__topo { display:flex; align-items:center; justify-content:space-between; gap:10px; }
  .as-certcard__num { font-size:11px; color:var(--as-fg-4); font-family:ui-monospace, Consolas, monospace; }
  .as-certcard h3 { font-size:15px; font-weight:800; color:var(--as-fg); margin:0; line-height:1.3; }
  .as-certcard__dl { display:grid; grid-template-columns:1fr 1fr; gap:8px 14px; margin:0; }
  .as-certcard__dl div { background:var(--as-bg); border:1px solid var(--as-line); border-radius:9px; padding:8px 10px; }
  .as-certcard__dl small { display:block; font-size:10px; letter-spacing:.1em; text-transform:uppercase; color:var(--as-fg-3); font-weight:700; }
  .as-certcard__dl b { display:block; font-size:14px; color:var(--as-fg); margin-top:2px; }
  .as-certcard__cod { font-size:11px; color:var(--as-fg-3); font-family:ui-monospace, Consolas, monospace; word-break:break-all; }
  .as-certcard__acoes { display:flex; gap:8px; flex-wrap:wrap; margin-top:auto; }
  .as-vazio--certs { padding:64px 20px; }
  .as-vazio--certs h2 { font-size:18px; font-weight:800; color:var(--as-fg); margin:14px 0 0; }
  .as-vazio--certs ol { text-align:left; display:inline-block; margin:16px auto 0; padding-left:20px; color:var(--as-fg-2); font-size:14px; line-height:1.7; }
</style>
@endpush

@section('content')

<div class="as-head">
  <div>
    <h1>Meus certificados</h1>
    <p>Certificados emitidos nos cursos em que você foi aprovado(a) na prova. Cada um abre a versão para visualizar e imprimir em PDF.</p>
  </div>
  <a href="{{ route('assinante.home') }}" class="as-btn as-btn--ghost">
    <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.2;"><polyline points="15 18 9 12 15 6"/></svg>
    Voltar ao catálogo
  </a>
</div>

@if($certificados->isEmpty())
  <div class="as-vazio as-vazio--certs">
    <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="6"/><path d="M15.5 13 17 22l-5-3-5 3 1.5-9"/></svg>
    <h2>Você ainda não tem certificados</h2>
    <p>Para emitir o certificado de um curso:</p>
    <ol>
      <li>Abra o curso no catálogo e vá até a aba <strong>Prova</strong>.</li>
      <li>Acerte pelo menos <strong>70%</strong> das questões (pode refazer quantas vezes quiser).</li>
      <li>Clique em <strong>Emitir certificado</strong>. Ele aparece aqui e pode ser impresso ou salvo em PDF.</li>
    </ol>
    <p class="as-muted" style="margin-top:14px;">Cursos Minissérie valem 12 horas; Cursos Livres Aprofundados, 20 horas.</p>
    <div style="margin-top:18px;"><a href="{{ route('assinante.home') }}" class="as-btn as-btn--primary">Ir para o catálogo</a></div>
  </div>
@else
  <div class="as-certs">
    @foreach($certificados as $c)
      <div class="as-perfil__card as-certcard">
        <div class="as-certcard__topo">
          @if($c->tipo)
            <span class="as-badge as-badge--{{ $c->tipo }}">{{ $c->tipo === 'minisserie' ? 'Curso Minissérie' : 'Curso Livre Aprofundado' }}</span>
          @else
            <span class="as-badge">Certificado</span>
          @endif
          <span class="as-certcard__num">nº {{ $c->numero }}</span>
        </div>
        <h3>{{ $c->titulo }}</h3>
        <dl class="as-certcard__dl">
          <div><small>Carga horária</small><b>{{ $c->horas }} horas</b></div>
          <div><small>Concluído em</small><b>{{ $c->concluidoEm->format('d/m/Y') }}</b></div>
        </dl>
        <div class="as-certcard__cod" title="Código de autenticidade">{{ $c->token }}</div>
        <div class="as-certcard__acoes">
          @if($c->url)
            <a href="{{ $c->url }}" target="_blank" rel="noopener" class="as-btn as-btn--primary">Ver / imprimir</a>
          @else
            <span class="as-muted">Curso indisponível no momento.</span>
          @endif
          <a href="{{ $c->urlValidar }}" target="_blank" rel="noopener" class="as-btn as-btn--ghost">Validação pública</a>
        </div>
      </div>
    @endforeach
  </div>
@endif

@endsection
