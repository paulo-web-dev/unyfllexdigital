@extends('layouts.vitrine')

@php use App\Services\AssinaturaVitrineService as V; @endphp

@section('meta_title', 'Planos — Assinatura Premium Unyflex para órgãos públicos')
@section('meta_description', 'Individual, Corporativo 5 e Corporativo 10 usuários. Pagamento anual à vista, aceita nota de empenho.')

@section('content')
  <section class="vt-hero vt-hero--curto">
    <div class="vt-wrap vt-hero-in">
      <p class="vt-eyebrow">Planos anuais · pagamento à vista</p>
      <h1 class="vt-h1">Um plano para cada <span class="vt-grad">tamanho de equipe</span></h1>
      <p class="vt-lead">Todos os planos dão acesso ao catálogo inteiro por 12 meses, com certificados ilimitados.</p>
    </div>
  </section>

  {{-- Bloco de impacto --}}
  @include('vitrine.partials.ancora')

  {{-- Cards de plano --}}
  <section class="vt-secao" id="planos" aria-labelledby="planos-titulo">
    <div class="vt-wrap">
      <h2 id="planos-titulo" class="vt-h2 vt-centro">Escolha o plano do seu órgão</h2>

      <div class="vt-planos">
        @foreach($planos as $plano)
          @php
            $extras = [];
            if ($plano->selo) $extras[] = ['icone' => 'trophy', 'texto' => $plano->selo, 'variante' => 'destaque'];
            if ($plano->economia > 0) {
                $extras[] = ['icone' => 'piggy-bank', 'texto' => 'Economize ' . V::brl($plano->economia, 0), 'variante' => 'economia'];
                $extras[] = ['icone' => 'percent', 'texto' => 'Economize ' . $plano->desconto_pct . '%', 'variante' => 'economia'];
            }
          @endphp
          <article class="vt-plano {{ $plano->destaque ? 'vt-plano--destaque' : '' }}">
            @if($plano->destaque)<span class="vt-plano-fita">{{ $plano->selo }}</span>@endif
            <h3 class="vt-plano-nome">{{ $plano->nome }}</h3>
            <p class="vt-plano-usuarios"><i data-lucide="users"></i>{{ $plano->usuarios }} {{ $plano->usuarios === 1 ? 'usuário' : 'usuários' }}</p>

            <div class="vt-plano-preco">
              @if($plano->preco_de)
                <span class="vt-plano-de">de <s>{{ V::brl($plano->preco_de) }}</s> por</span>
              @endif
              <strong>{{ V::brl($plano->preco) }}</strong><span>/ano</span>
            </div>
            <p class="vt-plano-unit">
              @if($plano->usuarios > 1)
                equivale a <strong>{{ V::brl($plano->por_usuario) }}</strong> por usuário
              @else
                à vista, por 12 meses
              @endif
            </p>

            @if($extras)
              <x-vitrine.selos :itens="[]" :extras="$extras" class="vt-selos--plano" />
            @endif

            <p class="vt-plano-chamada">{{ $plano->chamada }}</p>

            <ul class="vt-plano-recursos">
              @foreach($recursos as $r)
                <li><x-vitrine.marca :ok="true" />{{ $r }}</li>
              @endforeach
            </ul>

            <x-vitrine.whatsapp class="vt-btn {{ $plano->destaque ? 'vt-btn-primary' : 'vt-btn-outline' }} vt-btn-bloco"
                :mensagem="$plano->mensagem" :content-name="$plano->rotulo">
              <i data-lucide="message-circle"></i>Escolher plano
            </x-vitrine.whatsapp>
          </article>
        @endforeach
      </div>

      <x-vitrine.selos :itens="['mec', 'empenho', 'nf', 'carga']" class="vt-selos--centro" />
    </div>
  </section>

  @include('vitrine.partials.comparativo')
  @include('vitrine.partials.calculadora')
  @include('vitrine.partials.como-contratar')
  @include('vitrine.partials.plataforma')
  @include('vitrine.partials.faq')
  @include('vitrine.partials.cta-final', ['titulo' => 'Pronto para capacitar a sua equipe?', 'botao' => 'Solicitar proposta', 'contentName' => 'Planos: CTA final'])
@endsection
