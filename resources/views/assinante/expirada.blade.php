@extends('layouts.assinante')
@section('title', 'Assinatura expirada — Unyflex Digital')
@section('section', 'Assinatura')

@section('content')
@php
  $cancelada = $ultima && $ultima->status === 'cancelado';
  $fim       = $ultima?->end_date;
  $diasAtras = $fim ? (int) $fim->startOfDay()->diffInDays(now()->startOfDay()) : null;
@endphp

<div class="as-exp">

  <div class="as-exp__hero">
    <span class="as-badge as-badge--gravado">{{ $cancelada ? 'Assinatura cancelada' : 'Assinatura expirada' }}</span>
    <h1>Sua assinatura {{ $cancelada ? 'foi cancelada' : 'venceu' }}{{ $fim ? ' em ' . $fim->format('d/m/Y') : '' }}.</h1>
    <p>
      @if($diasAtras !== null)
        {{ $diasAtras === 0 ? 'Hoje' : ($diasAtras === 1 ? 'Há 1 dia' : "Há {$diasAtras} dias") }} o seu acesso ao catálogo completo foi encerrado.
      @endif
      Para voltar a assistir, é só renovar com o nosso time comercial — a liberação é feita na hora, sem novo cadastro.
    </p>
    <div class="as-exp__acoes">
      <a class="as-btn as-btn--primary" href="{{ $whatsapp }}" target="_blank" rel="noopener">
        <svg viewBox="0 0 24 24" style="width:16px;height:16px;fill:currentColor;"><path d="M20.5 3.5A11.9 11.9 0 0 0 12 0C5.4 0 .1 5.3.1 11.9c0 2.1.6 4.2 1.6 6L0 24l6.3-1.7a11.9 11.9 0 0 0 5.7 1.5c6.6 0 11.9-5.3 11.9-11.9 0-3.2-1.2-6.2-3.4-8.4zM12 21.8c-1.8 0-3.5-.5-5-1.4l-.4-.2-3.7 1 1-3.6-.2-.4A9.8 9.8 0 0 1 2.1 12C2.1 6.5 6.5 2.1 12 2.1c2.6 0 5.1 1 7 2.9a9.8 9.8 0 0 1 2.9 7c0 5.4-4.4 9.8-9.9 9.8zm5.4-7.3c-.3-.1-1.8-.9-2-1-.3-.1-.5-.1-.7.1l-.9 1.2c-.2.2-.3.2-.6.1-.3-.1-1.3-.5-2.4-1.5-.9-.8-1.5-1.8-1.7-2.1-.2-.3 0-.5.1-.6l.4-.5.3-.5c.1-.2 0-.4 0-.5L9 7.1c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.5s1.1 2.9 1.2 3.1c.1.2 2.1 3.2 5.1 4.5.7.3 1.3.5 1.7.6.7.2 1.4.2 1.9.1.6-.1 1.8-.7 2-1.4.2-.7.2-1.3.2-1.4-.1-.1-.3-.2-.6-.3z"/></svg>
        Falar com o comercial no WhatsApp
      </a>
      <a class="as-btn as-btn--ghost" href="{{ $mailto }}">Prefiro por e-mail</a>
    </div>
    <p class="as-exp__contato">WhatsApp {{ preg_replace('/^55(\d{2})(\d{4})(\d{4})$/', '($1) $2-$3', config('assinante.whatsapp_comercial')) }} · {{ $email }}</p>
  </div>

  <div class="as-exp__grid">
    <div class="as-exp__card">
      <p class="as-player__eyebrow">Sua última assinatura</p>
      @if($ultima)
        <dl class="as-exp__dl">
          <dt>Plano</dt><dd>{{ $ultima->plano ?: '—' }}</dd>
          <dt>Início</dt><dd>{{ $ultima->start_date?->format('d/m/Y') ?: '—' }}</dd>
          <dt>{{ $cancelada ? 'Cancelada em' : 'Venceu em' }}</dt><dd>{{ $fim?->format('d/m/Y') ?: '—' }}</dd>
          <dt>Situação</dt><dd><span class="as-exp__estado" style="--cor:{{ $ultima->estadoColor() }}">{{ $ultima->estadoLabel() }}</span></dd>
        </dl>
      @else
        <p class="as-muted">Não encontramos o registro da sua assinatura.</p>
      @endif
    </div>

    <div class="as-exp__card">
      <p class="as-player__eyebrow">O que a renovação libera</p>
      <ul class="as-exp__lista">
        <li><b>{{ $meta['paineis'] + $meta['modular'] }}</b> itens no catálogo</li>
        <li><b>{{ $meta['minisserie'] }}</b> painéis de minisséries</li>
        <li><b>{{ $meta['gravado'] }}</b> painéis de cursos gravados</li>
        <li><b>{{ $meta['modular'] }}</b> cursos modulares com resumo, cartões e prova</li>
        <li>Seu progresso fica guardado — você continua de onde parou.</li>
      </ul>
    </div>
  </div>

  <p class="as-muted" style="margin-top:6px;">
    Comprou uma minissérie avulsa? Ela continua no seu <a href="{{ route('dashboard') }}" style="color:var(--as-blue-2);">AVA de matrículas</a>.
  </p>
</div>
@endsection
