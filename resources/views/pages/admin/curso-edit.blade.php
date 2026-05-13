@extends('layouts.admin')
@section('title', 'Editar — ' . $classe->title)
@section('section', 'Cursos')

@section('content')
<div class="page">

  {{-- Breadcrumb --}}
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:12px;color:var(--fg-4);">
    <a href="{{ route('admin.cursos') }}" style="color:var(--fg-4);text-decoration:none;" onmouseover="this.style.color='var(--brand-300)'" onmouseout="this.style.color='var(--fg-4)'">Cursos</a>
    <span>/</span>
    <a href="{{ route('admin.cursos.show', $classe->id) }}" style="color:var(--fg-4);text-decoration:none;" onmouseover="this.style.color='var(--brand-300)'" onmouseout="this.style.color='var(--fg-4)'">{{ Str::limit($classe->title, 40) }}</a>
    <span>/</span>
    <span style="color:var(--fg-2);">Editar</span>
  </div>

  {{-- Flash --}}
  @if(session('success'))
    <div style="padding:12px 16px;background:rgba(43,217,161,0.10);border:1px solid rgba(43,217,161,0.35);border-radius:var(--r-md);color:#6FE6BD;font-size:13px;font-weight:500;margin-bottom:20px;">
      ✓ {{ session('success') }}
    </div>
  @endif

  <div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;">

    {{-- ══ Formulário principal ══════════════════════════════════════════ --}}
    <form action="{{ route('admin.cursos.update', $classe->id) }}" method="POST">
      @csrf
      @method('PUT')

      <div class="card" style="padding:0;margin-bottom:16px;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
          <h2 style="font-family:var(--font-display);font-weight:700;font-size:17px;color:#fff;margin:0;">
            Dados da Minissérie
          </h2>
        </div>
        <div style="padding:20px;display:flex;flex-direction:column;gap:18px;">

          {{-- Título --}}
          <div>
            <label class="field-label">Título *</label>
            <input type="text" name="title" class="field-input" value="{{ old('title', $classe->title) }}" required>
            @error('title') <span class="field-error">{{ $message }}</span> @enderror
          </div>

          {{-- Subtítulo --}}
          <div>
            <label class="field-label">Subtítulo</label>
            <input type="text" name="subtitle" class="field-input" value="{{ old('subtitle', $classe->subtitle) }}" placeholder="Frase de apoio exibida nos cards">
          </div>

          {{-- Slug --}}
          <div>
            <label class="field-label">Slug (URL)</label>
            <div style="display:flex;align-items:center;gap:0;">
              <span style="padding:10px 12px;background:var(--bg-3);border:1px solid var(--line-2);border-right:none;border-radius:var(--r-sm) 0 0 var(--r-sm);font-size:12px;color:var(--fg-4);white-space:nowrap;">/dashboard/player/</span>
              <input type="text" name="slug" class="field-input" value="{{ old('slug', $classe->slug) }}" style="border-radius:0 var(--r-sm) var(--r-sm) 0;" required>
            </div>
            @error('slug') <span class="field-error">{{ $message }}</span> @enderror
          </div>

          {{-- Carga horária --}}
          <div>
            <label class="field-label">Carga horária</label>
            <input type="text" name="workload" class="field-input" value="{{ old('workload', $classe->workload) }}" placeholder="Ex: 10h 30min">
          </div>

          {{-- Datas --}}
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div>
              <label class="field-label">Data de início</label>
              <input type="date" name="start_date" class="field-input" value="{{ old('start_date', $classe->start_date) }}">
            </div>
            <div>
              <label class="field-label">Data de término</label>
              <input type="date" name="end_date" class="field-input" value="{{ old('end_date', $classe->end_date) }}">
            </div>
          </div>

          {{-- Status --}}
          <div>
            <label class="field-label">Status</label>
            <select name="status" class="field-input">
              <option value="able"     {{ old('status', $classe->status) === 'able'     ? 'selected' : '' }}>Publicada</option>
              <option value="disabled" {{ old('status', $classe->status) === 'disabled' ? 'selected' : '' }}>Desativada</option>
            </select>
          </div>

          {{-- Photo --}}
          <div>
            <label class="field-label">Nome do arquivo de capa (photo)</label>
            <input type="text" name="photo" class="field-input" value="{{ old('photo', $classe->photo) }}" placeholder="Ex: 696bbed47bf3a699c6a0d761a9fe8303.png">
            <p style="font-size:11px;color:var(--fg-4);margin-top:4px;">
              Arquivo deve estar em <code>storage/cursos/banner/</code>
            </p>
            @if($classe->photo)
              <img src="https://unyflex.com.br/storage/cursos/banner/{{ $classe->photo }}"
                   alt="preview" style="width:100%;max-height:120px;object-fit:cover;border-radius:10px;margin-top:8px;border:1px solid var(--line-2);">
            @endif
          </div>

        </div>
      </div>

      {{-- Ações --}}
      <div style="display:flex;gap:10px;">
        <button type="submit" class="btn btn-primary">
          <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
          </svg>
          Salvar minissérie
        </button>
        <a href="{{ route('admin.cursos.show', $classe->id) }}" class="btn btn-ghost" style="text-decoration:none;">Cancelar</a>
      </div>
    </form>

    {{-- ══ Sidebar: temporadas ══════════════════════════════════════════ --}}
    <div>
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
        <h3 style="font-family:var(--font-display);font-weight:700;font-size:15px;color:#fff;margin:0;">
          Temporadas
        </h3>
        <span style="font-size:12px;color:var(--fg-4);">{{ $classe->panels->count() }} painéis</span>
      </div>

      @forelse($classe->panels as $panel)
        <div class="card" style="padding:14px 16px;margin-bottom:10px;">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
            <div style="min-width:0;">
              <div style="font-size:10px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--brand-300);margin-bottom:2px;">
                Temporada {{ $loop->iteration }}
              </div>
              <div style="font-size:13px;font-weight:600;color:var(--fg-1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px;">
                {{ $panel->title }}
              </div>
              <div style="font-size:11px;color:var(--fg-4);margin-top:3px;">
                {{ $panel->video_lesson->count() }} vídeos
              </div>
            </div>
            <a href="{{ route('admin.panels.edit', $panel->id) }}"
               class="btn btn-sm" style="font-size:11px;padding:5px 12px;text-decoration:none;flex-shrink:0;">
              Editar
            </a>
          </div>
        </div>
      @empty
        <div class="card" style="padding:24px;text-align:center;color:var(--fg-4);font-size:13px;">
          Nenhuma temporada cadastrada.
        </div>
      @endforelse
    </div>

  </div>
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
.field-error { font-size:11px;color:var(--danger);margin-top:4px;display:block; }
</style>
@endpush
