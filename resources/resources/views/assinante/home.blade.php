@extends('layouts.assinante')
@section('title', 'Catálogo — Assinatura Unyflex')
@section('section', 'Catálogo')

@section('content')

@if(session('warning'))
  <div style="padding:12px 16px;background:rgba(255,181,71,0.10);border:1px solid rgba(255,181,71,0.35);border-radius:10px;color:#FFB547;font-size:13px;margin-bottom:20px;">{{ session('warning') }}</div>
@endif

<div style="margin-bottom:18px;">
  <h1 style="font-size:24px;font-weight:800;color:#fff;margin:0;">Seu acesso completo</h1>
  <p style="color:#8A94A6;font-size:14px;margin:6px 0 0;">Como assinante, você tem acesso a todas as minisséries e todos os cursos modulares.</p>
</div>

{{-- Barra: abas + busca --}}
<div style="display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:22px;">
  <div id="tabs" style="display:flex;gap:6px;background:#0f1520;border:1px solid #1e2836;border-radius:10px;padding:4px;flex-wrap:wrap;">
    <button type="button" class="cat-tab active" data-filtro="todos">Todos</button>
    <button type="button" class="cat-tab" data-filtro="assistido">Continuar assistindo</button>
    <button type="button" class="cat-tab" data-filtro="minisserie">Minisséries</button>
    <button type="button" class="cat-tab" data-filtro="gravado">Cursos Gravados</button>
    <button type="button" class="cat-tab" data-filtro="modular">Modulares</button>
  </div>
  <div style="position:relative;flex:1;max-width:320px;min-width:200px;">
    <i data-lucide="search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:#8A94A6;"></i>
    <input id="busca" type="text" placeholder="Buscar curso..." autocomplete="off"
           style="width:100%;padding:10px 12px 10px 36px;background:#0f1520;border:1px solid #1e2836;border-radius:10px;color:#fff;font-size:13px;">
  </div>
</div>

@php
  $cardImg = 'width:100%;aspect-ratio:16/9;object-fit:cover;background:#0f1520;display:block;';
  $card = 'position:relative;background:#0f1520;border:1px solid #1e2836;border-radius:14px;overflow:hidden;text-decoration:none;display:flex;flex-direction:column;transition:border-color .15s,transform .15s;';
  $badge = 'position:absolute;top:8px;left:8px;z-index:2;font-size:10px;font-weight:700;padding:3px 9px;border-radius:20px;';
  $ph = $cardImg . 'display:flex;align-items:center;justify-content:center;color:#3a4658;';
@endphp

<div id="grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:16px;">

  @foreach($minisseries as $m)
    @php $banner = $m->photo ? 'https://unyflex.com.br/storage/cursos/banner/' . $m->photo : null; @endphp
    <a href="{{ route('player', $m->slug) }}" class="curso-card" data-tipo="minisserie" data-assistido="{{ in_array($m->id, $assistidasClasses) ? '1' : '0' }}" data-nome="{{ Str::lower($m->title) }}"
       style="{{ $card }}" onmouseover="this.style.borderColor='#00a3ff';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='#1e2836';this.style.transform='none'">
      <span style="{{ $badge }}background:rgba(0,163,255,.9);color:#fff;">Minissérie</span>
      @if($banner)
        <img src="{{ $banner }}" alt="" style="{{ $cardImg }}" loading="lazy">
      @else
        <div style="{{ $ph }}"><i data-lucide="film"></i></div>
      @endif
      <div style="padding:14px;">
        <div style="font-size:14px;font-weight:600;color:#fff;line-height:1.3;">{{ $m->title }}</div>
        @if($m->subtitle)<div style="font-size:12px;color:#8A94A6;margin-top:4px;">{{ Str::limit($m->subtitle, 60) }}</div>@endif
      </div>
    </a>
  @endforeach

  @foreach($gravados as $g)
    @php $bannerG = $g->photo ? 'https://unyflex.com.br/storage/cursos/banner/' . $g->photo : null; @endphp
    <a href="{{ route('player', $g->slug) }}" class="curso-card" data-tipo="gravado" data-assistido="{{ in_array($g->id, $assistidasClasses) ? '1' : '0' }}" data-nome="{{ Str::lower($g->title) }}"
       style="{{ $card }}" onmouseover="this.style.borderColor='#00a3ff';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='#1e2836';this.style.transform='none'">
      <span style="{{ $badge }}background:rgba(255,181,71,.92);color:#3a2600;">Curso Gravado</span>
      @if($bannerG)
        <img src="{{ $bannerG }}" alt="" style="{{ $cardImg }}" loading="lazy">
      @else
        <div style="{{ $ph }}"><i data-lucide="video"></i></div>
      @endif
      <div style="padding:14px;">
        <div style="font-size:14px;font-weight:600;color:#fff;line-height:1.3;">{{ $g->title }}</div>
        @if($g->subtitle)<div style="font-size:12px;color:#8A94A6;margin-top:4px;">{{ Str::limit($g->subtitle, 60) }}</div>@endif
      </div>
    </a>
  @endforeach

  @foreach($modulares as $c)
    @php $capa = $c->coverArt->firstWhere('status', 'pronto'); @endphp
    <a href="{{ route('ava.modulares.show', $c->slug) }}" class="curso-card" data-tipo="modular" data-assistido="{{ in_array($c->title, $modularesAssistidos) ? '1' : '0' }}" data-nome="{{ Str::lower($c->title) }}"
       style="{{ $card }}" onmouseover="this.style.borderColor='#00a3ff';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='#1e2836';this.style.transform='none'">
      <span style="{{ $badge }}background:rgba(43,217,161,.9);color:#04231a;">Modular</span>
      @if($capa)
        <img src="{{ $capa->imageUrl() }}" alt="" style="{{ $cardImg }}" loading="lazy">
      @else
        <div style="{{ $ph }}"><i data-lucide="book-open"></i></div>
      @endif
      <div style="padding:14px;">
        <div style="font-size:14px;font-weight:600;color:#fff;line-height:1.3;">{{ $c->title }}</div>
      </div>
    </a>
  @endforeach

</div>

<div id="vazio" style="display:none;text-align:center;padding:50px 20px;color:#8A94A6;">
  <i data-lucide="search-x" style="width:32px;height:32px;"></i>
  <p style="font-size:14px;margin:12px 0 0;">Nenhum curso encontrado.</p>
</div>

@push('styles')
<style>
  .cat-tab{background:none;border:none;color:#8A94A6;font-size:13px;font-weight:600;padding:7px 14px;border-radius:7px;cursor:pointer;white-space:nowrap;transition:all .15s;}
  .cat-tab:hover{color:#c9d1dc;}
  .cat-tab.active{background:#00a3ff;color:#fff;}
  .cat-tab .cnt{opacity:.7;font-weight:500;}
</style>
@endpush

@push('scripts')
<script>
  (function () {
    var tabs = Array.prototype.slice.call(document.querySelectorAll('.cat-tab'));
    var busca = document.getElementById('busca');
    var grid = document.getElementById('grid');
    var vazio = document.getElementById('vazio');
    var filtro = 'todos';
    function aplicar() {
      var termo = (busca.value || '').toLowerCase().trim();
      var cards = grid.querySelectorAll('.curso-card');
      var visiveis = 0;
      cards.forEach(function (c) {
        var tipoOk;
        if (filtro === 'todos') { tipoOk = true; }
        else if (filtro === 'assistido') { tipoOk = c.getAttribute('data-assistido') === '1'; }
        else { tipoOk = c.getAttribute('data-tipo') === filtro; }
        var nomeOk = !termo || (c.getAttribute('data-nome') || '').indexOf(termo) !== -1;
        var show = tipoOk && nomeOk;
        c.style.display = show ? '' : 'none';
        if (show) visiveis++;
      });
      vazio.style.display = visiveis ? 'none' : 'block';
    }
    tabs.forEach(function (t) {
      t.addEventListener('click', function () {
        tabs.forEach(function (x) { x.classList.remove('active'); });
        t.classList.add('active');
        filtro = t.getAttribute('data-filtro');
        aplicar();
      });
    });
    busca.addEventListener('input', aplicar);
  })();
</script>
@endpush

@endsection
