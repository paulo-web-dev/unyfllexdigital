@extends('layouts.site')
@section('meta_title', 'Sobre — Unyflex Digital')

@section('content')

<div style="padding-top:112px;">

  {{-- Hero sobre --}}
  <section class="section-py" style="background:radial-gradient(ellipse 60% 50% at 50% -5%, rgba(0,163,255,0.15), transparent 55%), var(--bg-0);">
    <div class="container">
      <div class="row justify-content-center text-center mb-5">
        <div class="col-lg-8 aos-fade">
          <div class="section-eyebrow">Nossa história</div>
          <h1 class="section-title" style="font-size:clamp(32px,4vw,52px);">
            Nascemos da gestão pública.<br><span class="text-brand-gradient">Construímos para ela.</span>
          </h1>
          <p style="font-size:18px;color:var(--fg-3);line-height:1.7;">
            A Unyflex Digital surgiu da frustração de gestores que precisavam de capacitação rápida e prática
            — não de cursos de 40 horas com teoria que nunca aparece na rotina real.
          </p>
        </div>
      </div>

      <div class="row g-4 justify-content-center">
        @foreach([['2016','Fundação da Faculdade Unypublica'],['2019','Primeiros treinamentos online para servidores'],['2021','Lançamento da Unyflex Digital'],['2024','Mais de 12.000 servidores capacitados']] as [$ano,$desc])
        <div class="col-md-3 col-6 text-center aos-fade">
          <div style="background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-lg);padding:24px 16px;box-shadow:inset 0 1px 0 rgba(255,255,255,0.04);">
            <div style="font-family:var(--font-display);font-weight:800;font-size:32px;color:var(--brand-400);margin-bottom:8px;">{{ $ano }}</div>
            <div style="font-size:13px;color:var(--fg-3);line-height:1.5;">{{ $desc }}</div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- Missão --}}
  <section class="section-py" style="background:var(--bg-1);border-top:1px solid var(--line-1);border-bottom:1px solid var(--line-1);">
    <div class="container">
      <div class="row g-5 align-items-center">
        <div class="col-lg-6 aos-fade">
          <div class="section-eyebrow">Nossa missão</div>
          <h2 class="section-title">Transformar conhecimento em resultados para o setor público</h2>
          <p style="font-size:16px;color:var(--fg-3);line-height:1.7;margin-bottom:20px;">
            Acreditamos que um servidor bem capacitado não é apenas um profissional melhor — é um agente
            de transformação que impacta diretamente a vida dos cidadãos atendidos pelo seu município.
          </p>
          <p style="font-size:16px;color:var(--fg-3);line-height:1.7;">
            Por isso criamos cápsulas de 10 a 20 minutos que vão direto ao ponto: problema real,
            solução prática, aplicação imediata.
          </p>
        </div>
        <div class="col-lg-6 aos-fade aos-delay-2">
          <div class="row g-3">
            @foreach([
              ['award','Excelência','Conteúdo revisado por especialistas com experiência real na gestão pública.'],
              ['target','Foco em aplicação','Cada cápsula termina com algo que você pode usar hoje no seu trabalho.'],
              ['users','Comunidade','Rede de servidores que aprendem juntos e trocam experiências da rotina.'],
              ['shield-check','Credibilidade','Respaldado pela Faculdade Unypublica, reconhecida pelo MEC.'],
            ] as [$ic,$t,$d])
            <div class="col-6">
              <div class="feature-card">
                <div class="feature-icon"><i data-lucide="{{ $ic }}" style="width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:1.75;"></i></div>
                <div class="feature-title" style="font-size:15px;">{{ $t }}</div>
                <div class="feature-desc" style="font-size:13px;">{{ $d }}</div>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- Faculdade Unypublica / MEC --}}
  <section class="section-py">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8 text-center aos-fade">
          <div class="section-eyebrow">Reconhecimento</div>
          <h2 class="section-title">Faculdade Unypublica</h2>
          <p style="font-size:17px;color:var(--fg-3);line-height:1.7;margin-bottom:32px;">
            A Unyflex Digital é o braço de educação digital da <strong style="color:#fff;">Faculdade Unypublica</strong>,
            instituição de ensino superior reconhecida pelo <strong style="color:#fff;">Ministério da Educação (MEC)</strong>.
            Nossos certificados têm validade institucional para progressão funcional, concursos e comprovação de capacitação profissional.
          </p>
          <div class="mec-badge" style="justify-content:center;margin:0 auto 32px;display:inline-flex;">
            <div class="mec-icon">✓</div>
            <div>
              <div style="font-size:10px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--success);opacity:0.7;">Reconhecida pelo Ministério da Educação</div>
              <div style="font-size:16px;font-weight:700;">Faculdade Unypublica</div>
            </div>
          </div>
          <div style="background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-xl);padding:32px;">
            <div class="row g-4 text-center">
              @foreach([['Portaria MEC','Reconhecimento institucional formal'],['Certificados válidos','Para progressão e concursos'],['Corpo docente','50+ especialistas em gestão pública'],['NPS 92','Satisfação dos alunos']] as [$t,$d])
              <div class="col-6 col-md-3">
                <div style="font-family:var(--font-display);font-weight:800;font-size:20px;color:#fff;margin-bottom:4px;">{{ $t }}</div>
                <div style="font-size:12px;color:var(--fg-3);">{{ $d }}</div>
              </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- CTA final --}}
  <section class="final-cta-section">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-7 text-center aos-fade">
          <h2 class="section-title" style="font-size:clamp(28px,3.5vw,44px);margin-bottom:16px;">
            Faça parte de uma comunidade que<br><span class="text-brand-gradient">transforma o setor público</span>
          </h2>
          <p style="font-size:17px;color:var(--fg-3);margin-bottom:32px;">Acesse o catálogo completo por R$ 998 e tenha 1 ano para aprender no seu ritmo.</p>
          <a href="{{ route('checkout') }}" class="btn-ux btn-ux-primary btn-ux-lg">
            <i data-lucide="zap" style="width:16px;height:16px;fill:currentColor;stroke:none;"></i>
            Garantir minha vaga
          </a>
        </div>
      </div>
    </div>
  </section>

</div>
@endsection
