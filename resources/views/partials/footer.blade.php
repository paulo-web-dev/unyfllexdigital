<footer class="site-footer">
  <div class="container">
    <div class="row g-5">

      {{-- Coluna 1: Marca --}}
      <div class="col-lg-4 col-md-6">
        <a href="{{ route('home') }}" class="navbar-logo" style="margin-bottom:16px; display:inline-flex;">
          <div class="navbar-logo-mark">
            <img src="{{ asset('img/logo-unyflex.png') }}" alt="Unyflex Digital">
          </div>
          <div>
            <div class="navbar-brand-text">UNYFLEX <span>DIGITAL</span></div>
            <span class="navbar-brand-sub">By Faculdade Unypublica · MEC</span>
          </div>
        </a>
        <p class="footer-brand-desc">
          Plataforma de minisséries de aprendizado rápido para servidores municipais, gestores públicos,
          pregoeiros e auditores. Conteúdo que vai direto ao ponto — aplicável no dia seguinte.
        </p>
        <div style="display:flex; gap:10px;">
          <a href="#" style="width:36px;height:36px;border-radius:8px;background:var(--bg-3);border:1px solid var(--line-2);display:flex;align-items:center;justify-content:center;color:var(--fg-3);transition:all 0.2s;" onmouseover="this.style.color='#fff';this.style.borderColor='var(--line-3)'" onmouseout="this.style.color='var(--fg-3)';this.style.borderColor='var(--line-2)'">
            <i data-lucide="instagram" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
          </a>
          <a href="#" style="width:36px;height:36px;border-radius:8px;background:var(--bg-3);border:1px solid var(--line-2);display:flex;align-items:center;justify-content:center;color:var(--fg-3);transition:all 0.2s;" onmouseover="this.style.color='#fff';this.style.borderColor='var(--line-3)'" onmouseout="this.style.color='var(--fg-3)';this.style.borderColor='var(--line-2)'">
            <i data-lucide="linkedin" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
          </a>
          <a href="#" style="width:36px;height:36px;border-radius:8px;background:var(--bg-3);border:1px solid var(--line-2);display:flex;align-items:center;justify-content:center;color:var(--fg-3);transition:all 0.2s;" onmouseover="this.style.color='#fff';this.style.borderColor='var(--line-3)'" onmouseout="this.style.color='var(--fg-3)';this.style.borderColor='var(--line-2)'">
            <i data-lucide="youtube" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
          </a>
        </div>
      </div>

      {{-- Coluna 2: Navegação --}}
      <div class="col-lg-2 col-md-6 col-6">
        <div class="footer-heading">Plataforma</div>
        <ul class="footer-links">
          <li><a href="{{ route('cursos') }}">Minisséries</a></li>
          <li><a href="{{ route('cursos') }}">Catálogo completo</a></li>
          <li><a href="{{ route('checkout') }}">Planos e preços</a></li>
          <li><a href="{{ route('sobre') }}">Sobre a Unyflex</a></li>
          <li><a href="{{ route('contato') }}">Contato</a></li>
        </ul>
      </div>

      {{-- Coluna 3: Conteúdo --}}
      <div class="col-lg-2 col-md-6 col-6">
        <div class="footer-heading">Conteúdo</div>
        <ul class="footer-links">
          <li><a href="#">Pregão eletrônico</a></li>
          <li><a href="#">Lei 14.133</a></li>
          <li><a href="#">Patrimônio público</a></li>
          <li><a href="#">Auditoria e controle</a></li>
          <li><a href="#">LGPD para servidores</a></li>
          <li><a href="#">Gestão de contratos</a></li>
        </ul>
      </div>

      {{-- Coluna 4: Contato --}}
      <div class="col-lg-4 col-md-6">
        <div class="footer-heading">Fale conosco</div>
        <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:20px;">
          <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:var(--fg-3);">
            <i data-lucide="mail" style="width:16px;height:16px;stroke:var(--brand-300);fill:none;stroke-width:1.75;flex-shrink:0;"></i>
            <span>atendimento@unyflex.com.br</span>
          </div>
          <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:var(--fg-3);">
            <i data-lucide="message-circle" style="width:16px;height:16px;stroke:var(--brand-300);fill:none;stroke-width:1.75;flex-shrink:0;"></i>
            <span>WhatsApp: (41) 8898-0259</span>
          </div>
          <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:var(--fg-3);">
            <i data-lucide="map-pin" style="width:16px;height:16px;stroke:var(--brand-300);fill:none;stroke-width:1.75;flex-shrink:0;"></i>
            <span>Curitiba, PR — Brasil</span>
          </div>
        </div>

        {{-- Certificação MEC --}}
        <div class="mec-badge" style="font-size:13px;">
          <div class="mec-icon" style="width:32px;height:32px;font-size:16px;">✓</div>
          <div>
            <div style="font-size:10px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--success);opacity:0.7;">Reconhecida pelo MEC</div>
            <div style="font-size:13px;">Faculdade Unypublica</div>
          </div>
        </div>
      </div>
    </div>

    <div class="footer-divider"></div>

    <div class="footer-bottom">
      <div>
        &copy; {{ date('Y') }} Unyflex Digital — Faculdade Unypublica. Todos os direitos reservados.
      </div>
      <div style="display:flex;gap:20px;">
        <a href="#" style="color:var(--fg-4);font-size:13px;text-decoration:none;">Política de Privacidade</a>
        <a href="#" style="color:var(--fg-4);font-size:13px;text-decoration:none;">Termos de Uso</a>
      </div>
    </div>
  </div>
</footer>
