@extends('layouts.admin')
@section('title', $classe->title)
@section('section', 'Cursos')

@section('content')
<div class="page">

  {{-- Flash --}}
  @if(session('success'))
    <div style="padding:12px 16px;background:rgba(43,217,161,0.10);border:1px solid rgba(43,217,161,0.35);border-radius:var(--r-md);color:#6FE6BD;font-size:13px;font-weight:500;margin-bottom:20px;">
      ✓ {{ session('success') }}
    </div>
  @endif

  {{-- ══ Breadcrumb ══════════════════════════════════════════════════════ --}}
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:12px;color:var(--fg-4);">
    <a href="{{ route('admin.cursos') }}" style="color:var(--fg-4);text-decoration:none;transition:color .15s;"
       onmouseover="this.style.color='var(--brand-300)'" onmouseout="this.style.color='var(--fg-4)'">
      Cursos
    </a>
    <span>/</span>
    <span style="color:var(--fg-2);">{{ Str::limit($classe->title, 50) }}</span>
  </div>

  {{-- ══ Hero do curso ══════════════════════════════════════════════════ --}}
  <div style="
    border-radius:var(--r-xl);overflow:hidden;
    border:1px solid var(--line-2);
    margin-bottom:20px;
    background: {{ $classe->photo
      ? 'linear-gradient(90deg, var(--bg-1) 50%, transparent 100%)'
      : 'var(--bg-2)' }};
    position:relative;
  ">
    {{-- Background foto --}}
    @if($classe->photo)
      <div style="
        position:absolute;inset:0;
        background: url('https://unyflex.com.br/storage/cursos/banner/{{ $classe->photo }}') center/cover no-repeat;
        opacity:0.15;
      "></div>
    @endif

    <div style="position:relative;display:grid;grid-template-columns:1fr auto;gap:24px;align-items:start;padding:28px 32px;">
      <div>
        {{-- Status badge --}}
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
          <span class="badge {{ $classe->status === 'able' ? 'success' : 'neutral' }}">
            {{ $classe->status === 'able' ? 'Publicada' : 'Desativada' }}
          </span>
          @if($classe->express == 1)
            <span class="badge brand">Express</span>
          @endif
          @if($classe->live)
            <span class="badge warn">Live</span>
          @endif
        </div>

        {{-- Título --}}
        <h1 style="font-family:var(--font-display);font-weight:800;font-size:clamp(20px,2.5vw,28px);color:#fff;letter-spacing:-0.02em;margin:0 0 8px;line-height:1.2;">
          {{ $classe->title }}
        </h1>
        @if($classe->subtitle)
          <p style="font-size:14px;color:var(--fg-3);margin:0 0 16px;max-width:600px;">
            {{ $classe->subtitle }}
          </p>
        @endif

        {{-- Meta info --}}
        <div style="display:flex;gap:20px;flex-wrap:wrap;font-size:12px;color:var(--fg-4);">
          <span>📅 Início: {{ $classe->start_date ? \Carbon\Carbon::parse($classe->start_date)->format('d/m/Y') : '—' }}</span>
          <span>📅 Fim: {{ $classe->end_date ? \Carbon\Carbon::parse($classe->end_date)->format('d/m/Y') : '—' }}</span>
          @if($classe->workload)
            <span>⏱ Carga horária: {{ $classe->workload }}</span>
          @endif
          <span>🆔 ID: #{{ $classe->id }}</span>
          <span>🔗 Slug: <code style="font-family:var(--font-mono);font-size:11px;color:var(--brand-300);">{{ $classe->slug }}</code></span>
        </div>
      </div>

      {{-- Foto/thumb --}}
      @if($classe->photo)
        <div style="width:120px;height:120px;border-radius:16px;overflow:hidden;border:1px solid var(--line-2);flex-shrink:0;">
          <img src="https://unyflex.com.br/storage/cursos/banner/{{ $classe->photo }}"
               style="width:100%;height:100%;object-fit:cover;" alt="{{ $classe->title }}">
        </div>
      @endif
    </div>
  </div>

  {{-- ══ KPIs ════════════════════════════════════════════════════════════ --}}
  <div class="kpi-row" style="grid-template-columns:repeat(auto-fill,minmax(160px,1fr));">
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Temporadas</span></div>
      <div class="kpi-value">{{ $kpis['totalPanels'] }}</div>
      <div class="kpi-delta neutral">painéis</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Cápsulas</span></div>
      <div class="kpi-value">{{ $kpis['totalVideos'] }}</div>
      <div class="kpi-delta neutral">{{ $kpis['duracao'] }}</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Materiais</span></div>
      <div class="kpi-value">{{ $kpis['totalMateriais'] }}</div>
      <div class="kpi-delta neutral">{{ $kpis['totalPdfs'] }} PDF · {{ $kpis['totalPodcasts'] }} podcast</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Matrículas</span></div>
      <div class="kpi-value">{{ number_format($kpis['totalMatriculas'], 0, ',', '.') }}</div>
      <div class="kpi-delta positive">{{ $kpis['matriculasChecked'] }} confirmadas</div>
    </div>
    <div class="kpi-card kpi-gold">
      <div class="kpi-top"><span class="kpi-label">Progresso médio</span></div>
      <div class="kpi-value" style="color:var(--gold-400);">{{ $kpis['progressoMedio'] }}%</div>
      <div class="kpi-delta neutral">dos alunos</div>
    </div>
  </div>

  {{-- ══ Temporadas / Painéis ════════════════════════════════════════════ --}}
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
    <h2 style="font-family:var(--font-display);font-size:18px;font-weight:700;color:#fff;margin:0;">
      Conteúdo da minissérie
    </h2>
    <span style="font-size:12px;color:var(--fg-4);">
      {{ $kpis['totalPanels'] }} temporadas · {{ $kpis['totalVideos'] }} cápsulas
    </span>
  </div>

  @forelse($panels as $panel)
    @php
      $pNum    = $loop->iteration;
      $videos  = $panel->video_lesson ?? collect();
      $mats    = $panel->material ?? collect();
      $teacher = $panel->teachers;
    @endphp

    <div class="card" style="padding:0;margin-bottom:14px;overflow:hidden;">

      {{-- Cabeçalho do painel --}}
      <div style="
        padding:16px 20px;
        background:linear-gradient(90deg,rgba(0,163,255,0.06),transparent);
        border-bottom:1px solid var(--line-2);
        display:flex;align-items:flex-start;justify-content:space-between;gap:16px;
      ">
        <div>
          <div style="font-size:10px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;color:var(--brand-300);margin-bottom:4px;">
            Temporada {{ $pNum }}
          </div>
          <h3 style="font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;margin:0 0 4px;">
            {{ $panel->title }}
          </h3>
          @if($panel->subtitle)
            <p style="font-size:13px;color:var(--fg-3);margin:0;">{{ $panel->subtitle }}</p>
          @endif
        </div>

        <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">
          <span class="badge {{ $panel->status === 'able' ? 'success' : 'neutral' }}">
            {{ $panel->status === 'able' ? 'Ativo' : 'Inativo' }}
          </span>
          <span style="font-size:11px;color:var(--fg-4);">
            {{ $videos->count() }} {{ $videos->count() === 1 ? 'cápsula' : 'cápsulas' }}
            @if($mats->count() > 0) · {{ $mats->count() }} mat. @endif
          </span>
          <a href="{{ route('admin.panels.edit', $panel->id) }}"
             class="btn btn-sm" style="font-size:11px;padding:5px 12px;text-decoration:none;">
            Editar temporada
          </a>
        </div>
      </div>

      {{-- Meta do painel --}}
      @if($panel->start_time || $panel->horario || $teacher)
        <div style="padding:10px 20px;border-bottom:1px solid var(--line-1);display:flex;gap:20px;flex-wrap:wrap;font-size:12px;color:var(--fg-4);">
          @if($panel->start_time)
            <span>📅 {{ \Carbon\Carbon::parse($panel->start_time)->format('d/m/Y') }}</span>
          @endif
          @if($panel->horario)
            <span>🕐 {{ $panel->horario }}</span>
          @endif
          @if($teacher)
            <span>👤 {{ $teacher->name ?? 'Professor' }}</span>
          @endif
          @if($panel->content)
            <span style="color:var(--fg-3);max-width:500px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
              📝 {{ Str::limit($panel->content, 80) }}
            </span>
          @endif
        </div>
      @endif

      {{-- ── Vídeos ─────────────────────────────────────────────────── --}}
      @if($videos->isNotEmpty())
        <div style="padding:12px 20px 4px;border-bottom:{{ $mats->isNotEmpty() ? '1px solid var(--line-1)' : 'none' }};">
          <div style="font-size:10px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--fg-4);margin-bottom:10px;">
            Cápsulas de vídeo
          </div>
          <div style="display:flex;flex-direction:column;gap:6px;margin-bottom:12px;">
            @foreach($videos as $video)
              @php $vNum = $loop->iteration; @endphp
              <div style="
                display:flex;align-items:center;gap:12px;
                padding:10px 14px;
                background:var(--bg-1);
                border:1px solid var(--line-1);
                border-radius:10px;
                transition:border-color .15s;
              "
              onmouseover="this.style.borderColor='rgba(0,163,255,0.3)'"
              onmouseout="this.style.borderColor='var(--line-1)'">

                {{-- Número --}}
                <div style="width:28px;height:28px;border-radius:8px;background:rgba(0,163,255,0.10);border:1px solid rgba(0,163,255,0.2);display:flex;align-items:center;justify-content:center;font-family:var(--font-mono);font-size:10px;color:var(--brand-300);flex-shrink:0;">
                  {{ $pNum }}.{{ $vNum }}
                </div>

                {{-- Ícone play --}}
                <div style="width:32px;height:32px;border-radius:8px;background:rgba(43,217,161,0.10);border:1px solid rgba(43,217,161,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                  <svg viewBox="0 0 24 24" style="width:12px;height:12px;fill:#6FE6BD;"><polygon points="6 4 20 12 6 20 6 4"/></svg>
                </div>

                {{-- Título + subtítulo --}}
                <div style="flex:1;min-width:0;">
                  <div style="font-size:13px;font-weight:500;color:var(--fg-1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $video->titulo ?? 'Sem título' }}
                  </div>
                  @if($video->subtitle)
                    <div style="font-size:11px;color:var(--fg-4);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                      {{ $video->subtitle }}
                    </div>
                  @endif
                </div>

                {{-- Source --}}
                @if($video->source)
                  <span style="font-size:10px;font-weight:600;padding:2px 8px;border-radius:999px;background:rgba(255,255,255,0.05);color:var(--fg-4);border:1px solid var(--line-1);flex-shrink:0;">
                    {{ strtoupper($video->source) }}
                  </span>
                @endif

                {{-- Status --}}
                <span class="badge {{ $video->status === 'able' ? 'success' : 'neutral' }}" style="flex-shrink:0;">
                  {{ $video->status === 'able' ? 'Ativo' : 'Inativo' }}
                </span>

                {{-- Links --}}
                <div style="display:flex;gap:6px;flex-shrink:0;">
                  @if($video->link)
                    <a href="{{ $video->link }}" target="_blank"
                       class="btn btn-sm"
                       style="font-size:11px;padding:4px 10px;text-decoration:none;background:rgba(0,163,255,0.10);border-color:rgba(0,163,255,0.3);color:var(--brand-300);">
                      <svg viewBox="0 0 24 24" style="width:11px;height:11px;stroke:currentColor;fill:none;stroke-width:2;"><polygon points="6 4 20 12 6 20 6 4"/></svg>
                      Assistir
                    </a>
                  @endif
                  @if($video->tasting_link)
                    <a href="{{ $video->tasting_link }}" target="_blank"
                       class="btn btn-sm"
                       style="font-size:11px;padding:4px 10px;text-decoration:none;">
                      Demo
                    </a>
                  @endif
                </div>

              </div>
            @endforeach
          </div>
        </div>
      @else
        <div style="padding:20px;text-align:center;color:var(--fg-4);font-size:13px;border-bottom:{{ $mats->isNotEmpty() ? '1px solid var(--line-1)' : 'none' }};">
          Nenhum vídeo cadastrado nesta temporada.
        </div>
      @endif

      {{-- ── Materiais ──────────────────────────────────────────────── --}}
      @if($mats->isNotEmpty())
        <div style="padding:12px 20px 16px;">
          <div style="font-size:10px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--fg-4);margin-bottom:10px;">
            Materiais de apoio
          </div>
          <div style="display:flex;gap:8px;flex-wrap:wrap;">
            @foreach($mats as $material)
              @php
                $icons  = ['PDF' => '📄', 'PODCAST' => '🎧'];
                $colors = ['PDF' => 'rgba(232,183,101,', 'PODCAST' => 'rgba(161,140,209,'];
                $ic     = $icons[$material->type]  ?? '📁';
                $cl     = $colors[$material->type] ?? 'rgba(255,255,255,';
              @endphp
              <a href="https://unygov.com.br/storage/materials/{{ $material->file_name }}"
                 target="_blank"
                 style="
                   display:inline-flex;align-items:center;gap:8px;
                   padding:8px 12px;
                   background:{{ $cl }}0.08);
                   border:1px solid {{ $cl }}0.25);
                   border-radius:10px;
                   text-decoration:none;
                   transition:border-color .15s;
                 "
                 onmouseover="this.style.borderColor='{{ $cl }}0.5)'"
                 onmouseout="this.style.borderColor='{{ $cl }}0.25)'">
                <span style="font-size:16px;">{{ $ic }}</span>
                <div>
                  <div style="font-size:11px;font-weight:600;color:var(--fg-1);">
                    {{ $material->name ?? $material->file_name }}
                  </div>
                  <div style="font-size:10px;color:var(--fg-4);text-transform:uppercase;letter-spacing:0.1em;">
                    {{ $material->type }}
                  </div>
                </div>
                <svg viewBox="0 0 24 24" style="width:13px;height:13px;stroke:var(--fg-4);fill:none;stroke-width:2;">
                  <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                  <polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
                </svg>
              </a>
            @endforeach
          </div>
        </div>
      @endif

    </div>
  @empty
    <div class="card" style="padding:48px;text-align:center;color:var(--fg-4);font-size:14px;">
      Nenhum painel/temporada cadastrado nesta minissérie.
    </div>
  @endforelse

  {{-- ══ Rodapé de ações ═════════════════════════════════════════════════ --}}
  <div style="display:flex;gap:10px;margin-top:8px;padding-top:16px;border-top:1px solid var(--line-1);">
    <a href="{{ route('admin.cursos.edit', $classe->id) }}" class="btn btn-primary" style="text-decoration:none;">
      <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24">
        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
      </svg>
      Editar minissérie
    </a>
    <a href="{{ route('player', $classe->slug) }}" target="_blank" class="btn" style="text-decoration:none;">
      <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24">
        <polygon points="6 4 20 12 6 20 6 4"/>
      </svg>
      Visualizar como aluno
    </a>
    <a href="{{ route('admin.matriculas', ['q' => $classe->title]) }}" class="btn" style="text-decoration:none;">
      Ver matrículas deste curso
    </a>
    <a href="{{ route('admin.cursos') }}" class="btn btn-ghost" style="text-decoration:none;">
      ← Voltar
    </a>
  </div>

</div>
@endsection
