@extends('layouts.site')
@section('meta_title', 'Pedido confirmado — Unyflex Digital')

@section('content')
<div style="min-height:80vh;display:flex;align-items:center;justify-content:center;padding:60px 20px;">
  <div style="max-width:480px;width:100%;text-align:center;">
    <div style="width:80px;height:80px;border-radius:50%;background:rgba(43,217,161,0.15);border:2px solid rgba(43,217,161,0.4);display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
      <i data-lucide="check" style="width:36px;height:36px;stroke:#6FE6BD;fill:none;stroke-width:2.5;"></i>
    </div>
    <h1 style="font-family:var(--font-display);font-weight:800;font-size:32px;color:#fff;margin-bottom:12px;">
      Acesso liberado! 🎉
    </h1>
    <p style="font-size:16px;color:var(--fg-3);margin-bottom:32px;line-height:1.6;">
      Seu pagamento foi confirmado e seu acesso à plataforma já está ativo. Bons estudos!
    </p>
    <a href="{{ route('dashboard') }}" class="btn-ux btn-ux-primary btn-ux-lg" style="width:100%;justify-content:center;">
      <i data-lucide="play-circle" style="width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
      Ir para minha área
    </a>
  </div>
</div>
@endsection
