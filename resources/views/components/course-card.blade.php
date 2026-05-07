{{--
    <x-course-card>
    Props:
        tone      (int 1-4)  — tema de cor do thumb
        badge     (string)   — ex: "EM ANDAMENTO", "NOVO"
        duration  (string)   — ex: "2h 48min"
        eyebrow   (string)   — ex: "MINISSÉRIE · 12 CÁPSULAS"
        title     (string)   — título da minissérie
        desc      (string)   — descrição curta (2 frases max)
        progress  (int|null) — % de progresso (0-100); omitir para mostrar CTA
        cta       (string)   — texto do botão (ex: "Acessar"); omitir se houver progress
        href      (string)   — URL do destino
--}}
@props([
    'tone'     => 1,
    'badge'    => null,
    'duration' => null,
    'eyebrow'  => null,
    'title'    => '',
    'desc'     => '',
    'progress' => null,
    'cta'      => null,
    'href'     => '#',
])

<a href="{{ $href }}" class="cc" style="text-decoration:none; color:inherit;">

    {{-- Thumb --}}
    <div class="cc-thumb tone-{{ $tone }}">
        @if($badge)
            <span class="badge">{{ $badge }}</span>
        @endif
        @if($duration)
            <span class="duration">{{ $duration }}</span>
        @endif
    </div>

    {{-- Corpo --}}
    <div class="cc-body">
        @if($eyebrow)
            <span class="eyebrow">{{ $eyebrow }}</span>
        @endif

        <h4>{{ $title }}</h4>
        <p class="desc">{{ $desc }}</p>

        {{-- Barra de progresso (se houver progresso) --}}
        @if(!is_null($progress))
            <div class="cc-progress">
                <div class="bar">
                    <i style="width: {{ $progress }}%;"></i>
                </div>
                <div class="row">
                    <span>Progresso</span>
                    <span class="pct">{{ $progress }}%</span>
                </div>
            </div>
        @endif

        {{-- CTA (se não houver progresso) --}}
        @if($cta)
            <button class="cc-cta">
                {{ $cta }}
                <span style="display:inline-flex;">
                    <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </span>
            </button>
        @endif
    </div>

</a>
