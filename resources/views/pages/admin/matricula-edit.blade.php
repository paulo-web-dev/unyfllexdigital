@extends('layouts.admin')
@section('title', 'Editar Matrícula #' . $enrollment->id)
@section('section', 'Matrículas')

@section('content')
<div class="page">

  <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:12px;color:var(--fg-4);">
    <a href="{{ route('admin.matriculas') }}" style="color:var(--fg-4);text-decoration:none;" onmouseover="this.style.color='var(--brand-300)'" onmouseout="this.style.color='var(--fg-4)'">Matrículas</a>
    <span>/</span>
    <span style="color:var(--fg-2);">Editar #{{ $enrollment->id }}</span>
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

  <form action="{{ route('admin.matriculas.update', $enrollment->id) }}" method="POST">
    @csrf @method('PUT')

    <div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start;">

      <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Aluno + Curso (somente leitura) --}}
        <div class="card" style="padding:0;">
          <div style="padding:14px 20px;border-bottom:1px solid var(--line-2);">
            <h2 style="font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;margin:0;">Aluno & Minissérie</h2>
          </div>
          <div style="padding:16px 20px;display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div style="padding:12px 14px;background:var(--bg-1);border:1px solid var(--line-1);border-radius:10px;">
              <div style="font-size:10px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--fg-4);margin-bottom:4px;">Aluno</div>
              <div style="font-size:14px;font-weight:600;color:#fff;">{{ optional($enrollment->student)->name ?? '—' }}</div>
              <div style="font-size:12px;color:var(--fg-4);">{{ optional($enrollment->student)->email ?? '' }}</div>
            </div>
            <div style="padding:12px 14px;background:var(--bg-1);border:1px solid var(--line-1);border-radius:10px;">
              <div style="font-size:10px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--fg-4);margin-bottom:4px;">Minissérie</div>
              <div style="font-size:14px;font-weight:600;color:#fff;">{{ optional($enrollment->classes)->title ?? '—' }}</div>
              <div style="font-size:12px;color:var(--fg-4);">ID #{{ $enrollment->classes_id }}</div>
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
                       value="{{ old('value', $enrollment->value) }}" min="0" step="0.01" oninput="calcFinal()">
              </div>
              <div>
                <label class="field-label">Desconto (R$)</label>
                <input type="number" name="discount" class="field-input"
                       value="{{ old('discount', $enrollment->discount) }}" min="0" step="0.01" oninput="calcFinal()">
              </div>
              <div>
                <label class="field-label">Valor final (R$)</label>
                <input type="number" name="final_value" id="final_value" class="field-input"
                       value="{{ old('final_value', $enrollment->final_value) }}" min="0" step="0.01">
              </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
              <div>
                <label class="field-label">Forma de pagamento</label>
                <select name="payment_method" class="field-input">
                  <option value="">— Selecionar —</option>
                  @foreach(['PIX','Cartão de crédito','Boleto','Transferência','Gratuito','Cortesia','Outros'] as $forma)
                    <option value="{{ $forma }}" {{ old('payment_method', $enrollment->payment_method) === $forma ? 'selected' : '' }}>{{ $forma }}</option>
                  @endforeach
                </select>
              </div>
              <div>
                <label class="field-label">Código de transação</label>
                <input type="text" name="transaction_code" class="field-input"
                       value="{{ old('transaction_code', $enrollment->transaction_code) }}">
              </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;">
              <div>
                <label class="field-label">Início da vigência</label>
                <input type="date" name="start_date" class="field-input"
                       value="{{ old('start_date', optional($enrollment->start_date)->format('Y-m-d')) }}">
              </div>
              <div>
                <label class="field-label">Fim da vigência</label>
                <input type="date" name="end_date" class="field-input"
                       value="{{ old('end_date', optional($enrollment->end_date)->format('Y-m-d')) }}">
              </div>
              <div>
                <label class="field-label">Data de pagamento</label>
                <input type="date" name="payday" class="field-input"
                       value="{{ old('payday', optional($enrollment->payday)->format('Y-m-d')) }}">
              </div>
            </div>

          </div>
        </div>

        {{-- Extra --}}
        <div class="card" style="padding:0;">
          <div style="padding:14px 20px;border-bottom:1px solid var(--line-2);">
            <h2 style="font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;margin:0;">Empresa / Extra</h2>
          </div>
          <div style="padding:20px;display:flex;flex-direction:column;gap:14px;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
              <div>
                <label class="field-label">Empresa</label>
                <input type="text" name="company" class="field-input" value="{{ old('company', $enrollment->company) }}">
              </div>
              <div>
                <label class="field-label">Entidade</label>
                <input type="text" name="entidade" class="field-input" value="{{ old('entidade', $enrollment->entidade) }}">
              </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
              <div>
                <label class="field-label">Plano</label>
                <input type="text" name="plano" class="field-input" value="{{ old('plano', $enrollment->plano) }}">
              </div>
              <div>
                <label class="field-label">Wallet / Responsável</label>
                <input type="text" name="wallet" class="field-input" value="{{ old('wallet', $enrollment->wallet) }}">
              </div>
            </div>
          </div>
        </div>

      </div>

      {{-- Sidebar --}}
      <div style="display:flex;flex-direction:column;gap:14px;position:sticky;top:80px;">

        <div class="card" style="padding:0;">
          <div style="padding:14px 18px;border-bottom:1px solid var(--line-2);">
            <h3 style="font-family:var(--font-display);font-weight:700;font-size:15px;color:#fff;margin:0;">Status</h3>
          </div>
          <div style="padding:16px 18px;display:flex;flex-direction:column;gap:12px;">
            <div>
              <label class="field-label">Status da matrícula</label>
              <select name="status" class="field-input">
                <option value="checked"          {{ old('status', $enrollment->status) === 'checked'          ? 'selected':'' }}>✓ Confirmada</option>
                <option value="not_checked"       {{ old('status', $enrollment->status) === 'not_checked'      ? 'selected':'' }}>⏳ Pendente</option>
                <option value="scheduled_billing" {{ old('status', $enrollment->status) === 'scheduled_billing'? 'selected':'' }}>📅 Agendada</option>
                <option value="canceled"          {{ old('status', $enrollment->status) === 'canceled'         ? 'selected':'' }}>✗ Cancelada</option>
              </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
              Salvar alterações
            </button>
            <a href="{{ route('admin.matriculas') }}" class="btn btn-ghost"
               style="text-decoration:none;width:100%;justify-content:center;display:flex;">
              Cancelar
            </a>
          </div>
        </div>

        {{-- Registro --}}
        <div class="card" style="padding:14px 18px;">
          <div style="font-size:10px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--fg-4);margin-bottom:8px;">Registro</div>
          <div style="display:flex;flex-direction:column;gap:4px;font-size:12px;">
            <div style="display:flex;justify-content:space-between;">
              <span style="color:var(--fg-4);">ID</span>
              <span style="font-family:var(--font-mono);">#{{ $enrollment->id }}</span>
            </div>
            @if($enrollment->log)
              <div style="display:flex;justify-content:space-between;">
                <span style="color:var(--fg-4);">Última edição</span>
                <span>{{ $enrollment->log }}</span>
              </div>
            @endif
          </div>
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
function calcFinal() {
  const v = parseFloat(document.querySelector('[name="value"]').value) || 0;
  const d = parseFloat(document.querySelector('[name="discount"]').value) || 0;
  document.getElementById('final_value').value = Math.max(0, v - d).toFixed(2);
}
</script>
@endpush
