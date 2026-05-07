@extends('layouts.site')
@section('meta_title', 'Entrar — Unyflex Digital')

@section('content')
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:100px 20px;">
  <div style="width:100%;max-width:440px;">
    <div class="text-center mb-4 aos-fade">
      <div class="navbar-logo-mark" style="width:56px;height:56px;margin:0 auto 16px;">
        <img src="{{ asset('img/logo-unyflex.png') }}" alt="Unyflex Digital">
      </div>
      <h1 style="font-family:var(--font-display);font-weight:800;font-size:26px;color:#fff;">Entrar na plataforma</h1>
      <p style="color:var(--fg-3);font-size:14px;margin-top:6px;">Acesse suas miniséries e continue de onde parou.</p>
    </div>

    <div class="checkout-card aos-fade aos-delay-1">
      <div class="checkout-form-group">
        <label class="checkout-label">E-mail</label>
        <input type="email" class="checkout-input" placeholder="seu@email.com">
      </div>
      <div class="checkout-form-group">
        <label class="checkout-label" style="display:flex;justify-content:space-between;">
          Senha
          <a href="#" style="color:var(--brand-300);font-size:12px;text-decoration:none;">Esqueci a senha</a>
        </label>
        <input type="password" class="checkout-input" placeholder="••••••••">
      </div>
      <a href="{{ route('dashboard') }}" class="btn-ux btn-ux-primary btn-ux-lg" style="width:100%;justify-content:center;margin-top:8px;">
        Entrar
      </a>
      <p style="text-align:center;color:var(--fg-4);font-size:13px;margin-top:16px;">
        Não tem conta?
        <a href="{{ route('checkout') }}" style="color:var(--brand-300);text-decoration:none;">Garantir minha vaga</a>
      </p>
    </div>
  </div>
</div>
@endsection
