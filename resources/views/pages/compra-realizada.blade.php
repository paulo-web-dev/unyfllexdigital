@extends('layouts.site')
@section('meta_title', 'Compra realizada — Unyflex Digital')

@section('content')
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:40px 20px;">
  <div style="max-width:520px;width:100%;text-align:center;">

    {{-- Ícone de sucesso --}}
    <div style="width:80px;height:80px;border-radius:50%;background:rgba(43,217,161,0.12);border:2px solid rgba(43,217,161,0.35);display:flex;align-items:center;justify-content:center;margin:0 auto 28px;animation:pulseSuccess 2s infinite;">
      <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="#6FE6BD" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="20 6 9 17 4 12"/>
      </svg>
    </div>

    {{-- Eyebrow --}}
    <div class="hero-eyebrow" style="justify-content:center;margin-bottom:16px;">
      <span class="dot"></span>
      <span>Pagamento confirmado · Acesso liberado</span>
    </div>

    {{-- Título --}}
    <h1 style="font-family:var(--font-display);font-weight:800;font-size:clamp(28px,4vw,40px);color:#fff;letter-spacing:-0.025em;margin-bottom:16px;line-height:1.15;">
      Bem-vindo à Unyflex Digital!
    </h1>

    {{-- Texto --}}
    <p style="font-size:16px;color:var(--fg-3);line-height:1.7;margin-bottom:10px;">
      Sua compra foi realizada com sucesso. Seu acesso já está liberado e você pode começar agora mesmo.
    </p>
    <p style="font-size:15px;color:var(--fg-3);line-height:1.65;margin-bottom:32px;">
      Acesse a plataforma com o <strong style="color:#fff;">e-mail e a senha cadastrados</strong> no momento da compra. Caso seja seu primeiro acesso, sua senha é o seu <strong style="color:#fff;">CPF</strong> (somente números).
    </p>

    {{-- CTA principal --}}
    <a href="{{ route('login') }}" class="btn-ux btn-ux-primary btn-ux-lg" style="width:100%;justify-content:center;margin-bottom:14px;">
      <i data-lucide="log-in" style="width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
      Acessar minha área agora
    </a>

    {{-- Suporte --}}
    <a href="https://api.whatsapp.com/send/?phone=554195906685&text=Ol%C3%A1%20acabei%20de%20adquiri%20uma%20minisserie%20e%20gostaria%20de%20ajuda&type=phone_number&app_absent=0"
       target="_blank"
       class="btn-ux btn-ux-ghost btn-ux-sm" style="width:100%;justify-content:center;">
      <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" style="flex-shrink:0;">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
      </svg>
      Preciso de ajuda para acessar
    </a>    

    {{-- Garantias --}}
    <div style="display:flex;justify-content:center;gap:20px;flex-wrap:wrap;margin-top:28px;">
      @foreach(['Acesso imediato','Certificado válido','Suporte pedagógico'] as $tag)
      <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--fg-4);">
        <i data-lucide="shield-check" style="width:13px;height:13px;stroke:var(--success);fill:none;stroke-width:1.75;"></i>
        {{ $tag }}
      </div>
      @endforeach
    </div>

  </div>
</div>

<style>
@keyframes pulseSuccess {
  0%   { box-shadow: 0 0 0 0 rgba(43,217,161,0.4); }
  70%  { box-shadow: 0 0 0 16px rgba(43,217,161,0); }
  100% { box-shadow: 0 0 0 0 rgba(43,217,161,0); }
}
</style>
@endsection