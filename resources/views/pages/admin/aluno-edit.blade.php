@extends('layouts.admin')
@section('title', 'Editar Aluno — ' . $user->name)
@section('section', 'Alunos')

@section('content')
<div class="page">

  <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:12px;color:var(--fg-4);">
    <a href="{{ route('admin.alunos') }}" style="color:var(--fg-4);text-decoration:none;" onmouseover="this.style.color='var(--brand-300)'" onmouseout="this.style.color='var(--fg-4)'">Alunos</a>
    <span>/</span>
    <span style="color:var(--fg-2);">{{ $user->name }}</span>
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

  <form action="{{ route('admin.alunos.update', $user->id) }}" method="POST">
    @csrf @method('PUT')

    <div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start;">

      {{-- ══ Coluna principal ══════════════════════════════════════════ --}}
      <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Identificação --}}
        <div class="card" style="padding:0;">
          <div style="padding:14px 20px;border-bottom:1px solid var(--line-2);">
            <h2 style="font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;margin:0;">Identificação</h2>
          </div>
          <div style="padding:20px;display:flex;flex-direction:column;gap:16px;">

            <div>
              <label class="field-label">Nome completo *</label>
              <input type="text" name="name" class="field-input"
                     value="{{ old('name', $user->name) }}" required>
              @error('name')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
              <div>
                <label class="field-label">E-mail *</label>
                <input type="email" name="email" class="field-input"
                       value="{{ old('email', $user->email) }}" required>
                @error('email')<span class="field-error">{{ $message }}</span>@enderror
              </div>
              <div>
                <label class="field-label">Telefone</label>
                <input type="text" name="telefone" class="field-input"
                       value="{{ old('telefone', $user->telefone) }}"
                       placeholder="(11) 99999-9999">
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
                <input type="text" name="funcao" class="field-input"
                       value="{{ old('funcao', $user->funcao) }}"
                       placeholder="Ex: Pregoeira">
              </div>
              <div>
                <label class="field-label">Setor / Órgão</label>
                <input type="text" name="setor" class="field-input"
                       value="{{ old('setor', $user->setor) }}"
                       placeholder="Ex: Prefeitura de SP">
              </div>
            </div>

            {{-- Dados do Student se existir --}}
            @if($student)
              <div style="padding:12px 14px;background:var(--bg-1);border:1px solid var(--line-1);border-radius:10px;">
                <div style="font-size:10px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--fg-4);margin-bottom:8px;">Dados adicionais (tabela students)</div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;font-size:12px;color:var(--fg-3);">
                  @if($student->cpf)
                    <div><span style="color:var(--fg-4);">CPF:</span> {{ $student->cpf }}</div>
                  @endif
                  @if($student->phone)
                    <div><span style="color:var(--fg-4);">Tel:</span> {{ $student->phone }}</div>
                  @endif
                  @if($student->city || $student->state)
                    <div><span style="color:var(--fg-4);">Local:</span> {{ $student->city }}{{ $student->state ? '/'.$student->state : '' }}</div>
                  @endif
                  @if($student->cargo)
                    <div><span style="color:var(--fg-4);">Cargo:</span> {{ $student->cargo }}</div>
                  @endif
                  @if($student->entidade)
                    <div><span style="color:var(--fg-4);">Entidade:</span> {{ $student->entidade }}</div>
                  @endif
                  @if($student->nascimento)
                    <div><span style="color:var(--fg-4);">Nascimento:</span> {{ \Carbon\Carbon::parse($student->nascimento)->format('d/m/Y') }}</div>
                  @endif
                </div>
              </div>
            @endif

          </div>
        </div>

        {{-- Alterar senha --}}
        <div class="card" style="padding:0;">
          <div style="padding:14px 20px;border-bottom:1px solid var(--line-2);">
            <h2 style="font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;margin:0;">Alterar senha</h2>
          </div>
          <div style="padding:20px;display:flex;flex-direction:column;gap:14px;">
            <div style="padding:10px 14px;background:rgba(255,181,71,0.06);border:1px solid rgba(255,181,71,0.2);border-radius:10px;font-size:12px;color:var(--fg-3);">
              ⚠️ Deixe em branco para manter a senha atual.
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
              <div>
                <label class="field-label">Nova senha</label>
                <input type="password" name="password" id="pwd-field" class="field-input"
                       placeholder="Mínimo 6 caracteres">
                @error('password')<span class="field-error">{{ $message }}</span>@enderror
              </div>
              <div>
                <label class="field-label">Confirmar nova senha</label>
                <input type="password" id="pwd-confirm" class="field-input"
                       placeholder="Repita a senha">
                <span id="pwd-match" style="font-size:11px;display:none;margin-top:4px;"></span>
              </div>
            </div>
          </div>
        </div>

      </div>

      {{-- ══ Sidebar ════════════════════════════════════════════════════ --}}
      <div style="display:flex;flex-direction:column;gap:14px;position:sticky;top:80px;">

        {{-- Ações --}}
        <div class="card" style="padding:0;">
          <div style="padding:14px 18px;border-bottom:1px solid var(--line-2);">
            <h3 style="font-family:var(--font-display);font-weight:700;font-size:15px;color:#fff;margin:0;">Publicar</h3>
          </div>
          <div style="padding:16px 18px;display:flex;flex-direction:column;gap:10px;">
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
              <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                <polyline points="17 21 17 13 7 13 7 21"/>
                <polyline points="7 3 7 8 15 8"/>
              </svg>
              Salvar alterações
            </button>
            <a href="{{ route('admin.matriculas.create', ['student_id' => $user->student_id]) }}"
               class="btn btn-secondary" style="text-decoration:none;width:100%;justify-content:center;display:flex;">
              <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24">
                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/>
              </svg>
              Nova matrícula
            </a>
            <a href="{{ route('admin.alunos') }}" class="btn btn-ghost"
               style="text-decoration:none;width:100%;justify-content:center;display:flex;">
              Cancelar
            </a>
          </div>
        </div>

        {{-- Power --}}
        <div class="card" style="padding:14px 18px;">
          <label class="field-label">Nível de acesso (power)</label>
          <input type="number" name="power" class="field-input"
                 value="{{ old('power', $user->power ?? 1) }}" min="0" max="100">
          <p style="font-size:11px;color:var(--fg-4);margin-top:4px;">
            Power &gt; 10 libera acesso ao painel admin.
          </p>
        </div>

        {{-- Registro --}}
        <div class="card" style="padding:14px 18px;">
          <div style="font-size:10px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--fg-4);margin-bottom:8px;">Registro</div>
          <div style="display:flex;flex-direction:column;gap:4px;font-size:12px;">
            <div style="display:flex;justify-content:space-between;">
              <span style="color:var(--fg-4);">User ID</span>
              <span style="font-family:var(--font-mono);">#{{ $user->id }}</span>
            </div>
            @if($user->student_id)
              <div style="display:flex;justify-content:space-between;">
                <span style="color:var(--fg-4);">Student ID</span>
                <span style="font-family:var(--font-mono);">#{{ $user->student_id }}</span>
              </div>
            @endif
            <div style="display:flex;justify-content:space-between;">
              <span style="color:var(--fg-4);">Cadastro</span>
              <span>{{ optional($user->created_at)->format('d/m/Y') }}</span>
            </div>
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
</style>
@endpush

@push('scripts')
<script>
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