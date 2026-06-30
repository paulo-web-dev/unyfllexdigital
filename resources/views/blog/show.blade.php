@extends('layouts.blog')

@section('meta_title', $post->metaTitleOr().' — Unyflex Digital')
@section('meta_description', $post->metaDescriptionOr())
@section('meta_keywords', $post->focus_keyword)
@section('og_type', 'article')
@section('og_title', $post->metaTitleOr())
@section('og_description', $post->metaDescriptionOr())
@section('og_image', $post->ogImageUrl())
@if($post->publishedDate())
@section('article_published', $post->publishedDate()->toIso8601String())
@endif
@if($post->updated_at)
@section('article_modified', $post->updated_at->toIso8601String())
@endif

@push('schema')
<script type="application/ld+json">{!! $post->jsonLdArticle() !!}</script>
@php $faqLd = $post->jsonLdFaq(); @endphp
@if($faqLd)
<script type="application/ld+json">{!! $faqLd !!}</script>
@endif
@php
  $bc = [['@type'=>'ListItem','position'=>1,'name'=>'Início','item'=>url('/')],
         ['@type'=>'ListItem','position'=>2,'name'=>'Blog','item'=>route('blog.index')]];
  $pos = 3;
  if ($post->category) { $bc[] = ['@type'=>'ListItem','position'=>$pos++,'name'=>$post->category->name,'item'=>$post->category->url()]; }
  $bc[] = ['@type'=>'ListItem','position'=>$pos,'name'=>$post->title,'item'=>$post->url()];
@endphp
<script type="application/ld+json">{!! json_encode([
  '@context'=>'https://schema.org','@type'=>'BreadcrumbList','itemListElement'=>$bc,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
<nav class="blog-bc">
  <a href="{{ url('/') }}">Início</a> › <a href="{{ route('blog.index') }}">Blog</a>
  @if($post->category) › <a href="{{ $post->category->url() }}">{{ $post->category->name }}</a>@endif
</nav>

<article>
  <header class="post-head">
    @if($post->category)
      <a href="{{ $post->category->url() }}" class="badge-cat">{{ $post->category->name }}</a>
    @endif
    <h1>{{ $post->title }}</h1>
    <div class="meta">
      <span><i data-lucide="user"></i>{{ $post->author ?: 'Equipe Unyflex' }}</span>
      @if($post->publishedDate())
        <span><i data-lucide="calendar"></i>{{ $post->publishedDate()->translatedFormat('d \d\e F \d\e Y') }}</span>
      @endif
      <span><i data-lucide="clock"></i>{{ $post->reading_time }} min de leitura</span>
    </div>
  </header>

  @if($post->hasFeatured())
    <figure class="post-featured">
      <img src="{{ $post->featuredUrl() }}" alt="{{ $post->title }}">
    </figure>
  @endif

  <div class="post-body">
    {!! $post->content !!}
  </div>

  @php $faq = $post->faqItems(); @endphp
  @if(!empty($faq))
    <section class="post-faq">
      <h2>Perguntas frequentes</h2>
      @foreach($faq as $item)
        @php $q = $item['q'] ?? $item['question'] ?? null; $a = $item['a'] ?? $item['answer'] ?? null; @endphp
        @if($q && $a)
          <details class="faq-item">
            <summary>{{ $q }}</summary>
            <div class="a">{!! nl2br(e($a)) !!}</div>
          </details>
        @endif
      @endforeach
    </section>
  @endif

  @if($post->tags->count())
    <div class="post-tags">
      @foreach($post->tags as $t)
        <a href="{{ $t->url() }}">#{{ $t->name }}</a>
      @endforeach
    </div>
  @endif

  @include('blog._cta', ['category' => $post->category])
</article>

@if($relacionados->count())
  <section class="post-rel">
    <h2>Continue lendo</h2>
    <div class="blog-grid">
      @foreach($relacionados as $post)
        @include('blog._card', ['post' => $post])
      @endforeach
    </div>
  </section>
@endif

@include('blog._styles')
@endsection
