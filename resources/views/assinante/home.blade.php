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
    <p>Cada card é um <strong style="color:var(--as-fg-2);">Curso Minissérie</strong>, um <strong style="color:var(--as-fg-2);">Curso Modular</strong>, um <strong style="color:var(--as-fg-2);">Curso Livre Aprofundado</strong> ou uma <strong style="color:var(--as-fg-2);">Apostila / Material de Pós-Graduação</strong>. Como assinante, você tem acesso a tudo.</p>
    <p class="as-note">Cada curso de uma turma gravada é um Curso Modular. O Curso Livre Aprofundado é a turma inteira, reunindo todos os seus cursos em sequência.</p>
  </div>
  {{-- Cada quadrado é um atalho: clicar já filtra o catálogo por tipo (o total limpa o filtro). --}}
  @php
    $kpis = [
      ['tipo' => '',           'n' => $meta['paineis'] + $meta['modular'], 'rotulo' => 'itens no catálogo'],
      ['tipo' => 'minisserie', 'n' => $meta['minisserie'],                'rotulo' => 'cursos minissérie'],
      ['tipo' => 'gravado',    'n' => $meta['gravado'],                   'rotulo' => 'cursos modulares'],
      ['tipo' => 'livre',      'n' => $meta['livre'],                     'rotulo' => 'cursos livres aprofundados'],
      ['tipo' => 'modular',    'n' => $meta['modular'],                   'rotulo' => 'apostilas e materiais pós-graduação'],
    ];
  @endphp
  <nav class="as-kpis" aria-label="Filtrar por tipo">
    @foreach($kpis as $k)
      @php $ativo = $filtros['tipo'] === $k['tipo']; @endphp
      <a class="as-kpi {{ $k['tipo'] === '' ? 'as-kpi--total' : '' }} {{ $ativo ? 'is-active' : '' }}"
         href="{{ request()->fullUrlWithQuery(['tipo' => $k['tipo'] === '' ? null : $k['tipo'], 'page' => null]) }}"
         title="{{ $k['tipo'] === '' ? 'Ver todo o catálogo' : 'Mostrar só ' . $k['rotulo'] }}"
         @if($ativo) aria-current="true" @endif>
        <b>{{ $k['n'] }}</b><span>{{ $k['rotulo'] }}</span>
      </a>
    @endforeach
  </nav>
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
