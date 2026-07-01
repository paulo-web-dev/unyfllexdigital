@extends('layouts.admin')
@section('title', $post ? 'Editar Post' : 'Novo Post')
@section('section', 'Instagram')

@section('content')
@include('admin.social._field-styles')
<div class="page">

  {{-- Breadcrumb --}}
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:12px;color:var(--fg-4);">
    <a href="{{ route('admin.social.posts.index') }}" style="color:var(--fg-4);text-decoration:none;">Instagram</a>
    <span>/</span>
    <span style="color:var(--fg-2);">{{ $post ? 'Editar post' : 'Novo post' }}</span>
  </div>

  @if(session('success'))
    <div style="padding:12px 16px;background:rgba(43,217,161,0.10);border:1px solid rgba(43,217,161,0.35);border-radius:var(--r-md);color:#6FE6BD;font-size:13px;font-weight:500;margin-bottom:20px;">✓ {{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div style="padding:12px 16px;background:rgba(255,92,122,0.10);border:1px solid rgba(255,92,122,0.35);border-radius:var(--r-md);color:#FF5C7A;font-size:13px;margin-bottom:20px;"><strong>Corrija os erros:</strong><ul style="margin:8px 0 0;padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  @if(!$account)
    <div style="padding:12px 16px;background:rgba(255,181,71,0.10);border:1px solid rgba(255,181,71,0.35);border-radius:var(--r-md);color:#FFB547;font-size:13px;margin-bottom:20px;">
      Nenhuma conta do Instagram configurada. Vá em <a href="{{ route('admin.social.accounts.index') }}" style="color:#FFB547;font-weight:600;">Conta</a> e cadastre a conta + token antes de agendar.
    </div>
  @endif

  <form action="{{ $post ? route('admin.social.posts.update', $post) : route('admin.social.posts.store') }}"
        method="POST" enctype="multipart/form-data" id="post-form">
    @csrf
    @if($post) @method('PUT') @endif
    <input type="hidden" name="social_account_id" value="{{ old('social_account_id', $post->social_account_id ?? ($account->id ?? '')) }}">

    <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;">

      {{-- Coluna principal --}}
      <div style="display:flex;flex-direction:column;gap:20px;">

        <div class="card" style="padding:20px;display:flex;flex-direction:column;gap:16px;">
          <h2 style="font-family:var(--font-display);font-weight:700;font-size:15px;color:#fff;margin:0;">Conteúdo</h2>

          <div>
            <label class="field-label">Legenda</label>
            <textarea name="caption" rows="7" class="field-input" placeholder="Escreva a legenda do post...">{{ old('caption', $post->caption ?? '') }}</textarea>
          </div>

          <div>
            <label class="field-label">1º comentário (hashtags)</label>
            <textarea name="first_comment" rows="3" class="field-input" placeholder="#licitacoes #lei14133 #gestaopublica">{{ old('first_comment', $post->first_comment ?? '') }}</textarea>
            <span style="font-size:11px;color:var(--fg-4);">Opcional. As hashtags podem ir no 1º comentário para deixar a legenda limpa.</span>
          </div>
        </div>

        {{-- Mídia --}}
        <div class="card" style="padding:20px;display:flex;flex-direction:column;gap:14px;">
          <h2 style="font-family:var(--font-display);font-weight:700;font-size:15px;color:#fff;margin:0;">Mídia</h2>

          @if($post && $post->media->count())
            <div style="display:flex;flex-wrap:wrap;gap:10px;">
              @foreach($post->media as $m)
                <div style="position:relative;width:110px;">
                  <img src="{{ $m->url() }}" style="width:110px;height:110px;object-fit:cover;border-radius:var(--r-sm);border:1px solid var(--line-2);">
                  <button type="submit" form="del-media-{{ $m->id }}" onclick="return confirm('Remover esta mídia?')"
                          style="position:absolute;top:4px;right:4px;background:rgba(0,0,0,.65);color:#FF5C7A;border:none;border-radius:50%;width:22px;height:22px;cursor:pointer;font-size:11px;line-height:1;">✕</button>
                </div>
              @endforeach
            </div>
          @endif

          <div>
            <label class="field-label">Adicionar imagem(ns)</label>
            <input type="file" name="media[]" accept="image/*" multiple class="field-input" onchange="previewNew(this)">
            <span style="font-size:11px;color:var(--fg-4);">JPG ou PNG até 8MB. Feed: 1:1 ou 4:5. Para carrossel, selecione várias.</span>
            <div id="new-preview" style="display:flex;flex-wrap:wrap;gap:10px;margin-top:10px;"></div>
          </div>
        </div>
      </div>

      {{-- Coluna lateral --}}
      <div style="display:flex;flex-direction:column;gap:20px;">

        <div class="card" style="padding:20px;display:flex;flex-direction:column;gap:14px;">
          <h2 style="font-family:var(--font-display);font-weight:700;font-size:15px;color:#fff;margin:0;">Publicação</h2>

          <div>
            <label class="field-label">Tipo</label>
            <select name="type" class="field-input">
              @foreach(\App\Models\SocialPost::TYPES as $val => $lbl)
                <option value="{{ $val }}" @selected(old('type', $post->type ?? 'feed_image') === $val)>{{ $lbl }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="field-label">Status</label>
            <select name="status" class="field-input">
              @foreach(['rascunho' => 'Rascunho', 'aprovado' => 'Aprovado', 'agendado' => 'Agendado'] as $val => $lbl)
                <option value="{{ $val }}" @selected(old('status', $post->status ?? 'rascunho') === $val)>{{ $lbl }}</option>
              @endforeach
            </select>
            <span style="font-size:11px;color:var(--fg-4);">"Agendado" precisa de data/hora abaixo.</span>
          </div>

          <div>
            <label class="field-label">Agendar para</label>
            <input type="datetime-local" name="scheduled_for" class="field-input"
                   value="{{ old('scheduled_for', ($post && $post->scheduled_for) ? $post->scheduled_for->format('Y-m-d\TH:i') : '') }}">
            <span style="font-size:11px;color:var(--fg-4);">Horário de Brasília.</span>
          </div>

          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
            {{ $post ? 'Salvar alterações' : 'Criar post' }}
          </button>

          @if($post)
            {{-- Botão ligado ao form isolado abaixo (evita <form> aninhado) --}}
            <button type="submit" form="social-delete-form" onclick="return confirm('Excluir este post? Esta ação não pode ser desfeita.')"
                    class="btn btn-ghost" style="width:100%;justify-content:center;color:#FF5C7A;border-color:rgba(255,92,122,0.35);">Excluir post</button>
          @endif
        </div>

        @if($post && $post->status === 'publicado' && $post->permalink)
        <div class="card" style="padding:18px;">
          <a href="{{ $post->permalink }}" target="_blank" rel="noopener" style="color:var(--brand-500);font-size:13px;text-decoration:none;">Ver no Instagram ↗</a>
        </div>
        @endif
      </div>
    </div>
  </form>

  {{-- Forms isolados, FORA do form de edição (nunca aninhar <form>) --}}
  @if($post)
    <form id="social-delete-form" action="{{ route('admin.social.posts.destroy', $post) }}" method="POST" style="display:none;">@csrf @method('DELETE')</form>
    @foreach($post->media as $m)
      <form id="del-media-{{ $m->id }}" action="{{ route('admin.social.posts.media.destroy', [$post, $m]) }}" method="POST" style="display:none;">@csrf @method('DELETE')</form>
    @endforeach
  @endif

</div>

@push('scripts')
<script>
  function previewNew(input) {
    var box = document.getElementById('new-preview');
    box.innerHTML = '';
    Array.prototype.forEach.call(input.files, function (f) {
      var img = document.createElement('img');
      img.src = URL.createObjectURL(f);
      img.style.cssText = 'width:90px;height:90px;object-fit:cover;border-radius:10px;border:1px solid var(--line-2);';
      box.appendChild(img);
    });
  }
</script>
@endpush
@endsection
