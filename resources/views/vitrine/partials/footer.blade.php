<footer class="vt-footer">
  <div class="vt-wrap">
    <x-vitrine.selos class="vt-selos--rodape" />

    <div class="vt-footer-grid">
      <div>
        <a href="{{ route('assinatura.home') }}" class="vt-logo">
          <img src="{{ asset('img/logo-unyflex.png') }}" alt="">
          <span class="vt-logo-txt">UNYFLEX <em>PREMIUM</em></span>
        </a>
        <p class="vt-footer-desc">Capacitação contínua para servidores de câmaras municipais e prefeituras. By Faculdade Unypublica, credenciada pelo MEC.</p>
      </div>
      <div>
        <h4>Navegação</h4>
        <a href="{{ route('assinatura.home') }}">Início</a>
        <a href="{{ route('assinatura.categorias') }}">Cursos e categorias</a>
        <a href="{{ route('assinatura.planos') }}">Planos</a>
        <a href="{{ route('assinatura.planos') }}#como-contratar">Como contratar</a>
        <a href="{{ route('login') }}">Área do assinante</a>
      </div>
      <div>
        <h4>Contato</h4>
        <x-vitrine.whatsapp content-name="Rodapé: WhatsApp"><i data-lucide="message-circle"></i>{{ config('assinatura_vitrine.contato.telefone') }}</x-vitrine.whatsapp>
        <a href="mailto:{{ config('assinatura_vitrine.contato.email') }}"><i data-lucide="mail"></i>{{ config('assinatura_vitrine.contato.email') }}</a>
      </div>
    </div>
    <p class="vt-footer-copy">© {{ date('Y') }} Unyflex Digital · Faculdade Unypublica</p>
  </div>
</footer>
