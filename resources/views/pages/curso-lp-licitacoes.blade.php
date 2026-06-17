@extends('layouts.site')

@section('meta_title', 'Aprofundamento Técnico na Nova Licitação — Unyflex Digital')
@section('meta_description', 'Aplique os termos-chave da Lei 14.133 com mais segurança na rotina pública. Cápsulas objetivas, materiais prontos e certificado MEC para servidores que atuam com licitações e contratos.')

@section('content')

@php
  $waBase  = 'https://api.whatsapp.com/send/?phone=554188980259&type=phone_number&app_absent=0';
  $waCurso = $waBase . '&text=' . rawurlencode('Olá! Tenho interesse na minisérie "' . $curso->title . '". Pode me ajudar?');
  $waIcon  = '<svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>';
  $thumb = 'https://unyflex.com.br/storage/cursos/banner/' . $curso->photo;
  $preco = $curso->price ?? 998;
@endphp

{{-- ================================================================
     1. HERO — promessa de RESULTADO, SEM preço (P0 + P1)
     ================================================================ --}}
<section class="hero-section" id="curso-hero" style="padding-top:48px;">
  <div class="container">
    <div class="row align-items-center g-5">

      <div class="col-lg-6">
        <div class="hero-eyebrow aos-fade">
          <span class="dot"></span>
          <span>Lei 14.133 na prática · Certificado MEC · +49.000 servidores capacitados</span>
        </div>

        <h1 class="hero-title aos-fade aos-delay-1" style="font-size:clamp(28px,4vw,44px);">
          Aplique os termos-chave da Nova Licitação com
          <span class="highlight">mais segurança na rotina pública</span>
        </h1>

        <p class="hero-subtitle aos-fade aos-delay-2">
          {{ $totalVideos }} cápsulas objetivas, materiais prontos e certificado para servidores,
          gestores e equipes que atuam com licitações e contratos.
          <strong style="color:#fff;">Sem curso longo, sem teoria solta.</strong>
        </p>

        {{-- CTAs — principal dominante, WhatsApp secundário (P0) --}}
        <div class="hero-cta-group aos-fade aos-delay-3">
          <a href="#oferta" class="btn-ux btn-ux-primary btn-ux-lg">
            <i data-lucide="zap" style="width:18px;height:18px;fill:currentColor;stroke:none;"></i>
            Garantir meu acesso
          </a>
          <a href="{{ $waCurso }}" target="_blank" style="display:inline-flex;align-items:center;gap:7px;font-size:13.5px;color:#25D366;font-weight:600;text-decoration:none;padding:8px 4px;">
            {!! $waIcon !!}
            Tirar dúvida no WhatsApp
          </a>
        </div>

        <div class="aos-fade aos-delay-4" style="display:flex;gap:18px;flex-wrap:wrap;margin-top:18px;">
          @foreach(['Acesso em 5 minutos','Certificado MEC','Garantia de 7 dias'] as $tag)
          <div style="display:flex;align-items:center;gap:6px;font-size:12.5px;color:var(--fg-4);">
            <i data-lucide="shield-check" style="width:13px;height:13px;stroke:var(--success);fill:none;stroke-width:1.75;"></i>
            {{ $tag }}
          </div>
          @endforeach
        </div>
      </div>

      {{-- Thumb (sem preço) --}}
      <div class="col-lg-6 aos-fade aos-delay-2">
        <div style="position:relative;border-radius:16px;overflow:hidden;border:1px solid rgba(59,130,246,0.25);box-shadow:0 0 40px rgba(59,130,246,0.12);aspect-ratio:16/9;background-image:url('{{ $thumb }}');background-size:cover;background-position:center;">
          <div style="position:absolute;inset:0;background:linear-gradient(135deg,rgba(5,10,30,0.3),rgba(10,20,50,0.15));"></div>
        </div>
        <div style="display:flex;gap:0;background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-lg);margin-top:14px;overflow:hidden;">
          @foreach([
            ['layers', $totalTemporadas, 'temporadas'],
            ['play-circle', $totalVideos, 'cápsulas'],
            ['file-text', $totalMateriais, 'materiais'],
            ['clock', ($curso->workload ?? '—') . 'h', 'conteúdo'],
          ] as $i => [$ic, $val, $lbl])
          <div style="flex:1;text-align:center;padding:14px 8px;{{ $i > 0 ? 'border-left:1px solid var(--line-1);' : '' }}">
            <i data-lucide="{{ $ic }}" style="width:16px;height:16px;stroke:var(--brand-300);fill:none;stroke-width:1.75;margin-bottom:4px;"></i>
            <div style="font-size:17px;font-weight:800;color:#fff;font-family:var(--font-display);line-height:1.1;">{{ $val }}</div>
            <div style="font-size:10.5px;color:var(--fg-4);">{{ $lbl }}</div>
          </div>
          @endforeach
        </div>
      </div>

    </div>
  </div>
</section>

{{-- ================================================================
     2. DOR / APLICAÇÃO — logo após o hero (P1)
     ================================================================ --}}
<section class="section-py" id="dor" style="background:var(--bg-1);border-top:1px solid var(--line-1);border-bottom:1px solid var(--line-1);">
  <div class="container">
    <div class="text-center mb-5 aos-fade">
      <div class="section-eyebrow">Você se reconhece aqui?</div>
      <h2 class="section-title">Se a Nova Licitação ainda gera<br><span class="text-brand-gradient">insegurança na sua rotina</span></h2>
    </div>

    <div class="row g-3 justify-content-center">
      @foreach([
        'Precisa interpretar termos da Lei 14.133 sem depender de explicações soltas?',
        'Sua equipe lida com editais, contratos, garantias, sobrepreço, inexequibilidade ou regimes de contratação?',
        'Precisa revisar conceitos técnicos com materiais de apoio e aulas curtas?',
        'Busca uma capacitação aplicável à rotina pública, sem curso longo e excessivamente teórico?',
      ] as $i => $dor)
      <div class="col-lg-6 aos-fade" style="transition-delay:{{ $i * 0.06 }}s;">
        <div style="background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-lg);padding:20px 22px;height:100%;display:flex;gap:14px;align-items:flex-start;">
          <div style="width:32px;height:32px;border-radius:9px;background:rgba(0,163,255,0.10);border:1px solid rgba(0,163,255,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="help-circle" style="width:17px;height:17px;stroke:var(--brand-300);fill:none;stroke-width:1.75;"></i>
          </div>
          <div style="font-size:14.5px;color:var(--fg-2);line-height:1.55;">{{ $dor }}</div>
        </div>
      </div>
      @endforeach
    </div>

    <div class="text-center mt-5 aos-fade">
      <p style="font-size:16px;color:var(--fg-2);margin-bottom:20px;max-width:600px;margin-left:auto;margin-right:auto;line-height:1.6;">
        Esta minisérie foi feita para resolver exatamente isso: <strong style="color:#fff;">conceito técnico explicado de forma direta, com aplicação imediata no seu setor.</strong>
      </p>
      <a href="#oferta" class="btn-ux btn-ux-primary btn-ux-lg">
        <i data-lucide="zap" style="width:16px;height:16px;fill:currentColor;stroke:none;"></i>
        Quero me capacitar
      </a>
    </div>
  </div>
</section>

{{-- ================================================================
     3. O QUE VOCÊ VAI DOMINAR (P-estrutura)
     ================================================================ --}}
<section class="section-py" id="aprender">
  <div class="container">
    <div class="text-center mb-5 aos-fade">
      <div class="section-eyebrow">O que você vai dominar</div>
      <h2 class="section-title">Ao concluir, você aplica<br><span class="text-brand-gradient">com segurança</span></h2>
    </div>

    <div class="row g-3 justify-content-center">
      @foreach($panels as $panel)
      <div class="col-lg-6 aos-fade" style="transition-delay:{{ $loop->index * 0.06 }}s;">
        <div style="background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-lg);padding:20px 22px;height:100%;display:flex;gap:14px;align-items:flex-start;">
          <div style="width:36px;height:36px;border-radius:10px;background:rgba(0,163,255,0.10);border:1px solid rgba(0,163,255,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="check" style="width:18px;height:18px;stroke:var(--brand-300);fill:none;stroke-width:2.5;"></i>
          </div>
          <div>
            <div style="font-size:15px;font-weight:700;color:#fff;margin-bottom:4px;">{{ $panel->title }}</div>
            <div style="font-size:12.5px;color:var(--fg-4);">{{ $panel->video_lesson->count() }} cápsulas @if($panel->material->count() > 0) · {{ $panel->material->count() }} materiais @endif</div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ================================================================
     4. SOBRE (se houver)
     ================================================================ --}}
@if($curso->info)
<section class="section-py" style="background:var(--bg-1);border-top:1px solid var(--line-1);border-bottom:1px solid var(--line-1);">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8 aos-fade">
        <div class="section-eyebrow">Sobre esta minisérie</div>
        <div style="font-size:16px;color:var(--fg-2);line-height:1.75;">
          {!! html_entity_decode($curso->info, ENT_QUOTES | ENT_HTML5, 'UTF-8') !!}
        </div>
      </div>
    </div>
  </div>
</section>
@endif

{{-- ================================================================
     5. CONTEÚDO PROGRAMÁTICO
     ================================================================ --}}
<section class="section-py" id="conteudo" style="{{ $curso->info ? '' : 'background:var(--bg-1);border-top:1px solid var(--line-1);border-bottom:1px solid var(--line-1);' }}">
  <div class="container">
    <div class="text-center mb-5 aos-fade">
      <div class="section-eyebrow">Conteúdo programático</div>
      <h2 class="section-title">Tudo o que está dentro<br><span class="text-brand-gradient">da minisérie</span></h2>
      <p class="section-subtitle mx-auto">{{ $totalTemporadas }} temporadas · {{ $totalVideos }} cápsulas · {{ $totalMateriais }} materiais de apoio</p>
    </div>

    <div class="row justify-content-center">
      <div class="col-lg-9">
        @foreach($panels as $panel)
        @php $pNum = $loop->iteration; @endphp
        <div class="aos-fade" style="margin-bottom:14px;">
          <div class="lp-accordion-header" onclick="toggleAccordion(this)"
               style="background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-lg);padding:18px 22px;cursor:pointer;display:flex;align-items:center;gap:14px;transition:border-color 0.2s;">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(0,163,255,0.10);border:1px solid rgba(0,163,255,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <span style="font-family:var(--font-display);font-weight:800;font-size:16px;color:var(--brand-300);">{{ $pNum }}</span>
            </div>
            <div style="flex:1;min-width:0;">
              <div style="font-size:10px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--brand-400);margin-bottom:2px;">Temporada {{ $pNum }}</div>
              <div style="font-size:15px;font-weight:700;color:#fff;">{{ $panel->title }}</div>
              <div style="font-size:12px;color:var(--fg-4);margin-top:2px;">
                {{ $panel->video_lesson->count() }} cápsulas
                @if($panel->material->count() > 0) · {{ $panel->material->count() }} materiais @endif
              </div>
            </div>
            <i data-lucide="chevron-down" class="lp-chevron" style="width:20px;height:20px;stroke:var(--fg-4);fill:none;stroke-width:2;flex-shrink:0;transition:transform 0.25s;"></i>
          </div>

          <div class="lp-accordion-body" style="display:none;padding:8px 0 0;">
            @foreach($panel->video_lesson as $video)
            @php $vNum = $loop->iteration; @endphp
            <div style="display:flex;align-items:center;gap:12px;padding:12px 22px;border-bottom:1px solid var(--line-1);">
              <div style="width:28px;height:28px;border-radius:50%;border:1.5px solid var(--line-2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg viewBox="0 0 24 24" style="width:11px;height:11px;fill:var(--brand-300);"><polygon points="6 4 20 12 6 20 6 4"/></svg>
              </div>
              <div style="flex:1;min-width:0;">
                <div style="font-size:14px;color:var(--fg-2);">{{ $pNum }}.{{ $vNum }} {{ $video->titulo }}</div>
              </div>
              <span style="font-size:11px;color:var(--fg-4);flex-shrink:0;">~12 min</span>
            </div>
            @endforeach

            @if($panel->material->count() > 0)
            <div style="padding:14px 22px;background:rgba(0,163,255,0.03);">
              <div style="font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--fg-4);margin-bottom:8px;">Materiais de apoio</div>
              <div style="display:flex;flex-wrap:wrap;gap:8px;">
                @foreach($panel->material as $mat)
                <span style="display:inline-flex;align-items:center;gap:6px;background:var(--bg-1);border:1px solid var(--line-2);border-radius:8px;padding:5px 10px;font-size:12px;color:var(--fg-2);">
                  <i data-lucide="{{ $mat->type === 'PODCAST' ? 'headphones' : 'file-text' }}" style="width:13px;height:13px;stroke:var(--brand-300);fill:none;stroke-width:1.75;"></i>
                  {{ $mat->name ?? $mat->file_name }}
                </span>
                @endforeach
              </div>
            </div>
            @endif
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</section>

{{-- ================================================================
     6. PARA QUEM É — cargos reais (P2)
     ================================================================ --}}
<section class="section-py" id="para-quem" style="background:var(--bg-1);border-top:1px solid var(--line-1);border-bottom:1px solid var(--line-1);">
  <div class="container">
    <div class="text-center mb-5 aos-fade">
      <div class="section-eyebrow">Para quem é</div>
      <h2 class="section-title">Feita para quem atua<br><span class="text-brand-gradient">com licitações e contratos</span></h2>
    </div>

    <div class="row g-3 justify-content-center">
      @foreach([
        ['gavel','Pregoeiros e agentes de contratação','Equipes de licitação que conduzem certames e precisam de segurança jurídica.'],
        ['clipboard-check','Gestores e fiscais de contratos','Secretários e servidores municipais responsáveis pela execução contratual.'],
        ['scale','Controladorias, procuradorias e jurídicos','Setores administrativos que analisam, fiscalizam e dão parecer em processos.'],
        ['book-open','Quem precisa dominar a Lei 14.133','Profissionais que aplicam os termos-chave da Nova Licitação na rotina.'],
        ['award','Quem precisa comprovar capacitação','Servidores que precisam de certificado válido para progressão funcional.'],
        ['users','Equipes administrativas','Times que precisam de capacitação rápida e aplicável, sem sair do expediente.'],
      ] as $i => $p)
      <div class="col-lg-4 col-md-6 aos-fade" style="transition-delay:{{ $i * 0.06 }}s;">
        <div style="background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-lg);padding:22px;height:100%;">
          <div style="width:42px;height:42px;border-radius:11px;background:rgba(0,163,255,0.10);border:1px solid rgba(0,163,255,0.2);display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
            <i data-lucide="{{ $p[0] }}" style="width:20px;height:20px;stroke:var(--brand-300);fill:none;stroke-width:1.75;"></i>
          </div>
          <div style="font-size:15px;font-weight:700;color:#fff;margin-bottom:6px;">{{ $p[1] }}</div>
          <p style="font-size:13px;color:var(--fg-3);line-height:1.55;margin:0;">{{ $p[2] }}</p>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ================================================================
     7. PROVA INSTITUCIONAL (P3)
     ================================================================ --}}
<section class="section-py" id="prova">
  <div class="container">
    <div class="text-center mb-5 aos-fade">
      <div class="section-eyebrow">Confiança</div>
      <h2 class="section-title">Uma instituição que servidores<br><span class="text-brand-gradient">de todo o Brasil já confiam</span></h2>
    </div>

    {{-- Indicadores --}}
    <div class="row g-3 justify-content-center mb-4">
      @foreach([
        ['users','+49.000','servidores capacitados'],
        ['building-2','Faculdade Unypública','instituição reconhecida'],
        ['award','Certificado MEC','válido para progressão'],
        ['map-pin','Todo o Brasil','municípios de qualquer porte'],
      ] as $i => $ind)
      <div class="col-lg-3 col-md-6 aos-fade" style="transition-delay:{{ $i * 0.06 }}s;">
        <div style="background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-lg);padding:22px;text-align:center;height:100%;">
          <i data-lucide="{{ $ind[0] }}" style="width:24px;height:24px;stroke:var(--brand-300);fill:none;stroke-width:1.75;margin-bottom:10px;"></i>
          <div style="font-size:18px;font-weight:800;color:#fff;font-family:var(--font-display);line-height:1.2;margin-bottom:4px;">{{ $ind[1] }}</div>
          <div style="font-size:12px;color:var(--fg-4);">{{ $ind[2] }}</div>
        </div>
      </div>
      @endforeach
    </div>

    {{-- Badge MEC --}}
    <div class="row justify-content-center">
      <div class="col-lg-8 aos-fade">
        <div style="background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-xl);padding:24px 28px;display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
          <div style="width:50px;height:50px;border-radius:50%;background:rgba(43,217,161,0.12);border:2px solid rgba(43,217,161,0.3);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:22px;">✓</div>
          <div style="flex:1;min-width:220px;">
            <div style="font-size:16px;font-weight:800;color:#fff;margin-bottom:3px;">Faculdade Unypública · Reconhecida pelo MEC</div>
            <div style="font-size:13px;color:var(--fg-3);">Certificados com validade para progressão funcional, concursos e comprovação de capacitação no setor público.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ================================================================
     8. PROFESSOR / ESPECIALISTA (P4)
     ================================================================ --}}
<section class="section-py" id="professor" style="background:var(--bg-1);border-top:1px solid var(--line-1);border-bottom:1px solid var(--line-1);">
  <div class="container">
    <div class="row justify-content-center align-items-center g-5">
      <div class="col-lg-4 col-md-5 aos-fade">
        {{-- Espaço para foto real do professor — troque o src --}}
        <div style="aspect-ratio:1;border-radius:var(--r-xl);overflow:hidden;border:1px solid var(--line-2);background:var(--bg-3);display:flex;align-items:center;justify-content:center;">
          {{-- <img src="https://unyflex.com.br/storage/professores/SEU-PROFESSOR.jpg" alt="Professor" style="width:100%;height:100%;object-fit:cover;"> --}}
          <i data-lucide="user" style="width:48px;height:48px;stroke:var(--fg-4);fill:none;stroke-width:1.25;"></i>
        </div>
      </div>
      <div class="col-lg-6 col-md-7 aos-fade aos-delay-2">
        <div class="section-eyebrow">Quem ensina</div>
        <h2 class="section-title" style="font-size:clamp(22px,3vw,30px);">Conteúdo de quem vive a<br>administração pública</h2>
        <p style="font-size:15px;color:var(--fg-3);line-height:1.7;margin:16px 0;">
          {{-- TROQUE pelos dados reais do professor --}}
          Nome do Especialista · Cargo / Formação
        </p>
        <p style="font-size:14px;color:var(--fg-3);line-height:1.7;">
          Especialista em licitações e contratos administrativos com atuação prática no setor público.
          Traduz a Lei 14.133 em conceitos diretos e aplicáveis, do jeito que o servidor precisa para
          executar com segurança no dia a dia.
        </p>
      </div>
    </div>
  </div>
</section>

{{-- ================================================================
     9. OFERTA — preço aparece SÓ AQUI (P0)
     ================================================================ --}}
<section class="section-py" id="oferta">
  <div class="container">
    <div class="text-center mb-5 aos-fade">
      <div class="section-eyebrow">Condição especial</div>
      <h2 class="section-title">Garanta seu acesso<br><span class="text-brand-gradient">ainda hoje</span></h2>
    </div>

    <div class="row justify-content-center">
      <div class="col-lg-6 col-md-8 aos-fade">
        <div style="background:linear-gradient(135deg,rgba(0,114,255,0.12),rgba(0,163,255,0.05));border:1px solid rgba(0,163,255,0.35);border-radius:var(--r-xl);padding:36px 32px;text-align:center;position:relative;">
          <div style="position:absolute;top:-13px;left:50%;transform:translateX(-50%);background:var(--grad-brand);color:#061224;font-size:11px;font-weight:800;letter-spacing:0.1em;text-transform:uppercase;padding:5px 18px;border-radius:999px;white-space:nowrap;">Oferta por tempo limitado</div>

          <div style="font-size:14px;font-weight:700;color:#fff;margin-bottom:18px;margin-top:8px;">{{ $curso->title }}</div>

          <div style="font-size:13px;color:var(--fg-4);text-decoration:line-through;margin-bottom:8px;">De R$ 1.990,00</div>

          <div style="background:rgba(0,163,255,0.10);border:1px solid rgba(0,163,255,0.25);border-radius:var(--r-md);padding:16px;margin-bottom:10px;">
            <div style="font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--brand-300);margin-bottom:6px;">Parcelado em até</div>
            <div style="display:flex;align-items:baseline;justify-content:center;gap:5px;">
              <span style="font-family:var(--font-display);font-weight:800;font-size:30px;color:#fff;">10x</span>
              <span style="font-family:var(--font-display);font-weight:800;font-size:50px;color:#fff;line-height:1;">R$ 98</span>
            </div>
            <div style="font-size:11px;color:var(--fg-4);margin-top:4px;">sem juros no cartão</div>
          </div>

          <div style="font-size:13px;color:var(--fg-3);margin-bottom:22px;">
            ou <strong style="color:#fff;">R$ {{ number_format($preco,0,',','.') }}</strong> à vista · acesso por 12 meses
          </div>

          <button class="btn-ux btn-ux-primary btn-ux-lg btn-add-to-cart" style="width:100%;justify-content:center;margin-bottom:12px;"
                  data-course-id="{{ $curso->id }}"
                  data-course-title="{{ $curso->title }}"
                  data-course-price="{{ $preco }}"
                  data-course-thumb="{{ $thumb }}"
                  data-course-slug="{{ $curso->slug }}">
            <i data-lucide="zap" style="width:17px;height:17px;fill:currentColor;stroke:none;"></i>
            <span class="btn-cart-label">Garantir meu acesso agora</span>
          </button>
          <a href="{{ $waCurso }}" target="_blank" style="display:inline-flex;align-items:center;justify-content:center;gap:7px;font-size:13px;color:#25D366;font-weight:600;text-decoration:none;padding:6px;margin-bottom:18px;">
            {!! $waIcon !!}
            Prefiro tirar uma dúvida antes
          </a>

          <div style="height:1px;background:var(--line-1);margin-bottom:18px;"></div>

          <div style="display:flex;flex-direction:column;gap:9px;text-align:left;">
            @foreach([
              'Acesso por 12 meses a todas as ' . $totalVideos . ' cápsulas',
              'Versão em podcast de cada aula',
              $totalMateriais . ' materiais, modelos e checklists',
              'Certificado reconhecido pelo MEC',
              'Suporte pedagógico durante o acesso',
              'Garantia incondicional de 7 dias',
            ] as $it)
            <div style="display:flex;align-items:center;gap:10px;font-size:13.5px;color:var(--fg-2);">
              <i data-lucide="check-circle" style="width:15px;height:15px;stroke:var(--success);fill:none;stroke-width:1.75;flex-shrink:0;"></i>
              {{ $it }}
            </div>
            @endforeach
          </div>
        </div>

        {{-- Garantia --}}
        <div class="aos-fade" style="display:flex;align-items:center;gap:14px;background:var(--bg-2);border:1px solid rgba(43,217,161,0.25);border-radius:var(--r-lg);padding:16px 20px;margin-top:16px;">
          <div style="width:44px;height:44px;border-radius:50%;background:rgba(43,217,161,0.12);border:2px solid rgba(43,217,161,0.35);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="shield-check" style="width:20px;height:20px;stroke:#6FE6BD;fill:none;stroke-width:1.75;"></i>
          </div>
          <div style="font-size:13px;color:var(--fg-2);line-height:1.5;">
            <strong style="color:#fff;">Risco zero:</strong> se em até 7 dias você achar que não é para você, devolvemos 100% do valor. Sem perguntas.
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ================================================================
     10. FAQ
     ================================================================ --}}
<section class="section-py" id="faq" style="background:var(--bg-1);border-top:1px solid var(--line-1);">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="text-center mb-5 aos-fade">
          <div class="section-eyebrow">Perguntas frequentes</div>
          <h2 class="section-title">Dúvidas comuns antes<br><span class="text-brand-gradient">de se matricular</span></h2>
        </div>

        @foreach([
          ['q'=>'O conteúdo cobre a Lei 14.133 atualizada?','a'=>'Sim. A minisérie é focada na Nova Lei de Licitações (14.133/21), com os termos-chave e regimes de contratação explicados de forma prática e atualizada.'],
          ['q'=>'Em quanto tempo recebo o acesso?','a'=>'No cartão de crédito, em até 5 minutos. No PIX, assim que o pagamento confirma. No boleto, em 1 a 2 dias úteis.'],
          ['q'=>'O certificado é reconhecido?','a'=>'Sim. Emitido pela Faculdade Unypública, reconhecida pelo MEC. Vale para progressão funcional, concursos e comprovação de capacitação.'],
          ['q'=>'Por quanto tempo tenho acesso?','a'=>'12 meses a partir da matrícula. Assista, revise e baixe os materiais quantas vezes quiser.'],
          ['q'=>'Posso pagar parcelado?','a'=>'Sim, em até 10x de R$ 98 sem juros no cartão. Também aceitamos PIX e boleto à vista.'],
          ['q'=>'Emite nota fiscal? Prefeitura pode comprar?','a'=>'Sim, nota fiscal para PF e PJ. Prefeituras podem comprar via CNPJ — fale com a gente no WhatsApp.'],
          ['q'=>'E se eu não gostar?','a'=>'Você tem 7 dias de garantia incondicional. Pediu, devolvemos 100% do valor, sem burocracia.'],
        ] as $faq)
        <div class="faq-item aos-fade">
          <div class="faq-question">
            <span>{{ $faq['q'] }}</span>
            <div class="faq-icon"><i data-lucide="plus" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5;"></i></div>
          </div>
          <div class="faq-answer"><div class="faq-answer-inner">{{ $faq['a'] }}</div></div>
        </div>
        @endforeach

        <div class="text-center mt-4 aos-fade">
          <a href="{{ $waCurso }}" target="_blank" style="font-size:14px;color:#25D366;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:8px;">
            {!! $waIcon !!}
            Outra dúvida? Chama no WhatsApp
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ================================================================
     11. CTA FINAL — repete o preço/acesso (P0)
     ================================================================ --}}
<section class="final-cta-section">
  <div class="container position-relative">
    <div class="row justify-content-center">
      <div class="col-lg-8 text-center aos-fade">
        <div class="hero-eyebrow" style="justify-content:center;">
          <span class="dot"></span>
          <span>Acesso em 5 minutos · Certificado MEC · Garantia de 7 dias</span>
        </div>
        <h2 class="section-title" style="font-size:clamp(28px,4vw,46px);margin-bottom:16px;">
          Domine a Nova Licitação<br><span class="text-brand-gradient">e aplique com segurança</span>
        </h2>
        <p style="font-size:17px;color:var(--fg-3);line-height:1.65;margin-bottom:32px;max-width:540px;margin-left:auto;margin-right:auto;">
          {{ $totalVideos }} cápsulas práticas por 10x de R$ 98. Junte-se a mais de 49.000 servidores capacitados.
        </p>

        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;align-items:center;">
          <button class="btn-ux btn-ux-primary btn-ux-lg btn-add-to-cart"
                  data-course-id="{{ $curso->id }}"
                  data-course-title="{{ $curso->title }}"
                  data-course-price="{{ $preco }}"
                  data-course-thumb="{{ $thumb }}"
                  data-course-slug="{{ $curso->slug }}">
            <i data-lucide="zap" style="width:18px;height:18px;fill:currentColor;stroke:none;"></i>
            <span class="btn-cart-label">Garantir meu acesso</span>
          </button>
          <a href="{{ $waCurso }}" target="_blank" style="display:inline-flex;align-items:center;gap:7px;font-size:13.5px;color:#25D366;font-weight:600;text-decoration:none;">
            {!! $waIcon !!}
            Tirar dúvida no WhatsApp
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- Barra fixa de conversão --}}
<div id="lp-sticky-bar" style="position:fixed;bottom:0;left:0;right:0;z-index:9980;background:rgba(8,14,28,0.97);border-top:1px solid var(--line-2);backdrop-filter:blur(10px);padding:10px 16px;display:none;align-items:center;gap:12px;">
  <div style="flex:1;min-width:0;">
    <div style="font-size:11px;color:var(--fg-4);text-decoration:line-through;">R$ 1.990</div>
    <div style="font-size:15px;font-weight:800;color:#fff;font-family:var(--font-display);">10x R$ 98 <span style="font-size:11px;font-weight:400;color:var(--fg-3);">sem juros</span></div>
  </div>
  <button class="btn-ux btn-ux-primary btn-ux-sm btn-add-to-cart" style="flex-shrink:0;"
          data-course-id="{{ $curso->id }}"
          data-course-title="{{ $curso->title }}"
          data-course-price="{{ $preco }}"
          data-course-thumb="{{ $thumb }}"
          data-course-slug="{{ $curso->slug }}">
    <span class="btn-cart-label">Garantir acesso</span>
  </button>
</div>

{{-- Botão flutuante WhatsApp (secundário, menor) --}}
<a href="{{ $waCurso }}" target="_blank" class="wa-float" aria-label="Tirar dúvida no WhatsApp">
  <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
</a>

{{-- Toast --}}
<div class="cart-toast" id="cartToast" role="status" aria-live="polite">
  <div class="cart-toast-icon">
    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
  </div>
  <div class="cart-toast-body">
    <div class="cart-toast-title">Adicionado ao carrinho!</div>
    <div class="cart-toast-sub" id="cartToastSub">Minisérie adicionada com sucesso.</div>
  </div>
  <a href="{{ route('checkout') }}" class="cart-toast-action">Finalizar compra →</a>
</div>

@push('styles')
<style>
@keyframes waPulse { 0%{box-shadow:0 0 0 0 rgba(37,211,102,.55)}70%{box-shadow:0 0 0 14px rgba(37,211,102,0)}100%{box-shadow:0 0 0 0 rgba(37,211,102,0)} }
.wa-float{position:fixed;bottom:84px;right:24px;z-index:9990;display:flex;align-items:center;justify-content:center;width:52px;height:52px;background:#25D366;color:#fff;border-radius:50%;text-decoration:none;box-shadow:0 8px 24px -4px rgba(37,211,102,.5);animation:waPulse 2.6s infinite;transition:transform .2s;}
.wa-float:hover{transform:scale(1.08);color:#fff;}
@media(max-width:600px){ .wa-float{bottom:74px;} }
.lp-accordion-header:hover{border-color:rgba(0,163,255,0.4)!important;}
.lp-chevron.open{transform:rotate(180deg);}
</style>
@endpush

@push('scripts')
<script>
function toggleAccordion(header) {
  const body = header.nextElementSibling;
  const chev = header.querySelector('.lp-chevron');
  const open = body.style.display !== 'none';
  body.style.display = open ? 'none' : 'block';
  chev.classList.toggle('open', !open);
}

document.addEventListener('DOMContentLoaded', function () {
  const first = document.querySelector('.lp-accordion-header');
  if (first) toggleAccordion(first);

  const stickyBar = document.getElementById('lp-sticky-bar');
  const oferta    = document.getElementById('oferta');
  window.addEventListener('scroll', function () {
    if (!stickyBar) return;
    const scrolled    = window.scrollY > 600;
    const ofertaRect  = oferta?.getBoundingClientRect();
    const ofertaVisible = ofertaRect && ofertaRect.top < window.innerHeight && ofertaRect.bottom > 0;
    stickyBar.style.display = (scrolled && !ofertaVisible) ? 'flex' : 'none';
  }, { passive: true });

  const cart = (window.UnyCart ? UnyCart.getCart() : []);

  document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
    if (cart.find(i => String(i.id) === String(btn.dataset.courseId))) setInCart(btn);

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
      if (result.added) {
        if (typeof registrarFunil === 'function') registrarFunil('carrinho', parseInt(item.id));
        document.querySelectorAll('.btn-add-to-cart').forEach(b => {
          if (String(b.dataset.courseId) === String(item.id)) setInCart(b);
        });
        showCartToast(item.title);
        setTimeout(() => { window.location.href = '{{ route('checkout') }}'; }, 1200);
      } else {
        window.location.href = '{{ route('checkout') }}';
      }
    });
  });

  if (typeof registrarFunil === 'function') registrarFunil('visualizou', {{ $curso->id }});

  function setInCart(btn) {
    btn.classList.add('in-cart');
    const lbl = btn.querySelector('.btn-cart-label');
    if (lbl) lbl.textContent = 'No carrinho ✓';
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
});
</script>
@endpush

@endsection