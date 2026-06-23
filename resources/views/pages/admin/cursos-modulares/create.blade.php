@extends('layouts.admin')
@section('title', 'Novo Curso Modular')
@section('section', 'Cursos Modulares')

@section('content')
<div class="page">

  {{-- Breadcrumb --}}
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:12px;color:var(--fg-4);">
    <a href="{{ route('admin.cursos-modulares') }}" style="color:var(--fg-4);text-decoration:none;" onmouseover="this.style.color='var(--brand-300)'" onmouseout="this.style.color='var(--fg-4)'">Cursos Modulares</a>
    <span>/</span>
    <span style="color:var(--fg-2);">Novo curso</span>
  </div>

  @if($errors->any())
    <div style="padding:12px 16px;background:rgba(255,90,90,0.08);border:1px solid rgba(255,90,90,0.30);border-radius:var(--r-md);color:#ff9a9a;font-size:13px;margin-bottom:20px;">
      <strong>Confira os campos:</strong>
      <ul style="margin:6px 0 0;padding-left:18px;">
        @foreach($errors->all() as $erro)
          <li>{{ $erro }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('admin.cursos-modulares.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div style="display:grid;grid-template-columns:1fr 380px;gap:20px;align-items:start;">

      {{-- ══ Dados do curso ════════════════════════════════════════════ --}}
      <div class="card" style="padding:0;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
          <h2 style="font-family:var(--font-display);font-weight:700;font-size:17px;color:#fff;margin:0;">Novo curso modular</h2>
          <p style="font-size:12px;color:var(--fg-4);margin:4px 0 0;">A apostila em PDF é a base do curso. As próximas etapas (resumos, podcast, materiais e prova) virão depois.</p>
        </div>
        <div style="padding:20px;display:flex;flex-direction:column;gap:18px;">

          <div>
            <label class="field-label">Título do curso *</label>
            <input type="text" name="title" class="field-input"
                   value="{{ old('title') }}"
                   placeholder="Ex: Contratações Públicas — Lei 14.133/2021" required>
            @error('title')<span class="field-error">{{ $message }}</span>@enderror
          </div>

          <div>
            <label class="field-label">Descrição</label>
            <textarea name="description" class="field-input" rows="4"
                      placeholder="Resumo curto do curso (opcional). Pode ser preenchido automaticamente a partir do PDF mais pra frente.">{{ old('description') }}</textarea>
            @error('description')<span class="field-error">{{ $message }}</span>@enderror
          </div>

          {{-- ══ Upload da apostila ══════════════════════════════════ --}}
          <div>
            <label class="field-label">Apostila (PDF)</label>

            <label id="dropzone" for="apostila"
                   style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;padding:32px 20px;border:2px dashed var(--line-2);border-radius:14px;background:var(--bg-2);cursor:pointer;text-align:center;transition:border-color .2s,background .2s;">
              <div style="width:48px;height:48px;border-radius:12px;background:rgba(0,163,255,0.10);border:1px solid rgba(0,163,255,0.20);display:flex;align-items:center;justify-content:center;">
                <svg viewBox="0 0 24 24" fill="none" stroke="var(--brand-300)" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" style="width:22px;height:22px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              </div>
              <div>
                <div id="dz-title" style="font-size:14px;font-weight:600;color:var(--fg-1);">Arraste o PDF aqui ou clique para selecionar</div>
                <div id="dz-sub" style="font-size:11px;color:var(--fg-4);margin-top:2px;">PDF até 64 MB</div>
              </div>
            </label>

            <input type="file" name="apostila" id="apostila" accept="application/pdf" style="display:none;">
            @error('apostila')<span class="field-error">{{ $message }}</span>@enderror

            <p style="font-size:11px;color:var(--fg-4);margin-top:8px;">
              O arquivo fica guardado em <code>public/storage/cursos-modulares/apostilas</code> e acessível por link público — é assim que a IA consegue lê-lo na hora de gerar os roteiros.
            </p>
          </div>

        </div>
      </div>

      {{-- ══ Sidebar — status + ações ══════════════════════════════ --}}
      <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card" style="padding:16px 18px;">
          <label class="field-label">Status inicial</label>
          <select name="status" class="field-input" style="margin-bottom:16px;">
            <option value="rascunho"    {{ old('status', 'rascunho') === 'rascunho'    ? 'selected' : '' }}>Rascunho</option>
            <option value="processando" {{ old('status') === 'processando' ? 'selected' : '' }}>Processando</option>
            <option value="publicado"   {{ old('status') === 'publicado'   ? 'selected' : '' }}>Publicado</option>
          </select>

          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-bottom:10px;">
            <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Criar curso modular
          </button>
          <a href="{{ route('admin.cursos-modulares') }}" class="btn btn-ghost" style="width:100%;justify-content:center;text-decoration:none;display:flex;">Cancelar</a>
        </div>

        <div class="card" style="padding:16px 18px;">
          <div style="font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--fg-4);margin-bottom:8px;">Próximos passos</div>
          <p style="font-size:12px;color:var(--fg-3);margin:0;line-height:1.6;">
            Depois de criar o curso e enviar a apostila, conectamos o fluxo (Claude API + n8n) para gerar automaticamente o resumo de cada módulo, o roteiro do podcast, os materiais complementares e a prova.
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
#dropzone.dragover { border-color:var(--brand-500);background:rgba(0,163,255,0.06); }
#dropzone.has-file { border-color:rgba(43,217,161,0.45);background:rgba(43,217,161,0.05); }
</style>
@endpush

@push('scripts')
<script>
(function () {
  const input = document.getElementById('apostila');
  const dz    = document.getElementById('dropzone');
  const title = document.getElementById('dz-title');
  const sub   = document.getElementById('dz-sub');

  function human(bytes) {
    if (bytes >= 1048576) return (bytes / 1048576).toFixed(1).replace('.', ',') + ' MB';
    return Math.round(bytes / 1024) + ' KB';
  }

  function paint() {
    const f = input.files && input.files[0];
    if (f) {
      dz.classList.add('has-file');
      title.textContent = f.name;
      sub.textContent   = human(f.size) + ' · clique para trocar';
    } else {
      dz.classList.remove('has-file');
      title.textContent = 'Arraste o PDF aqui ou clique para selecionar';
      sub.textContent   = 'PDF até 64 MB';
    }
  }

  input.addEventListener('change', paint);

  ['dragenter', 'dragover'].forEach(ev =>
    dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.add('dragover'); }));
  ['dragleave', 'drop'].forEach(ev =>
    dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.remove('dragover'); }));

  dz.addEventListener('drop', e => {
    if (e.dataTransfer.files && e.dataTransfer.files.length) {
      input.files = e.dataTransfer.files;
      paint();
    }
  });
})();
</script>
@endpush
