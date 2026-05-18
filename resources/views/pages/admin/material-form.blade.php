@extends('layouts.admin')
@section('title', isset($material) ? 'Editar Material' : 'Novo Material')
@section('section', 'Cursos')

@section('content')
<div class="page">

  {{-- Breadcrumb --}}
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:12px;color:var(--fg-4);">
    <a href="{{ route('admin.materiais') }}" style="color:var(--fg-4);text-decoration:none;" onmouseover="this.style.color='var(--brand-300)'" onmouseout="this.style.color='var(--fg-4)'">Materiais</a>
    <span>/</span>
    <span style="color:var(--fg-2);">{{ isset($material) ? 'Editar' : 'Novo material' }}</span>
  </div>

  @if(session('success'))
    <div style="padding:12px 16px;background:rgba(43,217,161,0.10);border:1px solid rgba(43,217,161,0.35);border-radius:var(--r-md);color:#6FE6BD;font-size:13px;font-weight:500;margin-bottom:20px;">
      ✓ {{ session('success') }}
    </div>
  @endif

  <form
    action="{{ isset($material) ? route('admin.materiais.update', $material->id) : route('admin.materiais.store') }}"
    method="POST">
    @csrf
    @if(isset($material)) @method('PUT') @endif

    <div style="display:grid;grid-template-columns:1fr 380px;gap:20px;align-items:start;">

      {{-- ══ Dados do material ═════════════════════════════════════════ --}}
      <div class="card" style="padding:0;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
          <h2 style="font-family:var(--font-display);font-weight:700;font-size:17px;color:#fff;margin:0;">
            {{ isset($material) ? 'Editar material' : 'Novo material' }}
          </h2>
        </div>
        <div style="padding:20px;display:flex;flex-direction:column;gap:18px;">

          <div>
            <label class="field-label">Nome do material *</label>
            <input type="text" name="name" class="field-input"
                   value="{{ old('name', $material->name ?? '') }}"
                   placeholder="Ex: Mapa Mental - Patrimônio Público" required>
            @error('name')<span class="field-error">{{ $message }}</span>@enderror
          </div>

          <div>
            <label class="field-label">
              Nome do arquivo *
              @if(isset($material) && $material->file_name)
                <a href="https://unygov.com.br/storage/materials/{{ $material->file_name }}"
                   target="_blank"
                   style="color:var(--brand-300);font-size:10px;text-decoration:none;margin-left:8px;">
                  ↗ Abrir arquivo atual
                </a>
              @endif
            </label>
            <input type="text" name="file_name" id="input-file" class="field-input"
                   value="{{ old('file_name', $material->file_name ?? '') }}"
                   placeholder="nome-do-arquivo.pdf"
                   oninput="previewLink()" required>
            <p style="font-size:11px;color:var(--fg-4);margin-top:4px;">
              Arquivo em <code>unygov.com.br/storage/materials/</code>
            </p>
            <div id="link-preview" style="margin-top:8px;display:none;">
              <a id="link-preview-a" href="#" target="_blank"
                 style="font-size:12px;color:var(--brand-300);word-break:break-all;"></a>
            </div>
            @error('file_name')<span class="field-error">{{ $message }}</span>@enderror
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div>
              <label class="field-label">Tipo *</label>
              <select name="type" class="field-input">
                <option value="PDF"     {{ old('type', $material->type ?? '') === 'PDF'     ? 'selected' : '' }}>📄 PDF / Mapa mental</option>
                <option value="PODCAST" {{ old('type', $material->type ?? '') === 'PODCAST' ? 'selected' : '' }}>🎧 Podcast / Áudio</option>
              </select>
            </div>
            <div>
              <label class="field-label">Status</label>
              <select name="status" class="field-input">
                <option value="able"     {{ old('status', $material->status ?? 'able') === 'able'     ? 'selected' : '' }}>✓ Ativo</option>
                <option value="disabled" {{ old('status', $material->status ?? 'able') === 'disabled' ? 'selected' : '' }}>✗ Inativo</option>
              </select>
            </div>
          </div>

        </div>
      </div>

      {{-- ══ Sidebar — publicar + ações ══════════════════════════════ --}}
      <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card" style="padding:16px 18px;">
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-bottom:10px;">
            <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24">
              <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
              <polyline points="17 21 17 13 7 13 7 21"/>
              <polyline points="7 3 7 8 15 8"/>
            </svg>
            {{ isset($material) ? 'Salvar alterações' : 'Criar material' }}
          </button>
          <a href="{{ route('admin.materiais') }}" class="btn btn-ghost" style="width:100%;justify-content:center;text-decoration:none;display:flex;">
            Cancelar
          </a>
        </div>
      </div>

    </div>

    {{-- ══ Vincular aos panels ════════════════════════════════════════════ --}}
    <div class="card" style="padding:0;margin-top:20px;">
      <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);display:flex;align-items:center;justify-content:space-between;">
        <div>
          <h2 style="font-family:var(--font-display);font-weight:700;font-size:17px;color:#fff;margin:0;">
            Vincular às temporadas
          </h2>
          <p style="font-size:12px;color:var(--fg-4);margin:4px 0 0;">
            Selecione em quais temporadas este material deve aparecer
          </p>
        </div>
        <div style="display:flex;gap:8px;">
          <button type="button" onclick="selecionarTodos()" class="btn btn-sm" style="font-size:11px;">Todos</button>
          <button type="button" onclick="deselecionarTodos()" class="btn btn-sm" style="font-size:11px;">Nenhum</button>
        </div>
      </div>

      {{-- Busca local --}}
      <div style="padding:10px 16px;border-bottom:1px solid var(--line-1);">
        <div class="search-mini">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:14px;height:14px;"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>
          <input type="search" id="busca-panel" placeholder="Filtrar temporadas…" oninput="filtrarPanels()">
        </div>
      </div>

      @php
        // Agrupa panels por minissérie
        $panelsPorClasse = $panels->groupBy(fn ($p) => optional($p->classes)->title ?? 'Sem minissérie');
      @endphp

      <div style="max-height:480px;overflow-y:auto;" id="panels-lista">
        @foreach($panelsPorClasse as $classeTitle => $panelGroup)
          <div class="panel-group">
            {{-- Cabeçalho da minissérie --}}
            <div style="padding:10px 20px 6px;background:rgba(255,255,255,0.02);border-bottom:1px solid var(--line-1);display:flex;align-items:center;justify-content:space-between;">
              <span style="font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--brand-300);">
                {{ $classeTitle }}
              </span>
              <span style="font-size:11px;color:var(--fg-4);">{{ $panelGroup->count() }} temporadas</span>
            </div>

            @foreach($panelGroup as $panel)
              @php
                $vinculado = isset($panelsVinculados) && $panelsVinculados->contains($panel->id);
                $checked   = old('panels') ? in_array($panel->id, old('panels', [])) : $vinculado;
              @endphp
              <label class="panel-item {{ $checked ? 'checked' : '' }}"
                     data-titulo="{{ strtolower($panel->title) }}"
                     style="display:flex;align-items:center;gap:12px;padding:12px 20px;cursor:pointer;border-bottom:1px solid var(--line-1);transition:background .15s;{{ $checked ? 'background:rgba(0,163,255,0.06);' : '' }}">
                <input type="checkbox" name="panels[]" value="{{ $panel->id }}"
                       {{ $checked ? 'checked' : '' }}
                       style="width:16px;height:16px;accent-color:var(--brand-500);cursor:pointer;flex-shrink:0;"
                       onchange="this.closest('label').classList.toggle('checked', this.checked);
                                 this.closest('label').style.background = this.checked ? 'rgba(0,163,255,0.06)' : '';">
                <div style="flex:1;min-width:0;">
                  <div style="font-size:13px;font-weight:500;color:var(--fg-1);">{{ $panel->title }}</div>
                  @if($panel->subtitle)
                    <div style="font-size:11px;color:var(--fg-4);">{{ $panel->subtitle }}</div>
                  @endif
                </div>
                @if($checked)
                  <span class="badge success" style="flex-shrink:0;">Vinculado</span>
                @endif
              </label>
            @endforeach
          </div>
        @endforeach

        @if($panels->isEmpty())
          <div style="padding:48px;text-align:center;color:var(--fg-4);font-size:13px;">
            Nenhuma temporada disponível.
          </div>
        @endif
      </div>

    </div>

    {{-- Botão salvar repetido no final da página --}}
    <div style="display:flex;gap:10px;margin-top:20px;">
      <button type="submit" class="btn btn-primary">
        <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24">
          <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
          <polyline points="17 21 17 13 7 13 7 21"/>
          <polyline points="7 3 7 8 15 8"/>
        </svg>
        {{ isset($material) ? 'Salvar alterações' : 'Criar material' }}
      </button>
      <a href="{{ route('admin.materiais') }}" class="btn btn-ghost" style="text-decoration:none;">Cancelar</a>
    </div>

  </form>
</div>
@endsection

@push('styles')
<style>
.field-label { display:block;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--fg-3);margin-bottom:6px; }
.field-input { width:100%;padding:10px 14px;background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-sm);color:var(--fg-2);font-family:var(--font-body);font-size:14px;outline:none;transition:border-color .2s,box-shadow .2s;box-sizing:border-box; }
.field-input:focus { border-color:var(--brand-500);box-shadow:0 0 0 3px rgba(0,163,255,.18); }
.field-error { font-size:11px;color:var(--danger);margin-top:4px;display:block; }
.panel-item:hover { background:rgba(255,255,255,0.03) !important; }
</style>
@endpush

@push('scripts')
<script>
function previewLink() {
  const val   = document.getElementById('input-file').value.trim();
  const wrap  = document.getElementById('link-preview');
  const a     = document.getElementById('link-preview-a');
  if (val) {
    const url   = 'https://unygov.com.br/storage/materials/' + val;
    a.href      = url;
    a.textContent = url;
    wrap.style.display = 'block';
  } else {
    wrap.style.display = 'none';
  }
}

function filtrarPanels() {
  const q = document.getElementById('busca-panel').value.toLowerCase().trim();
  document.querySelectorAll('.panel-item').forEach(el => {
    const titulo = el.dataset.titulo ?? '';
    el.style.display = (!q || titulo.includes(q)) ? '' : 'none';
  });
  // Oculta grupo inteiro se todos os itens estão ocultos
  document.querySelectorAll('.panel-group').forEach(g => {
    const visíveis = [...g.querySelectorAll('.panel-item')].some(el => el.style.display !== 'none');
    g.style.display = visíveis ? '' : 'none';
  });
}

function selecionarTodos() {
  document.querySelectorAll('input[name="panels[]"]').forEach(cb => {
    cb.checked = true;
    cb.closest('label').style.background = 'rgba(0,163,255,0.06)';
  });
}

function deselecionarTodos() {
  document.querySelectorAll('input[name="panels[]"]').forEach(cb => {
    cb.checked = false;
    cb.closest('label').style.background = '';
  });
}

// Preview do arquivo existente ao carregar
document.addEventListener('DOMContentLoaded', () => {
  const f = document.getElementById('input-file');
  if (f && f.value) previewLink();
});
</script>
@endpush
