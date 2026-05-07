<div class="popup-overlay" id="promoPopup">
  <div class="popup-card">
    <button class="popup-close" data-popup-close>×</button>

    <div style="text-align:center; margin-bottom:24px;">
      <div class="offer-badge" style="justify-content:center; margin-bottom:12px;">
        <span>Oferta exclusiva</span>
      </div>
      <div class="navbar-logo-mark" style="width:56px;height:56px;margin:0 auto 16px;">
        <img src="{{ asset('img/logo-unyflex.png') }}" alt="Unyflex Digital">
      </div>
      <h3 style="font-family:var(--font-display);font-weight:800;font-size:24px;color:#fff;letter-spacing:-0.02em;margin-bottom:8px;">
        Antes de ir embora…
      </h3>
      <p style="color:var(--fg-3);font-size:15px;line-height:1.6;">
        A minisérie mais vendida para servidores públicos está com
        <strong style="color:#fff;">50% de desconto por tempo limitado.</strong>
      </p>
    </div>

    <div style="background:var(--bg-1);border:1px solid var(--line-2);border-radius:var(--r-lg);padding:20px;margin-bottom:20px;text-align:center;">
      <div class="offer-price-old">De R$ 1.990,00</div>
      <div class="offer-price-new" style="font-size:42px;">
        <sup>R$</sup>998<span style="font-size:20px;color:var(--fg-3);font-weight:400;">,00</span>
      </div>
      <div class="offer-savings">Você economiza R$ 992,00 — 50% OFF</div>
    </div>

    <div data-countdown class="countdown-wrap" style="margin-bottom:20px;">
      <div class="countdown-label">Esta oferta expira em:</div>
      <div class="countdown-timer">
        <div class="countdown-unit">
          <span class="countdown-num" data-cd-days>07</span>
          <div class="countdown-lbl">Dias</div>
        </div>
        <div class="countdown-sep">:</div>
        <div class="countdown-unit">
          <span class="countdown-num" data-cd-hours>00</span>
          <div class="countdown-lbl">Horas</div>
        </div>
        <div class="countdown-sep">:</div>
        <div class="countdown-unit">
          <span class="countdown-num" data-cd-mins>00</span>
          <div class="countdown-lbl">Min</div>
        </div>
        <div class="countdown-sep">:</div>
        <div class="countdown-unit">
          <span class="countdown-num" data-cd-secs>00</span>
          <div class="countdown-lbl">Seg</div>
        </div>
      </div>
    </div>

    <a href="{{ route('checkout') }}" class="btn-ux btn-ux-primary btn-ux-lg" style="width:100%;justify-content:center;">
      <i data-lucide="zap" style="width:18px;height:18px;fill:currentColor;stroke:none;"></i>
      Quero minha vaga agora
    </a>

    <button data-popup-close style="width:100%;background:none;border:none;color:var(--fg-4);font-size:13px;margin-top:14px;cursor:pointer;padding:8px;">
      Não, prefiro pagar mais depois
    </button>
  </div>
</div>
