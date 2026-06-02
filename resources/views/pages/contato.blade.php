@extends('layouts.site')
@section('meta_title', 'Contato — Unyflex')

@section('content')
<div style="padding-top:112px; padding-bottom:80px;">
  <div class="container">
    <div class="row g-5 align-items-start justify-content-center">

      <div class="col-lg-7 aos-fade">
        <div class="section-eyebrow">Fale conosco</div>
        <h1 class="section-title" style="font-size:clamp(28px,3.5vw,44px);">Estamos aqui para ajudar</h1>
        <p style="font-size:16px;color:var(--fg-3);line-height:1.7;margin-bottom:36px;">
          Tem dúvidas sobre as miniséries, planos para equipes ou quer saber mais sobre nossos certificados?
          Nossa equipe responde em até 24h em dias úteis.
        </p>

        <div class="contact-item">
          <div class="contact-icon"><i data-lucide="mail" style="width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:1.75;"></i></div>
          <div>
            <div style="font-weight:600;color:#fff;margin-bottom:2px;">E-mail</div>
            <a href="mailto:atendimento@unyflex.com.br" style="font-size:14px;color:var(--fg-3);text-decoration:none;">atendimento@unyflex.com.br</a>
            <div style="font-size:12px;color:var(--fg-4);margin-top:2px;">Resposta em até 24h úteis</div>
          </div>
        </div>

        <div class="contact-item">
          <div class="contact-icon"><i data-lucide="message-circle" style="width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:1.75;"></i></div>
          <div>
            <div style="font-weight:600;color:#fff;margin-bottom:2px;">WhatsApp</div>
            <a href="https://wa.me/554188980259" target="_blank" rel="noopener" style="font-size:14px;color:var(--fg-3);text-decoration:none;">(41) 8898-0259</a>
            <div style="font-size:12px;color:var(--fg-4);margin-top:2px;">Seg–Sex das 8h às 18h</div>
          </div>
        </div>

        <div class="contact-item">
          <div class="contact-icon"><i data-lucide="map-pin" style="width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:1.75;"></i></div>
          <div>
            <div style="font-weight:600;color:#fff;margin-bottom:2px;">Endereço</div>
            <a href="https://maps.google.com/?q=R.+Voluntários+da+Pátria,+547+-+Centro,+Curitiba+-+PR" target="_blank" rel="noopener" style="font-size:14px;color:var(--fg-3);text-decoration:none;">
              R. Voluntários da Pátria, 547 - Centro<br>Curitiba - PR, 80020-000
            </a>
          </div>
        </div>

        <div style="background:rgba(43,217,161,0.08);border:1px solid rgba(43,217,161,0.22);border-radius:var(--r-lg);padding:18px;margin-top:20px;">
          <div style="font-weight:700;color:var(--success);font-size:14px;margin-bottom:6px;">
            <i data-lucide="users" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:1.75;display:inline;margin-right:6px;"></i>
            Planos para equipes
          </div>
          <div style="font-size:14px;color:var(--fg-3);line-height:1.6;">Descontos especiais para secretarias, prefeituras e equipes a partir de 5 pessoas. Entre em contato para orçamento.</div>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection