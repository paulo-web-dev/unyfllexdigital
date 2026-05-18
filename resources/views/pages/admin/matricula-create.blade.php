@extends('layouts.admin')
@section('title', 'Nova Matrícula')
@section('section', 'Matrículas')

@section('content')
<div class="page">

  <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:12px;color:var(--fg-4);">
    <a href="{{ route('admin.matriculas') }}" style="color:var(--fg-4);text-decoration:none;" onmouseover="this.style.color='var(--brand-300)'" onmouseout="this.style.color='var(--fg-4)'">Matrículas</a>
    <span>/</span>
    <span style="color:var(--fg-2);">Nova matrícula</span>
  </div>

  @if(session('success'))
    <div style="padding:12px 16px;background:rgba(43,217,161,0.10);border:1px solid rgba(43,217,161,0.35);border-radius:var(--r-md);color:#6FE6BD;font-size:13px;font-weight:500;margin-bottom:20px;">
      ✓ {{ session('success') }}
    </div>
  @endif

  @if($errors->any())
    <div style="padding:12px 16px;background:rgba(255,92,122,0.10);border:1px solid rgba(255,92,122,0.35);border-radius:var(--r-md);color:#FF5C7A;font-size:13px;margin-bottom:20px;">
      <strong>Corrija os erros:</strong>
      <ul style="margin:6px 0 0 16px;padding:0;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form action="{{ route('admin.matriculas.store') }}" method="POST">
    @csrf

    <div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start;">

      {{-- ══ Coluna principal ══════════════════════════════════════════ --}}
      <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Seleção do aluno --}}
        <div class="card" style="padding:0;">
          <div style="padding:14px 20px;border-bottom:1px solid var(--line-2);">
            <h2 style="font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;margin:0;">Aluno</h2>
          </div>
          <div style="padding:20px;">

            {{-- Campo hidden que guarda o ID --}}
            <input type="hidden" name="student_id" id="student_id" value="{{ old('student_id', $student?->id) }}">

            {{-- Busca com autocomplete --}}
            <label class="field-label">Buscar aluno por nome, e-mail ou CPF</label>
            <div style="position:relative;">
              <div class="search-mini" style="padding:0;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:14px;height:14px;flex-shrink:0;"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>
                <input type="search" id="busca-aluno"
                       class="field-input" style="border:none;background:transparent;padding:0;"
                       placeholder="Digite para buscar…"
                       value="{{ $student?->name }}"
                       autocomplete="off">
              </div>
              <div id="busca-dropdown"
                   style="display:none;position:absolute;top:calc(100% + 4px);left:0;right:0;background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-lg);z-index:9999;max-height:280px;overflow-y:auto;box-shadow:0 16px 32px rgba(0,0,0,0.4);">
              </div>
            </div>

            {{-- Card do aluno selecionado --}}
            <div id="aluno-selecionado" style="{{ $student ? '' : 'display:none;' }}margin-top:14px;">
              <div style="display:flex;align-items:center;gap:12px;padding:14px;background:rgba(43,217,161,0.06);border:1px solid rgba(43,217,161,0.25);border-radius:12px;">
                <div style="width:40px;height:40px;border-radius:50%;background:var(--grad-brand);color:#061224;font-weight:700;font-size:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;" id="aluno-avatar">
                  {{ $student ? strtoupper(substr($student->name,0,1)) : '' }}
                </div>
                <div style="flex:1;min-width:0;">
                  <div id="aluno-nome" style="font-size:14px;font-weight:600;color:#fff;">{{ $student?->name }}</div>
                  <div id="aluno-email" style="font-size:12px;color:var(--fg-4);">{{ $student?->email }}</div>
                  <div id="aluno-meta" style="font-size:11px;color:var(--fg-4);margin-top:2px;">
                    {{ $student ? ($student->cargo ?? '') . ($student->entidade ? ' · ' . $student->entidade : '') : '' }}
                  </div>
                </div>
                <button type="button" onclick="limparAluno()"
                        style="background:none;border:none;cursor:pointer;color:var(--fg-4);padding:4px;"
                        title="Remover">
                  <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                  </svg>
                </button>
              </div>
            </div>

            @error('student_id')
              <span class="field-error">{{ $message }}</span>
            @enderror

            <div style="margin-top:10px;">
              <a href="{{ route('admin.alunos.create') }}" target="_blank"
                 style="font-size:12px;color:var(--brand-300);text-decoration:none;">
                + Cadastrar novo aluno
              </a>
            </div>

          </div>
        </div>

        {{-- Seleção da minissérie --}}
        <div class="card" style="padding:0;">
          <div style="padding:14px 20px;border-bottom:1px solid var(--line-2);">
            <h2 style="font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;margin:0;">Minissérie</h2>
          </div>
          <div style="padding:20px;display:flex;flex-direction:column;gap:14px;">

            <div>
              <label class="field-label">Selecionar minissérie *</label>
              <div class="search-mini" style="margin-bottom:10px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:14px;height:14px;"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>
                <input type="search" id="filtro-classes" placeholder="Filtrar minisséries…" oninput="filtrarClasses()">
              </div>

              <div id="classes-lista" style="max-height:300px;overflow-y:auto;display:flex;flex-direction:column;gap:6px;">
                @foreach($classes as $classe)
                  <label class="classe-item"
                         data-titulo="{{ strtolower($classe->title) }}"
                         style="display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:10px;cursor:pointer;border:1px solid var(--line-2);background:var(--bg-2);transition:all .15s;">
                    <input type="radio" name="classes_id" value="{{ $classe->id }}"
                           {{ old('classes_id') == $classe->id ? 'checked' : '' }}
                           required
                           style="width:16px;height:16px;accent-color:var(--brand-500);cursor:pointer;flex-shrink:0;">
                    @if($classe->photo)
                      <img src="https://unyflex.com.br/storage/cursos/banner/{{ $classe->photo }}"
                           style="width:48px;height:32px;object-fit:cover;border-radius:6px;flex-shrink:0;">
                    @else
                      <div style="width:48px;height:32px;border-radius:6px;background:var(--bg-3);flex-shrink:0;"></div>
                    @endif
                    <div style="flex:1;min-width:0;">
                      <div style="font-size:13px;font-weight:500;color:var(--fg-1);">{{ $classe->title }}</div>
                      @if($classe->subtitle)
                        <div style="font-size:11px;color:var(--fg-4);">{{ $classe->subtitle }}</div>
                      @endif
                    </div>
                  </label>
                @endforeach
              </div>
              @error('classes_id')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
              <div>
                <label class="field-label">Modalidade</label>
                <select name="modality" class="field-input">
                  <option value="minisserie"        {{ old('modality','minisserie') === 'minisserie'        ? 'selected':'' }}>Minissérie (AVA)</option>
                  <option value="distance_learning"  {{ old('modality') === 'distance_learning'              ? 'selected':'' }}>EAD</option>
                  <option value="in_person"          {{ old('modality') === 'in_person'                      ? 'selected':'' }}>Presencial</option>
                  <option value="hybrid"             {{ old('modality') === 'hybrid'                         ? 'selected':'' }}>Híbrido</option>
                </select>
              </div>
              <div>
                <label class="field-label">Plano</label>
                <input type="text" name="plano" class="field-input" value="{{ old('plano') }}" placeholder="Ex: Anual, Mensal">
              </div>
            </div>

          </div>
        </div>

        {{-- Financeiro --}}
        <div class="card" style="padding:0;">
          <div style="padding:14px 20px;border-bottom:1px solid var(--line-2);">
            <h2 style="font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;margin:0;">Financeiro</h2>
          </div>
          <div style="padding:20px;display:flex;flex-direction:column;gap:16px;">

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;">
              <div>
                <label class="field-label">Valor (R$)</label>
                <input type="number" name="value" class="field-input"
                       value="{{ old('value', 0) }}" min="0" step="0.01"
                       oninput="calcFinal()">
              </div>
              <div>
                <label class="field-label">Desconto (R$)</label>
                <input type="number" name="discount" class="field-input"
                       value="{{ old('discount', 0) }}" min="0" step="0.01"
                       oninput="calcFinal()">
              </div>
              <div>
                <label class="field-label">Valor final (R$)</label>
                <input type="number" name="final_value" id="final_value" class="field-input"
                       value="{{ old('final_value', 0) }}" min="0" step="0.01">
              </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
              <div>
                <label class="field-label">Forma de pagamento</label>
                <select name="payment_method" class="field-input">
                  <option value="">— Selecionar —</option>
                  @foreach(['PIX','Cartão de crédito','Boleto','Transferência','Gratuito','Cortesia','Outros'] as $forma)
                    <option value="{{ $forma }}" {{ old('payment_method') === $forma ? 'selected' : '' }}>{{ $forma }}</option>
                  @endforeach
                </select>
              </div>
              <div>
                <label class="field-label">Código de transação</label>
                <input type="text" name="transaction_code" class="field-input"
                       value="{{ old('transaction_code') }}" placeholder="ID do gateway">
              </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;">
              <div>
                <label class="field-label">Início da vigência</label>
                <input type="date" name="start_date" class="field-input" value="{{ old('start_date', today()->format('Y-m-d')) }}">
              </div>
              <div>
                <label class="field-label">Fim da vigência</label>
                <input type="date" name="end_date" class="field-input" value="{{ old('end_date') }}">
              </div>
              <div>
                <label class="field-label">Data de pagamento</label>
                <input type="date" name="payday" class="field-input" value="{{ old('payday') }}">
              </div>
            </div>

          </div>
        </div>

        {{-- Empresa / extra --}}
        <div class="card" style="padding:0;">
          <div style="padding:14px 20px;border-bottom:1px solid var(--line-2);">
            <h2 style="font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;margin:0;">Empresa / Extra</h2>
          </div>
          <div style="padding:20px;display:flex;flex-direction:column;gap:16px;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
              <div>
                <label class="field-label">Empresa (company)</label>
                <input type="text" name="company" class="field-input" value="{{ old('company') }}" placeholder="Razão social ou nome">
              </div>
              <div>
                <label class="field-label">Entidade</label>
                <input type="text" name="entidade" class="field-input" value="{{ old('entidade') }}" placeholder="Prefeitura, órgão…">
              </div>
            </div>
            <div>
              <label class="field-label">Wallet / Responsável</label>
              <input type="text" name="wallet" class="field-input" value="{{ old('wallet') }}" placeholder="Responsável pela matrícula">
            </div>
          </div>
        </div>

      </div>

      {{-- ══ Sidebar ════════════════════════════════════════════════════ --}}
      <div style="display:flex;flex-direction:column;gap:14px;position:sticky;top:80px;">

        <div class="card" style="padding:0;">
          <div style="padding:14px 18px;border-bottom:1px solid var(--line-2);">
            <h3 style="font-family:var(--font-display);font-weight:700;font-size:15px;color:#fff;margin:0;">Status</h3>
          </div>
          <div style="padding:16px 18px;display:flex;flex-direction:column;gap:12px;">
            <div>
              <label class="field-label">Status da matrícula</label>
              <select name="status" class="field-input">
                <option value="checked"          {{ old('status','checked') === 'checked'          ? 'selected':'' }}>✓ Confirmada</option>
                <option value="not_checked"       {{ old('status') === 'not_checked'               ? 'selected':'' }}>⏳ Pendente</option>
                <option value="scheduled_billing" {{ old('status') === 'scheduled_billing'         ? 'selected':'' }}>📅 Agendada</option>
                <option value="canceled"          {{ old('status') === 'canceled'                  ? 'selected':'' }}>✗ Cancelada</option>
              </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
              <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24">
                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/>
              </svg>
              Criar matrícula
            </button>
            <a href="{{ route('admin.matriculas') }}" class="btn btn-ghost"
               style="text-decoration:none;width:100%;justify-content:center;display:flex;">
              Cancelar
            </a>
          </div>
        </div>

        <div style="padding:14px 16px;background:rgba(0,163,255,0.06);border:1px solid rgba(0,163,255,0.2);border-radius:var(--r-lg);">
          <div style="font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--brand-300);margin-bottom:6px;">Dica</div>
          <p style="font-size:12px;color:var(--fg-3);margin:0;line-height:1.6;">
            Com status <strong style="color:#fff;">Confirmada</strong>, o aluno já terá acesso imediato à minissérie no AVA.
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
.classe-item:hover { background:rgba(0,163,255,0.06)!important;border-color:rgba(0,163,255,0.35)!important; }
.classe-item:has(input:checked) { background:rgba(0,163,255,0.10)!important;border-color:rgba(0,163,255,0.5)!important; }
</style>
@endpush

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
let   timer = null;

// ── Busca de aluno ─────────────────────────────────────────────────────
const inputBusca  = document.getElementById('busca-aluno');
const dropdown    = document.getElementById('busca-dropdown');
const hiddenId    = document.getElementById('student_id');
const cardAluno   = document.getElementById('aluno-selecionado');

inputBusca.addEventListener('input', function () {
  clearTimeout(timer);
  const q = this.value.trim();
  if (q.length < 2) { dropdown.style.display = 'none'; return; }
  timer = setTimeout(() => buscarAluno(q), 280);
});

async function buscarAluno(q) {
  try {
    const res  = await fetch(`{{ route('admin.alunos.busca') }}?q=${encodeURIComponent(q)}`, {
      headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    });
    const data = await res.json();
    renderDropdown(data, q);
  } catch(e) {}
}

function hl(text, q) {
  if (!text) return '';
  const esc = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  return String(text).replace(new RegExp(esc, 'gi'), m => `<mark style="background:rgba(0,163,255,0.25);color:var(--brand-200);border-radius:2px;padding:0 2px;">${m}</mark>`);
}

function renderDropdown(data, q) {
  if (!data.length) {
    dropdown.innerHTML = `<div style="padding:16px;text-align:center;color:var(--fg-4);font-size:13px;">Nenhum aluno encontrado.<br><a href="{{ route('admin.alunos.create') }}" target="_blank" style="color:var(--brand-300);text-decoration:none;font-size:12px;">+ Cadastrar novo</a></div>`;
    dropdown.style.display = 'block';
    return;
  }
  dropdown.innerHTML = data.map(a => {
    const ini = (a.name ?? '').charAt(0).toUpperCase();
    return `
      <div onclick="selecionarAluno(${a.id}, ${JSON.stringify(a.name)}, ${JSON.stringify(a.email)}, ${JSON.stringify((a.cargo??'') + (a.entidade ? ' · '+a.entidade : ''))})"
           style="display:flex;align-items:center;gap:10px;padding:10px 16px;cursor:pointer;border-bottom:1px solid var(--line-1);transition:background .15s;"
           onmouseover="this.style.background='rgba(0,163,255,0.07)'"
           onmouseout="this.style.background=''">
        <div style="width:32px;height:32px;border-radius:50%;background:var(--grad-brand);color:#061224;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">${ini}</div>
        <div style="flex:1;min-width:0;">
          <div style="font-size:13px;font-weight:500;color:var(--fg-1);">${hl(a.name, q)}</div>
          <div style="font-size:11px;color:var(--fg-4);">${hl(a.email, q)}${a.cpf ? ' · '+hl(a.cpf,q) : ''}</div>
          ${a.cargo||a.entidade ? `<div style="font-size:11px;color:var(--fg-4);">${a.cargo??''} ${a.entidade ? '· '+a.entidade : ''}</div>` : ''}
        </div>
      </div>`;
  }).join('');
  dropdown.style.display = 'block';
}

function selecionarAluno(id, nome, email, meta) {
  hiddenId.value = id;
  inputBusca.value = nome;
  dropdown.style.display = 'none';
  document.getElementById('aluno-avatar').textContent = nome.charAt(0).toUpperCase();
  document.getElementById('aluno-nome').textContent   = nome;
  document.getElementById('aluno-email').textContent  = email;
  document.getElementById('aluno-meta').textContent   = meta;
  cardAluno.style.display = 'block';
}

function limparAluno() {
  hiddenId.value = '';
  inputBusca.value = '';
  cardAluno.style.display = 'none';
}

document.addEventListener('click', e => {
  if (!inputBusca.contains(e.target) && !dropdown.contains(e.target)) {
    dropdown.style.display = 'none';
  }
});

// ── Filtro de minisséries ──────────────────────────────────────────────
function filtrarClasses() {
  const q = document.getElementById('filtro-classes').value.toLowerCase().trim();
  document.querySelectorAll('.classe-item').forEach(el => {
    el.style.display = (!q || (el.dataset.titulo??'').includes(q)) ? '' : 'none';
  });
}

// ── Calcula valor final automaticamente ───────────────────────────────
function calcFinal() {
  const v = parseFloat(document.querySelector('[name="value"]').value) || 0;
  const d = parseFloat(document.querySelector('[name="discount"]').value) || 0;
  document.getElementById('final_value').value = Math.max(0, v - d).toFixed(2);
}
</script>
@endpush
