@extends('layouts.site')
@section('meta_title', 'Esqueci a senha — Unyflex Digital')

@section('content')
<div style="min-height:100vh; display:flex; align-items:center; justify-content:center; padding:100px 20px;">
  <div style="width:100%; max-width:440px;">

    <div class="text-center mb-4 aos-fade">
      <div class="navbar-logo-mark" style="width:56px; height:56px; margin:0 auto 16px;">
        <img src="{{ asset('img/logo-unyflex.png') }}" alt="Unyflex Digital">
      </div>
      <h1 style="font-family:var(--font-display); font-weight:800; font-size:26px; color:#fff;">
        Recuperar senha
      </h1>
      <p style="color:var(--fg-3); font-size:14px; margin-top:6px; max-width:340px; margin-left:auto; margin-right:auto;">
        Informe seu e-mail e enviaremos um link para redefinir sua senha.
      </p>
    </div>

    <div class="checkout-card aos-fade aos-delay-1">

      @if(session('success'))
        <div style="padding:14px 16px; background:rgba(43,217,161,0.10); border:1px solid rgba(43,217,161,0.35); border-radius:var(--r-md); color:var(--success); font-size:13px; font-weight:500; margin-bottom:20px;">
          <strong>E-mail enviado!</strong><br>
          {{ session('success') }}
        </div>
      @endif

      @if($errors->any())
        <div style="padding:12px 16px; background:rgba(255,92,122,0.10); border:1px solid rgba(255,92,122,0.35); border-radius:var(--r-md); color:#FF5C7A; font-size:13px; font-weight:500; margin-bottom:20px;">
          {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="checkout-form-group">
          <label class="checkout-label">E-mail cadastrado</label>
          <input
            type="email"
            name="email"
            class="checkout-input"
            placeholder="seu@email.com"
            value="{{ old('email') }}"
            autocomplete="email"
            autofocus
          >
        </div>

        <button type="submit" class="btn-ux btn-ux-primary btn-ux-lg" style="width:100%; justify-content:center; margin-top:4px;">
          Enviar link de recuperação
        </button>

        <p style="text-align:center; color:var(--fg-4); font-size:13px; margin-top:16px; margin-bottom:0;">
          Lembrou a senha?
          <a href="{{ route('login') }}" style="color:var(--brand-300); text-decoration:none; font-weight:500;">
            Voltar ao login
          </a>
        </p>
      </form>
    </div>

  </div>
</div>
@endsection
