{{--
  Partial: pages/admin/partials/video-row-exist.blade.php
  Campos para um VideoLesson JÁ EXISTENTE (com $video->id no name)
--}}
<div style="display:flex;flex-direction:column;gap:12px;">

  <div style="display:grid;grid-template-columns:1fr 140px;gap:12px;">
    <div>
      <label class="field-label">Título</label>
      <input type="text" name="videos[{{ $video->id }}][titulo]" class="field-input"
             value="{{ old("videos.{$video->id}.titulo", $video->titulo) }}"
             placeholder="Título da cápsula">
    </div>
    <div>
      <label class="field-label">Origem</label>
      <select name="videos[{{ $video->id }}][source]" class="field-input">
        <option value="youtube" {{ old("videos.{$video->id}.source", $video->source) === 'youtube' ? 'selected' : '' }}>YouTube</option>
        <option value="vimeo"   {{ old("videos.{$video->id}.source", $video->source) === 'vimeo'   ? 'selected' : '' }}>Vimeo</option>
      </select>
    </div>
  </div>

  <div>
    <label class="field-label">
      Link do vídeo
      @if($video->link)
        <a href="{{ $video->link }}" target="_blank" style="color:var(--brand-300);font-size:10px;text-decoration:none;margin-left:6px;">↗ Testar</a>
      @endif
    </label>
    <input type="text" name="videos[{{ $video->id }}][link]" class="field-input"
           value="{{ old("videos.{$video->id}.link", $video->link) }}"
           placeholder="https://www.youtube.com/embed/...">
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
    <div>
      <label class="field-label">Link degustação</label>
      <input type="text" name="videos[{{ $video->id }}][tasting_link]" class="field-input"
             value="{{ old("videos.{$video->id}.tasting_link", $video->tasting_link) }}"
             placeholder="Link para não-alunos">
    </div>
    <div>
      <label class="field-label">Link backup</label>
      <input type="text" name="videos[{{ $video->id }}][bkp_link]" class="field-input"
             value="{{ old("videos.{$video->id}.bkp_link", $video->bkp_link) }}"
             placeholder="Link reserva">
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 140px;gap:12px;">
    <div>
      <label class="field-label">Legenda / subtítulo</label>
      <input type="text" name="videos[{{ $video->id }}][subtitle]" class="field-input"
             value="{{ old("videos.{$video->id}.subtitle", $video->subtitle) }}"
             placeholder="Descrição curta">
    </div>
    <div>
      <label class="field-label">Status</label>
      <select name="videos[{{ $video->id }}][status]" class="field-input">
        <option value="able"     {{ old("videos.{$video->id}.status", $video->status) === 'able'     ? 'selected' : '' }}>✓ Ativo</option>
        <option value="disabled" {{ old("videos.{$video->id}.status", $video->status) === 'disabled' ? 'selected' : '' }}>✗ Inativo</option>
      </select>
    </div>
  </div>

  @if($video->link)
    <div style="border:1px solid var(--line-2);border-radius:10px;overflow:hidden;aspect-ratio:16/9;background:#000;">
      <iframe src="{{ $video->link }}" style="width:100%;height:100%;border:none;" allowfullscreen allow="autoplay;encrypted-media"></iframe>
    </div>
  @endif

</div>
