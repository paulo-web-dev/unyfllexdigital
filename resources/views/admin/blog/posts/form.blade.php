@extends('layouts.admin')
@section('title', $post ? 'Editar Post' : 'Novo Post')
@section('section', 'Blog')

@section('content')
@include('admin.blog._field-styles')
<div class="page">

  {{-- Breadcrumb --}}
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:12px;color:var(--fg-4);">
    <a href="{{ route('admin.blog.posts.index') }}" style="color:var(--fg-4);text-decoration:none;">Blog</a>
    <span>/</span>
    <span style="color:var(--fg-2);">{{ $post ? 'Editar post' : 'Novo post' }}</span>
  </div>

  @if(session('success'))
    <div style="padding:12px 16px;background:rgba(43,217,161,0.10);border:1px solid rgba(43,217,161,0.35);border-radius:var(--r-md);color:#6FE6BD;font-size:13px;font-weight:500;margin-bottom:20px;">
      ✓ {{ session('success') }}
    </div>
  @endif
  @if($errors->any())
    <div style="padding:12px 16px;background:rgba(255,92,122,0.10);border:1px solid rgba(255,92,122,0.35);border-radius:var(--r-md);color:#FF5C7A;font-size:13px;margin-bottom:20px;">
      <strong>Corrija os erros:</strong>
      <ul style="margin:8px 0 0;padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form action="{{ $post ? route('admin.blog.posts.update', $post) : route('admin.blog.posts.store') }}"
        method="POST" enctype="multipart/form-data" id="post-form">
    @csrf
    @if($post) @method('PUT') @endif

    @php
      $faqQ = old('faq_q');
      $faqA = old('faq_a');
      if ($faqQ === null) {
        $items = $post ? $post->faqItems() : [];
        $faqQ = array_column($items, 'q');
        $faqA = array_column($items, 'a');
      }
      $pubVal = old('published_at', optional($post?->published_at)->format('Y-m-d\TH:i'));
      $statusVal = old('status', $post->status ?? 'rascunho');
    @endphp

    <div style="display:grid;grid-template-columns:1fr 330px;gap:20px;align-items:start;">

      {{-- ══ Coluna principal ══════════════════════════════════════════ --}}
      <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Conteúdo --}}
        <div class="card" style="padding:0;">
          <div style="padding:14px 20px;border-bottom:1px solid var(--line-2);">
            <h2 style="font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;margin:0;">Conteúdo</h2>
          </div>
          <div style="padding:20px;display:flex;flex-direction:column;gap:16px;">

            <div>
              <label class="field-label">Título *</label>
              <input type="text" name="title" id="input-title" class="field-input"
                     value="{{ old('title', $post->title ?? '') }}" required
                     placeholder="Ex: Pregão eletrônico passo a passo pela Lei 14.133"
                     oninput="if(!slugTouched) gerarSlug()">
              @error('title')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div>
              <label class="field-label">Slug (URL)</label>
              <div style="display:flex;align-items:center;">
                <span style="padding:10px 12px;background:var(--bg-3);border:1px solid var(--line-2);border-right:none;border-radius:var(--r-sm) 0 0 var(--r-sm);font-size:11px;color:var(--fg-4);white-space:nowrap;">/blog/</span>
                <input type="text" name="slug" id="input-slug" class="field-input"
                       value="{{ old('slug', $post->slug ?? '') }}"
                       style="border-radius:0 var(--r-sm) var(--r-sm) 0;"
                       placeholder="gerado automaticamente"
                       onfocus="slugTouched=true">
              </div>
              @error('slug')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div>
              <label class="field-label">Resumo (excerpt)</label>
              <textarea name="excerpt" class="field-input" rows="2"
                        placeholder="1–2 frases que aparecem nos cards e na meta description">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
              @error('excerpt')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div>
              <label class="field-label">Corpo do artigo *</label>
              <div id="editor">{!! old('content', $post->content ?? '') !!}</div>
              <textarea name="content" id="content-input" style="display:none">{{ old('content', $post->content ?? '') }}</textarea>
              @error('content')<span class="field-error">{{ $message }}</span>@enderror
            </div>

          </div>
        </div>

        {{-- FAQ --}}
        <div class="card" style="padding:0;">
          <div style="padding:14px 20px;border-bottom:1px solid var(--line-2);display:flex;justify-content:space-between;align-items:center;">
            <h2 style="font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;margin:0;">Perguntas frequentes (FAQ Schema)</h2>
            <button type="button" class="btn btn-ghost" onclick="addFaq()" style="padding:6px 12px;font-size:12px;">+ Pergunta</button>
          </div>
          <div style="padding:20px;display:flex;flex-direction:column;gap:12px;" id="faq-list">
            @forelse($faqQ as $i => $q)
              <div class="faq-row" style="display:flex;flex-direction:column;gap:8px;border:1px solid var(--line-2);border-radius:var(--r-sm);padding:12px;">
                <input type="text" name="faq_q[]" class="field-input" placeholder="Pergunta" value="{{ $q }}">
                <textarea name="faq_a[]" class="field-input" rows="2" placeholder="Resposta">{{ $faqA[$i] ?? '' }}</textarea>
                <button type="button" class="btn btn-ghost" onclick="this.closest('.faq-row').remove()" style="align-self:flex-end;padding:4px 10px;font-size:11px;color:#FF5C7A;">Remover</button>
              </div>
            @empty
            @endforelse
          </div>
        </div>

        {{-- SEO --}}
        <div class="card" style="padding:0;">
          <div style="padding:14px 20px;border-bottom:1px solid var(--line-2);">
            <h2 style="font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;margin:0;">SEO</h2>
          </div>
          <div style="padding:20px;display:flex;flex-direction:column;gap:16px;">
            <div>
              <label class="field-label">Meta title <span style="color:var(--fg-4);">(≤ 60 caracteres — vazio usa o título)</span></label>
              <input type="text" name="meta_title" class="field-input" maxlength="255"
                     value="{{ old('meta_title', $post->meta_title ?? '') }}">
            </div>
            <div>
              <label class="field-label">Meta description <span style="color:var(--fg-4);">(140–160 caracteres)</span></label>
              <textarea name="meta_description" class="field-input" rows="2" maxlength="320">{{ old('meta_description', $post->meta_description ?? '') }}</textarea>
            </div>
            <div>
              <label class="field-label">Palavra-chave principal</label>
              <input type="text" name="focus_keyword" class="field-input"
                     value="{{ old('focus_keyword', $post->focus_keyword ?? '') }}"
                     placeholder="ex: pregão eletrônico">
            </div>
            <div>
              <label class="field-label">Palavras-chave secundárias <span style="color:var(--fg-4);">(separadas por vírgula)</span></label>
              <input type="text" name="secondary_keywords" class="field-input"
                     value="{{ old('secondary_keywords', $post->secondary_keywords ?? '') }}">
            </div>
          </div>
        </div>

      </div>

      {{-- ══ Coluna lateral ════════════════════════════════════════════ --}}
      <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Publicação --}}
        <div class="card" style="padding:20px;display:flex;flex-direction:column;gap:14px;">
          <h2 style="font-family:var(--font-display);font-weight:700;font-size:15px;color:#fff;margin:0;">Publicação</h2>
          <div>
            <label class="field-label">Status</label>
            <select name="status" id="status-select" class="field-input" onchange="togglePub()">
              <option value="rascunho"  {{ $statusVal==='rascunho'  ? 'selected' : '' }}>Rascunho</option>
              <option value="agendado"  {{ $statusVal==='agendado'  ? 'selected' : '' }}>Agendado</option>
              <option value="publicado" {{ $statusVal==='publicado' ? 'selected' : '' }}>Publicado</option>
            </select>
          </div>
          <div id="pubat-wrap">
            <label class="field-label">Data de publicação</label>
            <input type="datetime-local" name="published_at" class="field-input" value="{{ $pubVal }}">
            <span style="font-size:11px;color:var(--fg-4);">Agendado: publica nesta data. Publicado sem data: agora.</span>
          </div>
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
            {{ $post ? 'Salvar alterações' : 'Criar post' }}
          </button>
          @if($post)
            <a href="{{ route('admin.blog.posts.preview', $post) }}" target="_blank"
               class="btn btn-ghost" style="width:100%;justify-content:center;display:flex;text-decoration:none;">Pré-visualizar</a>
          @endif
        </div>

        {{-- Categoria --}}
        <div class="card" style="padding:20px;display:flex;flex-direction:column;gap:10px;">
          <h2 style="font-family:var(--font-display);font-weight:700;font-size:15px;color:#fff;margin:0;">Categoria</h2>
          <select name="blog_category_id" class="field-input">
            <option value="">— sem categoria —</option>
            @foreach($categories as $c)
              <option value="{{ $c->id }}" {{ (string)old('blog_category_id', $post->blog_category_id ?? '') === (string)$c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
          </select>
        </div>

        {{-- Tags --}}
        <div class="card" style="padding:20px;display:flex;flex-direction:column;gap:10px;">
          <h2 style="font-family:var(--font-display);font-weight:700;font-size:15px;color:#fff;margin:0;">Tags</h2>
          <input type="text" name="tags_input" class="field-input"
                 value="{{ old('tags_input', $tagsValue) }}"
                 placeholder="separadas por vírgula">
          <span style="font-size:11px;color:var(--fg-4);">Ex: Lei 14.133, Pregão Eletrônico</span>
        </div>

        {{-- Imagem destacada --}}
        <div class="card" style="padding:20px;display:flex;flex-direction:column;gap:10px;">
          <h2 style="font-family:var(--font-display);font-weight:700;font-size:15px;color:#fff;margin:0;">Imagem destacada</h2>
          <div id="img-preview" style="aspect-ratio:16/9;border-radius:var(--r-sm);border:1px solid var(--line-2);background:var(--bg-3) center/cover no-repeat;{{ $post && $post->hasFeatured() ? "background-image:url('".$post->featuredUrl()."');" : '' }}"></div>
          <input type="file" name="featured" accept="image/*" class="field-input" onchange="previewImg(this)">
          @error('featured')<span class="field-error">{{ $message }}</span>@enderror
          <span style="font-size:11px;color:var(--fg-4);">JPG, PNG ou WEBP até 4MB. Opcional.</span>
        </div>

        @if($post)
        <form action="{{ route('admin.blog.posts.destroy', $post) }}" method="POST"
              onsubmit="return confirm('Excluir este post? Esta ação não pode ser desfeita.')">
          @csrf @method('DELETE')
          <button type="submit" class="btn btn-ghost" style="width:100%;justify-content:center;color:#FF5C7A;border-color:rgba(255,92,122,0.35);">Excluir post</button>
        </form>
        @endif

      </div>
    </div>
  </form>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<style>
  #editor{min-height:340px;background:var(--bg-1);color:var(--fg-2);border-radius:0 0 var(--r-sm) var(--r-sm);font-size:15px;line-height:1.7}
  .ql-toolbar.ql-snow{background:var(--bg-3);border-color:var(--line-2)!important;border-radius:var(--r-sm) var(--r-sm) 0 0}
  .ql-container.ql-snow{border-color:var(--line-2)!important}
  .ql-snow .ql-stroke{stroke:var(--fg-3)}
  .ql-snow .ql-fill{fill:var(--fg-3)}
  .ql-snow .ql-picker{color:var(--fg-3)}
  .ql-editor.ql-blank::before{color:var(--fg-4);font-style:normal}
  .ql-snow .ql-picker-options{background:var(--bg-3);border-color:var(--line-2)!important}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
  let slugTouched = {{ $post ? 'true' : 'false' }};
  function gerarSlug(){
    const t = document.getElementById('input-title').value;
    document.getElementById('input-slug').value = t.toString().normalize('NFD')
      .replace(/[\u0300-\u036f]/g,'').toLowerCase().trim()
      .replace(/[^a-z0-9\s-]/g,'').replace(/\s+/g,'-').replace(/-+/g,'-');
  }
  function togglePub(){
    const s = document.getElementById('status-select').value;
    document.getElementById('pubat-wrap').style.display = (s === 'rascunho') ? 'none' : 'block';
  }
  function addFaq(){
    const wrap = document.createElement('div');
    wrap.className = 'faq-row';
    wrap.style.cssText = 'display:flex;flex-direction:column;gap:8px;border:1px solid var(--line-2);border-radius:var(--r-sm);padding:12px;';
    wrap.innerHTML = '<input type="text" name="faq_q[]" class="field-input" placeholder="Pergunta">'
      + '<textarea name="faq_a[]" class="field-input" rows="2" placeholder="Resposta"></textarea>'
      + '<button type="button" class="btn btn-ghost" onclick="this.closest(\'.faq-row\').remove()" style="align-self:flex-end;padding:4px 10px;font-size:11px;color:#FF5C7A;">Remover</button>';
    document.getElementById('faq-list').appendChild(wrap);
  }
  function previewImg(input){
    if(input.files && input.files[0]){
      const r = new FileReader();
      r.onload = e => document.getElementById('img-preview').style.backgroundImage = "url('"+e.target.result+"')";
      r.readAsDataURL(input.files[0]);
    }
  }

  const quill = new Quill('#editor', {
    theme: 'snow',
    placeholder: 'Escreva o artigo... use H2/H3 para os subtítulos.',
    modules: { toolbar: [
      [{ header: [2, 3, false] }],
      ['bold','italic','underline'],
      [{ list: 'ordered' }, { list: 'bullet' }],
      ['blockquote','link'],
      ['clean']
    ]}
  });
  document.getElementById('post-form').addEventListener('submit', function(){
    document.getElementById('content-input').value = quill.root.innerHTML;
  });
  togglePub();
</script>
@endpush
