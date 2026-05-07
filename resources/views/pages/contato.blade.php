@extends('layouts.site')
@section('meta_title', 'Contato — Unyflex Digital')

@section('content')
<div style="padding-top:112px; padding-bottom:80px;">
  <div class="container">
    <div class="row g-5 align-items-start">

      <div class="col-lg-5 aos-fade">
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
            <div style="font-size:14px;color:var(--fg-3);">contato@unyflexdigital.com.br</div>
            <div style="font-size:12px;color:var(--fg-4);margin-top:2px;">Resposta em até 24h úteis</div>
          </div>
        </div>

        <div class="contact-item">
          <div class="contact-icon"><i data-lucide="message-circle" style="width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:1.75;"></i></div>
          <div>
            <div style="font-weight:600;color:#fff;margin-bottom:2px;">WhatsApp</div>
            <div style="font-size:14px;color:var(--fg-3);">(11) 9 9999-0000</div>
            <div style="font-size:12px;color:var(--fg-4);margin-top:2px;">Seg–Sex das 8h às 18h</div>
          </div>
        </div>

        <div class="contact-item">
          <div class="contact-icon"><i data-lucide="map-pin" style="width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:1.75;"></i></div>
          <div>
            <div style="font-weight:600;color:#fff;margin-bottom:2px;">Endereço</div>
            <div style="font-size:14px;color:var(--fg-3);">São Paulo, SP — Brasil</div>
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

      <div class="col-lg-7 aos-fade aos-delay-2">
        <div class="checkout-card">
          <div style="font-size:11px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;color:var(--fg-4);margin-bottom:20px;">Envie uma mensagem</div>

          <div class="row g-3">
            <div class="col-md-6">
              <div class="checkout-form-group">
                <label class="checkout-label">Nome</label>
                <input type="text" class="checkout-input" placeholder="Seu nome completo">
              </div>
            </div>
            <div class="col-md-6">
              <div class="checkout-form-group">
                <label class="checkout-label">E-mail</label>
                <input type="email" class="checkout-input" placeholder="seu@email.com">
              </div>
            </div>
            <div class="col-md-6">
              <div class="checkout-form-group">
                <label class="checkout-label">WhatsApp (opcional)</label>
                <input type="tel" class="checkout-input" placeholder="(11) 9 0000-0000">
              </div>
            </div>
            <div class="col-md-6">
              <div class="checkout-form-group">
                <label class="checkout-label">Assunto</label>
                <select class="checkout-input" style="cursor:pointer;">
                  <option>Dúvidas sobre miniséries</option>
                  <option>Planos para equipes</option>
                  <option>Certificados e validade</option>
                  <option>Suporte técnico</option>
                  <option>Parcerias institucionais</option>
                  <option>Outro assunto</option>
                </select>
              </div>
            </div>
            <div class="col-12">
              <div class="checkout-form-group">
                <label class="checkout-label">Mensagem</label>
                <textarea class="checkout-input" rows="5" placeholder="Descreva sua dúvida ou necessidade…" style="resize:none;"></textarea>
              </div>
            </div>
            <div class="col-12">
              <button class="btn-ux btn-ux-primary btn-ux-lg" style="width:100%;justify-content:center;">
                <i data-lucide="send" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
                Enviar mensagem
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
