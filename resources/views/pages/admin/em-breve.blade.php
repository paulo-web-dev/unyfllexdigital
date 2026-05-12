@extends('layouts.admin')
@section('title', $titulo)
@section('section', 'Sistema')

@section('content')
<div class="page">

  <div class="page-header">
    <div>
      <h1 class="page-title">{{ $titulo }}</h1>
      <p class="page-subtitle">Esta seção está em desenvolvimento.</p>
    </div>
  </div>

  <div class="card" style="padding:60px;text-align:center;">
    <div style="width:64px;height:64px;border-radius:16px;background:rgba(0,163,255,0.10);border:1px solid rgba(0,163,255,0.20);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
      <svg viewBox="0 0 24 24" fill="none" stroke="var(--brand-300)" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" style="width:28px;height:28px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    </div>
    <h3 style="color:var(--fg-1);margin:0 0 8px;font-family:var(--font-display);font-size:20px;">{{ $titulo }} em breve</h3>
    <p style="color:var(--fg-3);font-size:14px;margin:0 0 24px;max-width:360px;margin-left:auto;margin-right:auto;">
      Esta seção está sendo construída. Volte em breve ou fale com a equipe técnica.
    </p>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary" style="display:inline-flex;">
      ← Voltar ao dashboard
    </a>
  </div>

</div>
@endsection
