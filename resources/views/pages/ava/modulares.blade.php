@extends('layouts.app')
@section('title', 'Cursos Modulares — Unyflex Digital')

@section('content')
<div class="scroll">

    {{-- Cabeçalho --}}
    <div class="page-head">
        <div>
            <div class="eyebrow">Catálogo</div>
            <h1>Cursos Modulares</h1>
            <p>Seus materiais de estudo: resumos em PDF, cartões de revisão e simulados com nota.</p>
        </div>
    </div>

    @if($cursos->isEmpty())
        <div class="card" style="padding:22px;background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-lg);">
            <p style="color:var(--fg-4);font-size:14px;margin:0;">Você ainda não tem cursos modulares liberados. Assim que for matriculado em um, ele aparece aqui.</p>
        </div>
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px;">
            @foreach($cursos as $c)
                @php $capa = $c->coverArt->firstWhere('status', 'pronto'); @endphp
                <a href="{{ route('ava.modulares.show', $c->slug) }}"
                   style="text-decoration:none;display:block;background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-lg);overflow:hidden;transition:border-color .15s;">
                    <div style="aspect-ratio:16/9;background:var(--bg-3);display:flex;align-items:center;justify-content:center;">
                        @if($capa)
                            <img src="{{ $capa->imageUrl() }}" alt="{{ $c->title }}" style="width:100%;height:100%;object-fit:cover;display:block;" />
                        @else
                            <i data-lucide="book-open" style="width:40px;height:40px;color:var(--fg-4);"></i>
                        @endif
                    </div>
                    <div style="padding:14px 16px;">
                        <div class="eyebrow" style="color:var(--brand-300);">Curso Modular</div>
                        <div style="font-size:15px;font-weight:700;color:var(--fg-1);margin-top:4px;line-height:1.3;">{{ $c->title }}</div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

</div>
@endsection
