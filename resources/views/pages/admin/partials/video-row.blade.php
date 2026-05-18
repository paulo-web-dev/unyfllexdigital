{{--
  Partial: pages/admin/partials/video-row.blade.php
  Usado em: panel-create.blade.php e panel-edit.blade.php

  Vars:
    $i     — índice (int ou string '__INDEX__' para template JS)
    $video — VideoLesson|null
--}}
<div style="display:grid;grid-template-columns:28px 1fr;gap:10px;align-items:start;">

  {{-- Número --}}
  <div style="width:28px;height:28px;border-radius:8px;background:rgba(0,163,255,0.10);border:1px solid rgba(0,163,255,0.2);display:flex;align-items:center;justify-content:center;font-family:var(--font-mono);font-size:11px;color:var(--brand-300);flex-shrink:0;margin-top:2px;">
    {{ is_numeric($i) ? $i + 1 : '?' }}
  </div>

  <div style="display:flex;flex-direction:column;gap:12px;">

    <div style="display:grid;grid-template-columns:1fr auto;gap:12px;align-items:start;">
      {{-- Título --}}
      <div>
        <label class="field-label">Título do vídeo</label>
        <input type="text"
               name="videos[{{ $i }}][titulo]"
               class="field-input"
               value="{{ old("videos.{$i}.titulo", $video->titulo ?? '') }}"
               placeholder="Ex: Introdução ao Patrimônio Público">
      </div>
      {{-- Botão remover --}}
      <div style="padding-top:22px;">
        <button type="button" onclick="removerVideo(this)"
                title="Remover este vídeo"
                style="width:32px;height:36px;background:rgba(255,92,122,0.10);border:1px solid rgba(255,92,122,0.25);border-radius:var(--r-sm);color:#FF5C7A;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;"
                onmouseover="this.style.background='rgba(255,92,122,0.20)'"
                onmouseout="this.style.background='rgba(255,92,122,0.10)'">
          <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;">
            <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
          </svg>
        </button>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 140px;gap:12px;">
      {{-- Link principal --}}
      <div>
        <label class="field-label">
          Link do vídeo
          @if(isset($video) && $video->link)
            <a href="{{ $video->link }}" target="_blank" style="color:var(--brand-300);font-size:10px;text-decoration:none;margin-left:6px;">↗ Testar</a>
          @endif
        </label>
        <input type="text"
               name="videos[{{ $i }}][link]"
               class="field-input"
               value="{{ old("videos.{$i}.link", $video->link ?? '') }}"
               placeholder="https://www.youtube.com/embed/...">
      </div>
      {{-- Source --}}
      <div>
        <label class="field-label">Origem</label>
        <select name="videos[{{ $i }}][source]" class="field-input">
          <option value="youtube" {{ old("videos.{$i}.source", $video->source ?? 'youtube') === 'youtube' ? 'selected' : '' }}>YouTube</option>
          <option value="vimeo"   {{ old("videos.{$i}.source", $video->source ?? 'youtube') === 'vimeo'   ? 'selected' : '' }}>Vimeo</option>
        </select>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
      {{-- Tasting link --}}
      <div>
        <label class="field-label">Link degustação (público)</label>
        <input type="text"
               name="videos[{{ $i }}][tasting_link]"
               class="field-input"
               value="{{ old("videos.{$i}.tasting_link", $video->tasting_link ?? '') }}"
               placeholder="Link para não-alunos">
      </div>
      {{-- Backup link --}}
      <div>
        <label class="field-label">Link backup</label>
        <input type="text"
               name="videos[{{ $i }}][bkp_link]"
               class="field-input"
               value="{{ old("videos.{$i}.bkp_link", $video->bkp_link ?? '') }}"
               placeholder="Link reserva">
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 140px;gap:12px;align-items:end;">
      {{-- Legenda --}}
      <div>
        <label class="field-label">Legenda / subtítulo</label>
        <input type="text"
               name="videos[{{ $i }}][subtitle]"
               class="field-input"
               value="{{ old("videos.{$i}.subtitle", $video->subtitle ?? '') }}"
               placeholder="Descrição curta do vídeo">
      </div>
      {{-- Status --}}
      <div>
        <label class="field-label">Status</label>
        <select name="videos[{{ $i }}][status]" class="field-input">
          <option value="able"     {{ old("videos.{$i}.status", $video->status ?? 'able') === 'able'     ? 'selected' : '' }}>✓ Ativo</option>
          <option value="disabled" {{ old("videos.{$i}.status", $video->status ?? 'able') === 'disabled' ? 'selected' : '' }}>✗ Inativo</option>
        </select>
      </div>
    </div>

    {{-- Preview do iframe (só se tiver link) --}}
    @if(isset($video) && $video->link)
      <div style="border:1px solid var(--line-2);border-radius:10px;overflow:hidden;aspect-ratio:16/9;background:#000;">
        <iframe src="{{ $video->link }}" style="width:100%;height:100%;border:none;" allowfullscreen allow="autoplay;encrypted-media"></iframe>
      </div>
    @endif

  </div>
</div>
