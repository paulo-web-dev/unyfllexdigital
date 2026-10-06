{{--
  Âncora de preço: transmissão avulsa (apagada) × Assinatura Premium Individual (acesa).
  $resumo vem do AssinaturaVitrineService::resumo(). `compacta` = versão resumida (home).
--}}
@php
    use App\Services\AssinaturaVitrineService as V;
    $avulsa     = config('assinatura_vitrine.avulsa');
    $individual = config('assinatura_vitrine.planos.individual');
    $diferenca  = $individual['preco'] - $avulsa['preco'];
    $compacta   = $compacta ?? false;
@endphp
<section class="vt-ancora {{ $compacta ? 'vt-ancora--compacta' : '' }}" aria-labelledby="ancora-titulo">
  <div class="vt-wrap">
    <p class="vt-eyebrow">Faça a conta</p>
    <h2 id="ancora-titulo" class="vt-h2">
      Por apenas <span class="vt-grad">{{ V::brl($diferenca) }}</span> a mais, o servidor tem acesso a
      <strong>todo o catálogo</strong> durante 1 ano.
    </h2>

    <div class="vt-ancora-cards">
      <div class="vt-ancora-card vt-ancora-card--off">
        <span class="vt-ancora-rotulo">{{ $avulsa['nome'] }}</span>
        <span class="vt-ancora-preco">{{ V::brl($avulsa['preco']) }}</span>
        <span class="vt-ancora-qtd"><strong>1</strong> curso</span>
        @unless($compacta)
          <ul class="vt-ancora-lista">
            <li><x-vitrine.marca :ok="false" />Um único tema</li>
            <li><x-vitrine.marca :ok="true" />1 certificado</li>
            <li><x-vitrine.marca :ok="false" />Sem novos cursos</li>
          </ul>
        @endunless
      </div>

      <div class="vt-ancora-vs" aria-hidden="true">vs</div>

      <div class="vt-ancora-card vt-ancora-card--on">
        <span class="vt-ancora-rotulo">Assinatura Premium</span>
        <span class="vt-ancora-preco">{{ V::brl($individual['preco']) }}</span>
        <span class="vt-ancora-qtd">catálogo inteiro: <strong>{{ $resumo['cursos_marketing'] }}</strong> cursos por 12 meses</span>
        @unless($compacta)
          <ul class="vt-ancora-lista">
            <li><x-vitrine.marca :ok="true" />Todos os temas da gestão pública</li>
            <li><x-vitrine.marca :ok="true" />Certificados ilimitados</li>
            <li><x-vitrine.marca :ok="true" />Novos cursos incluídos durante a vigência</li>
          </ul>
        @endunless
        @if($resumo['custo_por_curso'])
          <span class="vt-ancora-pill">equivale a {{ V::brl($resumo['custo_por_curso']) }} por curso</span>
        @endif
      </div>
    </div>

    <div class="vt-ancora-cta">
      <x-vitrine.whatsapp class="vt-btn vt-btn-primary" content-name="Âncora: Assinatura Individual"
          :mensagem="V::mensagem('plano', ['plano' => $individual['rotulo']])">
        <i data-lucide="message-circle"></i>Quero a Assinatura Premium
      </x-vitrine.whatsapp>
      @if($compacta)
        <a href="{{ route('assinatura.planos') }}" class="vt-btn vt-btn-ghost">Ver planos e comparativo</a>
      @endif
    </div>
  </div>
</section>
