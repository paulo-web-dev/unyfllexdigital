@extends('layouts.admin')
@section('title', 'Cursos & Minisséries')
@section('section', 'Operacional')

@section('content')
<div class="page">

  {{-- ══ Cabeçalho ══════════════════════════════════════════════════════ --}}
  <div class="page-header">
    <div>
      <h1 class="page-title">Cursos &amp; Minisséries</h1>
      <p class="page-subtitle">Catálogo completo · cápsulas, podcasts, PDFs e flashcards</p>
    </div>
    <div class="page-actions">
      <button class="btn">Upload em lote</button>
      <button class="btn btn-primary">
        <svg class="ico" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12 5v14M5 12h14"/></svg>
        Nova minissérie
      </button>
    </div>
  </div>

  {{-- ══ KPIs ════════════════════════════════════════════════════════════ --}}
  <div class="kpi-row">
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Minisséries ativas</span></div>
      <div class="kpi-value">{{ $kpis['totalMinisseries'] }}</div>
      <div class="kpi-delta positive">publicadas</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Cápsulas totais</span></div>
      <div class="kpi-value">{{ $kpis['totalCapsulas'] }}</div>
      <div class="kpi-delta positive">em {{ $kpis['totalMinisseries'] }} séries</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Materiais</span></div>
      <div class="kpi-value">{{ $kpis['totalMateriais'] }}</div>
      <div class="kpi-delta neutral">PDFs + podcasts</div>
    </div>
    <div class="kpi-card kpi-gold">
      <div class="kpi-top"><span class="kpi-label">Conclusão média</span></div>
      <div class="kpi-value" style="color:var(--gold-400);">{{ $kpis['progressoMedio'] }}%</div>
      <div class="kpi-delta {{ $kpis['progressoMedio'] >= 50 ? 'positive' : 'neutral' }}">
        média dos alunos
      </div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Matrículas</span></div>
      <div class="kpi-value">{{ number_format($kpis['totalMatriculas'], 0, ',', '.') }}</div>
      <div class="kpi-delta positive">em minisséries</div>
    </div>
  </div>

  {{-- ══ Barra de filtro ════════════════════════════════════════════════ --}}
  <div class="filter-bar" style="border-radius:14px;border:1px solid var(--line-2);background:var(--bg-2);margin-bottom:14px;">
    <div class="search-mini">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:14px;height:14px;">
        <circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/>
      </svg>
      <input type="search" id="filtro-cursos" placeholder="Buscar minisséries…" oninput="filtrarCards()">
    </div>
    <div style="flex:1;"></div>
    <span style="font-size:12px;color:var(--fg-4);">{{ $kpis['totalMinisseries'] }} minisséries</span>
  </div>

  {{-- ══ Grid de cursos ══════════════════════════════════════════════════ --}}
  @if($classes->isEmpty())
    <div class="card" style="padding:40px;text-align:center;color:var(--fg-4);font-size:13px;">
      Nenhuma minissérie publicada no momento.
    </div>
  @else
    <div id="grid-cursos" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px;">

      @foreach($classes as $classe)
        @php
          $panels     = $classe->panels ?? collect();
          $videos     = $panels->flatMap(fn ($p) => $p->video_lesson);
          $materiais  = $panels->flatMap(fn ($p) => $p->material);
          $totalV     = $videos->count();
          $totalM     = $materiais->count();
          $cargaHora  = $classe->workload ?? ($totalV * 12 . ' min');
        @endphp

        <div class="card curso-card" data-titulo="{{ strtolower($classe->title) }}"
             style="padding:0;overflow:hidden;display:flex;flex-direction:column;">

          {{-- Thumb --}}
          <div style="
            aspect-ratio:16/9;
            background: {{ $classe->photo
              ? 'url(https://unyflex.com.br/storage/cursos/banner/'.$classe->photo.') center/cover no-repeat'
              : 'linear-gradient(135deg,#00A3FF22,#002C4D)' }};
            position:relative;
          ">
            {{-- Badge status --}}
            <span style="
              position:absolute;top:10px;left:10px;
              padding:3px 10px;border-radius:999px;font-size:10px;font-weight:700;
              letter-spacing:0.12em;text-transform:uppercase;
              background:rgba(43,217,161,0.18);color:#6FE6BD;
              border:1px solid rgba(43,217,161,0.35);
            ">PUBLICADA</span>

            {{-- Carga horária --}}
            @if($totalV > 0)
              <span style="
                position:absolute;bottom:10px;right:10px;
                font-family:var(--font-mono);font-size:11px;color:rgba(255,255,255,0.8);
                background:rgba(0,0,0,0.55);padding:3px 8px;border-radius:6px;
              ">{{ $cargaHora }}</span>
            @endif
          </div>

          {{-- Corpo --}}
          <div style="padding:16px 18px;flex:1;display:flex;flex-direction:column;gap:10px;">

            {{-- Eyebrow --}}
            <div style="font-size:10px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--brand-300);">
              MINISSÉRIE · {{ $totalV }} {{ $totalV === 1 ? 'CÁPSULA' : 'CÁPSULAS' }}
            </div>

            {{-- Título --}}
            <h4 style="font-family:var(--font-display);font-weight:700;font-size:15px;color:#fff;margin:0;line-height:1.3;">
              {{ $classe->title }}
            </h4>

            {{-- Subtítulo --}}
            @if($classe->subtitle)
              <p style="font-size:12px;color:var(--fg-3);margin:0;line-height:1.5;">
                {{ Str::limit($classe->subtitle, 80) }}
              </p>
            @endif

            {{-- Meta: temporadas, materiais --}}
            <div style="display:flex;gap:14px;font-size:11px;color:var(--fg-4);">
              <span>📚 {{ $panels->count() }} temporada{{ $panels->count() !== 1 ? 's' : '' }}</span>
              @if($totalM > 0)
                <span>📎 {{ $totalM }} {{ $totalM === 1 ? 'material' : 'materiais' }}</span>
              @endif
              @if($classe->workload)
                <span>⏱ {{ $classe->workload }}</span>
              @endif
            </div>

            {{-- Spacer --}}
            <div style="flex:1;"></div>

            {{-- Ações --}}
            <div style="display:flex;gap:8px;margin-top:4px;">
              <a href="{{ route('player', $classe->slug) }}"
                 target="_blank"
                 class="btn btn-sm"
                 style="font-size:11px;padding:6px 12px;text-decoration:none;">
                <svg style="width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24">
                  <polygon points="6 4 20 12 6 20 6 4"/>
                </svg>
                Visualizar
              </a>
              <button class="btn btn-sm" style="font-size:11px;padding:6px 12px;">
                <svg style="width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Editar
              </button>
              <div style="flex:1;"></div>
              <span style="
                font-size:10px;font-family:var(--font-mono);
                color:var(--fg-4);align-self:center;
                ">#{{ $classe->id }}</span>
            </div>

          </div>
        </div>
      @endforeach

    </div>
  @endif

</div>
@endsection

@push('scripts')
<script>
function filtrarCards() {
  const q     = document.getElementById('filtro-cursos').value.toLowerCase().trim();
  const cards = document.querySelectorAll('.curso-card');
  cards.forEach(card => {
    const titulo = card.dataset.titulo ?? '';
    card.style.display = (!q || titulo.includes(q)) ? '' : 'none';
  });
}
</script>
@endpush
