@extends('layouts.admin')
@section('title', 'Editar Temporada — ' . $panel->title)
@section('section', 'Cursos')

@section('content')
<div class="page">

  {{-- Breadcrumb --}}
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:12px;color:var(--fg-4);">
    <a href="{{ route('admin.cursos') }}" style="color:var(--fg-4);text-decoration:none;" onmouseover="this.style.color='var(--brand-300)'" onmouseout="this.style.color='var(--fg-4)'">Cursos</a>
    <span>/</span>
    <a href="{{ route('admin.cursos.show', $panel->classes_id) }}" style="color:var(--fg-4);text-decoration:none;" onmouseover="this.style.color='var(--brand-300)'" onmouseout="this.style.color='var(--fg-4)'">
      {{ optional($panel->classes)->title ?? 'Minissérie' }}
    </a>
    <span>/</span>
    <a href="{{ route('admin.cursos.edit', $panel->classes_id) }}" style="color:var(--fg-4);text-decoration:none;" onmouseover="this.style.color='var(--brand-300)'" onmouseout="this.style.color='var(--fg-4)'">Editar</a>
    <span>/</span>
    <span style="color:var(--fg-2);">Temporada</span>
  </div>

  {{-- Flash --}}
  @if(session('success'))
    <div style="padding:12px 16px;background:rgba(43,217,161,0.10);border:1px solid rgba(43,217,161,0.35);border-radius:var(--r-md);color:#6FE6BD;font-size:13px;font-weight:500;margin-bottom:20px;">
      ✓ {{ session('success') }}
    </div>
  @endif

  <form action="{{ route('admin.panels.update', $panel->id) }}" method="POST" id="panel-form">
    @csrf
    @method('PUT')

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">

      {{-- ══ Dados do painel ═══════════════════════════════════════════ --}}
      <div class="card" style="padding:0;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
          <h2 style="font-family:var(--font-display);font-weight:700;font-size:17px;color:#fff;margin:0;">
            Dados da Temporada
          </h2>
        </div>
        <div style="padding:20px;display:flex;flex-direction:column;gap:16px;">

          <div>
            <label class="field-label">Título *</label>
            <input type="text" name="title" class="field-input" value="{{ old('title', $panel->title) }}" required>
          </div>

          <div>
            <label class="field-label">Subtítulo</label>
            <input type="text" name="subtitle" class="field-input" value="{{ old('subtitle', $panel->subtitle) }}">
          </div>

          <div>
            <label class="field-label">Conteúdo / Resumo</label>
            <textarea name="content" class="field-input" rows="5" style="resize:vertical;">{{ old('content', $panel->content) }}</textarea>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div>
              <label class="field-label">Data de início</label>
              <input type="date" name="start_time" class="field-input" value="{{ old('start_time', $panel->start_time) }}">
            </div>
            <div>
              <label class="field-label">Horário</label>
              <input type="time" name="horario" class="field-input" value="{{ old('horario', $panel->horario) }}">
            </div>
          </div>

          <div>
            <label class="field-label">Status</label>
            <select name="status" class="field-input">
              <option value="able"     {{ old('status', $panel->status) === 'able'     ? 'selected' : '' }}>Ativo</option>
              <option value="disabled" {{ old('status', $panel->status) === 'disabled' ? 'selected' : '' }}>Inativo</option>
            </select>
          </div>

        </div>
      </div>

      {{-- ══ Preview de materiais ════════════════════════════════════ --}}
      <div class="card" style="padding:0;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
          <h2 style="font-family:var(--font-display);font-weight:700;font-size:17px;color:#fff;margin:0;">
            Materiais de apoio
          </h2>
        </div>
        <div style="padding:16px 20px;">
          @if($panel->material->isEmpty())
            <p style="color:var(--fg-4);font-size:13px;text-align:center;padding:16px 0;">
              Nenhum material vinculado.
            </p>
          @else
            <div style="display:flex;flex-direction:column;gap:8px;">
              @foreach($panel->material as $mat)
                @php $ic = ['PDF'=>'📄','PODCAST'=>'🎧'][$mat->type] ?? '📁'; @endphp
                <div style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:var(--bg-1);border:1px solid var(--line-1);border-radius:10px;">
                  <span style="font-size:18px;">{{ $ic }}</span>
                  <div style="flex:1;min-width:0;">
                    <div style="font-size:13px;color:var(--fg-1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                      {{ $mat->name ?? $mat->file_name }}
                    </div>
                    <div style="font-size:10px;color:var(--fg-4);text-transform:uppercase;letter-spacing:0.1em;">
                      {{ $mat->type }}
                    </div>
                  </div>
                  <a href="https://unygov.com.br/storage/materials/{{ $mat->file_name }}"
                     target="_blank" class="btn btn-sm" style="font-size:11px;padding:4px 10px;text-decoration:none;">
                    Abrir
                  </a>
                </div>
              @endforeach
            </div>
          @endif
        </div>
      </div>

    </div>

    {{-- ══ Vídeos ═══════════════════════════════════════════════════════════ --}}
    <div class="card" style="padding:0;margin-top:20px;">
      <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);display:flex;align-items:center;justify-content:space-between;">
        <h2 style="font-family:var(--font-display);font-weight:700;font-size:17px;color:#fff;margin:0;">
          Cápsulas de vídeo
          <span style="font-size:13px;font-weight:400;color:var(--fg-4);margin-left:8px;">{{ $panel->video_lesson->count() }} vídeos</span>
        </h2>
        <span style="font-size:12px;color:var(--fg-4);">Editável inline — salva junto com a temporada</span>
      </div>

      @forelse($panel->video_lesson as $video)
        @php $vNum = $loop->iteration; @endphp
        <div style="border-bottom:1px solid var(--line-1);padding:18px 20px;" id="video-row-{{ $video->id }}">

          <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
            {{-- Número --}}
            <div style="width:28px;height:28px;border-radius:8px;background:rgba(0,163,255,0.10);border:1px solid rgba(0,163,255,0.2);display:flex;align-items:center;justify-content:center;font-family:var(--font-mono);font-size:10px;color:var(--brand-300);flex-shrink:0;">
              {{ $vNum }}
            </div>
            <div style="font-size:13px;font-weight:600;color:var(--fg-2);">
              {{ $video->titulo ?: 'Vídeo #' . $video->id }}
            </div>
            {{-- Status badge --}}
            <span class="badge {{ $video->status === 'able' ? 'success' : 'neutral' }}" style="margin-left:auto;">
              {{ $video->status === 'able' ? 'Ativo' : 'Inativo' }}
            </span>
            {{-- Toggle para expandir --}}
            <button type="button"
                    onclick="toggleVideo({{ $video->id }})"
                    class="btn btn-sm"
                    style="font-size:11px;padding:4px 10px;">
              Expandir
            </button>
          </div>

          {{-- Campos do vídeo (colapsáveis) --}}
          <div id="video-fields-{{ $video->id }}" style="display:none;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">

              <div>
                <label class="field-label">Título</label>
                <input type="text" name="videos[{{ $video->id }}][titulo]"
                       class="field-input" value="{{ old('videos.'.$video->id.'.titulo', $video->titulo) }}"
                       placeholder="Título da cápsula">
              </div>

              <div>
                <label class="field-label">Status</label>
                <select name="videos[{{ $video->id }}][status]" class="field-input">
                  <option value="able"     {{ old('videos.'.$video->id.'.status', $video->status) === 'able'     ? 'selected' : '' }}>Ativo</option>
                  <option value="disabled" {{ old('videos.'.$video->id.'.status', $video->status) === 'disabled' ? 'selected' : '' }}>Inativo</option>
                </select>
              </div>

              <div style="grid-column:1/-1;">
                <label class="field-label">
                  Link do vídeo
                  @if($video->link)
                    <a href="{{ $video->link }}" target="_blank"
                       style="color:var(--brand-300);font-size:10px;text-decoration:none;margin-left:6px;">
                      ↗ Testar link
                    </a>
                  @endif
                </label>
                <input type="text" name="videos[{{ $video->id }}][link]"
                       class="field-input" value="{{ old('videos.'.$video->id.'.link', $video->link) }}"
                       placeholder="https://www.youtube.com/embed/...">
              </div>

              <div style="grid-column:1/-1;">
                <label class="field-label">Link de degustação (tasting)</label>
                <input type="text" name="videos[{{ $video->id }}][tasting_link]"
                       class="field-input" value="{{ old('videos.'.$video->id.'.tasting_link', $video->tasting_link) }}"
                       placeholder="Link público para não-alunos">
              </div>

              <div style="grid-column:1/-1;">
                <label class="field-label">Legenda / Subtítulo</label>
                <textarea name="videos[{{ $video->id }}][subtitle]"
                          class="field-input" rows="2" style="resize:vertical;">{{ old('videos.'.$video->id.'.subtitle', $video->subtitle) }}</textarea>
              </div>

            </div>

            {{-- Preview do iframe --}}
            @if($video->link)
              <div style="border:1px solid var(--line-2);border-radius:10px;overflow:hidden;aspect-ratio:16/9;background:#000;margin-bottom:8px;">
                <iframe src="{{ $video->link }}" style="width:100%;height:100%;border:none;"
                        allowfullscreen allow="autoplay; encrypted-media"></iframe>
              </div>
            @endif
          </div>

        </div>
      @empty
        <div style="padding:48px;text-align:center;color:var(--fg-4);font-size:13px;">
          Nenhum vídeo cadastrado nesta temporada.
        </div>
      @endforelse
    </div>

    {{-- Ações --}}
    <div style="display:flex;gap:10px;margin-top:20px;">
      <button type="submit" class="btn btn-primary">
        <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24">
          <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
        </svg>
        Salvar temporada e vídeos
      </button>
      <a href="{{ route('admin.cursos.edit', $panel->classes_id) }}" class="btn btn-ghost" style="text-decoration:none;">
        ← Voltar para a minissérie
      </a>
    </div>

  </form>
</div>
@endsection

@push('styles')
<style>
.field-label {
  display:block;font-size:11px;font-weight:700;letter-spacing:0.1em;
  text-transform:uppercase;color:var(--fg-3);margin-bottom:6px;
}
.field-input {
  width:100%;padding:10px 14px;background:var(--bg-2);border:1px solid var(--line-2);
  border-radius:var(--r-sm);color:var(--fg-2);font-family:var(--font-body);font-size:14px;
  outline:none;transition:border-color .2s,box-shadow .2s;box-sizing:border-box;
}
.field-input:focus { border-color:var(--brand-500);box-shadow:0 0 0 3px rgba(0,163,255,.18); }
textarea.field-input { line-height:1.5; }
</style>
@endpush

@push('scripts')
<script>
function toggleVideo(id) {
  const el  = document.getElementById('video-fields-' + id);
  const btn = el.previousElementSibling.querySelector('button[onclick]');
  if (el.style.display === 'none') {
    el.style.display = 'block';
    btn.textContent  = 'Recolher';
  } else {
    el.style.display = 'none';
    btn.textContent  = 'Expandir';
  }
}

// Expande automaticamente se houver erro de validação
@if($errors->any())
  document.querySelectorAll('[id^="video-fields-"]').forEach(el => {
    el.style.display = 'block';
  });
@endif
</script>
@endpush
