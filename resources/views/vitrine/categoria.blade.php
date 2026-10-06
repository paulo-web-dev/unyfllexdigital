@extends('layouts.vitrine')

@php use App\Services\AssinaturaVitrineService as V; @endphp

@section('meta_title', $categoria->titulo . ' — Cursos da Assinatura Premium Unyflex')
@section('meta_description', $categoria->cursos . ' cursos de ' . $categoria->titulo . ' para servidores públicos, incluídos na Assinatura Premium Unyflex.')

@section('content')
  <section class="vt-hero vt-hero--curto vt-hero--cat" style="{{ $categoria->estilo }}">
    <div class="vt-wrap vt-hero-in">
      <nav class="vt-trilha" aria-label="Você está em">
        <a href="{{ route('assinatura.categorias') }}">Categorias</a><i data-lucide="chevron-right"></i><span>{{ $categoria->titulo }}</span>
      </nav>
      <h1 class="vt-h1"><i data-lucide="{{ $categoria->icone }}" class="vt-h1-ico"></i>{{ $categoria->titulo }}</h1>
      <p class="vt-lead">{{ $categoria->cursos }} cursos incluídos na Assinatura Premium, junto com todo o restante do catálogo.</p>
      <div class="vt-hero-ctas">
        <x-vitrine.whatsapp class="vt-btn vt-btn-primary" :mensagem="V::mensagem('categoria', ['categoria' => $categoria->titulo])"
            :content-name="'Categoria: ' . $categoria->titulo">
          <i data-lucide="message-circle"></i>Quero para minha equipe
        </x-vitrine.whatsapp>
        <a href="{{ route('assinatura.planos') }}" class="vt-btn vt-btn-ghost">Ver planos</a>
      </div>
    </div>
  </section>

  <section class="vt-secao">
    <div class="vt-wrap">
      <form method="get" action="{{ route('assinatura.categoria', $categoria->slug) }}" class="vt-busca vt-busca--larga" role="search">
        <i data-lucide="search"></i>
        <input type="search" name="busca" value="{{ $busca }}" placeholder="Buscar curso em {{ $categoria->titulo }}" aria-label="Buscar curso">
        @if($busca !== '')<a href="{{ route('assinatura.categoria', $categoria->slug) }}" class="vt-busca-limpar">Limpar</a>@endif
      </form>

      @if($itens->isEmpty())
        <p class="vt-vazio">Nenhum curso encontrado{{ $busca !== '' ? ' para "' . $busca . '"' : '' }}.</p>
      @else
        <div class="vt-grade">
          @foreach($itens as $curso)
            <x-vitrine.curso-card :curso="$curso" />
          @endforeach
        </div>
        @if($itens->hasPages())
          <nav class="vt-paginacao" aria-label="Páginas">
            @if($itens->onFirstPage())
              <span class="vt-btn vt-btn-ghost vt-btn-sm" aria-disabled="true">Anterior</span>
            @else
              <a href="{{ $itens->previousPageUrl() }}" class="vt-btn vt-btn-ghost vt-btn-sm">Anterior</a>
            @endif
            <span>Página {{ $itens->currentPage() }} de {{ $itens->lastPage() }}</span>
            @if($itens->hasMorePages())
              <a href="{{ $itens->nextPageUrl() }}" class="vt-btn vt-btn-ghost vt-btn-sm">Próxima</a>
            @else
              <span class="vt-btn vt-btn-ghost vt-btn-sm" aria-disabled="true">Próxima</span>
            @endif
          </nav>
        @endif
      @endif
    </div>
  </section>

  @if($outras->isNotEmpty())
    <section class="vt-secao vt-secao--alt" aria-labelledby="outras-titulo">
      <div class="vt-wrap">
        <h2 id="outras-titulo" class="vt-h3">Também na assinatura</h2>
        <div class="vt-chips">
          @foreach($outras as $cat)
            <a href="{{ $cat->url }}" class="vt-chip"><i data-lucide="{{ $cat->icone }}"></i>{{ $cat->titulo }} <small>{{ $cat->cursos }}</small></a>
          @endforeach
          <a href="{{ route('assinatura.categorias') }}" class="vt-chip vt-chip--todas">Todas as categorias</a>
        </div>
      </div>
    </section>
  @endif

  @include('vitrine.partials.cta-final')
@endsection
