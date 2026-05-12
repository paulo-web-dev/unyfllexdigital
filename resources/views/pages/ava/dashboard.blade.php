@extends('layouts.app')
@section('title', 'Dashboard — Unyflex Digital')

@section('content')
<div class="scroll">

    {{-- ═══════════════════════════════════════════════════════
         CABEÇALHO
    ═══════════════════════════════════════════════════════ --}}
    <div class="page-head">
        <div>
            <div class="eyebrow">Bem-vindo{{ auth()->user()->gender === 'F' ? 'a' : '' }} de volta</div>
            <h1>Olá, {{ $nomeAluno }} 👋</h1>
            <p>
                @if($stats['sequencia'] !== '—')
                    Você está em uma sequência de <strong style="color:#fff;">{{ $stats['sequencia'] }}</strong>.
                    Continue de onde parou e mantenha o ritmo.
                @else
                    Que tal assistir sua primeira cápsula hoje?
                    Cada aula é um passo a mais na sua carreira.
                @endif
            </p>
        </div>
        <a href="{{ route('player', $ultimaCapsula['slug']) }}" class="btn btn-primary">
            <i data-lucide="play" class="ico"></i>
            <span>{{ $ultimaCapsula['progresso'] > 0 ? 'Continuar assistindo' : 'Começar agora' }}</span>
        </a>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         ESTATÍSTICAS
    ═══════════════════════════════════════════════════════ --}}
    <div class="stats">
        <div class="stat">
            <div class="icon-tag">
                <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;stroke-linecap:round;stroke-linejoin:round;">
                    <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
                </svg>
            </div>
            <div class="label">Sequência</div>
            <div class="value">{{ $stats['sequencia'] }}</div>
            <div class="delta">{{ $stats['sequenciaDelta'] }}</div>
        </div>

        <div class="stat">
            <div class="icon-tag">
                <i data-lucide="clock" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
            </div>
            <div class="label">Tempo assistido</div>
            <div class="value">{{ $stats['tempoAssistido'] }}</div>
            <div class="delta">{{ $stats['tempoDelta'] }}</div>
        </div>

        <div class="stat">
            <div class="icon-tag">
                <i data-lucide="check-circle" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
            </div>
            <div class="label">Cápsulas concluídas</div>
            <div class="value">{{ $stats['capsulasConcluidas'] }}</div>
            <div class="delta">{{ $stats['capsulasDelta'] }}</div>
        </div>

        <div class="stat">
            <div class="icon-tag">
                <i data-lucide="award" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
            </div>
            <div class="label">Certificados</div>
            <div class="value">{{ $stats['certificados'] }}</div>
            <div class="delta" style="color:var(--fg-3);">{{ $stats['certificadosDelta'] }}</div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         HERO — CONTINUE DE ONDE PAROU
    ═══════════════════════════════════════════════════════ --}}
    <div class="hero">
        <div>
            <div class="eyebrow">
                {{ $ultimaCapsula['progresso'] > 0 ? 'Continue de onde parou' : 'Comece agora' }}
            </div>
            <h2>{{ $ultimaCapsula['numero'] }} — {{ $ultimaCapsula['titulo'] }}</h2>
            <p>{{ $ultimaCapsula['descricao'] }}</p>
            <div class="meta">
                <span>
                    <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <span>{{ $ultimaCapsula['tempoRestante'] }} restantes</span>
                </span>
                <span>
                    <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="2" width="20" height="20" rx="2.18"/>
                        <line x1="7" y1="2" x2="7" y2="22"/>
                        <line x1="17" y1="2" x2="17" y2="22"/>
                        <line x1="2" y1="12" x2="22" y2="12"/>
                    </svg>
                    <span>{{ $ultimaCapsula['meta'] }}</span>
                </span>
            </div>
            <div class="actions">
                <a href="{{ route('player', $ultimaCapsula['slug']) }}" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" style="width:16px;height:16px;fill:currentColor;">
                        <polygon points="6 4 20 12 6 20 6 4"/>
                    </svg>
                    <span>{{ $ultimaCapsula['progresso'] > 0 ? 'Retomar agora' : 'Assistir agora' }}</span>
                </a>
                <button class="btn btn-ghost" type="button">
                    <i data-lucide="bookmark" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
                    <span>Salvar para depois</span>
                </button>
            </div>
        </div>

        <div class="hero-thumb">
            <a href="{{ route('player', $ultimaCapsula['slug']) }}" class="play">
                <svg viewBox="0 0 24 24"><polygon points="6 4 20 12 6 20 6 4"/></svg>
            </a>
            <div class="progress-pill">
                <div class="bar">
                    <i style="width: {{ $ultimaCapsula['progresso'] }}%;"></i>
                </div>
                <div class="pct">{{ $ultimaCapsula['progresso'] }}%</div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         CURSOS EM ANDAMENTO
    ═══════════════════════════════════════════════════════ --}}
    <div class="section">
        <div class="section-head">
            <h3>Em andamento</h3>
            <a href="{{ route('ava.cursos') }}">Ver tudo →</a>
        </div>

        @if($cursosEmAndamento->isEmpty())
            {{-- Estado vazio elegante --}}
            <div style="
                padding: 40px 24px;
                text-align: center;
                background: var(--bg-2);
                border: 1px dashed var(--line-2);
                border-radius: var(--r-lg);
                color: var(--fg-3);
            ">
                <i data-lucide="film" style="width:32px;height:32px;stroke:var(--brand-300);fill:none;stroke-width:1.5;margin-bottom:12px;display:block;margin-left:auto;margin-right:auto;"></i>
                <p style="margin:0;font-size:14px;">Você ainda não iniciou nenhuma minissérie.</p>
                <a href="{{ route('ava.cursos') }}" class="btn btn-primary" style="margin-top:14px;display:inline-flex;">
                    Explorar catálogo
                </a>
            </div>
        @else
            <div class="grid-cards">
                @foreach($cursosEmAndamento as $curso)
                    <x-course-card
                        :tone="$curso['tone']"
                        :badge="$curso['badge'] ?? null"
                        :duration="$curso['duracao']"
                        :eyebrow="$curso['eyebrow']"
                        :title="$curso['titulo']"
                        :desc="$curso['descricao']"
                        :progress="$curso['progresso']"
                        :photo="$curso['photo'] ?? null"
                        :href="route('player', $curso['slug'])"
                    />
                @endforeach
            </div>
        @endif
    </div>

   
</div>
@endsection
