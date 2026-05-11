@extends('layouts.site')
@section('meta_title', 'Redefinir senha — Unyflex Digital')

@section('content')
<div style="min-height:100vh; display:flex; align-items:center; justify-content:center; padding:100px 20px;">
  <div style="width:100%; max-width:440px;">

    <div class="text-center mb-4 aos-fade">
      <div class="navbar-logo-mark" style="width:56px; height:56px; margin:0 auto 16px;">
        <img src="{{ asset('img/logo-unyflex.png') }}" alt="Unyflex Digital">
      </div>
      <h1 style="font-family:var(--font-display); font-weight:800; font-size:26px; color:#fff;">
        Nova senha
      </h1>
      <p style="color:var(--fg-3); font-size:14px; margin-top:6px;">
        Escolha uma senha com no mínimo 8 caracteres.
      </p>
    </div>

    <div class="checkout-card aos-fade aos-delay-1">

      @if($errors->any())
        <div style="padding:12px 16px; background:rgba(255,92,122,0.10); border:1px solid rgba(255,92,122,0.35); border-radius:var(--r-md); color:#FF5C7A; font-size:13px; font-weight:500; margin-bottom:20px;">
          {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="checkout-form-group">
          <label class="checkout-label">E-mail</label>
          <input
            type="email"
            name="email"
            class="checkout-input"
            value="{{ old('email', $email ?? '') }}"
            autocomplete="email"
            readonly
            style="opacity:0.6; cursor:not-allowed;"
          >
        </div>

        <div class="checkout-form-group">
          <label class="checkout-label">Nova senha</label>
          <div style="position:relative;">
            <input
              type="password"
              name="password"
              id="password-field"
              class="checkout-input"
              placeholder="Mínimo 8 caracteres"
              autocomplete="new-password"
              style="padding-right:44px;"
            >
            <button type="button" id="toggle-password"
              style="position:absolute; right:14px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:var(--fg-4); display:flex; align-items:center;">
              <svg id="eye-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="checkout-form-group">
          <label class="checkout-label">Confirmar nova senha</label>
          <input
            type="password"
            name="password_confirmation"
            class="checkout-input"
            placeholder="Repita a nova senha"
            autocomplete="new-password"
          >
        </div>

        <button type="submit" class="btn-ux btn-ux-primary btn-ux-lg" style="width:100%; justify-content:center; margin-top:4px;">
          Redefinir senha
        </button>

      </form>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
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
