@extends('layouts.site')
@section('meta_title', 'Entrar — Unyflex Digital')

@section('content')
<div style="min-height:100vh; display:flex; align-items:center; justify-content:center; padding:100px 20px;">
  <div style="width:100%; max-width:440px;">

    {{-- Logo + título --}}
    <div class="text-center mb-4 aos-fade">
      <div class="navbar-logo-mark" style="width:56px; height:56px; margin:0 auto 16px;">
        <img src="{{ asset('img/logo-unyflex.png') }}" alt="Unyflex Digital">
      </div>
      <h1 style="font-family:var(--font-display); font-weight:800; font-size:26px; color:#fff;">
        Entrar na plataforma
      </h1>
      <p style="color:var(--fg-3); font-size:14px; margin-top:6px;">
        Acesse suas miniséries e continue de onde parou.
      </p>
    </div>

    {{-- Card do formulário --}}
    <div class="checkout-card aos-fade aos-delay-1">

      {{-- Erro geral (e-mail ou senha incorretos) --}}
      @if($errors->any())
        <div style="
          padding: 12px 16px;
          background: rgba(255,92,122,0.10);
          border: 1px solid rgba(255,92,122,0.35);
          border-radius: var(--r-md);
          color: #FF5C7A;
          font-size: 13px;
          font-weight: 500;
          margin-bottom: 20px;
        ">
          {{ $errors->first() }}
        </div>
      @endif

      {{-- Flash de sucesso (ex: senha redefinida) --}}
      @if(session('success'))
        <div style="
          padding: 12px 16px;
          background: rgba(43,217,161,0.10);
          border: 1px solid rgba(43,217,161,0.35);
          border-radius: var(--r-md);
          color: var(--success);
          font-size: 13px;
          font-weight: 500;
          margin-bottom: 20px;
        ">
          {{ session('success') }}
        </div>
      @endif

      <form method="POST" action="{{ route('login.submit') }}" novalidate>
        @csrf

        {{-- E-mail --}}
        <div class="checkout-form-group">
          <label class="checkout-label">E-mail</label>
          <input
            type="email"
            name="email"
            class="checkout-input"
            placeholder="seu@email.com"
            value="{{ old('email') }}"
            autocomplete="email"
            autofocus
            style="{{ $errors->has('email') ? 'border-color: rgba(255,92,122,0.6);' : '' }}"
          >
        </div>

        {{-- Senha --}}
        <div class="checkout-form-group">
          <label class="checkout-label" style="display:flex; justify-content:space-between; align-items:center;">
            Senha
            <a href="{{ route('password.request') }}"
               style="color:var(--brand-300); font-size:12px; text-decoration:none; font-weight:500;">
              Esqueci a senha
            </a>
          </label>
          <div style="position:relative;">
            <input
              type="password"
              name="password"
              id="password-field"
              class="checkout-input"
              placeholder="••••••••"
              autocomplete="current-password"
              style="padding-right:44px; {{ $errors->has('email') ? 'border-color: rgba(255,92,122,0.6);' : '' }}"
            >
            {{-- Botão ver/ocultar senha --}}
            <button type="button" id="toggle-password"
              style="position:absolute; right:14px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:var(--fg-4); display:flex; align-items:center;"
              title="Mostrar/ocultar senha">
              <svg id="eye-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        {{-- Lembrar de mim --}}
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:20px; margin-top:-4px;">
          <input type="checkbox" name="remember" id="remember"
            style="width:16px; height:16px; accent-color:var(--brand-500); cursor:pointer;">
          <label for="remember" style="font-size:13px; color:var(--fg-3); cursor:pointer; user-select:none;">
            Manter conectado
          </label>
        </div>

        {{-- Botão entrar --}}
        <button type="submit"
          class="btn-ux btn-ux-primary btn-ux-lg"
          style="width:100%; justify-content:center;">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" style="margin-right:6px;"><polygon points="6 4 20 12 6 20 6 4"/></svg>
          Entrar
        </button>

        {{-- Link para criar conta --}}
        <p style="text-align:center; color:var(--fg-4); font-size:13px; margin-top:16px; margin-bottom:0;">
          Não tem conta?
          <a href="{{ route('checkout') }}" style="color:var(--brand-300); text-decoration:none; font-weight:500;">
            Garantir minha vaga
          </a>
        </p>

      </form>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
  // Toggle mostrar/ocultar senha
  document.getElementById('toggle-password').addEventListener('click', function () {
    const field = document.getElementById('password-field');
    const icon  = document.getElementById('eye-icon');
    if (field.type === 'password') {
      field.type = 'text';
      icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
    } else {
      field.type = 'password';
      icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }
  });
</script>
@endpush
