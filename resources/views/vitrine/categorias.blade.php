@extends('layouts.vitrine')

@section('meta_title', 'Categorias — Assinatura Premium Unyflex')
@section('meta_description', $resumo['cursos_marketing'] . ' cursos para servidores públicos em ' . $resumo['categorias'] . ' categorias: licitações, controle interno, legislativo, finanças municipais e mais.')

@section('content')
  <section class="vt-hero vt-hero--curto">
    <div class="vt-wrap vt-hero-in">
      <p class="vt-eyebrow">Catálogo</p>
      <h1 class="vt-h1">Cursos por <span class="vt-grad">categoria</span></h1>
      <p class="vt-lead">{{ $resumo['cursos_marketing'] }} cursos e {{ number_format($resumo['apostilas'], 0, ',', '.') }} apostilas incluídos na Assinatura Premium.</p>
    </div>
  </section>

  {{-- Destaques em mosaico --}}
  @if($destaques->isNotEmpty())
    <section class="vt-secao" aria-labelledby="destaques-titulo">
      <div class="vt-wrap">
        <h2 id="destaques-titulo" class="vt-h2">Categorias em destaque</h2>
        <div class="vt-mosaico">
          @foreach($destaques as $cat)
            <a href="{{ $cat->url }}" class="vt-mosaico-item" style="{{ $cat->estilo }}">
              <i data-lucide="{{ $cat->icone }}"></i>
              <strong>{{ $cat->titulo }}</strong>
              <span>{{ $cat->cursos }} cursos</span>
            </a>
          @endforeach
        </div>
      </div>
    </section>
  @endif

  {{-- Todas as categorias: busca e ordenação no cliente (vitrine.js) --}}
  <section class="vt-secao vt-secao--alt" aria-labelledby="todas-titulo" data-vt-lista-cat>
    <div class="vt-wrap">
      <div class="vt-lista-topo">
        <h2 id="todas-titulo" class="vt-h2">Todas as categorias</h2>
        <div class="vt-lista-ctrl">
          <label class="vt-busca">
            <i data-lucide="search"></i>
            <input type="search" placeholder="Buscar categoria" aria-label="Buscar categoria" data-vt-cat-busca>
          </label>
          <select aria-label="Ordenar" data-vt-cat-ordem>
            <option value="az">Nome (A–Z)</option>
            <option value="za">Nome (Z–A)</option>
            <option value="qtd">Mais cursos</option>
          </select>
        </div>
      </div>

      <ul class="vt-lista-cat" data-vt-cat-itens>
        @foreach($categorias as $cat)
          <li class="vt-lista-cat-item" data-nome="{{ $cat->titulo }}" data-qtd="{{ $cat->cursos }}">
            <span class="vt-lista-cat-ico" style="{{ $cat->estilo }}"><i data-lucide="{{ $cat->icone }}"></i></span>
            <span class="vt-lista-cat-txt"><strong>{{ $cat->titulo }}</strong><small>{{ $cat->cursos }} cursos</small></span>
            <a href="{{ $cat->url }}" class="vt-btn vt-btn-outline vt-btn-sm">Ver cursos</a>
          </li>
        @endforeach
      </ul>
      <p class="vt-vazio" data-vt-cat-vazio hidden>Nenhuma categoria encontrada.</p>
    </div>
  </section>

  @include('vitrine.partials.cta-final', [
      'titulo'      => 'Não encontrou o tema?',
      'texto'       => 'Fale com um consultor: ele mostra os cursos que atendem à necessidade da sua equipe.',
      'botao'       => 'Falar com um consultor',
      'mensagem'    => \App\Services\AssinaturaVitrineService::mensagem('sem_tema'),
      'contentName' => 'Categorias: Não encontrou o tema',
  ])
@endsection
