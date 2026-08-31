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

  @if(session('success'))
    <div style="padding:12px 16px;background:rgba(43,217,161,0.10);border:1px solid rgba(43,217,161,0.35);border-radius:var(--r-md);color:#6FE6BD;font-size:13px;font-weight:500;margin-bottom:20px;">
      ✓ {{ session('success') }}
    </div>
  @endif

  @if($errors->any())
    <div style="padding:12px 16px;background:rgba(255,92,122,0.10);border:1px solid rgba(255,92,122,0.35);border-radius:var(--r-md);color:#FF5C7A;font-size:13px;margin-bottom:20px;">
      <strong>Corrija os erros:</strong>
      <ul style="margin:6px 0 0 16px;padding:0;">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('admin.panels.update', $panel->id) }}" method="POST" id="panel-form">
    @csrf
    @method('PUT')

    <div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start;">

      {{-- ══ Coluna principal ══════════════════════════════════════════ --}}
      <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Dados do painel --}}
        <div class="card" style="padding:0;">
          <div style="padding:14px 20px;border-bottom:1px solid var(--line-2);">
            <h2 style="font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;margin:0;">Dados da temporada</h2>
          </div>
          <div style="padding:20px;display:flex;flex-direction:column;gap:16px;">

            <div>
              <label class="field-label">Título *</label>
              <input type="text" name="title" class="field-input"
                     value="{{ old('title', $panel->title) }}" required>
              @error('title')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div>
              <label class="field-label">Subtítulo</label>
              <input type="text" name="subtitle" class="field-input"
                     value="{{ old('subtitle', $panel->subtitle) }}">
            </div>

            <div>
              <label class="field-label">Conteúdo / Resumo</label>
              <textarea name="content" class="field-input" rows="4" style="resize:vertical;">{{ old('content', $panel->content) }}</textarea>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;">
              <div>
                <label class="field-label">Data de início</label>
                <input type="date" name="start_time" class="field-input"
                       value="{{ old('start_time', $panel->start_time) }}">
              </div>
              <div>
                <label class="field-label">Horário</label>
                <input type="time" name="horario" class="field-input"
                       value="{{ old('horario', $panel->horario) }}">
              </div>
              <div>
                <label class="field-label">Status</label>
                <select name="status" class="field-input">
                  <option value="able"     {{ old('status', $panel->status) === 'able'     ? 'selected' : '' }}>✓ Ativo</option>
                  <option value="disabled" {{ old('status', $panel->status) === 'disabled' ? 'selected' : '' }}>✗ Inativo</option>
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
                    <option value="{{ $teacher->id }}"
                            {{ old('teacher_id', $panel->teacher_id) == $teacher->id ? 'selected' : '' }}>
                      {{ $teacher->name }}
                      @if($teacher->email) · {{ $teacher->email }}@endif
                    </option>
                  @endforeach
                </select>
              </div>
            @endif

          </div>
        </div>

        {{-- ── Vídeos existentes ─────────────────────────────────────── --}}
        <div class="card" style="padding:0;">
          <div style="padding:14px 20px;border-bottom:1px solid var(--line-2);display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;margin:0;">
              Cápsulas de vídeo
              <span style="font-size:13px;font-weight:400;color:var(--fg-4);margin-left:8px;">{{ $panel->video_lesson->count() }} cadastradas</span>
            </h2>
            <button type="button" onclick="adicionarVideo()"
                    class="btn btn-primary" style="font-size:12px;padding:8px 14px;">
              <svg style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
              Novo vídeo
            </button>
          </div>

          {{-- Vídeos existentes (com ID) --}}
          @forelse($panel->video_lesson as $video)
            <div class="video-row-existing" style="padding:16px 20px;border-bottom:1px solid var(--line-1);position:relative;">
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                <div style="width:28px;height:28px;border-radius:8px;background:rgba(0,163,255,0.10);border:1px solid rgba(0,163,255,0.2);display:flex;align-items:center;justify-content:center;font-family:var(--font-mono);font-size:11px;color:var(--brand-300);flex-shrink:0;">
                  {{ $loop->iteration }}
                </div>
                <div style="font-size:13px;font-weight:600;color:var(--fg-2);">
                  {{ $video->titulo ?: 'Vídeo #'.$video->id }}
                </div>
                <span class="badge {{ $video->status === 'able' ? 'success' : 'neutral' }}" style="margin-left:auto;">
                  {{ $video->status === 'able' ? 'Ativo' : 'Inativo' }}
                </span>
                <button type="button" onclick="toggleVideoExist({{ $video->id }})"
                        class="btn btn-sm" style="font-size:11px;padding:4px 10px;">
                  Expandir
                </button>
              </div>

              <div id="video-exist-{{ $video->id }}" style="display:none;">
                @include('pages.admin.partials.video-row-exist', ['video' => $video, 'loop' => $loop])
              </div>
            </div>
          @empty
            <div style="padding:28px;text-align:center;color:var(--fg-4);font-size:13px;border-bottom:1px solid var(--line-1);">
              Nenhum vídeo cadastrado.
            </div>
          @endforelse

          {{-- Novos vídeos (sem ID, adicionados via JS) --}}
          <div id="videos-new-container"></div>

        </div>

        {{-- Materiais vinculados --}}
        @if($panel->material->isNotEmpty())
          <div class="card" style="padding:0;">
            <div style="padding:14px 20px;border-bottom:1px solid var(--line-2);">
              <h2 style="font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;margin:0;">
                Materiais vinculados
              </h2>
            </div>
            <div style="padding:14px 20px;display:flex;flex-direction:column;gap:8px;">
              @foreach($panel->material as $mat)
                @php $ic = ['PDF'=>'📄','PODCAST'=>'🎧'][$mat->type] ?? '📁'; @endphp
                <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:var(--bg-1);border:1px solid var(--line-1);border-radius:10px;">
                  <span style="font-size:18px;">{{ $ic }}</span>
                  <div style="flex:1;min-width:0;">
                    <div style="font-size:13px;color:var(--fg-1);">{{ $mat->name ?? $mat->file_name }}</div>
                    <div style="font-size:10px;color:var(--fg-4);text-transform:uppercase;letter-spacing:0.1em;">{{ $mat->type }}</div>
                  </div>
                  <a href="{{ route('admin.materiais.edit', $mat->id) }}"
                     class="btn btn-sm" style="font-size:11px;padding:4px 10px;text-decoration:none;">
                    Editar
                  </a>
                </div>
              @endforeach
              <a href="{{ route('admin.materiais.create') }}"
                 style="display:inline-flex;align-items:center;gap:6px;font-size:12px;color:var(--brand-300);text-decoration:none;margin-top:4px;">
                <svg viewBox="0 0 24 24" style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2;"><path d="M12 5v14M5 12h14"/></svg>
                Adicionar novo material
              </a>
            </div>
          </div>
        @endif

      </div>

      {{-- ══ Sidebar ════════════════════════════════════════════════════ --}}
      <div style="display:flex;flex-direction:column;gap:14px;position:sticky;top:80px;">
        <div class="card" style="padding:16px 18px;display:flex;flex-direction:column;gap:10px;">
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
            <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24">
              <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
              <polyline points="17 21 17 13 7 13 7 21"/>
              <polyline points="7 3 7 8 15 8"/>
            </svg>
            Salvar temporada
          </button>
          <a href="{{ route('admin.cursos.edit', $panel->classes_id) }}" class="btn btn-ghost"
             style="text-decoration:none;width:100%;justify-content:center;display:flex;">
            ← Voltar
          </a>
        </div>

        <div class="card" style="padding:14px 18px;">
          <div style="font-size:10px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--fg-4);margin-bottom:8px;">Registro</div>
          <div style="font-size:12px;color:var(--fg-3);display:flex;flex-direction:column;gap:4px;">
            <div style="display:flex;justify-content:space-between;">
              <span style="color:var(--fg-4);">ID</span>
              <span style="font-family:var(--font-mono);">#{{ $panel->id }}</span>
            </div>
            @if($panel->log)
              <div style="display:flex;justify-content:space-between;">
                <span style="color:var(--fg-4);">Última edição</span>
                <span>{{ $panel->log }}</span>
              </div>
            @endif
          </div>
        </div>
      </div>

    </div>
  </form>

  {{-- ── Prova do painel (aba Prova no player do assinante) ─────────────── --}}
  @php
    // try/catch: a tabela panel_provas pode ainda não existir (database/panel_provas.sql).
    try { $provaPainel = \App\Models\PanelProva::where('panel_id', $panel->id)->latest('id')->first(); $provaTabelaOk = true; }
    catch (\Throwable $e) { $provaPainel = null; $provaTabelaOk = false; }
    $fonteProva = \App\Services\PanelProvaService::fonte($panel);
  @endphp
  <div class="card" style="padding:0;margin-top:16px;">
    <div style="padding:14px 20px;border-bottom:1px solid var(--line-2);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
      <h2 style="font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;margin:0;">
        Prova (área do assinante)
        @if($provaPainel)
          <span class="badge {{ $provaPainel->status === 'pronto' ? 'success' : 'neutral' }}" style="margin-left:8px;">
            {{ ['pronto' => '✓ Pronta', 'gerando' => '⏳ Gerando…', 'erro' => '✗ Erro'][$provaPainel->status] ?? $provaPainel->status }}
            @if($provaPainel->status === 'pronto') · {{ count($provaPainel->questoes()) }} questões @endif
          </span>
        @endif
      </h2>
      @if($provaTabelaOk && mb_strlen($fonteProva) >= \App\Services\PanelProvaService::MIN_FONTE)
        <form action="{{ route('admin.panels.prova.gerar', $panel->id) }}" method="POST"
              onsubmit="return confirm('{{ $provaPainel ? 'Regerar a prova? A atual será substituída.' : 'Gerar a prova deste painel via n8n?' }}');">
          @csrf
          <button type="submit" class="btn btn-primary" style="font-size:12px;padding:8px 14px;">
            {{ $provaPainel ? 'Regerar prova' : 'Gerar prova' }}
          </button>
        </form>
      @endif
    </div>
    <div style="padding:14px 20px;font-size:12.5px;color:var(--fg-4);">
      @if(! $provaTabelaOk)
        Tabela <code>panel_provas</code> ainda não criada — rode <code>database/panel_provas.sql</code> no banco.
      @elseif(mb_strlen($fonteProva) < \App\Services\PanelProvaService::MIN_FONTE)
        O campo "Conteúdo / Resumo" precisa de pelo menos {{ \App\Services\PanelProvaService::MIN_FONTE }} caracteres — é ele a fonte da prova (hoje: {{ mb_strlen($fonteProva) }}).
      @else
        A prova é gerada pela IA (n8n) a partir do "Conteúdo / Resumo" e aparece na aba Prova do player do assinante — 10 questões no mesmo formato dos cursos modulares.
      @endif
    </div>
  </div>
</div>

{{-- Template para novos vídeos --}}
<template id="video-new-template">
  <div class="video-row" data-index="new___INDEX__"
       style="padding:16px 20px;border-top:1px solid var(--line-1);">
    @include('pages.admin.partials.video-row', ['i' => 'new___INDEX__', 'video' => null])
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
</style>
@endpush

@push('scripts')
<script>
let newVideoIndex = 0;

function adicionarVideo() {
  const template  = document.getElementById('video-new-template');
  const container = document.getElementById('videos-new-container');
  const html = template.innerHTML.replace(/__INDEX__/g, newVideoIndex);
  const div  = document.createElement('div');
  div.innerHTML = html;
  container.appendChild(div.firstElementChild);
  newVideoIndex++;
}

function removerVideo(btn) {
  btn.closest('.video-row').remove();
}

function toggleVideoExist(id) {
  const el  = document.getElementById('video-exist-' + id);
  const btn = el.previousElementSibling.querySelector('button[onclick]');
  if (el.style.display === 'none') {
    el.style.display = 'block';
    btn.textContent  = 'Recolher';
  } else {
    el.style.display = 'none';
    btn.textContent  = 'Expandir';
  }
}

// Expande tudo se houver erros
@if($errors->any())
  document.querySelectorAll('[id^="video-exist-"]').forEach(el => el.style.display = 'block');
@endif
</script>
@endpush
