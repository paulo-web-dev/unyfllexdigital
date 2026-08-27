@extends('layouts.assinante')
@section('title', 'Gestão AMAI — Pontos focais')
@section('section', 'Gestão AMAI')

@section('content')
@include('amai._flash')

<div class="as-head">
  <div>
    <h1>Pontos focais</h1>
    <p>Um por município. Cada ponto focal tem 14 vagas para usuários (além dele); você pode ampliar a cota de um município específico.</p>
  </div>
  <a class="as-btn as-btn--ghost" href="{{ route('amai.index') }}">Usuários</a>
</div>

<div class="am-tabela-wrap">
  <table class="am-tabela">
    <thead><tr><th>Município</th><th>Ponto focal</th><th>E-mail</th><th>Vagas</th><th>Cota</th><th>Último acesso</th><th></th></tr></thead>
    <tbody>
      @forelse($focais as $f)
        @php $u = $uso[$f->user_id] ?? null; @endphp
        <tr>
          <td><strong>{{ $f->municipio }}</strong></td>
          <td>{{ $f->user?->name ?? '—' }}</td>
          <td class="am-muted">{{ $f->user?->email ?? '—' }}</td>
          <td><span class="{{ $f->vagas_livres === 0 ? 'am-cheio' : '' }}">{{ $f->vagas_usadas }} / {{ $f->vagas_cota }}</span></td>
          <td>
            <form method="POST" action="{{ route('amai.focais.cota', $f->id) }}" class="am-inline">
              @csrf
              <input class="as-in am-cota" type="number" name="cota" min="0" max="500" value="{{ $f->vagas_cota }}">
              <button type="submit" class="as-btn as-btn--ghost am-btn-sm">Salvar</button>
            </form>
          </td>
          <td class="am-muted">{{ $u && $u['ultimo_login'] ? \Illuminate\Support\Carbon::parse($u['ultimo_login'])->format('d/m/Y H:i') : 'nunca entrou' }}</td>
          <td class="am-acoes">
            <a class="as-btn as-btn--ghost am-btn-sm" href="{{ route('amai.index', ['municipio' => $f->municipio]) }}">Usuários</a>
            <form method="POST" action="{{ route('amai.focais.remover', $f->id) }}" onsubmit="return confirm('Remover o ponto focal de {{ $f->municipio }}? A assinatura dele é encerrada; os usuários do município continuam ativos.');">
              @csrf
              <button type="submit" class="as-btn as-btn--ghost am-btn-sm am-btn-danger">Remover</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" class="am-muted">Nenhum ponto focal ativo.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<h2 class="am-sub">Cadastrar ponto focal</h2>
@if(empty($livres))
  <p class="as-muted">Todos os 14 municípios já têm ponto focal.</p>
@else
  <form method="POST" action="{{ route('amai.focais.salvar') }}" class="as-perfil__card as-form" style="max-width:720px;" autocomplete="off">
    @csrf
    <div class="as-form__grid">
      <div class="as-form__field as-form__field--full">
        <label for="p-municipio">Município (sem ponto focal)</label>
        <select id="p-municipio" name="municipio" class="as-in" required>
          <option value="">Selecione…</option>
          @foreach($livres as $m)<option value="{{ $m }}" @selected(old('municipio') === $m)>{{ $m }}</option>@endforeach
        </select>
      </div>
      <div class="as-form__field as-form__field--full"><label for="p-nome">Nome completo</label><input id="p-nome" class="as-in" type="text" name="nome" value="{{ old('nome') }}" required maxlength="120"></div>
      <div class="as-form__field"><label for="p-email">E-mail (login)</label><input id="p-email" class="as-in" type="email" name="email" value="{{ old('email') }}" required maxlength="180"></div>
      <div class="as-form__field"><label for="p-cpf">CPF</label><input id="p-cpf" class="as-in" type="text" name="cpf" value="{{ old('cpf') }}" required inputmode="numeric" placeholder="000.000.000-00"></div>
      <div class="as-form__field as-form__field--full"><label for="p-cargo">Cargo (opcional)</label><input id="p-cargo" class="as-in" type="text" name="cargo" value="{{ old('cargo') }}" maxlength="120"></div>
    </div>
    <div class="as-form__acoes"><button type="submit" class="as-btn as-btn--primary">Cadastrar ponto focal</button></div>
    <p class="as-muted" style="margin:10px 0 0;">Se o e-mail já tiver conta na Unyflex, ela é vinculada como ponto focal (sem criar outra). Senha inicial de conta nova: o CPF.</p>
  </form>
@endif
@endsection
