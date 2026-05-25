@extends('layouts.site')

@section('meta_title', 'Unyflex Digital — Treinamentos High-Performance para Servidores Públicos')
@section('meta_description', 'Miniséries de 10 a 20 minutos para servidores municipais, gestores, pregoeiros e auditores. Aprenda e aplique no dia seguinte. Instituição reconhecida pelo MEC.')

@section('content')

{{-- ================================================================
     HERO
     ================================================================ --}}
<section class="hero-section" id="hero">
  <div class="container">
    <div class="row align-items-center g-5">

      <div class="col-lg-6">
        <div class="hero-eyebrow aos-fade">
          <span class="dot"></span>
          <span>Miniséries para gestão pública · MEC</span>
        </div>
      
        <h1 class="hero-title aos-fade aos-delay-1">
          Domine a gestão pública em
          <span class="highlight">cápsulas de 20 minutos</span>
        </h1>
      
        <p class="hero-subtitle aos-fade aos-delay-2">
          Treinamentos de alta performance para servidores municipais, pregoeiros,
          gestores e auditores que precisam de resultados — não de teoria.
          Aplicável no dia seguinte ao trabalho.
        </p>
      
        <div class="hero-cta-group aos-fade aos-delay-3">
          <a href="{{ route('cursos') }}" class="btn-ux btn-ux-primary btn-ux-lg">
            <i data-lucide="zap" style="width:18px;height:18px;fill:currentColor;stroke:none;"></i>
            Ver minisséries
          </a>
          <a href="{{ route('cursos') }}" class="btn-ux btn-ux-ghost btn-ux-lg">
            Ver catálogo completo
          </a>
        </div>
      
        <div class="hero-proof aos-fade aos-delay-4">
          <div class="hero-proof-item">
            <i data-lucide="users" style="width:16px;height:16px;stroke:var(--brand-400);fill:none;stroke-width:1.75;"></i>
            <span>+49.000 servidores capacitados</span>
          </div>
          <div class="hero-proof-item">
            <i data-lucide="shield-check" style="width:16px;height:16px;stroke:var(--brand-400);fill:none;stroke-width:1.75;"></i>
            <span>Reconhecido pelo MEC</span>
          </div>
          <div class="hero-proof-item">
            <i data-lucide="star" style="width:16px;height:16px;stroke:var(--brand-400);fill:none;stroke-width:1.75;"></i>
            <span>4.9/5 de avaliação</span>
          </div>
        </div>
      </div>
      
      {{-- COLUNA DO VÍDEO --}}
      <div class="col-lg-6 aos-fade aos-delay-2">
        <div style="
          position: relative;
          border-radius: 16px;
          overflow: hidden;
          background: #0a0f1e;
          border: 1px solid rgba(59,130,246,0.25);
          box-shadow: 0 0 40px rgba(59,130,246,0.12);
          aspect-ratio: 16/9;
          width: 100%;
        ">
          <video
            id="heroVideo"
            src="https://unyflex.com.br/storage/fav/IMG_1902.mp4"
            preload="metadata"
            playsinline
            controls
            style="width:100%; height:100%; object-fit:cover; display:block;"
          ></video>
      
          <div id="heroVideoOverlay" onclick="playHeroVideo()" style="
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(5,10,30,0.72) 0%, rgba(10,20,50,0.55) 100%);
            cursor: pointer;
            transition: background 0.3s ease;
          "
          onmouseover="this.style.background='linear-gradient(135deg,rgba(5,10,30,0.45) 0%,rgba(10,20,50,0.3) 100%)'"
          onmouseout="this.style.background='linear-gradient(135deg,rgba(5,10,30,0.72) 0%,rgba(10,20,50,0.55) 100%)'"
          >
            <div style="
              width:72px; height:72px;
              border-radius:50%;
              background:rgba(255,255,255,0.95);
              display:flex; align-items:center; justify-content:center;
              animation: pulsePlay 2s infinite;
            ">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#1d4ed8" width="32" height="32">
                <path d="M8 5v14l11-7z"/>
              </svg>
            </div>
          </div>
      
          <div style="
            position:absolute; bottom:0; left:0; right:0;
            height:3px;
            background: linear-gradient(90deg, #2563eb, #38bdf8, #2563eb);
            background-size:200% 100%;
            animation: shimmer 2.5s linear infinite;
          "></div>
        </div>
      
        <div style="display:flex; gap:12px; margin-top:16px;">
          <a href="{{ route('cursos') }}" class="btn-ux btn-ux-primary btn-ux-lg" style="flex:1; justify-content:center;">
            <i data-lucide="play" style="width:16px;height:16px;fill:currentColor;stroke:none;"></i>
            Ver minisséries
          </a>
          <a href="{{ route('cursos') }}" class="btn-ux btn-ux-ghost btn-ux-lg">
            Catálogo
          </a>
        </div>
      </div>
      
      @once
      <style>
      @keyframes pulsePlay {
        0%   { box-shadow: 0 0 0 0 rgba(59,130,246,0.6); }
        70%  { box-shadow: 0 0 0 18px rgba(59,130,246,0); }
        100% { box-shadow: 0 0 0 0 rgba(59,130,246,0); }
      }
      @keyframes shimmer {
        0%   { background-position: 200% 0; }
        100% { background-position: -200% 0; }
      }
      </style>
      
      <script>
      function playHeroVideo() {
        document.getElementById('heroVideoOverlay').style.display = 'none';
        document.getElementById('heroVideo').play();
      }
      </script>
      @endonce

      <div class="col-lg-6 aos-fade aos-delay-2">
        <div class="hero-visual">
     

        </div>
      </div>

    </div>
  </div>
</section>

{{-- ================================================================
     STATS
     ================================================================ --}}
<section class="stats-section">
  <div class="container">
    <div class="row justify-content-center text-center g-0">
      <div class="col-6 col-md-3 aos-fade">
        <div class="stat-item">
          <div class="stat-number">
            <span data-stat-number data-target="49000" data-suffix="+">0</span>
          </div>
          <div class="stat-label">Servidores capacitados</div>
        </div>
      </div>
      <div class="d-none d-md-block col-md-auto"><div class="stat-divider" style="width:1px;height:80px;margin:auto;background:var(--line-1);"></div></div>
      <div class="col-6 col-md-3 aos-fade aos-delay-1">
        <div class="stat-item">
          <div class="stat-number">
            <span data-stat-number data-target="300" data-suffix="+">0</span>
          </div>
          <div class="stat-label">Cápsulas disponíveis</div>
        </div>
      </div>
      <div class="d-none d-md-block col-md-auto"><div class="stat-divider" style="width:1px;height:80px;margin:auto;background:var(--line-1);"></div></div>
      <div class="col-6 col-md-3 aos-fade aos-delay-2">
        <div class="stat-item">
          <div class="stat-number">
            <span data-stat-number data-target="4.9" data-suffix="/5">0</span>
          </div>
          <div class="stat-label">Avaliação média</div>
        </div>
      </div>
      <div class="d-none d-md-block col-md-auto"><div class="stat-divider" style="width:1px;height:80px;margin:auto;background:var(--line-1);"></div></div>
      <div class="col-6 col-md-3 aos-fade aos-delay-3">
        <div class="stat-item">
          <div class="stat-number">
            <span data-stat-number data-target="10" data-suffix="+">0</span>
          </div>
          <div class="stat-label">Minisséries no catálogo</div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ================================================================
     AUTHORITY / MEC
     ================================================================ --}}
<section class="authority-section section-py aos-fade">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div class="section-eyebrow">Autoridade e credibilidade</div>
        <h2 class="section-title">Respaldado por uma instituição <span class="text-brand-gradient">reconhecida pelo MEC</span></h2>
        <p style="font-size:16px;color:var(--fg-3);line-height:1.7;margin-bottom:28px;">
          A Unyflex Digital é o braço de educação digital da <strong style="color:#fff;">Faculdade Unypublica</strong>,
          instituição de ensino superior reconhecida pelo Ministério da Educação (MEC).
          Nosso conteúdo é desenvolvido por especialistas com décadas de atuação no setor público.
        </p>
        <div class="mec-badge" style="margin-bottom:20px;">
          <div class="mec-icon">✓</div>
          <div>
            <div style="font-size:10px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--success);opacity:0.7;">Instituição reconhecida</div>
            <div style="font-size:15px;font-weight:700;">Faculdade Unypublica · MEC</div>
          </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:12px;">
          @foreach([
            ['icon'=>'award','text'=>'Corpo docente com experiência prática em prefeituras e órgãos federais'],
            ['icon'=>'file-check','text'=>'Certificados com validade reconhecida para progressão de carreira'],
            ['icon'=>'book-open','text'=>'Conteúdo atualizado com as últimas alterações legislativas'],
          ] as $item)
          <div style="display:flex;align-items:center;gap:12px;font-size:14px;color:var(--fg-2);">
            <div style="width:28px;height:28px;border-radius:8px;background:rgba(0,163,255,0.10);border:1px solid rgba(0,163,255,0.20);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i data-lucide="{{ $item['icon'] }}" style="width:14px;height:14px;stroke:var(--brand-300);fill:none;stroke-width:1.75;"></i>
            </div>
            {{ $item['text'] }}
          </div>
          @endforeach
        </div>
      </div>
      <div class="col-lg-6 aos-fade aos-delay-2">
        <div style="background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-xl);padding:32px;box-shadow:var(--shadow-lg);">
          <div style="font-size:11px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;color:var(--fg-4);margin-bottom:20px;">Áreas de especialização</div>
          <div style="display:flex;flex-wrap:wrap;gap:10px;">
            @foreach(['Pregão Eletrônico','Lei 14.133/21','Patrimônio Público','Auditoria e Controle','LGPD Gov.','Gestão de Contratos','Compras Públicas','Orçamento Municipal','Frota Pública','Fiscalização','I.A. no Setor Público','Pesquisa de Preços'] as $area)
            <span style="background:rgba(0,163,255,0.08);border:1px solid rgba(0,163,255,0.20);border-radius:var(--r-pill);padding:6px 12px;font-size:12px;font-weight:500;color:var(--brand-200);">{{ $area }}</span>
            @endforeach
          </div>
          <div style="height:1px;background:var(--line-1);margin:24px 0;"></div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            @foreach([['100+','Especialistas no corpo docente'],['10 anos','De experiência em EaD pública'],['99%','Taxa de satisfação dos alunos'],['Especializado','Suporte pedagógico']] as [$num,$lbl])
            <div style="background:var(--bg-3);border-radius:var(--r-md);padding:16px;">
              <div style="font-family:var(--font-display);font-weight:800;font-size:24px;color:#fff;margin-bottom:4px;">{{ $num }}</div>
              <div style="font-size:12px;color:var(--fg-3);">{{ $lbl }}</div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ================================================================
     MINISÉRIES EM DESTAQUE — COM CARRINHO
     ================================================================ --}}
<section class="section-py" id="minisseries">
  <div class="container">
    <div class="text-center mb-5 aos-fade">
      <div class="section-eyebrow">Catálogo</div>
      <h2 class="section-title">Miniséries em destaque</h2>
      <p class="section-subtitle mx-auto">Cápsulas de 10 a 20 minutos com aplicação imediata no seu setor. Conteúdo criado por quem já trabalhou na gestão pública.</p>
    </div>

    <div class="row g-4">
      @foreach($classes as $curso)
      <div class="col-lg-4 col-md-6 aos-fade" style="transition-delay:{{ $loop->index * 0.08 }}s;">
        <div class="course-card" style="display:flex;flex-direction:column;">

          {{-- Thumb clicável leva para a página do curso --}}
          <a href="{{ route('curso.show', $curso->slug) }}" style="display:block;text-decoration:none;">
            <div class="course-card-thumb course-thumb-t"
                 style="
                    background-image:url('https://unyflex.com.br/storage/cursos/banner/{{$curso->photo}}');
                    background-size:cover;
                    background-position:center;
                    background-repeat:no-repeat;
                 ">
              @if($curso['badge'])
                <span class="course-card-badge novo">NOVO</span>
              @endif
              <span class="course-card-duration">{{$curso->workload}}H</span>
              <div class="course-card-play">
                <div style="width:52px;height:52px;border-radius:50%;background:rgba(255,255,255,0.95);color:#0072FF;display:flex;align-items:center;justify-content:center;box-shadow:0 12px 40px -8px rgba(0,163,255,0.6);">
                  <svg viewBox="0 0 24 24" style="width:20px;height:20px;fill:currentColor;margin-left:2px;">
                    <polygon points="6 4 20 12 6 20 6 4"/>
                  </svg>
                </div>
              </div>
            </div>
          </a>

          <div class="course-card-body" style="display:flex;flex-direction:column;flex:1;">
            <div class="course-eyebrow">MINISSÉRIE · 70 CÁPSULAS</div>

            <a href="{{ route('curso.show', $curso->slug) }}" style="text-decoration:none;color:inherit;">
              <div class="course-title">{{ $curso->title }}</div>
            </a>

            {{-- Preço --}}
            @if(isset($curso->price) && $curso->price)
            <div class="course-price-wrap" style="margin-top:auto;padding-top:12px;">
              @if(isset($curso->price_original) && $curso->price_original)
              <div style="font-size:12px;color:var(--fg-4);text-decoration:line-through;">
                R$ {{ number_format($curso->price_original, 2, ',', '.') }}
              </div>
              @endif
              <div style="font-size:20px;font-weight:800;color:#fff;font-family:var(--font-display);">
                R$ {{ number_format($curso->price, 2, ',', '.') }}
              </div>
            </div>
            @endif

            {{-- Botões: Ver detalhes + Adicionar ao carrinho --}}
            <div style="display:flex;gap:8px;margin-top:16px;">
              <a href="{{ route('curso.show', $curso->slug) }}"
                 class="btn-ux btn-ux-ghost btn-ux-sm"
                 style="flex:0 0 auto;">
                Ver detalhes
              </a>

              <button
                class="btn-ux btn-ux-primary btn-ux-sm btn-add-to-cart"
                style="flex:1;justify-content:center;"
                data-course-id="{{ $curso->id }}"
                data-course-title="{{ $curso->title }}"
                data-course-price="{{ $curso->price ?? 0 }}"
                data-course-thumb="https://unyflex.com.br/storage/cursos/banner/{{ $curso->photo }}"
                data-course-slug="{{ $curso->slug }}"
                aria-label="Adicionar {{ $curso->title }} ao carrinho">

                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                  <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>
                <span class="btn-cart-label">Adicionar</span>
              </button>
            </div>
          </div>
        </div>
      </div>
      @endforeach 
    </div>

    <div class="text-center mt-5 aos-fade">
      <a href="{{ route('cursos') }}" class="btn-ux btn-ux-secondary btn-ux-lg">
        Ver catálogo completo 
        <i data-lucide="arrow-right" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;"></i>
      </a>
    </div>
  </div>
</section>

{{-- ================================================================
     BENEFITS
     ================================================================ --}}
<section class="section-py" style="background:var(--bg-1);border-top:1px solid var(--line-1);border-bottom:1px solid var(--line-1);" id="beneficios">
  <div class="container">
    <div class="text-center mb-5 aos-fade">
      <div class="section-eyebrow">Por que a Unyflex?</div>
      <h2 class="section-title">Treinamento que funciona no <span class="text-brand-gradient">mundo real</span></h2>
      <p class="section-subtitle mx-auto">Não é teoria de faculdade. É conteúdo construído por quem trabalhou na gestão pública e sabe o que funciona na prática.</p>
    </div>

    <div class="row g-4">
      @foreach([
        ['icon'=>'clock','title'=>'Cápsulas de 10 a 20 min','desc'=>'Formato pensado para quem não tem 3 horas por dia. Assista durante o almoço, no transporte ou entre uma demanda e outra.'],
        ['icon'=>'check-circle','title'=>'Aplicação imediata','desc'=>'Cada cápsula termina com um checklist e modelo pronto para usar no seu setor no mesmo dia.'],
        ['icon'=>'award','title'=>'Certificados válidos','desc'=>'Certificados emitidos pela Faculdade Unypublica com validade para progressão funcional e concursos.'],
        ['icon'=>'refresh-cw','title'=>'Conteúdo sempre atualizado','desc'=>'Nossa equipe atualiza as miniséries a cada mudança legislativa relevante. Você nunca estuda algo desatualizado.'],
        ['icon'=>'headphones','title'=>'Versão podcast','desc'=>'Cada cápsula tem uma versão em áudio para você absorver o conteúdo no caminho do trabalho.'],
        ['icon'=>'users','title'=>'Comunidade exclusiva','desc'=>'Acesse grupos com outros servidores, troque experiências e tire dúvidas com especialistas da área.'],
      ] as $i => $b)
      <div class="col-lg-4 col-md-6 aos-fade" style="transition-delay:{{ $i * 0.08 }}s;">
        <div class="feature-card">
          <div class="feature-icon">
            <i data-lucide="{{ $b['icon'] }}" style="width:22px;height:22px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
          </div>
          <div class="feature-title">{{ $b['title'] }}</div>
          <div class="feature-desc">{{ $b['desc'] }}</div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ================================================================
     COMO FUNCIONA
     ================================================================ --}}
<section class="section-py" id="como-funciona">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-5 aos-fade">
        <div class="section-eyebrow">Processo</div>
        <h2 class="section-title">Como funciona na prática</h2>
        <p style="font-size:16px;color:var(--fg-3);line-height:1.65;margin-bottom:36px;">
          Do acesso até a aplicação no seu setor, o caminho é direto. Sem burocracia, sem enrolação.
        </p>
        <a href="{{ route('cursos') }}" class="btn-ux btn-ux-primary btn-ux-lg">
          Escolher minha minisérie
          <i data-lucide="arrow-right" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;"></i>
        </a>
      </div>
      <div class="col-lg-6 offset-lg-1 aos-fade aos-delay-2">
        <div class="how-step">
          <div class="how-step-number">1</div>
          <div class="how-step-content">
            <h4>Escolha as miniséries do seu interesse</h4>
            <p>Navegue pelo catálogo, adicione ao carrinho as miniséries que se encaixam na sua realidade e necessidade atual.</p>
          </div>
        </div>
        <div class="how-step">
          <div class="how-step-number">2</div>
          <div class="how-step-content">
            <h4>Revise o carrinho e finalize o pagamento</h4>
            <p>Confira os itens selecionados, escolha a forma de pagamento e finalize com segurança. Acesso liberado em menos de 5 minutos.</p>
          </div>
        </div>
        <div class="how-step">
          <div class="how-step-number">3</div>
          <div class="how-step-content">
            <h4>Assista cápsulas de 10 a 20 minutos</h4>
            <p>Cada cápsula é densa e direta ao ponto. Sem enrolação, sem papo de professor. Só o que você precisa para agir.</p>
          </div>
        </div>
        <div class="how-step" style="margin-bottom:0;">
          <div class="how-step-number">4</div>
          <div class="how-step-content">
            <h4>Emita o certificado ao concluir</h4>
            <p>Ao finalizar a minisérie, o certificado da Faculdade Unypublica é gerado automaticamente e fica disponível para download.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ================================================================
     TESTIMONIALS
     ================================================================ --}}
<section class="section-py" style="background:var(--bg-1);border-top:1px solid var(--line-1);border-bottom:1px solid var(--line-1);" id="depoimentos">
  <div class="container">
    <div class="text-center mb-5 aos-fade">
      <div class="section-eyebrow">Prova social</div>
      <h2 class="section-title">O que dizem os servidores</h2>
      <p class="section-subtitle mx-auto">Mais de 12.000 servidores públicos já transformaram sua rotina com nossas miniséries.</p>
    </div>

    <div class="row g-4">
      @foreach([
        ['init'=>'FP','name'=>'Fernanda Pereira','role'=>'','stars'=>5,'text'=>'Uma excelente experiência que vai auxiliar no meu crescimento profissional.'],
        ['init'=>'SP','name'=>'Sonia Petrini','role'=>'','stars'=>5,'text'=>'Muito bom. Organização incrível cursos ótimos e muito valiosos'],
        ['init'=>'BR','name'=>'Beatriz Rossini','role'=>'','stars'=>5,'text'=>'Os cursos são maravilhosos, didáticos, excelentes Professores e Profissionais sempre dispostos a sanar as dúvidas. Muito conhecimento adquirido'],
      ] as $i => $t)
      <div class="col-lg-4 col-md-6 aos-fade" style="transition-delay:{{ $i * 0.08 }}s;">
        <div class="testimonial-card">
          <div class="testimonial-stars">{{ str_repeat('★', $t['stars']) }}</div>
          <p class="testimonial-text">"{{ $t['text'] }}"</p>
          <div class="testimonial-author">
            <div class="testimonial-avatar">{{ $t['init'] }}</div>
            <div>
              <div class="testimonial-name">{{ $t['name'] }}</div>
              <div class="testimonial-role">{{ $t['role'] }}</div>
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ================================================================
     FAQ
     ================================================================ --}}
<section class="section-py" id="faq">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="text-center mb-5 aos-fade">
          <div class="section-eyebrow">Perguntas frequentes</div>
          <h2 class="section-title">Tira suas dúvidas</h2>
        </div>

        @foreach([
          ['q'=>'Para quem são as miniséries?','a'=>'Para servidores municipais, estaduais e federais que trabalham com compras, contratos, patrimônio, auditoria e gestão. O conteúdo é aplicável a qualquer esfera e porte de município.'],
          ['q'=>'Preciso ter formação específica para acompanhar?','a'=>'Não. As miniséries são desenvolvidas para diferentes níveis de experiência. Há conteúdo introdutório e avançado. Você escolhe pela sua realidade atual.'],
          ['q'=>'Posso comprar mais de uma minisérie de uma vez?','a'=>'Sim! Basta adicionar ao carrinho todas as miniséries que desejar e finalizar o pagamento em um único checkout. Quanto mais miniséries, maior o valor investido no seu desenvolvimento.'],
          ['q'=>'Os certificados têm validade?','a'=>'Sim. Os certificados são emitidos pela Faculdade Unypublica, instituição reconhecida pelo MEC, com validade para progressão funcional, concursos e comprovação de capacitação.'],
          ['q'=>'Posso assistir pelo celular?','a'=>'Sim. A plataforma é 100% responsiva. Funciona em celular, tablet e computador. Muitos alunos assistem no transporte público ou durante o almoço.'],
          ['q'=>'Tem garantia de reembolso?','a'=>'Sim. Você tem 7 dias após a compra para solicitar reembolso integral sem precisar justificar. Basta enviar um e-mail para nossa equipe.'],
          ['q'=>'Posso comprar para minha equipe inteira?','a'=>'Sim. Temos planos para equipes e secretarias municipais com desconto progressivo. Entre em contato pelo WhatsApp para um orçamento personalizado.'],
        ] as $faq)
        <div class="faq-item aos-fade">
          <div class="faq-question">
            <span>{{ $faq['q'] }}</span>
            <div class="faq-icon">
              <i data-lucide="plus" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5;"></i>
            </div>
          </div>
          <div class="faq-answer">
            <div class="faq-answer-inner">{{ $faq['a'] }}</div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</section>

{{-- ================================================================
     FINAL CTA — orientado ao carrinho
     ================================================================ --}}
<section class="final-cta-section">
  <div class="container position-relative">
    <div class="row justify-content-center">
      <div class="col-lg-8 text-center aos-fade">
        <div class="hero-eyebrow" style="justify-content:center;">
          <span class="dot"></span>
          <span>Escolha sua minisérie · Acesso imediato</span>
        </div>
        <h2 class="section-title" style="font-size:clamp(32px,4vw,52px);margin-bottom:16px;">
          Sua próxima licitação pode ser<br><span class="text-brand-gradient">a mais segura da sua carreira</span>
        </h2>
        <p style="font-size:18px;color:var(--fg-3);line-height:1.65;margin-bottom:36px;max-width:580px;margin-left:auto;margin-right:auto;">
          Junte-se a mais de 49.000 servidores. Escolha as miniséries, adicione ao carrinho
          e tenha acesso imediato com certificado válido e garantia de 7 dias.
        </p>

        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-bottom:28px;">
          <a href="{{ route('cursos') }}" class="btn-ux btn-ux-primary btn-ux-lg">
            <i data-lucide="zap" style="width:18px;height:18px;fill:currentColor;stroke:none;"></i>
            Explorar catálogo
          </a>
          <a href="#" class="btn-ux btn-ux-ghost btn-ux-lg">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
              <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
            Ver meu carrinho
          </a>
        </div>

        <div style="display:flex;justify-content:center;gap:28px;flex-wrap:wrap;">
          @foreach(['Pagamento 100% seguro','Garantia de 7 dias','Acesso imediato','Certificado válido'] as $tag)
          <div style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--fg-3);">
            <i data-lucide="shield-check" style="width:14px;height:14px;stroke:var(--success);fill:none;stroke-width:1.75;"></i>
            {{ $tag }}
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ================================================================
     TOAST NOTIFICATION DO CARRINHO
     Adicione o CSS abaixo ao seu site.css
     ================================================================ --}}
{{--
/* Cart Toast */
.cart-toast {
  position: fixed;
  bottom: 28px;
  right: 28px;
  z-index: 9999;
  background: var(--bg-2);
  border: 1px solid var(--line-2);
  border-left: 3px solid var(--success);
  border-radius: var(--r-lg);
  padding: 14px 18px;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: var(--shadow-lg);
  min-width: 280px;
  max-width: 360px;
  transform: translateY(20px);
  opacity: 0;
  transition: transform 0.3s cubic-bezier(.16,1,.3,1), opacity 0.3s ease;
  pointer-events: none;
}
.cart-toast.visible {
  transform: translateY(0);
  opacity: 1;
  pointer-events: auto;
}
.cart-toast-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(0,200,120,0.12);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: var(--success);
}
.cart-toast-body { flex: 1; }
.cart-toast-title { font-size: 13px; font-weight: 700; color: #fff; margin-bottom: 2px; }
.cart-toast-sub { font-size: 12px; color: var(--fg-3); }
.cart-toast-action {
  font-size: 12px;
  font-weight: 600;
  color: var(--brand-300);
  text-decoration: none;
  white-space: nowrap;
}
.cart-toast-action:hover { color: var(--brand-200); }

/* Estado "no carrinho" do botão */
.btn-add-to-cart.in-cart {
  background: rgba(0,200,120,0.15);
  border-color: rgba(0,200,120,0.4);
  color: var(--success);
  pointer-events: none;
}
--}}

{{-- Toast HTML --}}
<div class="cart-toast" id="cartToast" role="status" aria-live="polite">
  <div class="cart-toast-icon">
    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <polyline points="20 6 9 17 4 12"/>
    </svg>
  </div>
  <div class="cart-toast-body">
    <div class="cart-toast-title">Adicionado ao carrinho!</div>
    <div class="cart-toast-sub" id="cartToastSub">Minisérie adicionada com sucesso.</div>
  </div>
  <a href="#" class="cart-toast-action">Ver carrinho →</a>
</div>

{{-- ================================================================
     SCRIPT: Lógica do botão "Adicionar ao carrinho"
     ================================================================ --}}
     <script>
      document.addEventListener('DOMContentLoaded', function () {
      
        // Marca botões já no carrinho ao carregar a página
        const cart = UnyCart.getCart();
      
        document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
          const id = String(btn.dataset.courseId);
      
          if (cart.find(i => String(i.id) === id)) {
            setInCart(btn);
          }
        });
      
        // Listener nos botões
        document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
      
          btn.addEventListener('click', function (e) {
            e.preventDefault();
      
            const item = {
              id:    this.dataset.courseId,
              title: this.dataset.courseTitle,
              price: parseFloat(this.dataset.coursePrice) || 0,
              thumb: this.dataset.courseThumb,
              slug:  this.dataset.courseSlug,
            };
      
            const result = UnyCart.addItem(item);
      
            // Adicionou ou já existia → vai para checkout
            if (result.added) {
              setInCart(this);
            }
      
            window.location.href = '/checkout';
          });
      
        });
      
        function setInCart(btn) {
          btn.classList.add('in-cart');
      
          const label = btn.querySelector('.btn-cart-label');
      
          if (label) {
            label.textContent = 'No carrinho';
          }
      
          btn.setAttribute(
            'aria-label',
            btn.dataset.courseTitle + ' — já no carrinho'
          );
        }
      
        // Toast
        let toastTimer;
      
        function showCartToast(title) {
          const toast = document.getElementById('cartToast');
          const sub   = document.getElementById('cartToastSub');
      
          if (!toast || !sub) return;
      
          sub.textContent = title;
      
          toast.classList.add('visible');
      
          clearTimeout(toastTimer);
      
          toastTimer = setTimeout(() => {
            toast.classList.remove('visible');
          }, 4000);
        }
      
        // Atualização do carrinho
        document.addEventListener('cart:updated', function (e) {
      
          const ids = e.detail.cart.map(i => String(i.id));
      
          document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
      
            if (!ids.includes(String(btn.dataset.courseId))) {
      
              btn.classList.remove('in-cart');
      
              const label = btn.querySelector('.btn-cart-label');
      
              if (label) {
                label.textContent = 'Adicionar';
              }
            }
      
          });
      
        });
      
      });
      </script>

@endsection
