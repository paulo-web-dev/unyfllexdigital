@extends('layouts.admin')
@section('title', 'Nova Minissérie')
@section('section', 'Cursos')

@section('content')
<div class="page">

  {{-- Breadcrumb --}}
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:12px;color:var(--fg-4);">
    <a href="{{ route('admin.cursos') }}" style="color:var(--fg-4);text-decoration:none;"
       onmouseover="this.style.color='var(--brand-300)'" onmouseout="this.style.color='var(--fg-4)'">Cursos</a>
    <span>/</span>
    <span style="color:var(--fg-2);">Nova minissérie</span>
  </div>

  @if($errors->any())
    <div style="padding:12px 16px;background:rgba(255,92,122,0.10);border:1px solid rgba(255,92,122,0.35);border-radius:var(--r-md);color:#FF5C7A;font-size:13px;margin-bottom:20px;">
      <strong>Corrija os erros abaixo:</strong>
      <ul style="margin:6px 0 0 16px;padding:0;">
        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('admin.cursos.store') }}" method="POST">
    @csrf

    <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;">

      {{-- ══ Coluna principal ══════════════════════════════════════════ --}}
      <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Identificação --}}
        <div class="card" style="padding:0;">
          <div style="padding:14px 20px;border-bottom:1px solid var(--line-2);">
            <h2 style="font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;margin:0;">Identificação</h2>
          </div>
          <div style="padding:20px;display:flex;flex-direction:column;gap:16px;">

            <div>
              <label class="field-label">Título *</label>
              <input type="text" name="title" id="input-title" class="field-input"
                     value="{{ old('title') }}" required
                     placeholder="Ex: Patrimônio e Frotas Públicas com I.A."
                     oninput="gerarSlug()">
              @error('title')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div>
              <label class="field-label">Subtítulo</label>
              <input type="text" name="subtitle" class="field-input"
                     value="{{ old('subtitle') }}"
                     placeholder="Frase curta exibida nos cards">
            </div>

            <div>
              <label class="field-label">Slug (URL) *</label>
              <div style="display:flex;align-items:center;">
                <span style="padding:10px 12px;background:var(--bg-3);border:1px solid var(--line-2);border-right:none;border-radius:var(--r-sm) 0 0 var(--r-sm);font-size:11px;color:var(--fg-4);white-space:nowrap;">/dashboard/player/</span>
                <input type="text" name="slug" id="input-slug" class="field-input"
                       value="{{ old('slug') }}"
                       style="border-radius:0 var(--r-sm) var(--r-sm) 0;" required>
              </div>
              @error('slug')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div>
              <label class="field-label">Informações adicionais (info)</label>
              <input type="text" name="info" class="field-input"
                     value="{{ old('info') }}"
                     placeholder="Texto livre de apoio">
            </div>

          </div>
        </div>

        {{-- Configurações --}}
        <div class="card" style="padding:0;">
          <div style="padding:14px 20px;border-bottom:1px solid var(--line-2);">
            <h2 style="font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;margin:0;">Configurações</h2>
          </div>
          <div style="padding:20px;display:flex;flex-direction:column;gap:16px;">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
              <div>
                <label class="field-label">Carga horária</label>
                <input type="text" name="workload" class="field-input"
                       value="{{ old('workload') }}"
                       placeholder="Ex: 10h 30min">
              </div>
              <div>
                <label class="field-label">Valor (R$)</label>
                <input type="number" name="valor" class="field-input"
                       value="{{ old('valor', 0) }}"
                       min="0" placeholder="0">
              </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
              <div>
                <label class="field-label">Data de início</label>
                <input type="date" name="start_date" class="field-input" value="{{ old('start_date') }}">
              </div>
              <div>
                <label class="field-label">Data de término</label>
                <input type="date" name="end_date" class="field-input" value="{{ old('end_date') }}">
              </div>
            </div>

            <div>
              <label class="field-label">Polo</label>
              <input type="text" name="polo" class="field-input"
                     value="{{ old('polo') }}"
                     placeholder="Nome do polo ou região">
            </div>

          </div>
        </div>

        {{-- Capa --}}
        <div class="card" style="padding:0;">
          <div style="padding:14px 20px;border-bottom:1px solid var(--line-2);">
            <h2 style="font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;margin:0;">Imagem de capa</h2>
          </div>
          <div style="padding:20px;">
            <label class="field-label">Nome do arquivo</label>
            <input type="text" name="photo" id="input-photo" class="field-input"
                   value="{{ old('photo') }}"
                   placeholder="nome-do-arquivo.png"
                   oninput="previewFoto()">
            <p style="font-size:11px;color:var(--fg-4);margin-top:4px;">
              Arquivo deve estar em <code>unyflex.com.br/storage/cursos/banner/</code>
            </p>
            <div id="foto-preview" style="display:none;margin-top:12px;">
              <img id="foto-img" src=""
                   style="width:100%;max-height:140px;object-fit:cover;border-radius:10px;border:1px solid var(--line-2);">
            </div>
          </div>
        </div>

      </div>

      {{-- ══ Sidebar ════════════════════════════════════════════════════ --}}
      <div style="display:flex;flex-direction:column;gap:14px;">

        {{-- Publicar --}}
        <div class="card" style="padding:0;">
          <div style="padding:14px 18px;border-bottom:1px solid var(--line-2);">
            <h3 style="font-family:var(--font-display);font-weight:700;font-size:15px;color:#fff;margin:0;">Publicar</h3>
          </div>
          <div style="padding:16px 18px;display:flex;flex-direction:column;gap:12px;">
            <div>
              <label class="field-label">Status</label>
              <select name="status" class="field-input">
                <option value="able"     {{ old('status','able') === 'able'     ? 'selected' : '' }}>✓ Publicada</option>
                <option value="disabled" {{ old('status','able') === 'disabled' ? 'selected' : '' }}>✗ Desativada</option>
              </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
              <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                <polyline points="17 21 17 13 7 13 7 21"/>
                <polyline points="7 3 7 8 15 8"/>
              </svg>
              Criar minissérie
            </button>
            <a href="{{ route('admin.cursos') }}" class="btn btn-ghost" style="text-decoration:none;width:100%;justify-content:center;display:flex;">
              Cancelar
            </a>
          </div>
        </div>

        {{-- Flags / opções --}}
        <div class="card" style="padding:0;">
          <div style="padding:14px 18px;border-bottom:1px solid var(--line-2);">
            <h3 style="font-family:var(--font-display);font-weight:700;font-size:15px;color:#fff;margin:0;">Opções</h3>
          </div>
          <div style="padding:16px 18px;display:flex;flex-direction:column;gap:10px;">

            {{-- Express — FIXO 1 para minisséries, só informativo --}}
            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:rgba(43,217,161,0.06);border:1px solid rgba(43,217,161,0.25);border-radius:10px;">
              <div>
                <div style="font-size:12px;font-weight:600;color:#6FE6BD;">Express</div>
                <div style="font-size:11px;color:var(--fg-4);">Sempre ativo para minisséries</div>
              </div>
              <span class="badge success">Fixo = 1</span>
            </div>

            @foreach([
              ['live',      'Live',      'Aula ao vivo agendada'],
              ['novidade',  'Novidade',  'Destaque de lançamento'],
              ['incompany', 'In company','Turma fechada para empresa'],
            ] as [$field, $label, $desc])
              <label style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:var(--bg-2);border:1px solid var(--line-2);border-radius:10px;cursor:pointer;transition:border-color .15s;"
                     onmouseover="this.style.borderColor='rgba(0,163,255,0.35)'"
                     onmouseout="this.style.borderColor='var(--line-2)'">
                <div>
                  <div style="font-size:12px;font-weight:600;color:var(--fg-1);">{{ $label }}</div>
                  <div style="font-size:11px;color:var(--fg-4);">{{ $desc }}</div>
                </div>
                <input type="checkbox" name="{{ $field }}" value="1"
                       {{ old($field) ? 'checked' : '' }}
                       style="width:16px;height:16px;accent-color:var(--brand-500);cursor:pointer;">
              </label>
            @endforeach

          </div>
        </div>

        {{-- Dica --}}
        <div style="padding:14px 16px;background:rgba(0,163,255,0.06);border:1px solid rgba(0,163,255,0.2);border-radius:var(--r-lg);">
          <div style="font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--brand-300);margin-bottom:6px;">
            Próximo passo
          </div>
          <p style="font-size:12px;color:var(--fg-3);margin:0;line-height:1.6;">
            Após criar, acesse o detalhe da minissérie e clique em
            <strong style="color:#fff;">"Editar temporada"</strong> para adicionar painéis, vídeos e materiais.
          </p>
        </div>

      </div>
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
select.field-input { appearance:none;-webkit-appearance:none;cursor:pointer; }
</style>
@endpush

@push('scripts')
<script>
function gerarSlug() {
  const titulo = document.getElementById('input-title').value;
  const slug = titulo.toLowerCase()
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9\s-]/g, '').trim().replace(/\s+/g, '-');
  document.getElementById('input-slug').value = slug;
}

function previewFoto() {
  const val     = document.getElementById('input-photo').value.trim();
  const preview = document.getElementById('foto-preview');
  const img     = document.getElementById('foto-img');
  if (val) {
    img.src = 'https://unyflex.com.br/storage/cursos/banner/' + val;
    preview.style.display = 'block';
  } else {
    preview.style.display = 'none';
  }
}
</script>
@endpush
