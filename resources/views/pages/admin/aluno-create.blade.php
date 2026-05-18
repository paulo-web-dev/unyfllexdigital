@extends('layouts.admin')
@section('title', 'Novo Aluno')
@section('section', 'Alunos')

@section('content')
<div class="page">

  <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:12px;color:var(--fg-4);">
    <a href="{{ route('admin.alunos') }}" style="color:var(--fg-4);text-decoration:none;" onmouseover="this.style.color='var(--brand-300)'" onmouseout="this.style.color='var(--fg-4)'">Alunos</a>
    <span>/</span>
    <span style="color:var(--fg-2);">Novo aluno</span>
  </div>

  @if($errors->any())
    <div style="padding:12px 16px;background:rgba(255,92,122,0.10);border:1px solid rgba(255,92,122,0.35);border-radius:var(--r-md);color:#FF5C7A;font-size:13px;margin-bottom:20px;">
      <strong>Corrija os erros:</strong>
      <ul style="margin:6px 0 0 16px;padding:0;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form action="{{ route('admin.alunos.store') }}" method="POST">
    @csrf

    <div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start;">

      {{-- ══ Dados principais ══════════════════════════════════════════ --}}
      <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Identificação --}}
        <div class="card" style="padding:0;">
          <div style="padding:14px 20px;border-bottom:1px solid var(--line-2);">
            <h2 style="font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;margin:0;">Identificação</h2>
          </div>
          <div style="padding:20px;display:flex;flex-direction:column;gap:16px;">

            <div>
              <label class="field-label">Nome completo *</label>
              <input type="text" name="name" class="field-input" value="{{ old('name') }}" required placeholder="Nome Sobrenome">
              @error('name')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
              <div>
                <label class="field-label">E-mail *</label>
                <input type="email" name="email" class="field-input" value="{{ old('email') }}" required placeholder="aluno@email.com">
                @error('email')<span class="field-error">{{ $message }}</span>@enderror
              </div>
              <div>
                <label class="field-label">CPF</label>
                <input type="text" name="cpf" class="field-input" value="{{ old('cpf') }}" placeholder="000.000.000-00">
              </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
              <div>
                <label class="field-label">Telefone / WhatsApp</label>
                <input type="text" name="phone" class="field-input" value="{{ old('phone') }}" placeholder="(11) 99999-9999">
              </div>
              <div>
                <label class="field-label">Data de nascimento</label>
                <input type="date" name="nascimento" class="field-input" value="{{ old('nascimento') }}">
              </div>
            </div>

          </div>
        </div>

        {{-- Profissional --}}
        <div class="card" style="padding:0;">
          <div style="padding:14px 20px;border-bottom:1px solid var(--line-2);">
            <h2 style="font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;margin:0;">Dados profissionais</h2>
          </div>
          <div style="padding:20px;display:flex;flex-direction:column;gap:16px;">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
              <div>
                <label class="field-label">Cargo / Função</label>
                <input type="text" name="cargo" class="field-input" value="{{ old('cargo') }}" placeholder="Ex: Pregoeira">
              </div>
              <div>
                <label class="field-label">Órgão / Entidade</label>
                <input type="text" name="entidade" class="field-input" value="{{ old('entidade') }}" placeholder="Ex: Prefeitura de São Paulo">
              </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 80px;gap:14px;">
              <div>
                <label class="field-label">Cidade</label>
                <input type="text" name="city" class="field-input" value="{{ old('city') }}" placeholder="São Paulo">
              </div>
              <div>
                <label class="field-label">UF</label>
                <input type="text" name="state" class="field-input" value="{{ old('state') }}" maxlength="2" placeholder="SP">
              </div>
            </div>

          </div>
        </div>

        {{-- Acesso --}}
        <div class="card" style="padding:0;">
          <div style="padding:14px 20px;border-bottom:1px solid var(--line-2);">
            <h2 style="font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;margin:0;">Dados de acesso</h2>
          </div>
          <div style="padding:20px;display:flex;flex-direction:column;gap:16px;">
            <div style="padding:12px 14px;background:rgba(0,163,255,0.06);border:1px solid rgba(0,163,255,0.2);border-radius:10px;font-size:12px;color:var(--fg-3);">
              💡 Será criado um acesso no sistema automaticamente com o e-mail e senha informados.
            </div>
            <div>
              <label class="field-label">Senha *</label>
              <input type="password" name="password" id="pwd-field" class="field-input" required placeholder="Mínimo 6 caracteres">
              @error('password')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div>
              <label class="field-label">Confirmar senha</label>
              <input type="password" id="pwd-confirm" class="field-input" placeholder="Repita a senha">
              <span id="pwd-match" style="font-size:11px;display:none;margin-top:4px;"></span>
            </div>
          </div>
        </div>

      </div>

      {{-- ══ Sidebar ════════════════════════════════════════════════════ --}}
      <div style="display:flex;flex-direction:column;gap:14px;position:sticky;top:80px;">

        <div class="card" style="padding:0;">
          <div style="padding:14px 18px;border-bottom:1px solid var(--line-2);">
            <h3 style="font-family:var(--font-display);font-weight:700;font-size:15px;color:#fff;margin:0;">Publicar</h3>
          </div>
          <div style="padding:16px 18px;display:flex;flex-direction:column;gap:12px;">
            <div>
              <label class="field-label">Status</label>
              <select name="status" class="field-input">
                <option value="able"     {{ old('status','able') === 'able'     ? 'selected' : '' }}>✓ Ativo</option>
                <option value="disabled" {{ old('status','able') === 'disabled' ? 'selected' : '' }}>✗ Inativo</option>
              </select>
            </div>
            <button type="submit" name="matricular_agora" value="0" class="btn btn-primary" style="width:100%;justify-content:center;">
              <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                <polyline points="17 21 17 13 7 13 7 21"/>
                <polyline points="7 3 7 8 15 8"/>
              </svg>
              Criar aluno
            </button>
            <button type="submit" name="matricular_agora" value="1"
                    class="btn btn-secondary" style="width:100%;justify-content:center;">
              <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24">
                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/>
              </svg>
              Criar e já matricular
            </button>
            <a href="{{ route('admin.alunos') }}" class="btn btn-ghost" style="text-decoration:none;width:100%;justify-content:center;display:flex;">
              Cancelar
            </a>
          </div>
        </div>

        <div style="padding:14px 16px;background:rgba(43,217,161,0.06);border:1px solid rgba(43,217,161,0.2);border-radius:var(--r-lg);">
          <div style="font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#6FE6BD;margin-bottom:6px;">O que acontece</div>
          <ul style="font-size:12px;color:var(--fg-3);margin:0;padding-left:14px;line-height:1.8;">
            <li>Registro criado em <code>students</code></li>
            <li>Acesso criado em <code>users</code></li>
            <li>Flag <code>minisserie = 1</code> ativada</li>
            <li>Aluno pode fazer login imediatamente</li>
          </ul>
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
// Valida senhas em tempo real
const pwd     = document.getElementById('pwd-field');
const confirm = document.getElementById('pwd-confirm');
const msg     = document.getElementById('pwd-match');

function checkPwd() {
  if (!confirm.value) { msg.style.display = 'none'; return; }
  if (pwd.value === confirm.value) {
    msg.textContent = '✓ Senhas conferem';
    msg.style.color = 'var(--success)';
  } else {
    msg.textContent = '✗ Senhas não conferem';
    msg.style.color = 'var(--danger)';
  }
  msg.style.display = 'block';
}
pwd.addEventListener('input', checkPwd);
confirm.addEventListener('input', checkPwd);
</script>
@endpush
