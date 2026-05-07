@extends('layouts.app')
@section('title', 'Dashboard — Unyflex Digital')

@section('content')
<div class="scroll">

    {{-- Cabeçalho da página --}}
    <div class="page-head">
        <div>
            <div class="eyebrow">Bem-vinda de volta</div>
            <h1>Olá, {{ explode(' ', auth()->user()->name ?? 'servidor')[0] }}</h1>
            <p>
                Você está em uma sequência de <strong style="color:#fff;">7 dias</strong>.
                Continue de onde parou e mantenha o ritmo das cápsulas.
            </p>
        </div>
        <a href="{{ route('player', 1) }}" class="btn btn-primary">
            <i data-lucide="play" class="ico"></i>
            <span>Continuar assistindo</span>
        </a>
    </div>

    {{-- Stats --}}
    <div class="stats">
        <div class="stat">
            <div class="icon-tag">
                <svg viewBox="0 0 24 24"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z" stroke="currentColor" fill="none" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <div class="label">Sequência</div>
            <div class="value">7 dias</div>
            <div class="delta">+2 vs semana passada</div>
        </div>
        <div class="stat">
            <div class="icon-tag">
                <i data-lucide="clock" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
            </div>
            <div class="label">Tempo assistido</div>
            <div class="value">4h 32m</div>
            <div class="delta">+38m esta semana</div>
        </div>
        <div class="stat">
            <div class="icon-tag">
                <i data-lucide="check-circle" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
            </div>
            <div class="label">Cápsulas concluídas</div>
            <div class="value">12</div>
            <div class="delta">de 26 disponíveis</div>
        </div>
        <div class="stat">
            <div class="icon-tag">
                <i data-lucide="award" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
            </div>
            <div class="label">Certificados</div>
            <div class="value">2</div>
            <div class="delta" style="color:var(--fg-3);">Próximo: Patrimônio</div>
        </div>
    </div>

    {{-- Hero — Continue assistindo --}}
    <div class="hero">
        <div>
            <div class="eyebrow">Continue de onde parou</div>
            <h2>1.4 Controles Preventivos e Documentação</h2>
            <p>Como estruturar pontos de controle internos e gerar documentação auditável usando os modelos prontos da minissérie.</p>
            <div class="meta">
                <span>
                    <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span>14 min restantes</span>
                </span>
                <span>
                    <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"/><line x1="7" y1="2" x2="7" y2="22"/><line x1="17" y1="2" x2="17" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/></svg>
                    <span>Temporada 1 · cápsula 4 de 12</span>
                </span>
            </div>
            <div class="actions">
                <a href="{{ route('player', 1) }}" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" style="width:16px;height:16px;fill:currentColor;"><polygon points="6 4 20 12 6 20 6 4"/></svg>
                    <span>Retomar agora</span>
                </a>
                <button class="btn btn-ghost">
                    <i data-lucide="bookmark" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
                    <span>Salvar para depois</span>
                </button>
            </div>
        </div>
        <div class="hero-thumb">
            <div class="play">
                <svg viewBox="0 0 24 24"><polygon points="6 4 20 12 6 20 6 4"/></svg>
            </div>
            <div class="progress-pill">
                <div class="bar"><i style="width:42%;"></i></div>
                <div class="pct">42%</div>
            </div>
        </div>
    </div>

    {{-- Em andamento --}}
    <div class="section">
        <div class="section-head">
            <h3>Em andamento</h3>
            <a href="{{ route('cursos') }}">Ver tudo →</a>
        </div>
        <div class="grid-cards">
            <x-course-card
                tone="1"
                badge="EM ANDAMENTO"
                duration="2h 48min"
                eyebrow="MINISSÉRIE · 12 CÁPSULAS"
                title="Patrimônio e Frotas Públicas com I.A."
                desc="Levantamento, auditoria e controle de bens patrimoniais com apoio de inteligência artificial."
                :progress="42"
                href="{{ route('player', 1) }}"
            />
            <x-course-card
                tone="2"
                badge="EM ANDAMENTO"
                duration="1h 52min"
                eyebrow="MINISSÉRIE · 8 CÁPSULAS"
                title="Lei 14.133 na prática"
                desc="Como aplicar a Nova Lei de Licitações nos pregões eletrônicos do dia a dia da prefeitura."
                :progress="18"
                href="{{ route('player', 2) }}"
            />
            <x-course-card
                tone="3"
                badge="QUASE LÁ"
                duration="3h 04min"
                eyebrow="MINISSÉRIE · 14 CÁPSULAS"
                title="Pregão eletrônico avançado"
                desc="Estratégias de condução, análise de propostas e diligências bem documentadas."
                :progress="88"
                href="{{ route('player', 3) }}"
            />
        </div>
    </div>

    {{-- Recomendados --}}
    <div class="section">
        <div class="section-head">
            <h3>Recomendados para você</h3>
            <a href="{{ route('cursos') }}">Catálogo completo →</a>
        </div>
        <div class="grid-cards">
            <x-course-card
                tone="4" badge="NOVO" duration="1h 22min"
                eyebrow="MINISSÉRIE · 6 CÁPSULAS"
                title="Auditoria contínua com dashboards"
                desc="Construa indicadores que apontam riscos antes que virem problema."
                cta="Acessar"
                href="{{ route('cursos') }}"
            />
            <x-course-card
                tone="1" duration="58min"
                eyebrow="CÁPSULA AVULSA"
                title="Como redigir um Termo de Referência sem retrabalho"
                desc="Modelo comentado + checklist final."
                cta="Acessar"
                href="{{ route('cursos') }}"
            />
            <x-course-card
                tone="2" duration="2h 10min"
                eyebrow="MINISSÉRIE · 9 CÁPSULAS"
                title="Gestão de contratos públicos"
                desc="Do recebimento à fiscalização contínua, passando por aditivos."
                cta="Acessar"
                href="{{ route('cursos') }}"
            />
            <x-course-card
                tone="3" duration="46min"
                eyebrow="CÁPSULA AVULSA"
                title="LGPD para servidores municipais"
                desc="Aplicação prática nos processos administrativos."
                cta="Acessar"
                href="{{ route('cursos') }}"
            />
        </div>
    </div>

</div>
@endsection
