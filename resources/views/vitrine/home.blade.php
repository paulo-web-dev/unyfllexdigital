@extends('layouts.vitrine')

@section('meta_title', 'Assinatura Premium Unyflex — Capacitação contínua para órgãos públicos')
@section('meta_description', $resumo['cursos_marketing'] . ' cursos para servidores de câmaras e prefeituras em uma única contratação. Aceita nota de empenho e emite NF para órgão público.')

@section('content')
  {{-- Hero --}}
  <section class="vt-hero">
    <div class="vt-wrap vt-hero-in">
      <p class="vt-eyebrow">Assinatura Premium Unyflex · para câmaras e prefeituras</p>
      <h1 class="vt-h1">Capacitação contínua para toda a sua equipe, <span class="vt-grad">em uma única contratação</span></h1>
      <p class="vt-lead">
        {{ $resumo['cursos_marketing'] }} cursos de gestão pública, licitações, controle interno, legislativo e mais,
        com certificado, por 12 meses. O seu órgão contrata uma vez e capacita os servidores o ano inteiro.
      </p>
      <div class="vt-hero-ctas">
        <x-vitrine.whatsapp class="vt-btn vt-btn-primary vt-btn-lg" content-name="Hero: Solicitar proposta">
          <i data-lucide="message-circle"></i>Solicitar proposta
        </x-vitrine.whatsapp>
        <a href="{{ route('assinatura.planos') }}" class="vt-btn vt-btn-ghost vt-btn-lg">Ver planos</a>
      </div>
      <x-vitrine.selos />
    </div>
  </section>

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
