@extends('layouts.site')

@section('meta_title', 'Unyflex Digital — Capacitação Prática para Servidores Públicos')
@section('meta_description', 'Miniséries de 10 a 20 minutos para pregoeiros, gestores de contratos, auditores e equipes municipais. Conteúdo aplicável no órgão no dia seguinte. Reconhecido pelo MEC.')

@section('content')

{{-- ================================================================
     1. HERO — promessa direta + CTA principal + prova social curta
     ================================================================ --}}
<section class="hero-section" id="hero">
  <div class="container">
    <div class="row align-items-center g-5">

      <div class="col-lg-6">
        <div class="hero-eyebrow aos-fade">
          <span class="dot"></span>
          <span>Miniséries para gestão pública · Reconhecido pelo MEC</span>
        </div>

        <h1 class="hero-title aos-fade aos-delay-1">
          Aprenda rotinas práticas de gestão pública em
          <span class="highlight">cápsulas de 20 minutos</span>
        </h1>

        <p class="hero-subtitle aos-fade aos-delay-2">
          Conteúdo aplicável no seu órgão no dia seguinte. Licitações, contratos, auditoria,
          controle interno, LGPD e patrimônio público — direto ao que você precisa para
          decidir com mais segurança.
        </p>

        <div class="hero-cta-group aos-fade aos-delay-3">
          {{-- CTA principal --}}
          <a href="{{ route('checkout') }}" class="btn-ux btn-ux-primary btn-ux-lg">
            <i data-lucide="zap" style="width:18px;height:18px;fill:currentColor;stroke:none;"></i>
            Ver planos e garantir acesso
          </a>
          {{-- CTA secundário --}}
          <a href="{{ route('cursos') }}" class="btn-ux btn-ux-ghost btn-ux-lg">
            Conhecer catálogo
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

      {{-- Vídeo --}}
      <div class="col-lg-6 aos-fade aos-delay-2">
        <div style="position:relative;border-radius:16px;overflow:hidden;background:#0a0f1e;border:1px solid rgba(59,130,246,0.25);box-shadow:0 0 40px rgba(59,130,246,0.12);aspect-ratio:16/9;width:100%;">
          <video id="heroVideo" src="https://unyflex.com.br/storage/fav/IMG_1902.mp4"
                 preload="metadata" playsinline controls
                 style="width:100%;height:100%;object-fit:cover;display:block;"></video>

          <div id="heroVideoOverlay" onclick="playHeroVideo()"
               style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,rgba(5,10,30,0.72),rgba(10,20,50,0.55));cursor:pointer;transition:background 0.3s;"
               onmouseover="this.style.background='linear-gradient(135deg,rgba(5,10,30,0.45),rgba(10,20,50,0.3))'"
               onmouseout="this.style.background='linear-gradient(135deg,rgba(5,10,30,0.72),rgba(10,20,50,0.55))'">
            <div style="width:72px;height:72px;border-radius:50%;background:rgba(255,255,255,0.95);display:flex;align-items:center;justify-content:center;animation:pulsePlay 2s infinite;">
              <svg viewBox="0 0 24 24" fill="#1d4ed8" width="32" height="32"><path d="M8 5v14l11-7z"/></svg>
            </div>
          </div>
          <div style="position:absolute;bottom:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#2563eb,#38bdf8,#2563eb);background-size:200% 100%;animation:shimmer 2.5s linear infinite;"></div>
        </div>

        <div style="display:flex;gap:12px;margin-top:16px;">
          <a href="{{ route('checkout') }}" class="btn-ux btn-ux-primary btn-ux-lg" style="flex:1;justify-content:center;">
            <i data-lucide="zap" style="width:16px;height:16px;fill:currentColor;stroke:none;"></i>
            Garantir acesso agora
          </a>
          <a href="{{ route('cursos') }}" class="btn-ux btn-ux-ghost btn-ux-lg">Catálogo</a>
        </div>
      </div>

    </div>
  </div>
</section>

@once
<style>
@keyframes pulsePlay { 0%{box-shadow:0 0 0 0 rgba(59,130,246,.6)}70%{box-shadow:0 0 0 18px rgba(59,130,246,0)}100%{box-shadow:0 0 0 0 rgba(59,130,246,0)} }
@keyframes shimmer { 0%{background-position:200% 0}100%{background-position:-200% 0} }
</style>
<script>
function playHeroVideo(){
  document.getElementById('heroVideoOverlay').style.display='none';
  document.getElementById('heroVideo').play();
}
</script>
@endonce

{{-- ================================================================
     2. STATS
     ================================================================ --}}
<section class="stats-section">
  <div class="container">
    <div class="row justify-content-center text-center g-0">
      @foreach([
        ['49000','+','Servidores capacitados'],
        ['300','+','Cápsulas disponíveis'],
        ['4.9','/5','Avaliação média'],
        ['26','+','Minisséries no catálogo'],
      ] as [$num,$suf,$lbl])
      <div class="col-6 col-md-3 aos-fade">
        <div class="stat-item">
          <div class="stat-number">
            <span data-stat-number data-target="{{ $num }}" data-suffix="{{ $suf }}">0</span>
          </div>
          <div class="stat-label">{{ $lbl }}</div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ================================================================
     3. CATÁLOGO POR ÁREAS — mostrar valor concreto cedo
     ================================================================ --}}
<section class="section-py" id="catalogo-areas" style="background:var(--bg-1);border-top:1px solid var(--line-1);border-bottom:1px solid var(--line-1);">
  <div class="container">
    <div class="text-center mb-5 aos-fade">
      <div class="section-eyebrow">Catálogo por área</div>
      <h2 class="section-title">Veja alguns temas disponíveis<br><span class="text-brand-gradient">e escolha pela sua área</span></h2>
      <p class="section-subtitle mx-auto">Conteúdo organizado pelas rotinas reais da gestão pública municipal. Aplicável no seu órgão no dia seguinte.</p>
    </div>

    <div class="row g-3 mb-5">
      @foreach([
        ['icon'=>'file-text','area'=>'Licitações & Compras','temas'=>['Lei 14.133/21','Pregão Eletrônico','Pesquisa de Preços','Dispensa e Inexigibilidade'],'cor'=>'rgba(0,163,255,0.12)','borda'=>'rgba(0,163,255,0.25)'],
        ['icon'=>'clipboard-check','area'=>'Contratos Públicos','temas'=>['Gestão de Contratos','Fiscalização','Aditivos e Prorrogações','Sanções e Penalidades'],'cor'=>'rgba(43,217,161,0.10)','borda'=>'rgba(43,217,161,0.25)'],
        ['icon'=>'search','area'=>'Controle & Auditoria','temas'=>['Controle Interno','Auditoria Municipal','Prestação de Contas','Transparência Pública'],'cor'=>'rgba(138,92,246,0.10)','borda'=>'rgba(138,92,246,0.25)'],
        ['icon'=>'shield','area'=>'LGPD no Setor Público','temas'=>['LGPD Governamental','Proteção de Dados','Adequação Municipal','DPO Público'],'cor'=>'rgba(255,181,71,0.10)','borda'=>'rgba(255,181,71,0.25)'],
        ['icon'=>'box','area'=>'Patrimônio Público','temas'=>['Gestão Patrimonial','Inventário','Tombamento','Alienação'],'cor'=>'rgba(0,163,255,0.08)','borda'=>'rgba(0,163,255,0.2)'],
        ['icon'=>'cpu','area'=>'I.A. na Gestão Pública','temas'=>['I.A. para Servidores','Automação','Prompts para o Setor Público','Ferramentas Práticas'],'cor'=>'rgba(43,217,161,0.08)','borda'=>'rgba(43,217,161,0.2)'],
      ] as $i => $area)
      <div class="col-lg-4 col-md-6 aos-fade" style="transition-delay:{{ $i * 0.07 }}s;">
        <div style="background:{{ $area['cor'] }};border:1px solid {{ $area['borda'] }};border-radius:var(--r-lg);padding:22px;height:100%;">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
            <div style="width:36px;height:36px;border-radius:10px;background:{{ $area['borda'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i data-lucide="{{ $area['icon'] }}" style="width:18px;height:18px;stroke:var(--fg-1);fill:none;stroke-width:1.75;"></i>
            </div>
            <div style="font-size:14px;font-weight:700;color:#fff;">{{ $area['area'] }}</div>
          </div>
          <div style="display:flex;flex-direction:column;gap:6px;">
            @foreach($area['temas'] as $tema)
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--fg-2);">
              <div style="width:5px;height:5px;border-radius:50%;background:var(--brand-300);flex-shrink:0;"></div>
              {{ $tema }}
            </div>
            @endforeach
          </div>
        </div>
      </div>
      @endforeach
    </div>

    <div class="text-center aos-fade">
      <a href="{{ route('cursos') }}" class="btn-ux btn-ux-secondary btn-ux-lg">
        Ver catálogo completo
        <i data-lucide="arrow-right" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;"></i>
      </a>
    </div>
  </div>
</section>

{{-- ================================================================
     4. PERFIS ATENDIDOS
     ================================================================ --}}
<section class="section-py" id="perfis">
  <div class="container">
    <div class="text-center mb-5 aos-fade">
      <div class="section-eyebrow">Para quem é</div>
      <h2 class="section-title">Escolha seu perfil e veja as<br><span class="text-brand-gradient">minisséries da sua rotina</span></h2>
    </div>

    <div class="row g-3">
      @foreach([
        ['icon'=>'gavel','perfil'=>'Pregoeiro','desc'=>'Domine o pregão eletrônico, pesquisa de preços, julgamento de propostas e gestão de atas.','tags'=>['Pregão Eletrônico','Lei 14.133','Pesquisa de Preços']],
        ['icon'=>'clipboard-check','perfil'=>'Gestor de Contratos','desc'=>'Fiscalize, prorrogue e encerre contratos com segurança jurídica e sem risco de responsabilização.','tags'=>['Gestão de Contratos','Fiscalização','Aditivos']],
        ['icon'=>'search','perfil'=>'Controle Interno','desc'=>'Fortaleça o controle preventivo, prepare relatórios e antecipe irregularidades antes da auditoria.','tags'=>['Controle Interno','Transparência','Prestação de Contas']],
        ['icon'=>'trending-up','perfil'=>'Auditor Municipal','desc'=>'Aplique técnicas de auditoria adaptadas à realidade dos municípios com até 50 mil habitantes.','tags'=>['Auditoria','Riscos','Relatórios']],
        ['icon'=>'users','perfil'=>'Secretário Municipal','desc'=>'Tome decisões com base legal e reduza a exposição do gestor a responsabilizações administrativas.','tags'=>['Gestão Pública','Lei 14.133','LGPD']],
        ['icon'=>'briefcase','perfil'=>'Equipe Administrativa','desc'=>'Capacite toda a equipe com conteúdo prático, sem precisar sair do expediente ou do município.','tags'=>['Compras','Patrimônio','I.A.']],
      ] as $i => $p)
      <div class="col-lg-4 col-md-6 aos-fade" style="transition-delay:{{ $i * 0.07 }}s;">
        <div style="background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-lg);padding:24px;height:100%;transition:border-color 0.2s;"
             onmouseover="this.style.borderColor='rgba(0,163,255,0.4)'" onmouseout="this.style.borderColor='var(--line-2)'">
          <div style="width:44px;height:44px;border-radius:12px;background:rgba(0,163,255,0.10);border:1px solid rgba(0,163,255,0.20);display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
            <i data-lucide="{{ $p['icon'] }}" style="width:20px;height:20px;stroke:var(--brand-300);fill:none;stroke-width:1.75;"></i>
          </div>
          <div style="font-size:16px;font-weight:700;color:#fff;margin-bottom:8px;">{{ $p['perfil'] }}</div>
          <p style="font-size:13px;color:var(--fg-3);line-height:1.6;margin-bottom:14px;">{{ $p['desc'] }}</p>
          <div style="display:flex;flex-wrap:wrap;gap:6px;">
            @foreach($p['tags'] as $tag)
            <span style="background:rgba(0,163,255,0.08);border:1px solid rgba(0,163,255,0.18);border-radius:999px;padding:3px 10px;font-size:11px;color:var(--brand-200);">{{ $tag }}</span>
            @endforeach
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ================================================================
     5. MINISÉRIES EM DESTAQUE — com carrinho
     ================================================================ --}}
<section class="section-py" id="minisseries" style="background:var(--bg-1);border-top:1px solid var(--line-1);border-bottom:1px solid var(--line-1);">
  <div class="container">
    <div class="text-center mb-5 aos-fade">
      <div class="section-eyebrow">Catálogo</div>
      <h2 class="section-title">Miniséries em destaque</h2>
      <p class="section-subtitle mx-auto">Cápsulas de 10 a 20 minutos criadas por quem já trabalhou na gestão pública. Aplicação imediata no seu setor.</p>
    </div>

    <div class="row g-4">
      @foreach($classes as $curso)
      <div class="col-lg-4 col-md-6 aos-fade" style="transition-delay:{{ $loop->index * 0.08 }}s;">
        <div class="course-card" style="display:flex;flex-direction:column;">
          <a href="{{ route('curso.show', $curso->slug) }}" style="display:block;text-decoration:none;">
            <div class="course-card-thumb course-thumb-t"
                 style="background-image:url('https://unyflex.com.br/storage/cursos/banner/{{$curso->photo}}');background-size:cover;background-position:center;">
              @if($curso['badge'])<span class="course-card-badge novo">NOVO</span>@endif
              <span class="course-card-duration">{{$curso->workload}}H</span>
              <div class="course-card-play">
                <div style="width:52px;height:52px;border-radius:50%;background:rgba(255,255,255,0.95);color:#0072FF;display:flex;align-items:center;justify-content:center;box-shadow:0 12px 40px -8px rgba(0,163,255,0.6);">
                  <svg viewBox="0 0 24 24" style="width:20px;height:20px;fill:currentColor;margin-left:2px;"><polygon points="6 4 20 12 6 20 6 4"/></svg>
                </div>
              </div>
            </div>
          </a>
          <div class="course-card-body" style="display:flex;flex-direction:column;flex:1;">
            <div class="course-eyebrow">MINISSÉRIE</div>
            <a href="{{ route('curso.show', $curso->slug) }}" style="text-decoration:none;color:inherit;">
              <div class="course-title">{{ $curso->title }}</div>
            </a>
            <div style="display:flex;gap:8px;margin-top:auto;padding-top:14px;">
              <a href="{{ route('curso.show', $curso->slug) }}" class="btn-ux btn-ux-ghost btn-ux-sm" style="flex:0 0 auto;">Ver detalhes</a>
              <button class="btn-ux btn-ux-primary btn-ux-sm btn-add-to-cart" style="flex:1;justify-content:center;"
                      data-course-id="{{ $curso->id }}"
                      data-course-title="{{ $curso->title }}"
                      data-course-price="{{ $curso->price ?? 998 }}"
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
     6. COMO FUNCIONA — 4 passos
     ================================================================ --}}
<section class="section-py" id="como-funciona">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-5 aos-fade">
        <div class="section-eyebrow">Como funciona</div>
        <h2 class="section-title">Acesso online, aulas curtas,<br><span class="text-brand-gradient">certificado e aplicação prática</span></h2>
        <p style="font-size:16px;color:var(--fg-3);line-height:1.65;margin-bottom:20px;">
          Do acesso até a aplicação no seu setor, o caminho é direto. Sem burocracia, sem enrolação. Você escolhe as miniséries do seu interesse e começa a aprender hoje.
        </p>
        <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:28px;">
          @foreach(['Acesso online em qualquer dispositivo','Aulas de 10 a 20 minutos','Certificado emitido automaticamente','Suporte para dúvidas de matrícula e pagamento'] as $item)
          <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:var(--fg-2);">
            <i data-lucide="check-circle" style="width:16px;height:16px;stroke:var(--success);fill:none;stroke-width:1.75;flex-shrink:0;"></i>
            {{ $item }}
          </div>
          @endforeach
        </div>
        <a href="{{ route('cursos') }}" class="btn-ux btn-ux-primary btn-ux-lg">
          Escolher minha minisérie
          <i data-lucide="arrow-right" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;"></i>
        </a>
      </div>
      <div class="col-lg-6 offset-lg-1 aos-fade aos-delay-2">
        @foreach([
          ['1','Escolha o plano ou as miniséries','Navegue pelo catálogo e adicione ao carrinho as miniséries que se encaixam na sua realidade.'],
          ['2','Acesse as miniséries','Acesso liberado em menos de 5 minutos após o pagamento, em qualquer dispositivo.'],
          ['3','Assista em cápsulas rápidas','Cápsulas de 10 a 20 minutos, densas e diretas. Sem enrolação, só o que você precisa para agir.'],
          ['4','Aplique no trabalho e emita o certificado','Ao concluir, o certificado da Faculdade Unypublica é gerado automaticamente.'],
        ] as $step)
        <div class="how-step" style="{{ $loop->last ? 'margin-bottom:0;':'' }}">
          <div class="how-step-number">{{ $step[0] }}</div>
          <div class="how-step-content">
            <h4>{{ $step[1] }}</h4>
            <p>{{ $step[2] }}</p>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</section>

{{-- ================================================================
     7. PROVA SOCIAL — autoridade com contexto
     ================================================================ --}}
<section class="section-py" style="background:var(--bg-1);border-top:1px solid var(--line-1);border-bottom:1px solid var(--line-1);" id="depoimentos">
  <div class="container">

    {{-- Números de autoridade --}}
    <div class="text-center mb-5 aos-fade">
      <div class="section-eyebrow">Prova social</div>
      <h2 class="section-title">Mais de <span class="text-brand-gradient">49.000 servidores</span> capacitados</h2>
      <p class="section-subtitle mx-auto">Com uma metodologia objetiva, prática e reconhecida pelo Ministério da Educação.</p>
    </div>

    {{-- Badge MEC --}}
    <div class="row justify-content-center mb-5">
      <div class="col-lg-8 aos-fade">
        <div style="background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-xl);padding:28px 32px;display:flex;align-items:center;gap:24px;flex-wrap:wrap;">
          <div style="width:56px;height:56px;border-radius:50%;background:rgba(43,217,161,0.12);border:2px solid rgba(43,217,161,0.3);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:24px;">✓</div>
          <div style="flex:1;min-width:200px;">
            <div style="font-size:11px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--success);margin-bottom:4px;">Instituição reconhecida</div>
            <div style="font-size:18px;font-weight:800;color:#fff;margin-bottom:4px;">Faculdade Unypublica · MEC</div>
            <div style="font-size:13px;color:var(--fg-3);">Certificados com validade para progressão funcional, concursos e comprovação de capacitação.</div>
          </div>
          <div style="display:flex;flex-wrap:wrap;gap:12px;">
            @foreach(['Corpo docente com experiência em prefeituras','Conteúdo atualizado com as últimas alterações legislativas','Certificados válidos para progressão de carreira'] as $it)
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--fg-2);">
              <i data-lucide="check" style="width:14px;height:14px;stroke:var(--success);fill:none;stroke-width:2.5;flex-shrink:0;"></i>
              {{ $it }}
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    {{-- Depoimentos --}}
    <div class="row g-4">
      @foreach([
        ['init'=>'FP','name'=>'Fernanda Pereira','role'=>'Servidora Municipal','stars'=>5,'text'=>'Uma excelente experiência que vai auxiliar no meu crescimento profissional. Conteúdo direto ao ponto, sem enrolação.'],
        ['init'=>'SP','name'=>'Sonia Petrini','role'=>'Pregoeira','stars'=>5,'text'=>'Muito bom. Organização incrível, cursos ótimos e muito valiosos para o dia a dia no setor de compras.'],
        ['init'=>'BR','name'=>'Beatriz Rossini','role'=>'Gestora de Contratos','stars'=>5,'text'=>'Os cursos são maravilhosos, didáticos, excelentes professores sempre dispostos a sanar as dúvidas. Muito conhecimento adquirido.'],
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
     8. PLANOS / OFERTA COMERCIAL — depois do valor construído
     ================================================================ --}}
<section class="section-py" id="planos">
  <div class="container">
    <div class="text-center mb-5 aos-fade">
      <div class="section-eyebrow">Condição especial para novos alunos</div>
      <h2 class="section-title">Escolha sua minisérie e<br><span class="text-brand-gradient">comece ainda hoje</span></h2>
      <p class="section-subtitle mx-auto">Acesso imediato após o pagamento. Certificado válido. Garantia de 7 dias.</p>
    </div>

    <div class="row justify-content-center g-4 aos-fade">
      <div class="col-lg-5 col-md-6">
        <div style="background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-xl);padding:32px;text-align:center;">
          <div style="font-size:12px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--fg-4);margin-bottom:16px;">Por minisérie</div>
      
          {{-- Preço riscado --}}
          <div style="font-size:13px;color:var(--fg-4);text-decoration:line-through;margin-bottom:8px;">De R$ 1.990,00</div>
      
          {{-- Destaque parcelamento --}}
          <div style="background:rgba(0,163,255,0.08);border:1px solid rgba(0,163,255,0.2);border-radius:var(--r-md);padding:12px 16px;margin-bottom:10px;">
            <div style="font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--brand-300);margin-bottom:6px;">Parcelado em até</div>
            <div style="display:flex;align-items:baseline;justify-content:center;gap:4px;">
              <span style="font-family:var(--font-display);font-weight:800;font-size:28px;color:#fff;">10x</span>
              <span style="font-family:var(--font-display);font-weight:800;font-size:44px;color:#fff;line-height:1;">R$ 98</span>
              <span style="font-size:13px;color:var(--fg-3);align-self:flex-end;padding-bottom:4px;">,00</span>
            </div>
            <div style="font-size:11px;color:var(--fg-4);margin-top:4px;">sem juros no cartão</div>
          </div>
      
          {{-- Ou à vista --}}
          <div style="font-size:13px;color:var(--fg-3);margin-bottom:24px;">
            ou <strong style="color:#fff;">R$ 998</strong> à vista · acesso por 12 meses
          </div>
      
          <a href="{{ route('cursos') }}" class="btn-ux btn-ux-primary btn-ux-lg" style="width:100%;justify-content:center;margin-bottom:16px;">
            <i data-lucide="zap" style="width:16px;height:16px;fill:currentColor;stroke:none;"></i>
            Ver planos e garantir acesso
          </a>
          <a href="{{ route('cursos') }}" class="btn-ux btn-ux-ghost btn-ux-sm" style="width:100%;justify-content:center;">
            Conhecer catálogo completo
          </a>
      
          <div style="height:1px;background:var(--line-1);margin:20px 0;"></div>
      
          <div style="display:flex;flex-direction:column;gap:8px;text-align:left;">
            @foreach(['Acesso online por 12 meses','Todas as cápsulas da minisérie','Versão podcast de cada aula','Materiais, modelos e checklists','Certificado Faculdade Unypublica','Suporte pedagógico','Garantia de 7 dias'] as $it)
            <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:var(--fg-2);">
              <i data-lucide="check-circle" style="width:14px;height:14px;stroke:var(--success);fill:none;stroke-width:1.75;flex-shrink:0;"></i>
              {{ $it }}
            </div>
            @endforeach
          </div>
        </div>
      </div>

      <div class="col-lg-5 col-md-6">
        <div style="background:linear-gradient(135deg,rgba(0,114,255,0.15),rgba(0,163,255,0.08));border:1px solid rgba(0,163,255,0.3);border-radius:var(--r-xl);padding:32px;text-align:center;position:relative;">
          <div style="position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:var(--grad-brand);color:#061224;font-size:11px;font-weight:800;letter-spacing:0.1em;text-transform:uppercase;padding:4px 16px;border-radius:999px;">Mais popular</div>
          <div style="font-size:12px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--brand-300);margin-bottom:16px;">Para equipes e secretarias</div>
          <div style="font-size:28px;font-weight:800;color:#fff;line-height:1.2;margin-bottom:6px;">Plano sob medida</div>
          <div style="font-size:13px;color:var(--fg-3);margin-bottom:24px;">desconto progressivo para equipes · emissão de nota fiscal · compra por CNPJ</div>
          <a href="https://api.whatsapp.com/send/?phone=5541997587226&text=Ol%C3%A1%20gostaria%20de%20saber%20mais%20sobre%20as%20minisseries&type=phone_number&app_absent=0" target="_blank"
             class="btn-ux btn-ux-primary btn-ux-lg" style="width:100%;justify-content:center;margin-bottom:16px;">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            Falar com atendimento
          </a>
          <div style="height:1px;background:var(--line-1);margin:20px 0;"></div>
          <div style="display:flex;flex-direction:column;gap:8px;text-align:left;">
            @foreach(['Tudo do plano individual','Desconto progressivo por quantidade','Emissão de nota fiscal','Compra por CNPJ da prefeitura','Controle de acesso por equipe','Relatório de progresso da equipe','Suporte dedicado'] as $it)
            <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:var(--fg-2);">
              <i data-lucide="check-circle" style="width:14px;height:14px;stroke:var(--success);fill:none;stroke-width:1.75;flex-shrink:0;"></i>
              {{ $it }}
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ================================================================
     9. FAQ — focado em objeções de compra pública
     ================================================================ --}}
<section class="section-py" id="faq" style="background:var(--bg-1);border-top:1px solid var(--line-1);">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="text-center mb-5 aos-fade">
          <div class="section-eyebrow">Perguntas frequentes</div>
          <h2 class="section-title">Antes de comprar, tire as<br><span class="text-brand-gradient">principais dúvidas</span></h2>
          <p class="section-subtitle mx-auto">Certificado, acesso, pagamento, nota fiscal e uso por servidores públicos.</p>
        </div>

        @foreach([
          ['q'=>'Tem certificado reconhecido?','a'=>'Sim. Os certificados são emitidos pela Faculdade Unypublica, instituição reconhecida pelo Ministério da Educação (MEC). Têm validade para progressão funcional, concursos e comprovação de capacitação em órgãos públicos.'],
          ['q'=>'A instituição é reconhecida pelo MEC?','a'=>'Sim. A Unyflex Digital é o braço de educação digital da Faculdade Unypublica, instituição de ensino superior devidamente reconhecida pelo MEC. Nosso conteúdo é desenvolvido por especialistas com atuação real no setor público.'],
          ['q'=>'Emite nota fiscal?','a'=>'Sim. Emitimos nota fiscal para pessoa física e jurídica. Prefeituras e órgãos públicos podem realizar a compra via CNPJ. Entre em contato pelo WhatsApp para formalizar o processo.'],
          ['q'=>'Posso comprar pelo CNPJ da prefeitura?','a'=>'Sim. Temos processo específico para compras institucionais. A prefeitura pode adquirir acesso para um servidor ou para toda a equipe. Entre em contato pelo atendimento para receber uma proposta personalizada.'],
          ['q'=>'Serve para servidores municipais de qualquer cidade?','a'=>'Sim. O conteúdo é aplicável a municípios de qualquer porte e estado. As miniséries cobrem legislação federal e boas práticas que se aplicam independentemente da esfera administrativa.'],
          ['q'=>'Por quanto tempo tenho acesso?','a'=>'O acesso é de 12 meses a partir da data da matrícula. Durante esse período você pode assistir, revisar e baixar os materiais quantas vezes quiser.'],
          ['q'=>'Tem garantia de reembolso?','a'=>'Sim. Você tem 7 dias após a compra para solicitar reembolso integral sem precisar justificar. Basta entrar em contato com nossa equipe.'],
          ['q'=>'Posso comprar mais de uma minisérie?','a'=>'Sim. Basta adicionar ao carrinho as miniséries que desejar e finalizar em um único checkout. Cada minisérie gera um certificado separado ao ser concluída.'],
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
     10. CTA FINAL
     ================================================================ --}}
<section class="final-cta-section">
  <div class="container position-relative">
    <div class="row justify-content-center">
      <div class="col-lg-8 text-center aos-fade">
        <div class="hero-eyebrow" style="justify-content:center;">
          <span class="dot"></span>
          <span>Acesso imediato · Certificado válido · Garantia de 7 dias</span>
        </div>
        <h2 class="section-title" style="font-size:clamp(32px,4vw,52px);margin-bottom:16px;">
          Capacitação direta para quem precisa<br><span class="text-brand-gradient">decidir, executar e prestar contas</span>
        </h2>
        <p style="font-size:18px;color:var(--fg-3);line-height:1.65;margin-bottom:36px;max-width:560px;margin-left:auto;margin-right:auto;">
          Junte-se a mais de 49.000 servidores. Escolha sua minisérie, acesse agora e aplique o conteúdo no seu órgão ainda esta semana.
        </p>

        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-bottom:20px;">
          {{-- CTA principal --}}
          <a href="{{ route('checkout') }}" class="btn-ux btn-ux-primary btn-ux-lg">
            <i data-lucide="zap" style="width:18px;height:18px;fill:currentColor;stroke:none;"></i>
            Ver planos e garantir acesso
          </a>
          {{-- CTA secundário --}}
          <a href="{{ route('cursos') }}" class="btn-ux btn-ux-ghost btn-ux-lg">
            Conhecer catálogo
          </a>
          {{-- Suporte --}}
          <a href="https://api.whatsapp.com/send/?phone=5541997587226&text=Ol%C3%A1%20gostaria%20de%20saber%20mais%20sobre%20as%20minisseries&type=phone_number&app_absent=0" target="_blank" class="btn-ux btn-ux-ghost btn-ux-lg">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            Falar com atendimento
          </a>
        </div>

        <div style="display:flex;justify-content:center;gap:24px;flex-wrap:wrap;">
          @foreach(['Pagamento 100% seguro','Garantia de 7 dias','Acesso imediato','Certificado MEC'] as $tag)
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

{{-- Toast --}}
<div class="cart-toast" id="cartToast" role="status" aria-live="polite">
  <div class="cart-toast-icon">
    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
  </div>
  <div class="cart-toast-body">
    <div class="cart-toast-title">Adicionado ao carrinho!</div>
    <div class="cart-toast-sub" id="cartToastSub">Minisérie adicionada com sucesso.</div>
  </div>
  <a href="{{ route('checkout') }}" class="cart-toast-action">Ver carrinho →</a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const cart = UnyCart.getCart();

  document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
    if (cart.find(i => String(i.id) === String(btn.dataset.courseId))) {
      setInCart(btn);
    }
  });

  document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const item = {
        id:    this.dataset.courseId,
        title: this.dataset.courseTitle,
        price: parseFloat(this.dataset.coursePrice) || 998,
        thumb: this.dataset.courseThumb,
        slug:  this.dataset.courseSlug,
      };
      const result = UnyCart.addItem(item);
      if (result.added) { setInCart(this); showCartToast(item.title); }
      else { window.location.href = '{{ route('checkout') }}'; }
    });
  });

  function setInCart(btn) {
    btn.classList.add('in-cart');
    const lbl = btn.querySelector('.btn-cart-label');
    if (lbl) lbl.textContent = 'No carrinho';
    btn.setAttribute('aria-label', (btn.dataset.courseTitle ?? '') + ' — já no carrinho');
  }

  let toastTimer;
  function showCartToast(title) {
    const toast = document.getElementById('cartToast');
    const sub   = document.getElementById('cartToastSub');
    if (!toast) return;
    sub.textContent = title;
    toast.classList.add('visible');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('visible'), 4000);
  }

  document.addEventListener('cart:updated', function (e) {
    const ids = (e.detail?.cart ?? []).map(i => String(i.id));
    document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
      if (!ids.includes(String(btn.dataset.courseId))) {
        btn.classList.remove('in-cart');
        const lbl = btn.querySelector('.btn-cart-label');
        if (lbl) lbl.textContent = 'Adicionar';
      }
    });
  });
});
</script>

@endsection