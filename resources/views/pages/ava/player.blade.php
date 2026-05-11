@extends('layouts.app')
@section('title', 'Player — Unyflex Digital')

@section('content')
<div class="scroll" style="padding: 24px 32px 36px;">

    {{-- Breadcrumb --}}
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:18px;">
        <a href="{{ route('ava.cursos') }}" class="btn btn-ghost" style="padding:8px 12px;">
            <i data-lucide="chevron-left" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;"></i>
            <span>Cursos</span>
        </a>
        <span style="color:var(--fg-4);">/</span>
        <span style="font-size:12px; font-weight:600; letter-spacing:0.14em; text-transform:uppercase; color:var(--brand-300);">
            {{ $curso['titulo'] }}
        </span>
        <span style="color:var(--fg-4);">·</span>
        <span style="font-size:12px; color:var(--fg-3);">{{ $capsula['meta'] }}</span>
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
                        <div class="ep-title">{{ $capsula['trecho'] }}</div>
                        <div class="ep-sub">UNYFLEX DIGITAL</div>
                    </div>
                </div>

                {{-- Placeholder de vídeo — trocar pelo embed real quando disponível --}}
                <div class="play-large" style="cursor:pointer;">
                    <svg viewBox="0 0 24 24"><polygon points="6 4 20 12 6 20 6 4"/></svg>
                </div>

                <div class="video-controls">
                    <span class="timestamp">{{ $capsula['posicaoAtual'] }}</span>
                    <div class="progress-bar">
                        <i style="width: {{ $capsula['progressoVideo'] }}%;"></i>
                    </div>
                    <span class="timestamp" style="color:var(--fg-3);">{{ $capsula['duracao'] }}</span>
                    <div class="ctrl-icons">
                        <button title="Velocidade" type="button">
                            <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                        </button>
                        <button title="Volume" type="button">
                            <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
                        </button>
                        <button title="Tela cheia" type="button">
                            <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Info da cápsula --}}
            <div class="lesson-info">
                <h2>{{ $capsula['numero'] }} {{ $capsula['titulo'] }}</h2>
                <div class="meta">
                    <span>
                        <svg style="width:14px;height:14px;stroke:var(--brand-300);fill:none;stroke-width:1.75;stroke-linecap:round;stroke-linejoin:round;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span>{{ $capsula['duracao'] }}</span>
                    </span>
                    <span>
                        <svg style="width:14px;height:14px;stroke:var(--brand-300);fill:none;stroke-width:1.75;stroke-linecap:round;stroke-linejoin:round;" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        <span>{{ $capsula['qtdMateriais'] }} materiais</span>
                    </span>
                    <span>
                        <svg style="width:14px;height:14px;stroke:var(--brand-300);fill:none;stroke-width:1.75;stroke-linecap:round;stroke-linejoin:round;" viewBox="0 0 24 24"><path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96-.44l-1.1-5.5A2.5 2.5 0 0 1 8 10.9V7.5A2.5 2.5 0 0 1 9.5 2z"/><path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96-.44l1.1-5.5A2.5 2.5 0 0 0 16 10.9V7.5A2.5 2.5 0 0 0 14.5 2z"/></svg>
                        <span>{{ $capsula['qtdMapas'] }} mapa mental</span>
                    </span>
                </div>
                <p>{{ $capsula['descricao'] }}</p>
                <div class="lesson-actions">
                    @if($idAnterior)
                        <a href="{{ route('player', $idAnterior) }}" class="btn btn-secondary">
                            <i data-lucide="chevron-left" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;"></i>
                            <span>Aula anterior</span>
                        </a>
                    @endif
                    @if($idProximo)
                        <a href="{{ route('player', $idProximo) }}" class="btn btn-primary">
                            <span>Próxima aula</span>
                            <i data-lucide="chevron-right" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;"></i>
                        </a>
                    @endif
                    <button class="btn btn-ghost" type="button">
                        <i data-lucide="bookmark" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
                        <span>Salvar</span>
                    </button>
                    <button class="btn btn-ghost" type="button">
                        <i data-lucide="download" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
                        <span>Materiais</span>
                    </button>
                </div>
            </div>

            {{-- Abas --}}
            <div class="tabs" id="player-tabs">
                <button class="tab active" data-tab="resumo" type="button">
                    <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:1.75;" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <span>Resumo</span>
                </button>
                <button class="tab" data-tab="mapa" type="button">
                    <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:1.75;" viewBox="0 0 24 24"><path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96-.44l-1.1-5.5A2.5 2.5 0 0 1 8 10.9V7.5A2.5 2.5 0 0 1 9.5 2z"/><path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96-.44l1.1-5.5A2.5 2.5 0 0 0 16 10.9V7.5A2.5 2.5 0 0 0 14.5 2z"/></svg>
                    <span>Mapa Mental</span>
                </button>
                <button class="tab" data-tab="podcast" type="button">
                    <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:1.75;" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
                    <span>Podcast</span>
                </button>
                <button class="tab" data-tab="checklist" type="button">
                    <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:1.75;" viewBox="0 0 24 24"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    <span>Checklist</span>
                </button>
            </div>

            {{-- Painel: Resumo --}}
            <div class="tab-panel" data-panel="resumo">
                <h4 class="tp-h">Resumo da cápsula</h4>
                {!! $capsula['resumo'] !!}
                <p class="tp-p" style="color:var(--fg-3); margin-top:14px;">Última atualização: {{ $capsula['atualizadoEm'] }}</p>
            </div>

            {{-- Painel: Mapa Mental --}}
            <div class="tab-panel" data-panel="mapa" style="display:none;">
                <h4 class="tp-h">Mapa mental</h4>
                <p class="tp-p">Visão de uma página dos conceitos da cápsula — imprima ou cole no caderno.</p>
                <div class="mindmap">
                    <div style="text-align:center;">
                        <div style="font-family:var(--font-mono);font-size:11px;color:var(--brand-300);letter-spacing:0.14em;">MAPA MENTAL</div>
                        <div style="font-family:var(--font-display);font-size:22px;color:#fff;margin-top:8px;font-weight:600;">{{ $capsula['titulo'] }}</div>
                        <div style="display:flex;gap:16px;margin-top:24px;justify-content:center;flex-wrap:wrap;">
                            @foreach($capsula['mapaConceitos'] as $conceito)
                                <div style="padding:10px 14px;background:var(--bg-2);border:1px solid rgba(0,163,255,0.30);border-radius:10px;font-size:12px;color:#fff;">{{ $conceito }}</div>
                            @endforeach
                        </div>
                        <button class="btn btn-ghost" style="margin-top:22px;" type="button">
                            <i data-lucide="download" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
                            <span>Baixar PDF</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Painel: Podcast --}}
            <div class="tab-panel" data-panel="podcast" style="display:none;">
                <h4 class="tp-h">Versão em áudio</h4>
                <p class="tp-p">Mesmo conteúdo da cápsula, com narração estendida e exemplos extras — ouça no caminho do trabalho.</p>
                <div class="podcast">
                    <div class="cover">
                        <svg viewBox="0 0 24 24" style="width:30px;height:30px;fill:currentColor;"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/></svg>
                    </div>
                    <div class="meta">
                        <div class="eyebrow">Episódio {{ $capsula['numero'] }} · {{ $capsula['duracaoPodcast'] }}</div>
                        <h4>{{ $capsula['titulo'] }}</h4>
                        <div class="row">
                            <div class="bar"><i style="width:{{ $capsula['progressoPodcast'] }}%;"></i></div>
                            <span class="ts">{{ $capsula['posicaoPodcast'] }} / {{ $capsula['duracaoPodcast'] }}</span>
                        </div>
                    </div>
                    <button class="play-mini" type="button">
                        <svg viewBox="0 0 24 24"><polygon points="6 4 20 12 6 20 6 4"/></svg>
                    </button>
                </div>
            </div>

            {{-- Painel: Checklist --}}
            <div class="tab-panel" data-panel="checklist" style="display:none;">
                <h4 class="tp-h">Checklist da cápsula</h4>
                <p class="tp-p" style="margin-bottom:18px;">Marque conforme aplicar no seu setor. Salva automaticamente.</p>
                <ul class="tp-list">
                    @foreach($capsula['checklist'] as $item)
                        <li class="tp-check {{ $item['feito'] ? 'done' : '' }}" data-check-id="{{ $item['id'] }}" style="cursor:pointer;">
                            <span class="box">
                                @if($item['feito'])
                                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                @endif
                            </span>
                            <span class="lbl">{{ $item['texto'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

        </div>{{-- /.video-pane --}}

        {{-- ══════════ COLUNA DIREITA: lista de cápsulas ══════════ --}}
        <aside class="lessons">
            <div class="lessons-head">
                <div class="eyebrow">{{ $temporada['label'] }}</div>
                <h3>{{ $temporada['titulo'] }}</h3>
                <div class="progress-row">
                    <div class="bar"><i style="width:{{ $temporada['progresso'] }}%;"></i></div>
                    <span class="pct">{{ $temporada['concluidas'] }} / {{ $temporada['total'] }}</span>
                </div>
            </div>
            <div class="lessons-list">
                @foreach($capsulas as $item)
                    <a href="{{ route('player', $item['id']) }}"
                       class="lesson-row {{ $item['ativa'] ? 'active' : '' }} {{ $item['feita'] ? 'done' : '' }}"
                       style="text-decoration:none; color:inherit;">
                        <div class="num">
                            @if($item['feita'])
                                <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            @elseif($item['ativa'])
                                <svg viewBox="0 0 24 24"><polygon points="6 4 20 12 6 20 6 4"/></svg>
                            @else
                                {{ $item['n'] }}
                            @endif
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div class="ep-title">{{ $item['n'] }} {{ $item['titulo'] }}</div>
                            <div class="ep-meta">{{ $item['duracao'] }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </aside>

    </div>{{-- /.player-grid --}}
</div>
@endsection

@push('scripts')
<script>
// Troca de abas
document.querySelectorAll('#player-tabs .tab').forEach(btn => {
    btn.addEventListener('click', () => {
        const target = btn.dataset.tab;
        document.querySelectorAll('#player-tabs .tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.style.display = 'none');
        btn.classList.add('active');
        document.querySelector(`.tab-panel[data-panel="${target}"]`).style.display = '';
        if (window.lucide) window.lucide.createIcons();
    });
});

// Checklist toggle
document.querySelectorAll('.tp-check').forEach(item => {
    item.addEventListener('click', () => {
        item.classList.toggle('done');
        const box = item.querySelector('.box');
        if (item.classList.contains('done')) {
            box.innerHTML = '<svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>';
        } else {
            box.innerHTML = '';
        }
        // TODO: POST via fetch para persistir
        // fetch('{{ route("player.concluir", ":id") }}'.replace(':id', item.dataset.checkId), {
        //     method: 'POST',
        //     headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json'}
        // });
    });
});
</script>
@endpush
