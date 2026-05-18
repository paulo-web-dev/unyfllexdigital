@extends('layouts.admin')
@section('title', 'Nova Temporada — ' . $classe->title)
@section('section', 'Cursos')

@section('content')
<div class="page">

  {{-- Breadcrumb --}}
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:12px;color:var(--fg-4);">
    <a href="{{ route('admin.cursos') }}" style="color:var(--fg-4);text-decoration:none;" onmouseover="this.style.color='var(--brand-300)'" onmouseout="this.style.color='var(--fg-4)'">Cursos</a>
    <span>/</span>
    <a href="{{ route('admin.cursos.edit', $classe->id) }}" style="color:var(--fg-4);text-decoration:none;" onmouseover="this.style.color='var(--brand-300)'" onmouseout="this.style.color='var(--fg-4)'">{{ Str::limit($classe->title, 40) }}</a>
    <span>/</span>
    <span style="color:var(--fg-2);">Nova temporada</span>
  </div>

  @if($errors->any())
    <div style="padding:12px 16px;background:rgba(255,92,122,0.10);border:1px solid rgba(255,92,122,0.35);border-radius:var(--r-md);color:#FF5C7A;font-size:13px;margin-bottom:20px;">
      <strong>Corrija os erros abaixo:</strong>
      <ul style="margin:6px 0 0 16px;padding:0;">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('admin.panels.store', $classe->id) }}" method="POST">
    @csrf

    <div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start;">

      {{-- ══ Coluna principal ══════════════════════════════════════════ --}}
      <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Dados da temporada --}}
        <div class="card" style="padding:0;">
          <div style="padding:14px 20px;border-bottom:1px solid var(--line-2);">
            <h2 style="font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;margin:0;">
              Dados da temporada
            </h2>
          </div>
          <div style="padding:20px;display:flex;flex-direction:column;gap:16px;">

            <div>
              <label class="field-label">Título *</label>
              <input type="text" name="title" class="field-input"
                     value="{{ old('title') }}" required
                     placeholder="Ex: Levantamento de Infraestrutura e Recursos">
              @error('title')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div>
              <label class="field-label">Subtítulo</label>
              <input type="text" name="subtitle" class="field-input"
                     value="{{ old('subtitle') }}"
                     placeholder="Frase de apoio (opcional)">
            </div>

            <div>
              <label class="field-label">Conteúdo / Resumo</label>
              <textarea name="content" class="field-input" rows="4"
                        style="resize:vertical;"
                        placeholder="Descrição da temporada, tópicos abordados…">{{ old('content') }}</textarea>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;">
              <div>
                <label class="field-label">Data de início</label>
                <input type="date" name="start_time" class="field-input"
                       value="{{ old('start_time') }}">
              </div>
              <div>
                <label class="field-label">Horário</label>
                <input type="time" name="horario" class="field-input"
                       value="{{ old('horario') }}">
              </div>
              <div>
                <label class="field-label">Status</label>
                <select name="status" class="field-input">
                  <option value="able"     {{ old('status','able') === 'able'     ? 'selected' : '' }}>✓ Ativo</option>
                  <option value="disabled" {{ old('status','able') === 'disabled' ? 'selected' : '' }}>✗ Inativo</option>
                </select>
              </div>
            </div>

            {{-- Professor --}}
            @if($teachers->isNotEmpty())
              <div>
                <label class="field-label">Professor responsável</label>
                <select name="teacher_id" class="field-input">
                  <option value="">— Nenhum —</option>
                  @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                      {{ $teacher->name }}
                      @if($teacher->email) · {{ $teacher->email }}@endif
                    </option>
                  @endforeach
                </select>
              </div>
            @endif

          </div>
        </div>

        {{-- ── Vídeos ─────────────────────────────────────────────────── --}}
        <div class="card" style="padding:0;" id="videos-card">
          <div style="padding:14px 20px;border-bottom:1px solid var(--line-2);display:flex;align-items:center;justify-content:space-between;">
            <div>
              <h2 style="font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;margin:0;">
                Cápsulas de vídeo
              </h2>
              <p style="font-size:12px;color:var(--fg-4);margin:4px 0 0;">
                Adicione agora ou depois via "Editar temporada"
              </p>
            </div>
            <button type="button" onclick="adicionarVideo()"
                    class="btn btn-primary"
                    style="font-size:12px;padding:8px 14px;">
              <svg style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
              Adicionar vídeo
            </button>
          </div>

          {{-- Container de vídeos dinâmicos --}}
          <div id="videos-container" style="padding:0;">
            {{-- Linha vazia inicial --}}
            <div class="video-row" data-index="0"
                 style="padding:16px 20px;border-bottom:1px solid var(--line-1);">
              @include('pages.admin.partials.video-row', ['i' => 0, 'video' => null])
            </div>
          </div>

          <div id="videos-empty" style="display:none;padding:32px;text-align:center;color:var(--fg-4);font-size:13px;">
            Clique em "Adicionar vídeo" para incluir cápsulas.
          </div>
        </div>

      </div>

      {{-- ══ Sidebar ════════════════════════════════════════════════════ --}}
      <div style="display:flex;flex-direction:column;gap:14px;position:sticky;top:80px;">

        {{-- Info do curso --}}
        <div class="card" style="padding:14px 18px;">
          <div style="font-size:10px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--fg-4);margin-bottom:10px;">Minissérie</div>
          <div style="font-size:14px;font-weight:600;color:#fff;margin-bottom:4px;">{{ $classe->title }}</div>
          @if($classe->subtitle)
            <div style="font-size:12px;color:var(--fg-3);">{{ $classe->subtitle }}</div>
          @endif
          <div style="margin-top:10px;font-size:12px;color:var(--fg-4);">
            {{ $classe->panels->count() }} temporada(s) existente(s)
          </div>
        </div>

        {{-- Ações --}}
        <div class="card" style="padding:16px 18px;display:flex;flex-direction:column;gap:10px;">
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
            <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24">
              <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
              <polyline points="17 21 17 13 7 13 7 21"/>
              <polyline points="7 3 7 8 15 8"/>
            </svg>
            Criar temporada
          </button>
          <a href="{{ route('admin.cursos.edit', $classe->id) }}" class="btn btn-ghost"
             style="text-decoration:none;width:100%;justify-content:center;display:flex;">
            Cancelar
          </a>
        </div>

        {{-- Dica --}}
        <div style="padding:14px 16px;background:rgba(0,163,255,0.06);border:1px solid rgba(0,163,255,0.2);border-radius:var(--r-lg);">
          <div style="font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--brand-300);margin-bottom:6px;">Dica</div>
          <p style="font-size:12px;color:var(--fg-3);margin:0;line-height:1.6;">
            Você pode criar a temporada sem vídeos agora e adicioná-los depois via <strong style="color:#fff;">Editar temporada</strong>.
          </p>
        </div>

      </div>
    </div>

  </form>
</div>

{{-- Template hidden para clonar via JS --}}
<template id="video-row-template">
  <div class="video-row" data-index="__INDEX__"
       style="padding:16px 20px;border-bottom:1px solid var(--line-1);position:relative;">
    @include('pages.admin.partials.video-row', ['i' => '__INDEX__', 'video' => null])
  </div>
</template>
@endsection

@push('styles')
<style>
.field-label { display:block;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--fg-3);margin-bottom:6px; }
.field-input { width:100%;padding:10px 14px;background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-sm);color:var(--fg-2);font-family:var(--font-body);font-size:14px;outline:none;transition:border-color .2s,box-shadow .2s;box-sizing:border-box; }
.field-input:focus { border-color:var(--brand-500);box-shadow:0 0 0 3px rgba(0,163,255,.18); }
.field-error { font-size:11px;color:var(--danger);margin-top:4px;display:block; }
select.field-input { appearance:none;-webkit-appearance:none;cursor:pointer; }
textarea.field-input { line-height:1.5; }
.video-row:last-child { border-bottom:none; }
</style>
@endpush

@push('scripts')
<script>
let videoIndex = 1; // começa em 1 porque já temos o índice 0

function adicionarVideo() {
  const template  = document.getElementById('video-row-template');
  const container = document.getElementById('videos-container');

  // Clona o template, substitui __INDEX__ pelo índice real
  let html = template.innerHTML.replace(/__INDEX__/g, videoIndex);
  const wrapper = document.createElement('div');
  wrapper.innerHTML = html;
  const row = wrapper.firstElementChild;
  container.appendChild(row);

  videoIndex++;
  document.getElementById('videos-empty').style.display = 'none';
}

function removerVideo(btn) {
  const row       = btn.closest('.video-row');
  const container = document.getElementById('videos-container');
  row.remove();
  if (!container.querySelector('.video-row')) {
    document.getElementById('videos-empty').style.display = 'block';
  }
}
</script>
@endpush
