@extends('layouts.assinante')
@section('title', 'Meu perfil — Assinatura Unyflex')
@section('section', 'Meu perfil')

@section('content')
@php
  $u = auth()->user();
  $abaSenha = session('perfil_aba') === 'senha' || $errors->has('password_current') || $errors->has('password');
@endphp

<div class="as-head">
  <div>
    <h1>Meu perfil</h1>
    <p>Dados da sua conta de assinante. Alterações valem para o login e para os certificados.</p>
  </div>
  <a href="{{ route('assinante.home') }}" class="as-btn as-btn--ghost">
    <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.2;"><polyline points="15 18 9 12 15 6"/></svg>
    Voltar ao catálogo
  </a>
</div>

@if(session('success'))
  <div class="as-alert as-alert--ok">{{ session('success') }}</div>
@endif
@if($errors->any())
  <div class="as-alert as-alert--warn">
    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
  </div>
@endif

<div class="as-perfil">

  <div class="as-perfil__card as-perfil__id">
    <div class="as-perfil__avatar">{{ $u->initials }}</div>
    <div class="as-perfil__who">
      <strong>{{ $u->name }}</strong>
      <span>{{ $u->email }}</span>
      <span class="as-badge as-badge--visto" style="margin-top:6px;">Assinante</span>
    </div>
  </div>

  <form method="POST" action="{{ route('perfil.update') }}" class="as-perfil__card as-form" autocomplete="off">
    @csrf
    <h3>Dados cadastrais</h3>
    <div class="as-form__grid">
      <div class="as-form__field as-form__field--full">
        <label for="p-name">Nome completo</label>
        <input id="p-name" class="as-in" type="text" name="name" value="{{ old('name', $u->name) }}" required maxlength="120">
      </div>
      <div class="as-form__field as-form__field--full">
        <label for="p-email">E-mail (login)</label>
        <input id="p-email" class="as-in" type="email" name="email" value="{{ old('email', $u->email) }}" required maxlength="180">
      </div>
      <div class="as-form__field">
        <label for="p-cargo">Cargo</label>
        <input id="p-cargo" class="as-in" type="text" name="cargo" value="{{ old('cargo', $u->funcao) }}" maxlength="120">
      </div>
      <div class="as-form__field">
        <label for="p-orgao">Órgão / entidade</label>
        <input id="p-orgao" class="as-in" type="text" name="orgao" value="{{ old('orgao', $u->setor) }}" maxlength="180">
      </div>
    </div>
    <div class="as-form__acoes">
      <button type="submit" class="as-btn as-btn--primary">Salvar alterações</button>
    </div>
  </form>

  <form method="POST" action="{{ route('perfil.update') }}" class="as-perfil__card as-form" id="form-senha" autocomplete="off">
    @csrf
    <input type="hidden" name="action" value="password">
    <h3>Alterar senha</h3>
    <div class="as-form__grid">
      <div class="as-form__field as-form__field--full">
        <label for="p-atual">Senha atual</label>
        <input id="p-atual" class="as-in" type="password" name="password_current" required autocomplete="current-password">
      </div>
      <div class="as-form__field">
        <label for="p-nova">Nova senha (mín. 8)</label>
        <input id="p-nova" class="as-in" type="password" name="password" required minlength="8" autocomplete="new-password">
      </div>
      <div class="as-form__field">
        <label for="p-conf">Confirmar nova senha</label>
        <input id="p-conf" class="as-in" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
      </div>
    </div>
    <div class="as-form__acoes">
      <button type="submit" class="as-btn as-btn--ghost">Atualizar senha</button>
    </div>
  </form>

</div>

@if($abaSenha)
  @push('scripts')<script>document.getElementById('form-senha')?.scrollIntoView({ block: 'start' });</script>@endpush
@endif
@endsection
