@extends('layouts.app')
@section('title', 'Player — Unyflex Digital')

@section('content')
<div class="scroll" style="padding: 24px 32px 36px;">

    {{-- Breadcrumb --}}
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:18px;">
        <a href="{{ route('cursos') }}" class="btn btn-ghost" style="padding:8px 12px;">
            <i data-lucide="chevron-left" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;"></i>
            <span>Cursos</span>
        </a>
        <span style="color:var(--fg-4);">/</span>
        <span style="font-size:12px; font-weight:600; letter-spacing:0.14em; text-transform:uppercase; color:var(--brand-300);">
            Patrimônio e Frotas Públicas com I.A.
        </span>
        <span style="color:var(--fg-4);">·</span>
        <span style="font-size:12px; color:var(--fg-3);">Temporada 1 · cápsula 4 de 12</span>
    </div>

    {{-- Grid player --}}
    <div class="player-grid">

        {{-- ══════════ COLUNA ESQUERDA: vídeo + info + abas ══════════ --}}
        <div class="video-pane">

            {{-- Vídeo --}}
            <div class="video">
                <div class="video-head">
                    <div class="mark">
                        <img src="{{ asset('img/logo-unyflex.png') }}" alt="Unyflex">
                    </div>
                    <div class="title-row">
                        <div class="ep-title">Trecho 1 — Controles Preventivos e Documentação</div>
                        <div class="ep-sub">UNYFLEX DIGITAL</div>
                    </div>
                </div>

                <div class="play-large">
                    <svg viewBox="0 0 24 24"><polygon points="6 4 20 12 6 20 6 4"/></svg>
                </div>

                <div class="video-controls">
                    <span class="timestamp">05:31</span>
                    <div class="progress-bar"><i></i></div>
                    <span class="timestamp" style="color:var(--fg-3);">14:32</span>
                    <div class="ctrl-icons">
                        <button title="Velocidade">
                            <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;">
                                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                            </svg>
                        </button>
                        <button title="Volume">
                            <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;">
                                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/>
                                <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/>
                            </svg>
                        </button>
                        <button title="Tela cheia">
                            <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;">
                                <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Info da aula --}}
            <div class="lesson-info">
                <h2>1.4 Controles Preventivos e Documentação</h2>
                <div class="meta">
                    <span>
                        <svg style="width:14px;height:14px;stroke:var(--brand-300);fill:none;stroke-width:1.75;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span>14:32 minutos</span>
                    </span>
                    <span>
                        <svg style="width:14px;height:14px;stroke:var(--brand-300);fill:none;stroke-width:1.75;" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        <span>3 materiais</span>
                    </span>
                    <span>
                        <svg style="width:14px;height:14px;stroke:var(--brand-300);fill:none;stroke-width:1.75;" viewBox="0 0 24 24"><path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96-.46 2.5 2.5 0 0 1-1.07-4.61V7a3.5 3.5 0 0 1 3.53-5z"/></svg>
                        <span>1 mapa mental</span>
                    </span>
                </div>
                <p>
                    Nesta cápsula você vai estruturar pontos de controle internos para o setor de patrimônio e gerar
                    documentação auditável a partir dos modelos prontos da minissérie. Foco em aplicação imediata:
                    ao final, você sai com um checklist preenchível e um modelo de relatório.
                </p>
                <div class="lesson-actions">
                    <button class="btn btn-secondary">
                        <i data-lucide="chevron-left" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;"></i>
                        <span>Aula anterior</span>
                    </button>
                    <button class="btn btn-primary">
                        <span>Próxima aula</span>
                        <i data-lucide="chevron-right" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;"></i>
                    </button>
                    <button class="btn btn-ghost">
                        <i data-lucide="bookmark" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
                        <span>Salvar</span>
                    </button>
                    <button class="btn btn-ghost">
                        <i data-lucide="download" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
                        <span>Materiais</span>
                    </button>
                </div>
            </div>

            {{-- Abas --}}
            <div class="tabs" id="player-tabs">
                <button class="tab active" onclick="switchTab('resumo', this)">
                    <i data-lucide="file-text" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
                    <span>Resumo</span>
                </button>
                <button class="tab" onclick="switchTab('mapa', this)">
                    <i data-lucide="brain-circuit" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
                    <span>Mapa Mental</span>
                </button>
                <button class="tab" onclick="switchTab('podcast', this)">
                    <i data-lucide="mic" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
                    <span>Podcast</span>
                </button>
                <button class="tab" onclick="switchTab('checklist', this)">
                    <i data-lucide="check-square" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
                    <span>Checklist</span>
                </button>
            </div>

            {{-- Painel Resumo --}}
            <div class="tab-panel" id="tab-resumo">
                <h4 class="tp-h">Resumo da cápsula</h4>
                <p class="tp-p">
                    Controles preventivos são pontos do processo onde a probabilidade de um erro virar problema é alta.
                    Identificá-los exige mapear o fluxo do bem patrimonial — da entrada à baixa — e marcar os momentos
                    em que falta documentação ou confirmação humana.
                </p>
                <p class="tp-p">
                    A documentação auditável segue três princípios:
                    <strong style="color:#fff;">rastreabilidade</strong> (quem, quando, com base em quê),
                    <strong style="color:#fff;">imutabilidade</strong> (não se edita, se anexa correção) e
                    <strong style="color:#fff;">recuperação</strong> (qualquer auditor encontra em &lt; 5 minutos).
                </p>
                <p class="tp-p" style="color:var(--fg-3);">
                    Tempo estimado de leitura: 4 min · Última atualização: 06/05/2026
                </p>
            </div>

            {{-- Painel Mapa Mental --}}
            <div class="tab-panel" id="tab-mapa" style="display:none;">
                <h4 class="tp-h">Mapa mental</h4>
                <p class="tp-p">Visão de uma página dos conceitos da cápsula, com setas e destaques para imprimir ou colar no caderno.</p>
                <div class="mindmap">
                    <div style="text-align:center;">
                        <div style="font-family:var(--font-mono); font-size:11px; color:var(--brand-300); letter-spacing:0.14em;">MAPA MENTAL</div>
                        <div style="font-family:var(--font-display); font-size:22px; color:#fff; margin-top:8px; font-weight:600;">Controles Preventivos</div>
                        <div style="display:flex; gap:16px; margin-top:24px; justify-content:center; flex-wrap:wrap;">
                            <div style="padding:10px 14px; background:var(--bg-2); border:1px solid rgba(0,163,255,0.30); border-radius:10px; font-size:12px; color:#fff;">Rastreabilidade</div>
                            <div style="padding:10px 14px; background:var(--bg-2); border:1px solid rgba(0,163,255,0.30); border-radius:10px; font-size:12px; color:#fff;">Imutabilidade</div>
                            <div style="padding:10px 14px; background:var(--bg-2); border:1px solid rgba(0,163,255,0.30); border-radius:10px; font-size:12px; color:#fff;">Recuperação</div>
                        </div>
                        <button class="btn btn-ghost" style="margin-top:22px;">
                            <i data-lucide="download" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
                            <span>Baixar PDF</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Painel Podcast --}}
            <div class="tab-panel" id="tab-podcast" style="display:none;">
                <h4 class="tp-h">Versão em áudio</h4>
                <p class="tp-p">Ouça a cápsula no caminho do trabalho — mesmo conteúdo, narração estendida com exemplos extras.</p>
                <div class="podcast">
                    <div class="cover">
                        <svg viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z" fill="currentColor"/></svg>
                    </div>
                    <div class="meta">
                        <div class="eyebrow">Episódio 4 · 22 min</div>
                        <h4>Controles Preventivos e Documentação</h4>
                        <div class="row">
                            <div class="bar"><i style="width:22%;"></i></div>
                            <span class="ts">04:48 / 22:30</span>
                        </div>
                    </div>
                    <button class="play-mini">
                        <svg viewBox="0 0 24 24"><polygon points="6 4 20 12 6 20 6 4" fill="currentColor"/></svg>
                    </button>
                </div>
            </div>

            {{-- Painel Checklist --}}
            <div class="tab-panel" id="tab-checklist" style="display:none;">
                <h4 class="tp-h">Checklist da cápsula</h4>
                <p class="tp-p" style="margin-bottom:18px;">Marque conforme aplicar no seu setor. Salva automaticamente.</p>
                <ul class="tp-list">
                    <li class="tp-check done">
                        <span class="box">
                            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" stroke="currentColor" fill="none" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <span class="lbl">Mapeei o fluxo do bem patrimonial da entrada à baixa</span>
                    </li>
                    <li class="tp-check done">
                        <span class="box">
                            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" stroke="currentColor" fill="none" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <span class="lbl">Identifiquei os 3 pontos críticos do meu setor</span>
                    </li>
                    <li class="tp-check">
                        <span class="box"></span>
                        <span class="lbl">Adicionei rastreabilidade (quem / quando / base) em cada ponto</span>
                    </li>
                    <li class="tp-check">
                        <span class="box"></span>
                        <span class="lbl">Defini formato imutável de registro (anexo, não edição)</span>
                    </li>
                    <li class="tp-check">
                        <span class="box"></span>
                        <span class="lbl">Testei recuperação: auditor encontra documento em &lt; 5 min</span>
                    </li>
                </ul>
            </div>

        </div>
        {{-- /video-pane --}}

        {{-- ══════════ COLUNA DIREITA: lista de aulas ══════════ --}}
        <aside class="lessons">
            <div class="lessons-head">
                <div class="eyebrow">Temporada 1</div>
                <h3>Levantamento de Infraestrutura e Recursos</h3>
                <div class="progress-row">
                    <div class="bar"><i style="width:30%;"></i></div>
                    <span class="pct">3 / 10</span>
                </div>
            </div>
            <div class="lessons-list">

                @php
                $lessons = [
                    ['n'=>'1.1',  'title'=>'Introdução e Apresentação do Curso',    'dur'=>'12:48', 'done'=>true,  'active'=>false],
                    ['n'=>'1.2',  'title'=>'Fundamentos da Auditoria Automatizada', 'dur'=>'15:20', 'done'=>true,  'active'=>false],
                    ['n'=>'1.3',  'title'=>'Gestão por Riscos e Priorização',       'dur'=>'18:04', 'done'=>true,  'active'=>false],
                    ['n'=>'1.4',  'title'=>'Controles Preventivos e Documentação',  'dur'=>'14:32', 'done'=>false, 'active'=>true],
                    ['n'=>'1.5',  'title'=>'Cruzamento de Dados entre Setores',     'dur'=>'17:11', 'done'=>false, 'active'=>false],
                    ['n'=>'1.6',  'title'=>'Gestão de Pessoas e Desafios',          'dur'=>'13:45', 'done'=>false, 'active'=>false],
                    ['n'=>'1.7',  'title'=>'Integração com Sistemas de TI',         'dur'=>'16:08', 'done'=>false, 'active'=>false],
                    ['n'=>'1.8',  'title'=>'Indicadores de Performance',            'dur'=>'11:50', 'done'=>false, 'active'=>false],
                    ['n'=>'1.9',  'title'=>'Painéis e Visualização de Dados',       'dur'=>'19:22', 'done'=>false, 'active'=>false],
                    ['n'=>'1.10', 'title'=>'Revisão e Próximos Passos',             'dur'=>'09:36', 'done'=>false, 'active'=>false],
                ];
                @endphp

                @foreach($lessons as $lesson)
                <div class="lesson-row {{ $lesson['active'] ? 'active' : '' }} {{ $lesson['done'] ? 'done' : '' }}">
                    <div class="num">
                        @if($lesson['done'])
                            <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        @elseif($lesson['active'])
                            <svg viewBox="0 0 24 24">
                                <polygon points="6 4 20 12 6 20 6 4" fill="currentColor"/>
                            </svg>
                        @else
                            {{ $lesson['n'] }}
                        @endif
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div class="ep-title">{{ $lesson['n'] }} {{ $lesson['title'] }}</div>
                        <div class="ep-meta">{{ $lesson['dur'] }}</div>
                    </div>
                </div>
                @endforeach

            </div>
        </aside>
        {{-- /lessons --}}

    </div>
    {{-- /player-grid --}}

</div>
@endsection

@push('scripts')
<script>
function switchTab(id, btn) {
    // Ocultar todos os painéis
    document.querySelectorAll('.tab-panel').forEach(p => p.style.display = 'none');
    // Desativar todas as abas
    document.querySelectorAll('#player-tabs .tab').forEach(t => t.classList.remove('active'));
    // Mostrar o painel selecionado
    document.getElementById('tab-' + id).style.display = 'block';
    btn.classList.add('active');
    // Re-inicializar ícones Lucide no painel recém-visível
    if (window.lucide) lucide.createIcons();
}
</script>
@endpush
