@extends('layouts.blog')

@section('meta_title', 'Tag: '.$tag->name.' — Blog Unyflex Digital')
@section('meta_description', 'Artigos marcados com "'.$tag->name.'" no blog da Unyflex Digital — licitações, gestão pública e capacitação para servidores.')
@section('og_title', 'Tag: '.$tag->name.' — Blog Unyflex Digital')

@push('schema')
<script type="application/ld+json">{!! json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'BreadcrumbList',
  'itemListElement' => [
    ['@type'=>'ListItem','position'=>1,'name'=>'Início','item'=>url('/')],
    ['@type'=>'ListItem','position'=>2,'name'=>'Blog','item'=>route('blog.index')],
    ['@type'=>'ListItem','position'=>3,'name'=>$tag->name,'item'=>$tag->url()],
  ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
<nav class="blog-bc">
  <a href="{{ url('/') }}">Início</a> › <a href="{{ route('blog.index') }}">Blog</a> › <span>{{ $tag->name }}</span>
</nav>

<main class="blog-wrap">
  <section class="arch-head">
    <div class="k">Tag</div>
    <h1>{{ $tag->name }}</h1>
    <p>Tudo o que publicamos sobre {{ $tag->name }}.</p>
  </section>

  @if($posts->count())
    <div class="blog-grid">
      @foreach($posts as $post)
        @include('blog._card', ['post' => $post])
      @endforeach
    </div>
    <div class="blog-pag">{{ $posts->links('pagination::bootstrap-5') }}</div>
  @else
    <div class="blog-empty">Nenhum artigo publicado com esta tag ainda.</div>
  @endif
</main>

@include('blog._cta')
<div style="height:56px"></div>
@include('blog._styles')
@endsection
