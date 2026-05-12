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

    {{-- Spotlight — minissérie mais recente com matrícula --}}
    @if($spotlight)
    <div class="spotlight">
        <div>
            <div class="eyebrow">Sua minissérie mais recente</div>
            <h2>{{ $spotlight['titulo'] }}</h2>
            <p>{{ $spotlight['descricao'] }}</p>
            <div class="actions">
                <a href="{{ route('player', $spotlight['slug']) }}" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" style="width:16px;height:16px;fill:currentColor;"><polygon points="6 4 20 12 6 20 6 4"/></svg>
                    <span>Continuar assistindo</span>
                </a>
            </div>
        </div>
        <div class="spotlight-art">
            <div class="ring">
                @if($spotlight['photo'])
                    <img src="https://unyflex.com.br/storage/cursos/banner/{{ $spotlight['photo'] }}" alt="{{ $spotlight['titulo'] }}">
                @else
                    <img src="{{ asset('img/logo-unyflex.png') }}" alt="Unyflex Digital">
                @endif
            </div>
        </div>
    </div>
    @endif

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
                {{ $totalMinisseries }} {{ $totalMinisseries === 1 ? 'minissérie' : 'minisséries' }}
                · {{ $totalCapsulas }} {{ $totalCapsulas === 1 ? 'cápsula' : 'cápsulas' }}
            </span>
        </div>
    </form>

    {{-- Grid de cursos --}}
    @if($cursos->isEmpty())
        <div style="
            padding: 60px 24px;
            text-align: center;
            background: var(--bg-2);
            border: 1px dashed var(--line-2);
            border-radius: var(--r-lg);
            color: var(--fg-3);
        ">
            <i data-lucide="film" style="width:36px;height:36px;stroke:var(--brand-300);fill:none;stroke-width:1.5;margin-bottom:14px;display:block;margin-left:auto;margin-right:auto;"></i>
            @if($categoriaAtiva !== 'todos')
                <p style="margin:0 0 16px;font-size:14px;">Nenhuma minissérie encontrada para este filtro.</p>
                <a href="{{ route('ava.cursos') }}" class="btn btn-ghost" style="display:inline-flex;">Ver todas</a>
            @else
                <p style="margin:0 0 16px;font-size:14px;">Você ainda não possui nenhuma minissérie.</p>
            @endif
        </div>
    @else
        <div class="grid-cards">
            @foreach($cursos as $curso)
                <x-course-card
                    :tone="$curso['tone']"
                    :badge="$curso['badge'] ?? null"
                    :duration="$curso['duracao']"
                    :eyebrow="$curso['eyebrow']"
                    :title="$curso['titulo']"
                    :desc="$curso['descricao']"
                    :progress="$curso['progresso'] ?? null"
                    :cta="is_null($curso['progresso']) ? 'Acessar' : null"
                    :photo="$curso['photo'] ?? null"
                    :href="$curso['slug'] ? route('player', $curso['slug']) : route('ava.cursos')"
                />
            @endforeach
        </div>
    @endif

</div>
@endsection

@push('styles')
<style>
.spotlight {
    position: relative; border-radius: 22px; overflow: hidden;
    padding: 28px 32px; margin-bottom: 28px;
    background: radial-gradient(60% 110% at 90% 50%, rgba(0,163,255,0.25), transparent 60%),
                linear-gradient(120deg, #0F1726 0%, #050A18 100%);
    border: 1px solid var(--line-2);
    display: grid; grid-template-columns: 1fr 220px; gap: 24px; align-items: center;
}
.spotlight .eyebrow { color: var(--brand-300); }
.spotlight h2 { font: var(--t-h2); color: #fff; margin: 8px 0; letter-spacing: -0.015em; }
.spotlight p  { color: var(--fg-3); margin: 0 0 16px; max-width: 480px; }
.spotlight .actions { display: flex; gap: 10px; flex-wrap: wrap; }
.spotlight-art { display: flex; align-items: center; justify-content: center; }
.spotlight-art .ring {
    width: 180px; height: 180px; border-radius: 50%; background: #000;
    box-shadow: 0 0 60px -10px rgba(0,163,255,0.55), 0 0 0 1px rgba(0,163,255,0.30);
    overflow: hidden; display: flex; align-items: center; justify-content: center;
}
.spotlight-art .ring img { width: 100%; height: 100%; object-fit: cover; }
.filter-bar { display: flex; align-items: center; gap: 10px; margin-bottom: 22px; flex-wrap: wrap; }
.chip { font-size:12px; font-weight:500; color:var(--fg-2); background:var(--bg-2); border:1px solid var(--line-2); padding:8px 14px; border-radius:var(--r-pill); cursor:pointer; transition:all var(--dur) var(--ease-out); }
.chip:hover  { background:var(--bg-3); color:#fff; }
.chip.active { background:rgba(0,163,255,0.12); border-color:rgba(0,163,255,0.45); color:var(--brand-200); box-shadow:0 0 14px -4px rgba(0,163,255,0.45); }
</style>
@endpush
