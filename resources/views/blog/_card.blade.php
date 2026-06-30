<a href="{{ $post->url() }}" class="post-card">
  <div class="thumb">
    @if($post->hasFeatured())
      <img src="{{ $post->featuredUrl() }}" alt="{{ $post->title }}" loading="lazy">
    @endif
    @if($post->category)
      <span class="badge-cat">{{ $post->category->name }}</span>
    @endif
  </div>
  <div class="body">
    <h3>{{ $post->title }}</h3>
    <p>{{ $post->excerptOr() }}</p>
    <div class="meta">
      <span><i data-lucide="clock"></i>{{ $post->reading_time }} min de leitura</span>
      @if($post->publishedDate())
        <span><i data-lucide="calendar"></i>{{ $post->publishedDate()->translatedFormat('d M Y') }}</span>
      @endif
    </div>
  </div>
</a>
