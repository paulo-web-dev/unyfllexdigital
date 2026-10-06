@extends('layouts.vitrine')

@section('meta_title', 'Assinatura Premium Unyflex — Capacitação contínua para órgãos públicos')
@section('meta_description', $resumo['cursos_marketing'] . ' cursos para servidores de câmaras e prefeituras em uma única contratação. Aceita nota de empenho e emite NF para órgão público.')

@section('content')
  {{-- Hero --}}
  <section class="vt-hero vt-hero--home">
    <div class="vt-wrap vt-hero-in">
      <h1 class="vt-h1">Assinatura Unyflex <span class="vt-grad">Premium</span></h1>
      <p class="vt-hero-sub">Mais de {{ $resumo['cursos_centena'] }} cursos e transmissões ao vivo</p>
      <p class="vt-hero-preco">
        <span class="vt-hero-preco-por">por apenas</span>
        <strong>{{ \App\Services\AssinaturaVitrineService::brl(config('assinatura_vitrine.planos.individual.preco'), 0) }}</strong>
        <span class="vt-hero-preco-unid">/ano por usuário</span>
      </p>
      <div class="vt-hero-ctas">
        <x-vitrine.whatsapp class="vt-btn vt-btn-primary vt-btn-lg" content-name="Hero: Assinar agora">
          <i data-lucide="message-circle"></i>Assinar agora
        </x-vitrine.whatsapp>
        <a href="{{ route('assinatura.planos') }}" class="vt-btn vt-btn-ghost vt-btn-lg">Ver planos</a>
      </div>
      <x-vitrine.selos :itens="config('assinatura_vitrine.selos_hero')" class="vt-selos--hero" />
    </div>
  </section>

  @include('vitrine.partials.plataforma')

  {{-- Âncora resumida --}}
  @include('vitrine.partials.ancora', ['compacta' => true])

  {{-- Carrosséis por categoria, com a faixa intermediária depois das 4 primeiras --}}
  <div class="vt-catalogo" id="cursos">
    <div class="vt-wrap">
      <p class="vt-eyebrow">Catálogo</p>
      <h2 class="vt-h2">Cursos incluídos na assinatura</h2>
    </div>

    @foreach($carrosseis as $faixa)
      @include('vitrine.partials.carrossel', ['faixa' => $faixa])

      @if($loop->iteration === 4 && ! $loop->last)
        <section class="vt-faixa-meio">
          <div class="vt-wrap vt-faixa-meio-in">
            <div>
              <strong>{{ \App\Services\AssinaturaVitrineService::brl(config('assinatura_vitrine.avulsa.preco')) }}</strong> em 1 transmissão avulsa
              <span class="vt-faixa-meio-x">ou</span>
              <strong class="vt-grad">{{ \App\Services\AssinaturaVitrineService::brl(config('assinatura_vitrine.planos.individual.preco')) }}</strong>
              em {{ $resumo['cursos_marketing'] }} cursos por 12 meses
            </div>
            <a href="{{ route('assinatura.planos') }}" class="vt-btn vt-btn-ghost vt-btn-sm">Ver o comparativo</a>
          </div>
        </section>
      @endif
    @endforeach

    <div class="vt-wrap vt-centro">
      <a href="{{ route('assinatura.categorias') }}" class="vt-btn vt-btn-ghost">Ver todas as {{ $resumo['categorias'] }} categorias</a>
    </div>
  </div>

  @include('vitrine.partials.cta-final')
@endsection
