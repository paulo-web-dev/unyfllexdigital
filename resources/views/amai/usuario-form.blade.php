@extends('layouts.assinante')
@section('title', 'Gestão AMAI — Novo usuário')
@section('section', 'Gestão AMAI')

@section('content')
@include('amai._flash')

<div class="as-head">
  <div>
    <h1>Novo usuário</h1>
    <p>O usuário recebe acesso de assinante ao catálogo. Senha inicial: o CPF (só números). Ele pode trocar em "Meu perfil".</p>
  </div>
  <a class="as-btn as-btn--ghost" href="{{ route('amai.index') }}">Voltar</a>
</div>

<form method="POST" action="{{ route('amai.usuarios.salvar') }}" class="as-perfil__card as-form" style="max-width:720px;" autocomplete="off">
  @csrf
  <div class="as-form__grid">
    <div class="as-form__field as-form__field--full">
      <label for="f-focal">Município / ponto focal</label>
      @if($eu->isMaster())
        <select id="f-focal" name="focal_id" class="as-in" required>
          <option value="">Selecione…</option>
          @foreach($focais as $f)
            <option value="{{ $f->id }}" @selected(old('focal_id', $focal?->id) == $f->id)>{{ $f->municipio }} — {{ $f->user?->name }} ({{ $f->vagas_usadas }}/{{ $f->vagas_cota }} vagas em uso)</option>
          @endforeach
        </select>
      @else
        <input type="hidden" name="focal_id" value="{{ $eu->id }}">
        <input class="as-in" type="text" value="{{ $eu->municipio }} — {{ $vagas['usadas'] }}/{{ $vagas['cota'] }} vagas em uso" disabled>
      @endif
    </div>
    <div class="as-form__field as-form__field--full">
      <label for="f-nome">Nome completo</label>
      <input id="f-nome" class="as-in" type="text" name="nome" value="{{ old('nome') }}" required maxlength="120">
    </div>
    <div class="as-form__field">
      <label for="f-email">E-mail (será o login)</label>
      <input id="f-email" class="as-in" type="email" name="email" value="{{ old('email') }}" required maxlength="180">
    </div>
    <div class="as-form__field">
      <label for="f-cpf">CPF</label>
      <input id="f-cpf" class="as-in" type="text" name="cpf" value="{{ old('cpf') }}" required inputmode="numeric" placeholder="000.000.000-00">
    </div>
    <div class="as-form__field as-form__field--full">
      <label for="f-cargo">Cargo (opcional)</label>
      <input id="f-cargo" class="as-in" type="text" name="cargo" value="{{ old('cargo') }}" maxlength="120">
    </div>
  </div>
  <div class="as-form__acoes">
    <button type="submit" class="as-btn as-btn--primary">Cadastrar e liberar acesso</button>
  </div>
</form>
@endsection
