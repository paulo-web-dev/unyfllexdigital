@extends('layouts.site')

@section('meta_title', 'Unyflex Digital — Capacitação Prática para Servidores Públicos')
@section('meta_description', 'Aulas de 10 a 20 minutos para pregoeiros, gestores de contratos, auditores e equipes municipais. Conteúdo aplicável no órgão no dia seguinte. Reconhecido pelo MEC.')

@section('content')

@php
  // ── Link único do WhatsApp (reaproveitado em toda a página) ──
  $waBase = 'https://api.whatsapp.com/send/?phone=554188980259&type=phone_number&app_absent=0';
  $waDuvidas   = $waBase . '&text=' . rawurlencode('Olá! Tenho dúvidas sobre as minisséries da Unyflex Digital.');
  $waEquipe    = $waBase . '&text=' . rawurlencode('Olá! Quero um plano para minha equipe/secretaria.');
  $waCertif    = $waBase . '&text=' . rawurlencode('Olá! Gostaria de saber sobre o certificado e a nota fiscal.');
  $waAjuda     = $waBase . '&text=' . rawurlencode('Olá! Preciso de ajuda para escolher a minissérie ideal para o meu cargo.');
  // SVG do ícone do WhatsApp reaproveitável
  $waIcon = '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>';
@endphp

{{-- ================================================================
     1. HERO
     ================================================================ --}}
<section class="hero-section" id="hero">
  <div class="container">
    <div class="row align-items-center g-5">

      <div class="col-lg-6">
        <div class="hero-eyebrow aos-fade">
          <span class="dot"></span>
          <span>+49.000 servidores capacitados · Reconhecido pelo MEC</span>
        </div>

        <h1 class="hero-title aos-fade aos-delay-1">
          Pare de travar diante de uma
          <span class="highlight">licitação, contrato ou auditoria</span>
        </h1>

        <p class="hero-subtitle aos-fade aos-delay-2">
          Capacitações completas que ensinam exatamente o que você precisa para
          executar com segurança no seu órgão — sem teoria interminável, sem juridiquês.
          <strong style="color:#fff;">Aplicável já no dia seguinte.</strong>
        </p>

        <div class="hero-cta-group aos-fade aos-delay-3">
          <a href="#minisseries" class="btn-ux btn-ux-primary btn-ux-lg">
            <i data-lucide="zap" style="width:18px;height:18px;fill:currentColor;stroke:none;"></i>
            Ver minisséries — a partir de 10x R$ 99
          </a>
          <a href="{{ $waDuvidas }}" target="_blank" class="btn-ux btn-ux-ghost btn-ux-lg" style="color:#25D366;border-color:rgba(37,211,102,0.4);">
            {!! $waIcon !!}
            Tirar dúvidas no WhatsApp
          </a>
        </div>

        <div class="hero-proof aos-fade aos-delay-4">
          <div class="hero-proof-item">
            <i data-lucide="clock" style="width:16px;height:16px;stroke:var(--brand-400);fill:none;stroke-width:1.75;"></i>
            <span>Acesso em 5 minutos</span>
          </div>
          <div class="hero-proof-item">
            <i data-lucide="award" style="width:16px;height:16px;stroke:var(--brand-400);fill:none;stroke-width:1.75;"></i>
            <span>Certificado MEC</span>
          </div>
          <div class="hero-proof-item">
            <i data-lucide="shield-check" style="width:16px;height:16px;stroke:var(--brand-400);fill:none;stroke-width:1.75;"></i>
            <span>Garantia de 7 dias</span>
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
      </div>

    </div>
  </div>
</section>

@once
<style>
@keyframes pulsePlay { 0%{box-shadow:0 0 0 0 rgba(59,130,246,.6)}70%{box-shadow:0 0 0 18px rgba(59,130,246,0)}100%{box-shadow:0 0 0 0 rgba(59,130,246,0)} }
@keyframes shimmer { 0%{background-position:200% 0}100%{background-position:-200% 0} }
@keyframes waPulse { 0%{box-shadow:0 0 0 0 rgba(37,211,102,.55)}70%{box-shadow:0 0 0 16px rgba(37,211,102,0)}100%{box-shadow:0 0 0 0 rgba(37,211,102,0)} }
.wa-float{position:fixed;bottom:24px;right:24px;z-index:9990;display:flex;align-items:center;gap:10px;background:#25D366;color:#fff;padding:14px 20px 14px 16px;border-radius:999px;text-decoration:none;font-weight:700;font-size:14px;box-shadow:0 10px 30px -6px rgba(37,211,102,.5);animation:waPulse 2.4s infinite;transition:transform .2s;}
.wa-float:hover{transform:scale(1.05);color:#fff;}
.wa-float .wa-label{white-space:nowrap;}
@media(max-width:600px){ .wa-float{padding:14px;} .wa-float .wa-label{display:none;} }
</style>
<script>
function playHeroVideo(){
  document.getElementById('heroVideoOverlay').style.display='none';
  document.getElementById('heroVideo').play();
}
</script>
@endonce

{{-- ================================================================
     2. MINISSÉRIES — logo no começo
     ================================================================ --}}
<section class="section-py" id="minisseries" style="background:var(--bg-1);border-top:1px solid var(--line-1);border-bottom:1px solid var(--line-1);">
  <div class="container">
    <div class="text-center mb-4 aos-fade">
      <div class="section-eyebrow">Comece agora</div>
      <h2 class="section-title">Escolha sua minissérie e<br><span class="text-brand-gradient">acesse em 5 minutos</span></h2>
      <p class="section-subtitle mx-auto">Episódios de 10 a 20 minutos criados por quem já trabalhou na gestão pública. Cada uma com certificado próprio.</p>
    </div>

    {{-- Barra de oferta + ajuda no WhatsApp --}}
    <div class="aos-fade" style="max-width:680px;margin:0 auto 36px;background:linear-gradient(135deg,rgba(0,114,255,0.12),rgba(0,163,255,0.06));border:1px solid rgba(0,163,255,0.3);border-radius:var(--r-lg);padding:16px 24px;display:flex;align-items:center;justify-content:center;gap:14px;flex-wrap:wrap;text-align:center;">
      <span style="font-size:14px;color:var(--fg-2);">
        <i data-lucide="tag" style="width:16px;height:16px;stroke:var(--brand-300);fill:none;stroke-width:1.75;vertical-align:-3px;margin-right:4px;"></i>
        De <span style="text-decoration:line-through;color:var(--fg-4);">R$ 1.990</span>
        por <strong style="color:#fff;">10x de R$ 98</strong> ou <strong style="color:#fff;">R$ 899 à vista</strong>
      </span>
      <a href="{{ $waAjuda }}" target="_blank" style="font-size:13px;color:#25D366;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
        {!! $waIcon !!}
        Não sabe qual escolher? Fale com a gente
      </a>
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
            <div class="course-eyebrow">MINISSÉRIE · CERTIFICADO INCLUSO</div>
            <a href="{{ route('curso.show', $curso->slug) }}" style="text-decoration:none;color:inherit;">
              <div class="course-title">{{ $curso->title }}</div>
            </a>
            <div style="display:flex;gap:8px;margin-top:auto;padding-top:14px;">
              <a href="{{ route('curso.show', $curso->slug) }}" class="btn-ux btn-ux-ghost btn-ux-sm" style="flex:0 0 auto;">Ver detalhes</a>
              <button class="btn-ux btn-ux-primary btn-ux-sm btn-add-to-cart" style="flex:1;justify-content:center;"
                      data-course-id="{{ $curso->id }}"
                      data-course-title="{{ $curso->title }}"
                      data-course-price="{{ $curso->price ?? 899 }}"
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
      <a href="{{ route('cursos') }}" class="btn-ux btn-ux-primary btn-ux-lg">
        Ver catálogo completo
        <i data-lucide="arrow-right" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;"></i>
      </a>
    </div>
  </div>
</section>

{{-- ================================================================
     3. STATS
     ================================================================ --}}
<section class="stats-section">
  <div class="container">
    <div class="row justify-content-center text-center g-0">
      @foreach([
        ['49000','+','Servidores capacitados'],
        ['300','+','Episódios disponíveis'],
        ['4.9','/5','Avaliação média'],
        ['98','%','Recomendam a outros'],
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
     4. DORES
     ================================================================ --}}
<section class="section-py" id="dores" style="background:var(--bg-1);border-top:1px solid var(--line-1);border-bottom:1px solid var(--line-1);">
  <div class="container">
    <div class="text-center mb-5 aos-fade">
      <div class="section-eyebrow">Você se identifica?</div>
      <h2 class="section-title">Se algum desses problemas é seu,<br><span class="text-brand-gradient">a Unyflex resolve</span></h2>
    </div>

    <div class="row g-3">
      @foreach([
        ['icon'=>'alert-triangle','dor'=>'Medo de errar e ser responsabilizado','sol'=>'Aprenda o passo a passo correto de cada processo e decida com respaldo legal.'],
        ['icon'=>'book-open','dor'=>'Cursos longos e cheios de teoria','sol'=>'Episódios de 10 a 20 minutos, direto ao que se aplica na prática.'],
        ['icon'=>'clock','dor'=>'Falta de tempo durante o expediente','sol'=>'Assista no ritmo que der, do celular ou do computador, quando quiser.'],
        ['icon'=>'file-warning','dor'=>'Insegurança com a nova Lei 14.133','sol'=>'Conteúdo atualizado com as últimas mudanças e exemplos reais de aplicação.'],
        ['icon'=>'help-circle','dor'=>'Dúvidas sem ninguém para perguntar','sol'=>'Suporte pedagógico durante todo o período de acesso.'],
        ['icon'=>'award','dor'=>'Precisa comprovar capacitação','sol'=>'Certificado reconhecido pelo MEC, válido para progressão funcional.'],
      ] as $i => $d)
      <div class="col-lg-4 col-md-6 aos-fade" style="transition-delay:{{ $i * 0.06 }}s;">
        <div style="background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-lg);padding:22px;height:100%;">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
            <div style="width:36px;height:36px;border-radius:10px;background:rgba(255,77,77,0.10);border:1px solid rgba(255,77,77,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i data-lucide="{{ $d['icon'] }}" style="width:18px;height:18px;stroke:#ff6b6b;fill:none;stroke-width:1.75;"></i>
            </div>
            <div style="font-size:14px;font-weight:700;color:#fff;">{{ $d['dor'] }}</div>
          </div>
          <div style="display:flex;align-items:flex-start;gap:8px;font-size:13px;color:var(--fg-2);line-height:1.55;">
            <i data-lucide="check-circle" style="width:15px;height:15px;stroke:var(--success);fill:none;stroke-width:1.75;flex-shrink:0;margin-top:2px;"></i>
            {{ $d['sol'] }}
          </div>
        </div>
      </div>
      @endforeach
    </div>

    {{-- CTA WhatsApp após as dores --}}
    <div class="text-center mt-5 aos-fade">
      <p style="font-size:15px;color:var(--fg-3);margin-bottom:16px;">Ainda com dúvida se serve para o seu cargo?</p>
      <a href="{{ $waAjuda }}" target="_blank" class="btn-ux btn-ux-lg" style="background:#25D366;color:#fff;border:none;">
        {!! $waIcon !!}
        Conversar com um especialista
      </a>
    </div>
  </div>
</section>

{{-- ================================================================
     5. COMO FUNCIONA
     ================================================================ --}}
<section class="section-py" id="como-funciona">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-5 aos-fade">
        <div class="section-eyebrow">Como funciona</div>
        <h2 class="section-title">Do clique à aplicação<br><span class="text-brand-gradient">em menos de 1 dia</span></h2>
        <p style="font-size:16px;color:var(--fg-3);line-height:1.65;margin-bottom:20px;">
          Sem burocracia, sem espera. Você compra, acessa na hora e já começa a assistir os episódios que importam para a sua rotina.
        </p>
        <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:28px;">
          @foreach(['Acesso liberado em até 5 minutos','Episódios de 10 a 20 minutos','Certificado MEC gerado automaticamente','Suporte para dúvidas de matrícula e conteúdo'] as $item)
          <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:var(--fg-2);">
            <i data-lucide="check-circle" style="width:16px;height:16px;stroke:var(--success);fill:none;stroke-width:1.75;flex-shrink:0;"></i>
            {{ $item }}
          </div>
          @endforeach
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
          <a href="#minisseries" class="btn-ux btn-ux-primary btn-ux-lg">
            Escolher minha minissérie
            <i data-lucide="arrow-right" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;"></i>
          </a>
          <a href="{{ $waDuvidas }}" target="_blank" class="btn-ux btn-ux-ghost btn-ux-lg" style="color:#25D366;border-color:rgba(37,211,102,0.4);">
            {!! $waIcon !!}
            WhatsApp
          </a>
        </div>
      </div>
      <div class="col-lg-6 offset-lg-1 aos-fade aos-delay-2">
        @foreach([
          ['1','Escolha sua minissérie','Adicione ao carrinho as minisséries que se encaixam na sua rotina e finalize a compra.'],
          ['2','Acesse em 5 minutos','Pagou, liberou. Login imediato em qualquer dispositivo, sem espera.'],
          ['3','Assista em episódios rápidos','10 a 20 minutos por episódio. Densos, diretos e sem enrolação.'],
          ['4','Aplique e emita o certificado','Use no seu órgão no dia seguinte e baixe o certificado MEC ao concluir.'],
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
     6. PROVA SOCIAL
     ================================================================ --}}
<section class="section-py" style="background:var(--bg-1);border-top:1px solid var(--line-1);border-bottom:1px solid var(--line-1);" id="depoimentos">
  <div class="container">
    <div class="text-center mb-5 aos-fade">
      <div class="section-eyebrow">Quem já fez, aprova</div>
      <h2 class="section-title">Mais de <span class="text-brand-gradient">49.000 servidores</span> capacitados</h2>
      <p class="section-subtitle mx-auto">Metodologia objetiva, prática e reconhecida pelo Ministério da Educação.</p>
    </div>

    <div class="row justify-content-center mb-5">
      <div class="col-lg-8 aos-fade">
        <div style="background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-xl);padding:28px 32px;display:flex;align-items:center;gap:24px;flex-wrap:wrap;">
          <div style="width:56px;height:56px;border-radius:50%;background:rgba(43,217,161,0.12);border:2px solid rgba(43,217,161,0.3);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:24px;">✓</div>
          <div style="flex:1;min-width:200px;">
            <div style="font-size:11px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--success);margin-bottom:4px;">Instituição reconhecida</div>
            <div style="font-size:18px;font-weight:800;color:#fff;margin-bottom:4px;">Faculdade Unypublica · MEC</div>
            <div style="font-size:13px;color:var(--fg-3);">Certificados válidos para progressão funcional, concursos e comprovação de capacitação.</div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      @foreach([
        ['init'=>'FP','name'=>'Fernanda Pereira','role'=>'Servidora Municipal','stars'=>5,'text'=>'Uma excelente experiência que vai auxiliar no meu crescimento profissional. Conteúdo direto ao ponto, sem enrolação.'],
        ['init'=>'SP','name'=>'Sonia Petrini','role'=>'Pregoeira','stars'=>5,'text'=>'Muito bom. Organização incrível, cursos ótimos e muito valiosos para o dia a dia no setor de compras.'],
        ['init'=>'BR','name'=>'Beatriz Rossini','role'=>'Gestora de Contratos','stars'=>5,'text'=>'Os cursos são maravilhosos, didáticos, excelentes professores sempre dispostos a sanar as dúvidas.'],
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
     7. PLANO PARA EQUIPES — CTA WhatsApp forte
     ================================================================ --}}
<section class="section-py" id="equipes">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-9 aos-fade">
        <div style="background:linear-gradient(135deg,rgba(0,114,255,0.12),rgba(37,211,102,0.06));border:1px solid rgba(0,163,255,0.3);border-radius:var(--r-xl);padding:36px 40px;display:flex;align-items:center;gap:32px;flex-wrap:wrap;">
          <div style="flex:1;min-width:260px;">
            <div style="font-size:11px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--brand-300);margin-bottom:8px;">Para prefeituras e secretarias</div>
            <h3 style="font-family:var(--font-display);font-weight:800;font-size:26px;color:#fff;margin-bottom:10px;line-height:1.2;">Quer capacitar toda a sua equipe?</h3>
            <p style="font-size:15px;color:var(--fg-3);line-height:1.6;margin-bottom:0;">
              Desconto progressivo por quantidade, emissão de nota fiscal e compra por CNPJ da prefeitura. Fale com a gente e receba uma proposta sob medida.
            </p>
          </div>
          <div style="flex-shrink:0;">
            <a href="{{ $waEquipe }}" target="_blank" class="btn-ux btn-ux-lg" style="background:#25D366;color:#fff;border:none;white-space:nowrap;">
              {!! $waIcon !!}
              Solicitar proposta no WhatsApp
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ================================================================
     8. FAQ
     ================================================================ --}}
<section class="section-py" id="faq" style="background:var(--bg-1);border-top:1px solid var(--line-1);">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="text-center mb-5 aos-fade">
          <div class="section-eyebrow">Perguntas frequentes</div>
          <h2 class="section-title">Antes de comprar, tire as<br><span class="text-brand-gradient">principais dúvidas</span></h2>
        </div>

        @foreach([
          ['q'=>'Tem certificado reconhecido?','a'=>'Sim. Os certificados são emitidos pela Faculdade Unypublica, reconhecida pelo MEC. Válidos para progressão funcional, concursos e comprovação de capacitação.'],
          ['q'=>'Em quanto tempo recebo o acesso?','a'=>'No cartão de crédito, o acesso é liberado em até 5 minutos. No PIX, assim que o pagamento é confirmado. No boleto, em 1 a 2 dias úteis após a compensação.'],
          ['q'=>'Emite nota fiscal? Posso comprar pelo CNPJ?','a'=>'Sim. Emitimos nota fiscal para pessoa física e jurídica. Prefeituras podem comprar via CNPJ — fale com nosso atendimento no WhatsApp para formalizar.'],
          ['q'=>'Serve para servidores de qualquer cidade?','a'=>'Sim. O conteúdo cobre legislação federal e boas práticas aplicáveis a municípios de qualquer porte e estado.'],
          ['q'=>'Por quanto tempo tenho acesso?','a'=>'12 meses a partir da matrícula. Pode assistir, revisar e baixar os materiais quantas vezes quiser.'],
          ['q'=>'E se eu não gostar?','a'=>'Você tem 7 dias para pedir reembolso integral, sem precisar justificar. É só falar com a nossa equipe.'],
        ] as $faq)
        <div class="faq-item aos-fade">
          <div class="faq-question">
            <span>{{ $faq['q'] }}</span>
            <div class="faq-icon"><i data-lucide="plus" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5;"></i></div>
          </div>
          <div class="faq-answer"><div class="faq-answer-inner">{{ $faq['a'] }}</div></div>
        </div>
        @endforeach

        {{-- CTA WhatsApp após FAQ --}}
        <div class="text-center mt-5 aos-fade">
          <p style="font-size:15px;color:var(--fg-3);margin-bottom:16px;">Não encontrou sua dúvida?</p>
          <a href="{{ $waCertif }}" target="_blank" class="btn-ux btn-ux-lg" style="background:#25D366;color:#fff;border:none;">
            {!! $waIcon !!}
            Falar com o atendimento
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ================================================================
     9. CTA FINAL
     ================================================================ --}}
<section class="final-cta-section">
  <div class="container position-relative">
    <div class="row justify-content-center">
      <div class="col-lg-8 text-center aos-fade">
        <div class="hero-eyebrow" style="justify-content:center;">
          <span class="dot"></span>
          <span>Acesso imediato · Certificado MEC · Garantia de 7 dias</span>
        </div>
        <h2 class="section-title" style="font-size:clamp(32px,4vw,52px);margin-bottom:16px;">
          Comece hoje e aplique<br><span class="text-brand-gradient">no seu órgão amanhã</span>
        </h2>
        <p style="font-size:18px;color:var(--fg-3);line-height:1.65;margin-bottom:36px;max-width:560px;margin-left:auto;margin-right:auto;">
          Junte-se a mais de 49.000 servidores. A partir de 10x de R$ 99 — com certificado e garantia de 7 dias.
        </p>

        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-bottom:20px;">
          <a href="#minisseries" class="btn-ux btn-ux-primary btn-ux-lg">
            <i data-lucide="zap" style="width:18px;height:18px;fill:currentColor;stroke:none;"></i>
            Ver minisséries e garantir acesso
          </a>
          <a href="{{ $waDuvidas }}" target="_blank" class="btn-ux btn-ux-lg" style="background:#25D366;color:#fff;border:none;">
            {!! $waIcon !!}
            Tirar dúvidas no WhatsApp
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

{{-- ================================================================
     BOTÃO FLUTUANTE FIXO DO WHATSAPP
     ================================================================ --}}
<a href="{{ $waDuvidas }}" target="_blank" class="wa-float" aria-label="Fale conosco no WhatsApp">
  <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
  <span class="wa-label">Fale conosco</span>
</a>

{{-- Toast --}}
<div class="cart-toast" id="cartToast" role="status" aria-live="polite">
  <div class="cart-toast-icon">
    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
  </div>
  <div class="cart-toast-body">
    <div class="cart-toast-title">Adicionado ao carrinho!</div>
    <div class="cart-toast-sub" id="cartToastSub">Minissérie adicionada com sucesso.</div>
  </div>
  <a href="{{ route('checkout') }}" class="cart-toast-action">Ver carrinho →</a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const cart = UnyCart.getCart();

  document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
    if (cart.find(i => String(i.id) === String(btn.dataset.courseId))) setInCart(btn);
  });

  document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const item = {
        id:    this.dataset.courseId,
        title: this.dataset.courseTitle,
        price: parseFloat(this.dataset.coursePrice) || 899,
        thumb: this.dataset.courseThumb,
        slug:  this.dataset.courseSlug,
      };
      const result = UnyCart.addItem(item);
      if (result.added) {
        if (typeof registrarFunil === 'function') registrarFunil('carrinho', parseInt(item.id));
        setInCart(this);
        showCartToast(item.title);
      } else {
        window.location.href = '{{ route('checkout') }}';
      }
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