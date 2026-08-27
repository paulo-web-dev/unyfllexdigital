@extends('layouts.assinante')
@section('title', 'Gestão AMAI — Usuários')
@section('section', 'Gestão AMAI')

@section('content')
@include('amai._flash')

<div class="as-head">
  <div>
    <h1>{{ $eu->isMaster() ? 'Usuários da AMAI' : 'Usuários de ' . $eu->municipio }}</h1>
    <p>
      @if($eu->isMaster())
        Você é o <strong style="color:var(--as-fg-2);">master</strong>: vê todos os municípios, cadastra pontos focais e usuários em qualquer município.
      @else
        Você é o <strong style="color:var(--as-fg-2);">ponto focal</strong> de {{ $eu->municipio }}: cadastra e remove os usuários do seu município. Cada usuário ocupa uma vaga; remover libera.
      @endif
    </p>
  </div>
  <div class="as-kpis">
    @if($vagas)
      <div class="as-kpi as-kpi--total"><b>{{ $vagas['livres'] }}</b><span>vagas livres</span></div>
      <div class="as-kpi"><b>{{ $vagas['usadas'] }}</b><span>em uso</span></div>
      <div class="as-kpi"><b>{{ $vagas['cota'] }}</b><span>cota</span></div>
    @else
      <div class="as-kpi as-kpi--total"><b>{{ $usuarios->count() }}</b><span>usuários ativos</span></div>
      <div class="as-kpi"><b>{{ $focais->count() }}</b><span>pontos focais</span></div>
      <div class="as-kpi"><b>{{ $focais->sum('vagas_livres') }}</b><span>vagas livres</span></div>
    @endif
  </div>
</div>

<div class="am-bar">
  @if($eu->isMaster())
    <form method="GET" action="{{ route('amai.index') }}" class="am-filtro">
      <select name="municipio" class="as-in" onchange="this.form.submit()">
        <option value="">Todos os municípios</option>
        @foreach(\App\Services\AmaiService::MUNICIPIOS as $m)
          <option value="{{ $m }}" @selected($municipio === $m)>{{ $m }}</option>
        @endforeach
      </select>
    </form>
    <a class="as-btn as-btn--ghost" href="{{ route('amai.focais') }}">Pontos focais</a>
    <a class="as-btn as-btn--primary" href="{{ route('amai.usuarios.novo', $municipio ? ['focal' => optional($focais->firstWhere('municipio', $municipio))->id] : []) }}">+ Novo usuário</a>
  @else
    @if($vagas['livres'] > 0)
      <a class="as-btn as-btn--primary" href="{{ route('amai.usuarios.novo') }}">+ Novo usuário</a>
    @else
      <span class="as-btn as-btn--ghost" style="opacity:.6;cursor:not-allowed;" title="Cota esgotada">+ Novo usuário (cota esgotada)</span>
    @endif
  @endif
</div>

@if($usuarios->isEmpty())
  <div class="as-vazio"><p>Nenhum usuário ativo{{ $municipio ? ' em ' . $municipio : '' }}.</p></div>
@else
  <div class="am-tabela-wrap">
    <table class="am-tabela">
      <thead>
        <tr>
          <th>Nome</th><th>E-mail</th>
          @if($eu->isMaster())<th>Município</th><th>Ponto focal</th>@endif
          <th>Cadastro</th><th>Último acesso</th><th>Aulas vistas</th><th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($usuarios as $v)
          @php $u = $uso[$v->user_id] ?? null; @endphp
          <tr>
            <td><strong>{{ $v->user?->name ?? '—' }}</strong></td>
            <td class="am-muted">{{ $v->user?->email ?? '—' }}</td>
            @if($eu->isMaster())
              <td>{{ $v->municipio }}</td>
              <td class="am-muted">{{ $v->pontoFocal?->name ?? '—' }}</td>
            @endif
            <td class="am-muted">{{ $v->created_at?->format('d/m/Y') }}</td>
            <td class="am-muted">{{ $u && $u['ultimo_login'] ? \Illuminate\Support\Carbon::parse($u['ultimo_login'])->format('d/m/Y H:i') : 'nunca entrou' }}</td>
            <td>{{ $u['aulas'] ?? 0 }}</td>
            <td class="am-acoes">
              @if($eu->isMaster())
                <a class="as-btn as-btn--ghost am-btn-sm" href="{{ route('amai.usuarios.historico', $v->id) }}">Histórico</a>
              @endif
              <form method="POST" action="{{ route('amai.usuarios.remover', $v->id) }}" onsubmit="return confirm('Encerrar o acesso de {{ addslashes($v->user?->name ?? '') }}? A vaga será liberada e o histórico fica guardado.');">
                @csrf
                <button type="submit" class="as-btn as-btn--ghost am-btn-sm am-btn-danger">Remover</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endif
@endsection
