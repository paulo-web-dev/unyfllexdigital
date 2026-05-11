@extends('layouts.app')
@section('title', 'Minisséries — Unyflex Digital')

@section('content')
<div class="scroll">

    {{-- Cabeçalho --}}
    <div class="page-head">
        <div>
            <div class="eyebrow">Catálogo</div>
            <h1>Minisséries</h1>
            <p>Cápsulas de 10 a 20 minutos pensadas para servidores municipais aplicarem o conteúdo na rotina logo após assistir.</p>
        </div>
    </div>

    {{-- Spotlight --}}
    <div class="spotlight">
        <div>
            <div class="eyebrow">Lançamento desta semana</div>
            <h2>{{ $spotlight['titulo'] }}</h2>
            <p>{{ $spotlight['descricao'] }}</p>
            <div class="actions">
                <a href="{{ route('player', $spotlight['player_id']) }}" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" style="width:16px;height:16px;fill:currentColor;"><polygon points="6 4 20 12 6 20 6 4"/></svg>
                    <span>Começar agora</span>
                </a>
                <button class="btn btn-ghost" type="button">
                    <i data-lucide="file-text" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
                    <span>Ver ementa</span>
                </button>
            </div>
        </div>
        <div class="spotlight-art">
            <div class="ring">
                <img src="{{ asset('img/logo-unyflex.png') }}" alt="Unyflex Digital">
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('ava.cursos') }}">
        <div class="filter-bar">
            @foreach($filtros as $filtro)
                <button
                    type="submit"
                    name="categoria"
                    value="{{ $filtro['valor'] }}"
                    class="chip {{ $categoriaAtiva === $filtro['valor'] ? 'active' : '' }}"
                >{{ $filtro['label'] }}</button>
            @endforeach
            <span style="margin-left:auto; font-size:12px; color:var(--fg-3);">
                {{ $totalMinisseries }} minisséries · {{ $totalCapsulas }} cápsulas
            </span>
        </div>
    </form>

    {{-- Grid de cursos --}}
    <div class="grid-cards">
        @forelse($cursos as $curso)
            <x-course-card
                :tone="$curso['tone']"
                :badge="$curso['badge'] ?? null"
                :duration="$curso['duracao']"
                :eyebrow="$curso['eyebrow']"
                :title="$curso['titulo']"
                :desc="$curso['descricao']"
                :progress="$curso['progresso'] ?? null"
                :cta="isset($curso['progresso']) ? null : 'Acessar'"
                :href="route('player', $curso['player_id'])"
            />
        @empty
            <p style="color:var(--fg-3); grid-column:1/-1;">Nenhuma minissérie encontrada.</p>
        @endforelse
    </div>

</div>
@endsection

@push('styles')
<style>
/* Spotlight — componente que ainda não está no ava.css do projeto */
.spotlight {
    position: relative;
    border-radius: 22px;
    overflow: hidden;
    padding: 28px 32px;
    margin-bottom: 28px;
    background:
        radial-gradient(60% 110% at 90% 50%, rgba(0,163,255,0.25), transparent 60%),
        linear-gradient(120deg, #0F1726 0%, #050A18 100%);
    border: 1px solid var(--line-2);
    display: grid;
    grid-template-columns: 1fr 220px;
    gap: 24px;
    align-items: center;
}
.spotlight .eyebrow { color: var(--brand-300); }
.spotlight h2 { font: var(--t-h2); color: #fff; margin: 8px 0; letter-spacing: -0.015em; }
.spotlight p  { color: var(--fg-3); margin: 0 0 16px; max-width: 480px; }
.spotlight .actions { display: flex; gap: 10px; flex-wrap: wrap; }
.spotlight-art { display: flex; align-items: center; justify-content: center; }
.spotlight-art .ring {
    width: 180px; height: 180px; border-radius: 50%;
    background: #000;
    box-shadow: 0 0 60px -10px rgba(0,163,255,0.55), 0 0 0 1px rgba(0,163,255,0.30);
    overflow: hidden;
    display: flex; align-items: center; justify-content: center;
}
.spotlight-art .ring img { width: 100%; height: 100%; object-fit: cover; }

/* Filter bar */
.filter-bar { display: flex; align-items: center; gap: 10px; margin-bottom: 22px; flex-wrap: wrap; }
.chip { font-size:12px; font-weight:500; color:var(--fg-2); background:var(--bg-2); border:1px solid var(--line-2); padding:8px 14px; border-radius:var(--r-pill); cursor:pointer; transition:all var(--dur) var(--ease-out); }
.chip:hover { background:var(--bg-3); color:#fff; }
.chip.active { background:rgba(0,163,255,0.12); border-color:rgba(0,163,255,0.45); color:var(--brand-200); box-shadow:0 0 14px -4px rgba(0,163,255,0.45); }
</style>
@endpush
