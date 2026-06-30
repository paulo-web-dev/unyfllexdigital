@extends('layouts.blog')

@section('meta_title', 'Blog Unyflex Digital — Licitações, Lei 14.133 e Gestão Pública')
@section('meta_description', 'Artigos práticos sobre licitações, Lei 14.133, pregão eletrônico, controle interno, LGPD e gestão pública municipal. Conteúdo para servidores aplicarem no dia seguinte.')
@section('og_title', 'Blog Unyflex Digital')
@section('og_description', 'Conteúdo prático sobre licitações e gestão pública para servidores municipais.')

@section('content')
<main class="blog-wrap">
  <section class="blog-hero">
    <h1>Blog Unyflex Digital</h1>
    <p>Conteúdo prático sobre licitações, Lei 14.133, controle interno, LGPD e gestão pública — para servidores que precisam aplicar na rotina, não só estudar a teoria.</p>
    <nav class="blog-cats">
      <a href="{{ route('blog.index') }}" class="blog-cat-pill active">Todos</a>
      @foreach($categories as $c)
        <a href="{{ $c->url() }}" class="blog-cat-pill">{{ $c->name }} <span class="n">{{ $c->published_count }}</span></a>
      @endforeach
    </nav>
  </section>

  @if($posts->count())
    <div class="blog-grid">
      @foreach($posts as $post)
        @include('blog._card', ['post' => $post])
      @endforeach
    </div>
    <div class="blog-pag">{{ $posts->links('pagination::bootstrap-5') }}</div>
  @else
    <div class="blog-empty">Em breve, novos conteúdos por aqui. 👀</div>
  @endif
</main>

@include('blog._cta')
<div style="height:56px"></div>
@include('blog._styles')
@endsection
