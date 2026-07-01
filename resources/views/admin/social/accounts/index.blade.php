@extends('layouts.admin')
@section('title', 'Conta — Instagram')
@section('section', 'Instagram')

@section('content')
@include('admin.social._field-styles')
<div class="page">

  @include('admin.social._nav')

  <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:12px;color:var(--fg-4);">
    <a href="{{ route('admin.social.posts.index') }}" style="color:var(--fg-4);text-decoration:none;">Instagram</a>
    <span>/</span>
    <span style="color:var(--fg-2);">Conta</span>
  </div>

  @if(session('success'))
    <div style="padding:12px 16px;background:rgba(43,217,161,0.10);border:1px solid rgba(43,217,161,0.35);border-radius:var(--r-md);color:#6FE6BD;font-size:13px;font-weight:500;margin-bottom:20px;">✓ {{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div style="padding:12px 16px;background:rgba(255,92,122,0.10);border:1px solid rgba(255,92,122,0.35);border-radius:var(--r-md);color:#FF5C7A;font-size:13px;margin-bottom:20px;"><strong>Corrija os erros:</strong><ul style="margin:8px 0 0;padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  @php
    $ts = $account ? $account->tokenStatus() : 'sem_token';
    $tsMap = [
      'ok'        => ['#2BD9A1', 'Token ativo'],
      'expirando' => ['#FFB547', 'Token expira em breve'],
      'expirado'  => ['#FF5C7A', 'Token expirado — renove'],
      'sem_token' => ['#97A3B8', 'Sem token cadastrado'],
    ];
    [$tsColor, $tsLabel] = $tsMap[$ts];
  @endphp

  <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;">

    <div class="card" style="padding:24px;">
      <form action="{{ route('admin.social.accounts.update') }}" method="POST" style="display:flex;flex-direction:column;gap:16px;">
        @csrf @method('PUT')
        <h2 style="font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;margin:0;">Conta do Instagram</h2>

        <div>
          <label class="field-label">Nome</label>
          <input type="text" name="name" class="field-input" value="{{ old('name', $account->name ?? 'Unyflex Digital') }}">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div>
            <label class="field-label">Instagram Business ID</label>
            <input type="text" name="ig_user_id" class="field-input" value="{{ old('ig_user_id', $account->ig_user_id ?? '') }}">
          </div>
          <div>
            <label class="field-label">Facebook Page ID</label>
            <input type="text" name="fb_page_id" class="field-input" value="{{ old('fb_page_id', $account->fb_page_id ?? '') }}">
          </div>
        </div>

        <div>
          <label class="field-label">Token da Página (long-lived)</label>
          <textarea name="access_token" rows="3" class="field-input" placeholder="Cole aqui o token da Página. Deixe em branco para manter o atual."></textarea>
          <span style="font-size:11px;color:var(--fg-4);">Atual: <strong>{{ $account ? $account->maskedToken() : '—' }}</strong>. Cole um novo só quando renovar.</span>
        </div>

        <div style="width:160px;">
          <label class="field-label">Validade (dias)</label>
          <input type="number" name="token_days" class="field-input" value="60" min="1" max="120">
        </div>

        <button type="submit" class="btn btn-primary" style="align-self:flex-start;">Salvar conta</button>
      </form>
    </div>

    <div class="card" style="padding:20px;display:flex;flex-direction:column;gap:12px;">
      <h3 style="font-family:var(--font-display);font-weight:700;font-size:14px;color:#fff;margin:0;">Status do token</h3>
      <div style="display:inline-flex;align-items:center;gap:8px;">
        <span style="width:10px;height:10px;border-radius:50%;background:{{ $tsColor }};"></span>
        <span style="font-size:13px;color:var(--fg-2);">{{ $tsLabel }}</span>
      </div>
      @if($account && $account->token_expires_at)
        <span style="font-size:12px;color:var(--fg-4);">Expira em {{ $account->token_expires_at->format('d/m/Y') }} ({{ $account->tokenDaysLeft() }} dias).</span>
      @endif
      <span style="font-size:12px;color:var(--fg-4);line-height:1.5;">O token da Página é o que publica no Instagram. Ele dura ~60 dias — renove antes de expirar (na Fase 3 isso vira automático).</span>
    </div>

  </div>
</div>
@endsection
