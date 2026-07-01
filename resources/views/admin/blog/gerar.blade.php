@extends('layouts.admin')
@section('title', 'Gerar artigo com IA')
@section('section', 'Blog')

@section('content')
<div class="page">

  <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:12px;color:var(--fg-4);">
    <a href="{{ route('admin.blog.posts.index') }}" style="color:var(--fg-4);text-decoration:none;">Blog</a>
    <span>/</span>
    <span style="color:var(--fg-2);">Gerar com IA</span>
  </div>

  @if($errors->any())
    <div style="padding:12px 16px;background:rgba(255,92,122,0.10);border:1px solid rgba(255,92,122,0.35);border-radius:var(--r-md);color:#FF5C7A;font-size:13px;margin-bottom:16px;"><ul style="margin:0;padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  <div style="max-width:680px;">
    <div style="margin-bottom:20px;">
      <h1 style="font-family:var(--font-display);font-weight:800;font-size:22px;color:#fff;margin:0;">✨ Gerar artigo com IA</h1>
      <p style="color:var(--fg-3);font-size:13.5px;margin:6px 0 0;line-height:1.5;">Escolha um tema do plano de SEO (ou escreva o seu). O Claude escreve o artigo completo — com H2/H3, FAQ e meta tags — e ele chega como <strong style="color:#fff;">rascunho</strong> para você revisar antes de publicar.</p>
    </div>

    <form action="{{ route('admin.blog.generate.run') }}" method="POST">
      @csrf
      <div class="card" style="padding:24px;display:flex;flex-direction:column;gap:18px;">

        <div>
          <label class="field-label">Tema / Título *</label>
          <input type="text" name="titulo" id="g-titulo" class="field-input" list="temas-list" autocomplete="off"
                 value="{{ old('titulo') }}" required
                 placeholder="Comece a digitar ou escolha uma sugestão...">
          <datalist id="temas-list">
            @foreach($temas as $t)
              <option value="{{ $t['t'] }}"></option>
            @endforeach
          </datalist>
          <span style="font-size:11px;color:var(--fg-4);">Há {{ count($temas) }} temas sugeridos do seu plano de SEO. Escolher um preenche a palavra-chave e a categoria.</span>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div>
            <label class="field-label">Palavra-chave principal *</label>
            <input type="text" name="palavra_chave" id="g-kw" class="field-input"
                   value="{{ old('palavra_chave') }}" required placeholder="ex: pregão eletrônico">
          </div>
          <div>
            <label class="field-label">Categoria</label>
            <select name="blog_category_id" id="g-cat" class="field-input">
              <option value="">— escolher —</option>
              @foreach($categories as $c)
                <option value="{{ $c->id }}" data-slug="{{ $c->slug }}" {{ (string)old('blog_category_id')===(string)$c->id ? 'selected' : '' }}>{{ $c->name }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div>
          <label class="field-label">Palavras-chave secundárias <span style="color:var(--fg-4);">(opcional, separadas por vírgula)</span></label>
          <input type="text" name="palavras_secundarias" class="field-input" value="{{ old('palavras_secundarias') }}"
                 placeholder="cauda longa: ex: lei 14133 resumo, etp e termo de referência">
        </div>

        <div style="display:grid;grid-template-columns:200px 1fr;gap:14px;align-items:end;">
          <div>
            <label class="field-label">Tamanho</label>
            <select name="tamanho" class="field-input">
              <option value="1500" {{ old('tamanho')=='1500'?'selected':'' }}>Curto (~1500)</option>
              <option value="1800" {{ old('tamanho','1800')=='1800'?'selected':'' }}>Médio (~1800)</option>
              <option value="2500" {{ old('tamanho')=='2500'?'selected':'' }}>Longo (~2500)</option>
              <option value="3000" {{ old('tamanho')=='3000'?'selected':'' }}>Pilar (~3000)</option>
            </select>
          </div>
          <div>
            <label class="field-label">Instruções extras <span style="color:var(--fg-4);">(opcional)</span></label>
            <input type="text" name="instrucoes" class="field-input" value="{{ old('instrucoes') }}"
                   placeholder="ex: focar em municípios pequenos; citar prazos">
          </div>
        </div>

        <button type="submit" class="btn btn-primary" style="justify-content:center;">✨ Gerar artigo</button>
        <p style="font-size:11.5px;color:var(--fg-4);margin:0;text-align:center;">A geração leva ~1 minuto. O artigo aparece como rascunho na lista de posts.</p>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const TEMAS = @json($temas);
  const titulo = document.getElementById('g-titulo');
  const kw = document.getElementById('g-kw');
  const cat = document.getElementById('g-cat');

  titulo.addEventListener('input', function(){
    const found = TEMAS.find(t => t.t === titulo.value);
    if (found) {
      if (!kw.value) kw.value = found.k;
      else kw.value = found.k;
      // seleciona categoria pelo slug
      for (const opt of cat.options) {
        if (opt.getAttribute('data-slug') === found.c) { cat.value = opt.value; break; }
      }
    }
  });
</script>
@endpush
