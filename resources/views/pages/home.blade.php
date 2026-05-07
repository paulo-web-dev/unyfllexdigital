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
          <a href="{{ route('checkout') }}" class="btn-ux btn-ux-primary btn-ux-lg">
            <i data-lucide="zap" style="width:18px;height:18px;fill:currentColor;stroke:none;"></i>
            Começar agora — R$ 998
          </a>
          <a href="{{ route('cursos') }}" class="btn-ux btn-ux-ghost btn-ux-lg">
            Ver minisséries
          </a>
        </div>

        <div class="hero-proof aos-fade aos-delay-4">
          <div class="hero-proof-item">
            <i data-lucide="users" style="width:16px;height:16px;stroke:var(--brand-400);fill:none;stroke-width:1.75;"></i>
            <span>+12.000 servidores capacitados</span>
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

      <div class="col-lg-6 aos-fade aos-delay-2">
        <div class="hero-visual">
          <div class="floating-badge floating-badge-1">
            <div style="font-size:10px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--success);margin-bottom:2px;">Concluído</div>
            <div style="font-size:13px;font-weight:600;color:#fff;">Lei 14.133 na prática</div>
            <div style="display:flex;align-items:center;gap:6px;margin-top:6px;">
              <div style="height:3px;width:80px;background:rgba(255,255,255,0.1);border-radius:2px;overflow:hidden;">
                <div style="height:100%;width:100%;background:var(--grad-brand);"></div>
              </div>
              <span style="font-family:var(--font-mono);font-size:10px;color:var(--success);">100%</span>
            </div>
          </div>

          <div class="hero-card">
            <div class="hero-card-thumb">
              <div class="hero-play-btn">
                <svg viewBox="0 0 24 24" style="width:26px;height:26px;fill:currentColor;margin-left:3px;"><polygon points="6 4 20 12 6 20 6 4"/></svg>
              </div>
              <div style="position:absolute;bottom:12px;left:12px;right:12px;">
                <div style="background:rgba(5,8,15,0.75);backdrop-filter:blur(8px);border:1px solid var(--line-2);border-radius:10px;padding:8px 12px;display:flex;align-items:center;gap:10px;">
                  <div style="flex:1;height:3px;background:rgba(255,255,255,0.1);border-radius:2px;overflow:hidden;">
                    <div style="height:100%;width:42%;background:var(--grad-brand);"></div>
                  </div>
                  <span style="font-family:var(--font-mono);font-size:11px;color:var(--brand-200);">42%</span>
                </div>
              </div>
            </div>

            <div style="margin-bottom:16px;">
              <div style="font-size:10px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;color:var(--fg-4);margin-bottom:6px;">MINISSÉRIE · 12 CÁPSULAS</div>
              <div style="font-family:var(--font-display);font-weight:700;font-size:17px;color:#fff;margin-bottom:4px;">Patrimônio e Frotas Públicas com I.A.</div>
              <div style="font-size:13px;color:var(--fg-3);">Temporada 1 · Cápsula 4 de 12</div>
            </div>

            <div style="display:flex;gap:10px;">
              <a href="{{ route('checkout') }}" class="btn-ux btn-ux-primary" style="flex:1;justify-content:center;">
                <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><polygon points="6 4 20 12 6 20 6 4"/></svg>
                Continuar
              </a>
              <a href="{{ route('cursos') }}" class="btn-ux btn-ux-ghost">Ver cursos</a>
            </div>
          </div>

          <div class="floating-badge floating-badge-2">
            <div style="display:flex;align-items:center;gap:8px;">
              <div style="width:32px;height:32px;border-radius:50%;background:var(--grad-brand);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:var(--fg-on-brand);">MA</div>
              <div>
                <div style="font-size:12px;font-weight:600;color:#fff;">Maria Aparecida</div>
                <div style="font-size:10px;color:var(--brand-300);">Concluiu Pregão Avançado</div>
              </div>
            </div>
          </div>
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
            <span data-stat-number data-target="12000" data-suffix="+">0</span>
          </div>
          <div class="stat-label">Servidores capacitados</div>
        </div>
      </div>
      <div class="d-none d-md-block col-md-auto"><div class="stat-divider" style="width:1px;height:80px;margin:auto;background:var(--line-1);"></div></div>
      <div class="col-6 col-md-3 aos-fade aos-delay-1">
        <div class="stat-item">
          <div class="stat-number">
            <span data-stat-number data-target="184" data-suffix="+">0</span>
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
            <span data-stat-number data-target="26" data-suffix="+">0</span>
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
            @foreach([['50+','Especialistas no corpo docente'],['8 anos','De experiência em EaD pública'],['99%','Taxa de satisfação dos alunos'],['24h','Suporte pedagógico']] as [$num,$lbl])
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
     PROMOÇÃO COM COUNTDOWN
     ================================================================ --}}
<section class="promo-section" id="oferta">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="offer-card">
          <div class="row g-5 align-items-center">
            <div class="col-lg-6 aos-fade">
              <div class="offer-badge">🔥 Promoção por tempo limitado</div>
              <h2 style="font-family:var(--font-display);font-weight:800;font-size:clamp(26px,3vw,36px);color:#fff;letter-spacing:-0.025em;margin-bottom:12px;">
                Trilha Completa de Gestão Pública
              </h2>
              <p style="font-size:16px;color:var(--fg-3);line-height:1.65;margin-bottom:20px;">
                Acesso a <strong style="color:#fff;">todas as miniséries</strong> do catálogo por 1 ano.
                Mais de 184 cápsulas, certificados válidos, materiais e suporte pedagógico.
              </p>

              <div style="margin-bottom:20px;">
                @foreach(['Acesso a 26+ miniséries completas','184+ cápsulas de 10-20 minutos','Certificados com validade institucional','Materiais, mapas mentais e checklists','Versão em podcast de cada cápsula','Suporte pedagógico por 1 ano','Atualizações legislativas inclusas'] as $item)
                <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:var(--fg-2);margin-bottom:10px;">
                  <i data-lucide="check-circle" style="width:16px;height:16px;stroke:var(--success);fill:none;stroke-width:1.75;flex-shrink:0;"></i>
                  {{ $item }}
                </div>
                @endforeach
              </div>
            </div>

            <div class="col-lg-6 aos-fade aos-delay-2">
              <div style="background:var(--bg-1);border:1px solid var(--line-2);border-radius:var(--r-xl);padding:32px;">
                <div class="offer-price-old">De R$ 1.990,00</div>
                <div class="offer-price-new"><sup>R$</sup>998<span style="font-size:22px;color:var(--fg-3);font-weight:400;">,00</span></div>
                <div class="offer-savings">Você economiza R$ 992 — 50% de desconto</div>

                <div data-countdown class="countdown-wrap">
                  <div class="countdown-label">Oferta encerra em:</div>
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

                <div class="vagas-bar">
                  <i data-lucide="alert-triangle" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;flex-shrink:0;"></i>
                  <span>Restam apenas <strong>23 vagas</strong> neste preço</span>
                </div>

                <a href="{{ route('checkout') }}" class="btn-ux btn-ux-primary btn-ux-lg" style="width:100%;justify-content:center;margin-top:20px;">
                  <i data-lucide="zap" style="width:18px;height:18px;fill:currentColor;stroke:none;"></i>
                  Garantir minha vaga agora
                </a>

                <div style="display:flex;justify-content:center;gap:20px;margin-top:14px;flex-wrap:wrap;">
                  @foreach(['Pagamento seguro','Acesso imediato','Garantia 7 dias'] as $tag)
                  <div style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--fg-4);">
                    <i data-lucide="shield" style="width:12px;height:12px;stroke:var(--success);fill:none;stroke-width:1.75;"></i>
                    {{ $tag }}
                  </div>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ================================================================
     MINISÉRIES EM DESTAQUE
     ================================================================ --}}
<section class="section-py" id="minisseries">
  <div class="container">
    <div class="text-center mb-5 aos-fade">
      <div class="section-eyebrow">Catálogo</div>
      <h2 class="section-title">Miniséries em destaque</h2>
      <p class="section-subtitle mx-auto">Cápsulas de 10 a 20 minutos com aplicação imediata no seu setor. Conteúdo criado por quem já trabalhou na gestão pública.</p>
    </div>

    <div class="row g-4">
      @foreach([
        ['tone'=>1,'badge'=>'MAIS VENDIDO','badgeClass'=>'badge-destaque','dur'=>'2h 48min','eyebrow'=>'MINISSÉRIE · 12 CÁPSULAS','title'=>'Patrimônio e Frotas Públicas com I.A.','desc'=>'Levantamento, auditoria e controle de bens patrimoniais usando inteligência artificial para identificar inconsistências.','progress'=>null,'cta'=>'Acessar curso','href'=>'curso-1'],
        ['tone'=>2,'badge'=>'NOVO','badgeClass'=>'badge-novo','dur'=>'1h 52min','eyebrow'=>'MINISSÉRIE · 8 CÁPSULAS','title'=>'Lei 14.133 na Prática','desc'=>'Como aplicar a Nova Lei de Licitações nos pregões eletrônicos do dia a dia da prefeitura, com exemplos reais.','progress'=>null,'cta'=>'Acessar curso','href'=>'curso-2'],
        ['tone'=>3,'badge'=>'DESTAQUE','badgeClass'=>'badge-destaque','dur'=>'3h 04min','eyebrow'=>'MINISSÉRIE · 14 CÁPSULAS','title'=>'Pregão Eletrônico Avançado','desc'=>'Estratégias de condução, análise de propostas e diligências bem documentadas para pregoeiros experientes.','progress'=>null,'cta'=>'Acessar curso','href'=>'curso-3'],
        ['tone'=>4,'badge'=>null,'badgeClass'=>'','dur'=>'1h 22min','eyebrow'=>'MINISSÉRIE · 6 CÁPSULAS','title'=>'Auditoria Contínua com Dashboards','desc'=>'Construa indicadores que apontam riscos antes que virem problema, com dashboard pronto para clonar.','progress'=>null,'cta'=>'Acessar curso','href'=>'curso-4'],
        ['tone'=>1,'badge'=>null,'badgeClass'=>'','dur'=>'2h 10min','eyebrow'=>'MINISSÉRIE · 9 CÁPSULAS','title'=>'Gestão de Contratos Públicos','desc'=>'Do recebimento à fiscalização contínua, passando por aditivos e penalidades contratuais.','progress'=>null,'cta'=>'Acessar curso','href'=>'curso-5'],
        ['tone'=>2,'badge'=>null,'badgeClass'=>'','dur'=>'46min','eyebrow'=>'CÁPSULA AVULSA','title'=>'LGPD para Servidores Municipais','desc'=>'Aplicação prática da Lei de Proteção de Dados nos processos administrativos da prefeitura.','progress'=>null,'cta'=>'Acessar curso','href'=>'curso-6'],
      ] as $i => $curso)
      <div class="col-lg-4 col-md-6 aos-fade" style="transition-delay:{{ $i * 0.08 }}s;">
        <a href="{{ route('curso.show', $curso['href']) }}" class="course-card" style="display:flex;text-decoration:none;color:inherit;">
          <div class="course-card-thumb course-thumb-t{{ $curso['tone'] }}">
            @if($curso['badge'])
            <span class="course-card-badge {{ $curso['badgeClass'] }}">{{ $curso['badge'] }}</span>
            @endif
            <span class="course-card-duration">{{ $curso['dur'] }}</span>
            <div class="course-card-play">
              <div style="width:52px;height:52px;border-radius:50%;background:rgba(255,255,255,0.95);color:#0072FF;display:flex;align-items:center;justify-content:center;box-shadow:0 12px 40px -8px rgba(0,163,255,0.6);">
                <svg viewBox="0 0 24 24" style="width:20px;height:20px;fill:currentColor;margin-left:2px;"><polygon points="6 4 20 12 6 20 6 4"/></svg>
              </div>
            </div>
          </div>
          <div class="course-card-body">
            <div class="course-eyebrow">{{ $curso['eyebrow'] }}</div>
            <div class="course-title">{{ $curso['title'] }}</div>
            <p class="course-desc">{{ $curso['desc'] }}</p>
            <button class="course-card-cta">
              {{ $curso['cta'] }}
              <i data-lucide="arrow-right" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;"></i>
            </button>
          </div>
        </a>
      </div>
      @endforeach
    </div>

    <div class="text-center mt-5 aos-fade">
      <a href="{{ route('cursos') }}" class="btn-ux btn-ux-secondary btn-ux-lg">
        Ver catálogo completo — 26 miniséries
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
        <a href="{{ route('checkout') }}" class="btn-ux btn-ux-primary btn-ux-lg">
          Começar agora
          <i data-lucide="arrow-right" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;"></i>
        </a>
      </div>
      <div class="col-lg-6 offset-lg-1 aos-fade aos-delay-2">
        <div class="how-step">
          <div class="how-step-number">1</div>
          <div class="how-step-content">
            <h4>Acesso imediato após o pagamento</h4>
            <p>Em menos de 5 minutos após confirmar o pagamento, você já tem acesso a todo o catálogo na plataforma. Sem burocracia.</p>
          </div>
        </div>
        <div class="how-step">
          <div class="how-step-number">2</div>
          <div class="how-step-content">
            <h4>Escolha a minisérie pelo seu desafio atual</h4>
            <p>Está preparando um pregão? Com uma auditoria chegando? Vai assinar um contrato? Há uma minisérie específica para cada situação.</p>
          </div>
        </div>
        <div class="how-step">
          <div class="how-step-number">3</div>
          <div class="how-step-content">
            <h4>Assista cápsulas de 10 a 20 minutos</h4>
            <p>Cada cápsula é densa e direta ao ponto. Sem enrolação, sem papo de professor. Só o que você precisa para agir.</p>
          </div>
        </div>
        <div class="how-step">
          <div class="how-step-number">4</div>
          <div class="how-step-content">
            <h4>Aplique com o checklist no mesmo dia</h4>
            <p>Cada cápsula vem com um checklist preenchível e materiais prontos. Você termina a aula e já tem o que precisa para executar.</p>
          </div>
        </div>
        <div class="how-step" style="margin-bottom:0;">
          <div class="how-step-number">5</div>
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
        ['init'=>'MA','name'=>'Maria Aparecida S.','role'=>'Pregoeira · Prefeitura de Campinas/SP','stars'=>5,'text'=>'Fiz a minisérie de Pregão Eletrônico Avançado antes de uma licitação complexa. No dia da sessão, sabia exatamente o que fazer com cada fase. O conteúdo é diferente de tudo que estudei antes — aplicável de verdade.'],
        ['init'=>'RO','name'=>'Roberto Oliveira','role'=>'Gestor de Contratos · Câmara Municipal','stars'=>5,'text'=>'A cápsula sobre aditivos contratuais valeu mais do que uma pós-graduação inteira. Em 18 minutos aprendi o que precisava para revisar três contratos que estavam com problemas. Recomendo para qualquer servidor de compras.'],
        ['init'=>'CL','name'=>'Claudia Lima','role'=>'Auditora · TCE-PR','stars'=>5,'text'=>'O conteúdo sobre documentação auditável é o melhor que já encontrei sobre o tema. Apliquei os três princípios da cápsula e consegui estruturar um fluxo que antes era um caos. A equipe adorou.'],
        ['init'=>'JS','name'=>'João Silva','role'=>'Contador · Secretaria Municipal de Finanças','stars'=>5,'text'=>'Estava com uma auditoria chegando e precisava de conteúdo rápido. A minisérie de Auditoria com Dashboards me deu o que precisava. O dashboard que eles deram de modelo está em uso no meu setor até hoje.'],
        ['init'=>'AN','name'=>'Ana Nascimento','role'=>'Chefe de Patrimônio · Pref. de Guarulhos','stars'=>5,'text'=>'A questão do patrimônio com I.A. é exatamente o que precisávamos. Implementamos o processo de levantamento da cápsula 1.3 e reduzimos o tempo de inventário em 60%. Impressionante para um conteúdo de 15 minutos.'],
        ['init'=>'FM','name'=>'Felipe Martins','role'=>'Pregoeiro · Governo do Estado do Paraná','stars'=>5,'text'=>'Formato perfeito para quem trabalha com demanda alta. Assisto uma cápsula por dia antes do trabalho. Em um mês de plataforma já me sinto mais seguro em todas as fases do pregão. Valeu cada centavo.'],
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
          ['q'=>'Os certificados têm validade?','a'=>'Sim. Os certificados são emitidos pela Faculdade Unypublica, instituição reconhecida pelo MEC, com validade para progressão funcional, concursos e comprovação de capacitação.'],
          ['q'=>'Posso assistir pelo celular?','a'=>'Sim. A plataforma é 100% responsiva. Funciona em celular, tablet e computador. Muitos alunos assistem no transporte público ou durante o almoço.'],
          ['q'=>'O que acontece depois de 1 ano?','a'=>'Você pode renovar o acesso com condições especiais para alunos ativos. As miniséries concluídas e certificados permanecem disponíveis independentemente da renovação.'],
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
     FINAL CTA
     ================================================================ --}}
<section class="final-cta-section">
  <div class="container position-relative">
    <div class="row justify-content-center">
      <div class="col-lg-8 text-center aos-fade">
        <div class="hero-eyebrow" style="justify-content:center;">
          <span class="dot"></span>
          <span>Oferta ativa · Vagas limitadas</span>
        </div>
        <h2 class="section-title" style="font-size:clamp(32px,4vw,52px);margin-bottom:16px;">
          Sua próxima licitação pode ser<br><span class="text-brand-gradient">a mais segura da sua carreira</span>
        </h2>
        <p style="font-size:18px;color:var(--fg-3);line-height:1.65;margin-bottom:36px;max-width:580px;margin-left:auto;margin-right:auto;">
          Junte-se a mais de 12.000 servidores que já transformaram sua atuação profissional.
          Acesso imediato, certificado válido, risco zero com garantia de 7 dias.
        </p>

        <div style="background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-xl);padding:32px;max-width:480px;margin:0 auto 32px;">
          <div class="offer-price-old">De R$ 1.990,00</div>
          <div class="offer-price-new" style="font-size:48px;"><sup>R$</sup>998<span style="font-size:20px;color:var(--fg-3);font-weight:400;">,00</span></div>
          <div class="offer-savings">50% de desconto · Acesso por 1 ano</div>

          <div data-countdown class="countdown-wrap" style="margin:20px 0;">
            <div class="countdown-label">Oferta expira em:</div>
            <div class="countdown-timer">
              <div class="countdown-unit"><span class="countdown-num" data-cd-days>07</span><div class="countdown-lbl">Dias</div></div>
              <div class="countdown-sep">:</div>
              <div class="countdown-unit"><span class="countdown-num" data-cd-hours>00</span><div class="countdown-lbl">Horas</div></div>
              <div class="countdown-sep">:</div>
              <div class="countdown-unit"><span class="countdown-num" data-cd-mins>00</span><div class="countdown-lbl">Min</div></div>
              <div class="countdown-sep">:</div>
              <div class="countdown-unit"><span class="countdown-num" data-cd-secs>00</span><div class="countdown-lbl">Seg</div></div>
            </div>
          </div>

          <a href="{{ route('checkout') }}" class="btn-ux btn-ux-primary btn-ux-lg" style="width:100%;justify-content:center;">
            <i data-lucide="zap" style="width:18px;height:18px;fill:currentColor;stroke:none;"></i>
            Garantir minha vaga agora
          </a>

          <div class="vagas-bar" style="margin-top:14px;">
            <i data-lucide="alert-triangle" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;flex-shrink:0;"></i>
            <span>Restam apenas <strong>23 vagas</strong> neste preço</span>
          </div>
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

@endsection
