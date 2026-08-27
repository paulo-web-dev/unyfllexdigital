@extends('layouts.assinante')
@section('title', 'Gestão AMAI — Unyflex Digital')
@section('section', 'Gestão AMAI')

@section('content')
<div class="as-vazio" style="max-width:620px;">
  <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
  <p><strong style="color:var(--as-fg);">A gestão AMAI ainda não está disponível.</strong></p>
  <p>A estrutura de acessos está sendo ativada. Tente novamente mais tarde ou fale com a Unyflex.</p>
  <p style="margin-top:14px;"><a class="as-btn as-btn--ghost" href="{{ route('assinante.home') }}">Voltar ao catálogo</a></p>
</div>
@endsection
