@extends('layouts.admin')
@section('title', 'Cursos Modulares')
@section('section', 'Operacional')

@section('content')
<div class="page">

  {{-- ══ Cabeçalho ══════════════════════════════════════════════════════ --}}
  <div class="page-header">
    <div>
      <h1 class="page-title">Cursos Modulares</h1>
      <p class="page-subtitle">Apostila PDF → curso modular · resumos, podcast, materiais e prova (em breve)</p>
    </div>
    <div class="page-actions">
      <a href="{{ route('admin.cursos-modulares.create') }}" class="btn btn-primary" style="text-decoration:none;display:inline-flex;">
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Novo curso modular
      </a>
    </div>
  </div>

  @if(session('success'))
    <div style="padding:12px 16px;background:rgba(43,217,161,0.10);border:1px solid rgba(43,217,161,0.35);border-radius:var(--r-md);color:#6FE6BD;font-size:13px;font-weight:500;margin-bottom:20px;">
      ✓ {{ session('success') }}
    </div>
  @endif

  {{-- ══ KPIs ════════════════════════════════════════════════════════════ --}}
  <div class="kpi-row">
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Cursos modulares</span></div>
      <div class="kpi-value">{{ $kpis['total'] }}</div>
      <div class="kpi-delta neutral">no total</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Rascunhos</span></div>
      <div class="kpi-value">{{ $kpis['rascunhos'] }}</div>
      <div class="kpi-delta neutral">aguardando</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Publicados</span></div>
      <div class="kpi-value">{{ $kpis['publicados'] }}</div>
      <div class="kpi-delta positive">no ar</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-top"><span class="kpi-label">Apostilas enviadas</span></div>
      <div class="kpi-value">{{ $kpis['apostilas'] }}</div>
      <div class="kpi-delta neutral">PDFs</div>
    </div>
  </div>

  {{-- ══ Lista ═══════════════════════════════════════════════════════════ --}}
  @if($cursos->isEmpty())
    <div class="card" style="padding:60px;text-align:center;">
      <div style="width:64px;height:64px;border-radius:16px;background:rgba(0,163,255,0.10);border:1px solid rgba(0,163,255,0.20);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="var(--brand-300)" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" style="width:28px;height:28px;"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
      </div>
      <h3 style="color:var(--fg-1);margin:0 0 8px;font-family:var(--font-display);font-size:20px;">Nenhum curso modular ainda</h3>
      <p style="color:var(--fg-3);font-size:14px;margin:0 0 24px;max-width:380px;margin-left:auto;margin-right:auto;">
        Crie o primeiro curso enviando uma apostila em PDF. Nas próximas etapas ele vira resumos, podcast, materiais e prova.
      </p>
      <a href="{{ route('admin.cursos-modulares.create') }}" class="btn btn-primary" style="display:inline-flex;text-decoration:none;">
        + Novo curso modular
      </a>
    </div>
  @else
    <div class="card" style="padding:0;overflow:hidden;">
      <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <thead>
          <tr style="text-align:left;border-bottom:1px solid var(--line-2);">
            <th style="padding:12px 18px;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--fg-4);">Curso</th>
            <th style="padding:12px 18px;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--fg-4);">Apostila</th>
            <th style="padding:12px 18px;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--fg-4);">Status</th>
            <th style="padding:12px 18px;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--fg-4);">Criado</th>
            <th style="padding:12px 18px;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--fg-4);text-align:right;">Ações</th>
          </tr>
        </thead>
        <tbody>
          @foreach($cursos as $curso)
            @php
              $badge = [
                'rascunho'    => ['bg' => 'rgba(255,255,255,0.06)', 'bd' => 'var(--line-2)',         'fg' => 'var(--fg-3)'],
                'processando' => ['bg' => 'rgba(232,183,101,0.12)', 'bd' => 'rgba(232,183,101,0.35)', 'fg' => 'var(--gold-400)'],
                'publicado'   => ['bg' => 'rgba(43,217,161,0.12)',  'bd' => 'rgba(43,217,161,0.35)',  'fg' => '#6FE6BD'],
              ][$curso->status] ?? ['bg' => 'rgba(255,255,255,0.06)', 'bd' => 'var(--line-2)', 'fg' => 'var(--fg-3)'];
            @endphp
            <tr style="border-bottom:1px solid var(--line-1);">
              <td style="padding:14px 18px;">
                <a href="{{ route('admin.cursos-modulares.show', $curso->id) }}" style="font-weight:600;color:var(--fg-1);text-decoration:none;">{{ $curso->title }}</a>
                @if($curso->description)
                  <div style="font-size:11px;color:var(--fg-4);margin-top:2px;max-width:420px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $curso->description }}</div>
                @endif
              </td>
              <td style="padding:14px 18px;color:var(--fg-3);">
                @if($curso->hasApostila())
                  <div style="display:flex;align-items:center;gap:8px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="var(--brand-300)" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px;flex-shrink:0;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <span style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $curso->apostila_original_name }}</span>
                    <span style="color:var(--fg-4);font-size:11px;">· {{ $curso->sizeHuman() }}</span>
                  </div>
                @else
                  <span style="color:var(--fg-4);">Sem apostila</span>
                @endif
              </td>
              <td style="padding:14px 18px;">
                <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600;background:{{ $badge['bg'] }};border:1px solid {{ $badge['bd'] }};color:{{ $badge['fg'] }};">
                  {{ $curso->statusLabel() }}
                </span>
              </td>
              <td style="padding:14px 18px;color:var(--fg-4);font-size:12px;">
                {{ optional($curso->created_at)->format('d/m/Y') ?? '—' }}
              </td>
              <td style="padding:14px 18px;text-align:right;white-space:nowrap;">
                @if($curso->hasApostila())
                  <a href="{{ route('admin.cursos-modulares.download', $curso->id) }}" class="btn btn-sm" style="text-decoration:none;font-size:11px;" title="Baixar apostila">Baixar</a>
                @endif
                <a href="{{ route('admin.cursos-modulares.show', $curso->id) }}" class="btn btn-sm" style="text-decoration:none;font-size:11px;">Ver</a>
                <form action="{{ route('admin.cursos-modulares.destroy', $curso->id) }}" method="POST" style="display:inline;"
                      onsubmit="return confirm('Remover este curso modular e a apostila? Esta ação não pode ser desfeita.');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm" style="font-size:11px;color:var(--danger);border-color:rgba(255,90,90,0.3);">Excluir</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div style="margin-top:16px;">
      {{ $cursos->links() }}
    </div>
  @endif

</div>
@endsection
