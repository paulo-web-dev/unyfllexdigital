@extends('layouts.assinante')
@section('title', 'Catálogo — Assinatura Unyflex')
@section('section', 'Catálogo')

@section('content')

@if(session('warning'))
  <div class="as-alert as-alert--warn">{{ session('warning') }}</div>
@endif

<div class="as-head">
  <div>
    <h1>Seu acesso completo</h1>
    <p>Cada card é um <strong style="color:var(--as-fg-2);">curso</strong>: um módulo de minissérie ou de curso gravado, ou um curso modular completo. Como assinante, você tem acesso a tudo.</p>
    <p class="as-note">Minisséries e cursos gravados são divididos em cursos por tema, cada um com suas aulas e materiais.</p>
  </div>
  <div class="as-kpis">
    <div class="as-kpi as-kpi--total"><b>{{ $meta['paineis'] + $meta['modular'] }}</b><span>cursos no catálogo</span></div>
    <div class="as-kpi"><b>{{ $meta['minisserie'] }}</b><span>cursos · minisséries</span></div>
    <div class="as-kpi"><b>{{ $meta['gravado'] }}</b><span>cursos · gravados</span></div>
    <div class="as-kpi"><b>{{ $meta['modular'] }}</b><span>cursos modulares</span></div>
  </div>
</div>

@include('assinante.catalogo._filtros')

@if($itens->isEmpty())
  <div class="as-vazio">
    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
    <p>Nenhum item encontrado com esses filtros.</p>
  </div>
@else
  <div class="as-grid">
    @foreach($itens as $item)
      @include('assinante.catalogo._card', ['item' => $item])
    @endforeach
  </div>

  <div class="as-pag">
    <span>Mostrando {{ $itens->firstItem() }}–{{ $itens->lastItem() }} de {{ $itens->total() }} {{ $itens->total() === 1 ? 'item' : 'itens' }}</span>
    {{ $itens->onEachSide(1)->links('pagination::bootstrap-5') }}
  </div>
@endif

@endsection
