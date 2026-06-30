@extends('layouts.blog')

@section('meta_title', ($category->name).' — Blog Unyflex Digital')
@section('meta_description', $category->description ?: ('Artigos sobre '.$category->name.' para servidores públicos municipais. Conteúdo prático da Unyflex Digital.'))
@section('og_title', $category->name.' — Blog Unyflex Digital')
@section('og_description', $category->description ?: ('Artigos sobre '.$category->name.' para servidores públicos.'))

@push('schema')
<script type="application/ld+json">{!! json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'BreadcrumbList',
  'itemListElement' => [
    ['@type'=>'ListItem','position'=>1,'name'=>'Início','item'=>url('/')],
    ['@type'=>'ListItem','position'=>2,'name'=>'Blog','item'=>route('blog.index')],
    ['@type'=>'ListItem','position'=>3,'name'=>$category->name,'item'=>$category->url()],
  ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
<nav class="blog-bc">
  <a href="{{ url('/') }}">Início</a> › <a href="{{ route('blog.index') }}">Blog</a> › <span>{{ $category->name }}</span>
</nav>

<main class="blog-wrap">
  <section class="arch-head">
    <div class="k">Categoria</div>
    <h1>{{ $category->name }}</h1>
    @if($category->description)<p>{{ $category->description }}</p>@endif
    <nav class="blog-cats">
      <a href="{{ route('blog.index') }}" class="blog-cat-pill">Todos</a>
      @foreach($categories as $c)
        <a href="{{ $c->url() }}" class="blog-cat-pill {{ $c->id === $category->id ? 'active' : '' }}">{{ $c->name }} <span class="n">{{ $c->published_count }}</span></a>
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
    <div class="blog-empty">Ainda não há artigos publicados nesta categoria.</div>
  @endif
</main>

@include('blog._cta', ['category' => $category])
<div style="height:56px"></div>
@include('blog._styles')
@endsection
