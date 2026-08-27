@extends('layouts.assinante')
@section('title', 'Gestão AMAI — Histórico')
@section('section', 'Gestão AMAI')

@section('content')
<div class="as-head">
  <div>
    <h1>{{ $alvo->user?->name ?? 'Usuário' }}</h1>
    <p>{{ $alvo->user?->email }} · {{ $alvo->municipio }} · ponto focal: {{ $alvo->pontoFocal?->name ?? '—' }}
      @if($alvo->removed_at) · <span style="color:#FFB547;">acesso encerrado em {{ $alvo->removed_at->format('d/m/Y') }}</span>@endif
    </p>
  </div>
  <a class="as-btn as-btn--ghost" href="{{ route('amai.index', ['municipio' => $alvo->municipio]) }}">Voltar</a>
</div>

<div class="as-exp__grid">
  <div class="as-exp__card">
    <p class="as-player__eyebrow">Cursos acessados</p>
    @if($cursos->isEmpty())<p class="as-muted">Nenhum acesso registrado.</p>@else
      <ul class="am-lista">
        @foreach($cursos as $c)<li><span>{{ $c->detail }}</span><small>{{ $c->vezes }}× · último {{ \Illuminate\Support\Carbon::parse($c->ultimo)->format('d/m/Y H:i') }}</small></li>@endforeach
      </ul>
    @endif
  </div>
  <div class="as-exp__card">
    <p class="as-player__eyebrow">Logins (últimos 30)</p>
    @if($logins->isEmpty())<p class="as-muted">Nunca entrou.</p>@else
      <ul class="am-lista">
        @foreach($logins as $l)<li><span>{{ $l->created_at->format('d/m/Y H:i') }}</span><small>{{ $l->ip }}</small></li>@endforeach
      </ul>
    @endif
  </div>
</div>

<div class="as-exp__card" style="margin-top:16px;">
  <p class="as-player__eyebrow">Aulas assistidas (últimas 30)</p>
  @if($aulas->isEmpty())<p class="as-muted">Nenhuma aula assistida.</p>@else
    <ul class="am-lista">
      @foreach($aulas as $a)
        <li><span>{{ $a->classes?->title ?? 'Turma #' . $a->classes_id }} — {{ $a->video?->titulo ?? 'Aula #' . $a->video_id }}</span><small>{{ $a->updated_at?->format('d/m/Y H:i') }}</small></li>
      @endforeach
    </ul>
  @endif
</div>
@endsection
