@extends('layouts.site')

@section('meta_title', $curso->title . ' — Unyflex Digital')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($curso->info ?? $curso->subtitle ?? 'Minisérie prática para servidores públicos.'), 155))

@section('content')

@php
  $waBase  = 'https://api.whatsapp.com/send/?phone=554188980259&type=phone_number&app_absent=0';
  $waCurso = $waBase . '&text=' . rawurlencode('Olá! Tenho interesse na minisérie "' . $curso->title . '". Pode me ajudar?');
  $waIcon  = '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>';
  $thumb = 'https://unyflex.com.br/storage/cursos/banner/' . $curso->photo;
  $preco = $curso->price ?? 998;
@endphp

{{-- ================================================================
     1. HERO — promessa + prova, SEM preço ainda (tráfego frio)
     ================================================================ --}}
<section class="hero-section" id="curso-hero" style="padding-top:48px;">
  <div class="container">
    <div class="row align-items-center g-5">

      <div class="col-lg-6">
        <div class="hero-eyebrow aos-fade">
          <span class="dot"></span>
          <span>Minisérie prática · Certificado MEC · +49.000 servidores capacitados</span>
        </div>

        <h1 class="hero-title aos-fade aos-delay-1" style="font-size:clamp(28px,4vw,44px);">
          {{ $curso->title }}
        </h1>

        @if($curso->subtitle)
        <p class="hero-subtitle aos-fade aos-delay-2">{{ $curso->subtitle }}</p>
        @else
        <p class="hero-subtitle aos-fade aos-delay-2">
          Domine este tema em cápsulas de 10 a 20 minutos e aplique no seu órgão
          <strong style="color:#fff;">já no dia seguinte</strong> — sem teoria interminável, sem juridiquês.
        </p>
        @endif

        {{-- Bullets de credibilidade --}}
        <div class="aos-fade aos-delay-3" style="display:flex;flex-direction:column;gap:10px;margin:22px 0;">
          @foreach([
            $totalVideos . ' cápsulas diretas ao ponto, de 10 a 20 minutos cada',
            'Certificado reconhecido pelo MEC ao concluir',
            'Materiais, modelos e checklists prontos para usar',
            'Acesso liberado em até 5 minutos após a compra',
          ] as $b)
          <div style="display:flex;align-items:center;gap:10px;font-size:14.5px;color:var(--fg-2);">
            <i data-lucide="check-circle" style="width:17px;height:17px;stroke:var(--success);fill:none;stroke-width:1.75;flex-shrink:0;"></i>
            {{ $b }}
          </div>
          @endforeach
        </div>

        {{-- CTAs --}}
        <div class="hero-cta-group aos-fade aos-delay-4">
          <a href="#oferta" class="btn-ux btn-ux-primary btn-ux-lg">
            <i data-lucide="zap" style="width:18px;height:18px;fill:currentColor;stroke:none;"></i>
            Quero me matricular
          </a>
          <a href="{{ $waCurso }}" target="_blank" class="btn-ux btn-ux-lg" style="background:#25D366;color:#fff;border:none;">
            {!! $waIcon !!}
            Tirar dúvidas
          </a>
        </div>

        <div class="aos-fade aos-delay-4" style="display:flex;gap:18px;flex-wrap:wrap;margin-top:18px;">
          @foreach(['Garantia de 7 dias','Pagamento seguro','Parcele em até 10x'] as $tag)
          <div style="display:flex;align-items:center;gap:6px;font-size:12.5px;color:var(--fg-4);">
            <i data-lucide="shield-check" style="width:13px;height:13px;stroke:var(--success);fill:none;stroke-width:1.75;"></i>
            {{ $tag }}
          </div>
          @endforeach
        </div>
      </div>

      {{-- Thumb --}}
      <div class="col-lg-6 aos-fade aos-delay-2">
        <div style="position:relative;border-radius:16px;overflow:hidden;border:1px solid rgba(59,130,246,0.25);box-shadow:0 0 40px rgba(59,130,246,0.12);aspect-ratio:16/9;background-image:url('{{ $thumb }}');background-size:cover;background-position:center;">
          <div style="position:absolute;inset:0;background:linear-gradient(135deg,rgba(5,10,30,0.3),rgba(10,20,50,0.15));"></div>
          @if($curso->novidade)
          <span style="position:absolute;top:16px;left:16px;background:var(--grad-brand);color:#061224;font-size:11px;font-weight:800;letter-spacing:0.08em;text-transform:uppercase;padding:4px 12px;border-radius:999px;">Novidade</span>
          @endif
        </div>

        {{-- Métricas abaixo da thumb --}}
        <div style="display:flex;gap:0;background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-lg);margin-top:14px;overflow:hidden;">
          @foreach([
            ['layers', $totalTemporadas, 'temporadas'],
            ['play-circle', $totalVideos, 'cápsulas'],
            ['file-text', $totalMateriais, 'materiais'],
            ['clock', ($curso->workload ?? '—') . 'h', 'de conteúdo'],
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
     2. O QUE VOCÊ VAI APRENDER — extraído das temporadas
     ================================================================ --}}
<section class="section-py" id="aprender" style="background:var(--bg-1);border-top:1px solid var(--line-1);border-bottom:1px solid var(--line-1);">
  <div class="container">
    <div class="text-center mb-5 aos-fade">
      <div class="section-eyebrow">O que você vai aprender</div>
      <h2 class="section-title">Ao concluir, você vai dominar<br><span class="text-brand-gradient">na prática</span></h2>
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
            <div style="font-size:12.5px;color:var(--fg-4);">{{ $panel->video_lesson->count() }} cápsulas práticas @if($panel->material->count() > 0) · {{ $panel->material->count() }} materiais de apoio @endif</div>
          </div>
        </div>
      </div>
      @endforeach
    </div>

    {{-- CTA leve --}}
    <div class="text-center mt-5 aos-fade">
      <a href="#oferta" class="btn-ux btn-ux-primary btn-ux-lg">
        <i data-lucide="zap" style="width:16px;height:16px;fill:currentColor;stroke:none;"></i>
        Quero aprender isso
      </a>
    </div>
  </div>
</section>

{{-- ================================================================
     3. SOBRE (se houver) — autoridade
     ================================================================ --}}
@if($curso->info)
<section class="section-py">
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
     4. CONTEÚDO PROGRAMÁTICO — accordion completo
     ================================================================ --}}
<section class="section-py" id="conteudo" style="background:var(--bg-1);border-top:1px solid var(--line-1);border-bottom:1px solid var(--line-1);">
  <div class="container">
    <div class="text-center mb-5 aos-fade">
      <div class="section-eyebrow">Conteúdo programático</div>
      <h2 class="section-title">Veja tudo o que está<br><span class="text-brand-gradient">dentro da minisérie</span></h2>
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
     5. PARA QUEM É + COMO FUNCIONA (compacto)
     ================================================================ --}}
<section class="section-py" id="como-funciona">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-6 aos-fade">
        <div class="section-eyebrow">Para quem é</div>
        <h2 class="section-title" style="font-size:clamp(22px,3vw,30px);">Feita para quem precisa<br>executar com segurança</h2>
        <div style="display:flex;flex-direction:column;gap:12px;margin-top:20px;">
          @foreach([
            'Servidores que precisam aplicar o tema na rotina do órgão',
            'Quem não tem tempo para cursos longos e teóricos',
            'Quem precisa comprovar capacitação com certificado válido',
            'Equipes administrativas de prefeituras de qualquer porte',
          ] as $item)
          <div style="display:flex;align-items:flex-start;gap:10px;font-size:14.5px;color:var(--fg-2);line-height:1.5;">
            <i data-lucide="user-check" style="width:17px;height:17px;stroke:var(--brand-300);fill:none;stroke-width:1.75;flex-shrink:0;margin-top:2px;"></i>
            {{ $item }}
          </div>
          @endforeach
        </div>
      </div>
      <div class="col-lg-6 aos-fade aos-delay-2">
        <div class="section-eyebrow">Como funciona</div>
        <h2 class="section-title" style="font-size:clamp(22px,3vw,30px);">Do clique à aplicação<br>em menos de 1 dia</h2>
        <div style="margin-top:20px;">
          @foreach([
            ['1','Matricule-se','Cartão, PIX ou boleto — em até 10x sem juros.'],
            ['2','Acesse em 5 minutos','Login imediato em qualquer dispositivo.'],
            ['3','Assista e aplique','Cápsulas curtas + materiais prontos para usar no órgão.'],
            ['4','Emita o certificado','Certificado MEC gerado automaticamente ao concluir.'],
          ] as $step)
          <div style="display:flex;gap:14px;margin-bottom:14px;">
            <div style="width:32px;height:32px;border-radius:50%;background:rgba(0,163,255,0.12);border:1px solid rgba(0,163,255,0.3);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-family:var(--font-display);font-weight:800;font-size:14px;color:var(--brand-300);">{{ $step[0] }}</div>
            <div>
              <div style="font-size:14.5px;font-weight:700;color:#fff;">{{ $step[1] }}</div>
              <div style="font-size:13px;color:var(--fg-3);">{{ $step[2] }}</div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ================================================================
     6. OFERTA — preço aparece SÓ AQUI, após todo o valor
     ================================================================ --}}
<section class="section-py" id="oferta" style="background:var(--bg-1);border-top:1px solid var(--line-1);border-bottom:1px solid var(--line-1);">
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
            <span class="btn-cart-label">Garantir minha vaga agora</span>
          </button>
          <a href="{{ $waCurso }}" target="_blank" class="btn-ux btn-ux-ghost btn-ux-sm" style="width:100%;justify-content:center;color:#25D366;border-color:rgba(37,211,102,0.4);margin-bottom:20px;">
            {!! $waIcon !!}
            Prefiro tirar dúvidas antes
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

        {{-- Selo de garantia --}}
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
     7. FAQ — mata objeções do tráfego frio
     ================================================================ --}}
<section class="section-py" id="faq">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="text-center mb-5 aos-fade">
          <div class="section-eyebrow">Perguntas frequentes</div>
          <h2 class="section-title">Dúvidas comuns antes<br><span class="text-brand-gradient">de se matricular</span></h2>
        </div>

        @foreach([
          ['q'=>'Em quanto tempo recebo o acesso?','a'=>'No cartão de crédito, em até 5 minutos. No PIX, assim que o pagamento confirma. No boleto, em 1 a 2 dias úteis.'],
          ['q'=>'O certificado é reconhecido?','a'=>'Sim. Emitido pela Faculdade Unypublica, reconhecida pelo MEC. Vale para progressão funcional, concursos e comprovação de capacitação.'],
          ['q'=>'Por quanto tempo tenho acesso?','a'=>'12 meses a partir da matrícula. Assista, revise e baixe os materiais quantas vezes quiser.'],
          ['q'=>'Posso pagar parcelado?','a'=>'Sim, em até 10x de R$ 98 sem juros no cartão. Também aceitamos PIX e boleto à vista.'],
          ['q'=>'E se eu não gostar?','a'=>'Você tem 7 dias de garantia incondicional. Pediu, devolvemos 100% do valor, sem burocracia.'],
          ['q'=>'Emite nota fiscal? Prefeitura pode comprar?','a'=>'Sim, nota fiscal para PF e PJ. Prefeituras podem comprar via CNPJ — fale com a gente no WhatsApp.'],
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
     8. CTA FINAL
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
          Comece <span class="text-brand-gradient">{{ \Illuminate\Support\Str::limit($curso->title, 40) }}</span> ainda hoje
        </h2>
        <p style="font-size:17px;color:var(--fg-3);line-height:1.65;margin-bottom:32px;max-width:540px;margin-left:auto;margin-right:auto;">
          {{ $totalVideos }} cápsulas práticas por 10x de R$ 98. Junte-se a mais de 49.000 servidores capacitados.
        </p>

        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
          <button class="btn-ux btn-ux-primary btn-ux-lg btn-add-to-cart"
                  data-course-id="{{ $curso->id }}"
                  data-course-title="{{ $curso->title }}"
                  data-course-price="{{ $preco }}"
                  data-course-thumb="{{ $thumb }}"
                  data-course-slug="{{ $curso->slug }}">
            <i data-lucide="zap" style="width:18px;height:18px;fill:currentColor;stroke:none;"></i>
            <span class="btn-cart-label">Garantir meu acesso</span>
          </button>
          <a href="{{ $waCurso }}" target="_blank" class="btn-ux btn-ux-lg" style="background:#25D366;color:#fff;border:none;">
            {!! $waIcon !!}
            Falar no WhatsApp
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- Barra fixa de conversão (mobile-first) --}}
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
    <span class="btn-cart-label">Garantir vaga</span>
  </button>
</div>

{{-- Botão flutuante WhatsApp --}}
<a href="{{ $waCurso }}" target="_blank" class="wa-float" aria-label="Fale conosco no WhatsApp">
  <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
  <span class="wa-label">Tirar dúvidas</span>
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
@keyframes waPulse { 0%{box-shadow:0 0 0 0 rgba(37,211,102,.55)}70%{box-shadow:0 0 0 16px rgba(37,211,102,0)}100%{box-shadow:0 0 0 0 rgba(37,211,102,0)} }
.wa-float{position:fixed;bottom:84px;right:24px;z-index:9990;display:flex;align-items:center;gap:10px;background:#25D366;color:#fff;padding:14px 20px 14px 16px;border-radius:999px;text-decoration:none;font-weight:700;font-size:14px;box-shadow:0 10px 30px -6px rgba(37,211,102,.5);animation:waPulse 2.4s infinite;transition:transform .2s;}
.wa-float:hover{transform:scale(1.05);color:#fff;}
@media(max-width:600px){ .wa-float{padding:14px;bottom:74px;} .wa-float .wa-label{display:none;} }
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
  // Abre a primeira temporada por padrão
  const first = document.querySelector('.lp-accordion-header');
  if (first) toggleAccordion(first);

  // Barra fixa aparece após rolar além do hero
  const stickyBar = document.getElementById('lp-sticky-bar');
  const oferta    = document.getElementById('oferta');
  window.addEventListener('scroll', function () {
    if (!stickyBar) return;
    const scrolled    = window.scrollY > 600;
    // Esconde quando a seção de oferta está visível (evita duplicidade)
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
        // Tráfego pago: leva direto ao checkout após 1.2s
        setTimeout(() => { window.location.href = '{{ route('checkout') }}'; }, 1200);
      } else {
        window.location.href = '{{ route('checkout') }}';
      }
    });
  });

  // Registra visualização no funil
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