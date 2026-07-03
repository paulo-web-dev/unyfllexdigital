@extends('layouts.admin')
@section('title', 'Aprovação — Instagram')
@section('section', 'Instagram')

@section('content')
@include('admin.social._field-styles')
<div class="page">

  @include('admin.social._nav')

  @if(session('success'))
    <div style="padding:12px 16px;background:rgba(43,217,161,0.10);border:1px solid rgba(43,217,161,0.35);border-radius:var(--r-md);color:#6FE6BD;font-size:13px;font-weight:500;margin-bottom:20px;">✓ {{ session('success') }}</div>
  @endif
  @if(session('warning'))
    <div style="padding:12px 16px;background:rgba(255,181,71,0.10);border:1px solid rgba(255,181,71,0.35);border-radius:var(--r-md);color:#FFB547;font-size:13px;margin-bottom:20px;">{{ session('warning') }}</div>
  @endif

  <div style="margin-bottom:20px;">
    <h1 style="font-family:var(--font-display);font-weight:700;font-size:20px;color:#fff;margin:0 0 4px;">Fila de aprovação</h1>
    <p style="font-size:13px;color:var(--fg-3);margin:0;">Revise as artes geradas. Aprovar cria o post agendado; descartar remove da fila.</p>
  </div>

  @if($drafts->isEmpty())
    <div style="text-align:center;padding:60px 20px;color:var(--fg-4);">
      <div style="font-size:40px;margin-bottom:12px;">🎨</div>
      <p style="font-size:14px;margin:0 0 4px;color:var(--fg-3);">Nenhuma arte aguardando aprovação.</p>
      <p style="font-size:13px;margin:0;">Gere posts pelo <a href="{{ route('admin.social.calendar') }}" style="color:var(--brand-500);text-decoration:none;">calendário</a> e eles aparecem aqui.</p>
    </div>
  @else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:18px;">
      @foreach($drafts as $draft)
        <div class="card" style="padding:0;overflow:hidden;display:flex;flex-direction:column;">

          {{-- preview --}}
          <div style="position:relative;background:#05080f;aspect-ratio:{{ $draft->tipo === 'story' ? '9/16' : '4/5' }};display:flex;align-items:center;justify-content:center;overflow:hidden;">
            @if($draft->status === 'gerando')
              <div style="text-align:center;color:var(--fg-4);padding:20px;">
                <div style="width:26px;height:26px;border:3px solid var(--line-2);border-top-color:var(--brand-500);border-radius:50%;margin:0 auto 10px;animation:spin 0.8s linear infinite;"></div>
                <span style="font-size:12px;">Gerando…</span>
              </div>
            @elseif($draft->url())
              <img src="{{ $draft->url() }}" alt="" style="width:100%;height:100%;object-fit:cover;">
            @else
              <span style="font-size:12px;color:var(--fg-4);">sem imagem</span>
            @endif

            <div style="position:absolute;top:8px;left:8px;display:flex;gap:6px;">
              <span style="font-size:10px;font-weight:700;padding:3px 8px;border-radius:20px;background:rgba(5,8,15,.7);color:#fff;backdrop-filter:blur(4px);">{{ $draft->tipoLabel() }}</span>
              @if($draft->versao > 1)
                <span style="font-size:10px;font-weight:700;padding:3px 8px;border-radius:20px;background:rgba(0,163,255,.85);color:#fff;">v{{ $draft->versao }}</span>
              @endif
            </div>
          </div>

          {{-- corpo --}}
          <div style="padding:14px;display:flex;flex-direction:column;flex:1;">
            <p style="font-size:13px;color:var(--fg-1);margin:0 0 6px;font-weight:500;line-height:1.4;">{{ \Illuminate\Support\Str::limit($draft->pedido, 90) }}</p>
            @if($draft->briefingResumo())
              <p style="font-size:11px;color:var(--fg-4);margin:0 0 8px;">{{ $draft->briefingResumo() }}</p>
            @endif
            @if($draft->scheduled_date)
              <p style="font-size:11px;color:var(--fg-3);margin:0 0 12px;">📅 {{ $draft->scheduled_date->format('d/m') }} às {{ $draft->horario ?: '09:00' }}</p>
            @endif

            @if($draft->status === 'revisao')
              <div style="margin-top:auto;display:flex;gap:8px;">
                <form action="{{ route('admin.social.review.aprovar', $draft) }}" method="POST" style="flex:1;">
                  @csrf
                  <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:9px;font-size:13px;">✓ Aprovar</button>
                </form>
                <form action="{{ route('admin.social.review.descartar', $draft) }}" method="POST" onsubmit="return confirm('Descartar esta arte?');">
                  @csrf
                  <button type="submit" class="btn btn-ghost" style="padding:9px 12px;font-size:13px;color:#FF5C7A;">✕</button>
                </form>
              </div>
            @endif
          </div>
        </div>
      @endforeach
    </div>
  @endif

</div>

@push('styles')
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>
@endpush
@endsection
