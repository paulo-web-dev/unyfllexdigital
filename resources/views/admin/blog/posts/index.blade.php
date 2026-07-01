@extends('layouts.admin')
@section('title', 'Posts do Blog')
@section('section', 'Blog')

@section('content')
@include('admin.blog._field-styles')
<div class="page">

  <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:20px;flex-wrap:wrap;">
    <div>
      <h1 style="font-family:var(--font-display);font-weight:800;font-size:22px;color:#fff;margin:0;">Posts do Blog</h1>
      <p style="color:var(--fg-4);font-size:13px;margin:4px 0 0;">Gerencie os artigos, rascunhos e agendamentos.</p>
    </div>
    <a href="{{ route('admin.blog.posts.create') }}" class="btn btn-primary" style="text-decoration:none;">+ Novo post</a>
  </div>

  @if(session('success'))
    <div style="padding:12px 16px;background:rgba(43,217,161,0.10);border:1px solid rgba(43,217,161,0.35);border-radius:var(--r-md);color:#6FE6BD;font-size:13px;font-weight:500;margin-bottom:16px;">
      ✓ {{ session('success') }}
    </div>
  @endif
  @if(session('warning'))
    <div style="padding:12px 16px;background:rgba(255,181,71,0.10);border:1px solid rgba(255,181,71,0.35);border-radius:var(--r-md);color:#FFB547;font-size:13px;margin-bottom:16px;">
      {{ session('warning') }}
    </div>
  @endif

  {{-- Filtros por status --}}
  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px;">
    @php
      $tabs = ['' => ['Todos', $counts['todos']], 'publicado' => ['Publicados', $counts['publicado']], 'agendado' => ['Agendados', $counts['agendado']], 'rascunho' => ['Rascunhos', $counts['rascunho']]];
    @endphp
    @foreach($tabs as $key => $info)
      <a href="{{ route('admin.blog.posts.index', array_filter(['status' => $key, 'q' => $q])) }}"
         style="padding:7px 14px;border-radius:var(--r-pill);font-size:13px;text-decoration:none;border:1px solid {{ (string)$status === (string)$key ? 'var(--line-3)' : 'var(--line-2)' }};background:{{ (string)$status === (string)$key ? 'var(--bg-3)' : 'var(--bg-2)' }};color:{{ (string)$status === (string)$key ? '#fff' : 'var(--fg-3)' }};">
        {{ $info[0] }} <span style="color:var(--fg-4);">{{ $info[1] }}</span>
      </a>
    @endforeach
  </div>

  {{-- Busca --}}
  <form method="GET" action="{{ route('admin.blog.posts.index') }}" style="margin-bottom:16px;display:flex;gap:8px;max-width:420px;">
    @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
    <input type="text" name="q" value="{{ $q }}" class="field-input" placeholder="Buscar por título...">
    <button type="submit" class="btn btn-ghost">Buscar</button>
  </form>

  {{-- Tabela --}}
  <div class="card" style="padding:0;overflow:hidden;">
    @if($posts->count())
    <table style="width:100%;border-collapse:collapse;font-size:13.5px;">
      <thead>
        <tr style="text-align:left;color:var(--fg-4);font-size:12px;">
          <th style="padding:12px 16px;font-weight:600;">Título</th>
          <th style="padding:12px 16px;font-weight:600;">Categoria</th>
          <th style="padding:12px 16px;font-weight:600;">Status</th>
          <th style="padding:12px 16px;font-weight:600;">Data</th>
          <th style="padding:12px 16px;font-weight:600;text-align:center;">Views</th>
          <th style="padding:12px 16px;font-weight:600;text-align:right;">Ações</th>
        </tr>
      </thead>
      <tbody>
        @foreach($posts as $p)
        @php
          $badge = ['publicado' => ['#6FE6BD','rgba(43,217,161,0.12)'], 'agendado' => ['#FFB547','rgba(255,181,71,0.12)'], 'rascunho' => ['#97A3B8','rgba(151,163,184,0.12)']][$p->status] ?? ['#97A3B8','rgba(151,163,184,0.12)'];
        @endphp
        <tr style="border-top:1px solid var(--line-1);">
          <td style="padding:12px 16px;color:#fff;max-width:340px;">
            <a href="{{ route('admin.blog.posts.edit', $p) }}" style="color:#fff;text-decoration:none;font-weight:500;">{{ $p->title }}</a>
            <div style="color:var(--fg-4);font-size:11px;">/blog/{{ $p->slug }}</div>
          </td>
          <td style="padding:12px 16px;color:var(--fg-3);">{{ $p->category->name ?? '—' }}</td>
          <td style="padding:12px 16px;"><span style="padding:3px 10px;border-radius:var(--r-pill);font-size:11px;font-weight:600;color:{{ $badge[0] }};background:{{ $badge[1] }};">{{ ucfirst($p->status) }}</span></td>
          <td style="padding:12px 16px;color:var(--fg-3);">{{ optional($p->published_at ?: $p->created_at)->format('d/m/Y') }}</td>
          <td style="padding:12px 16px;color:var(--fg-3);text-align:center;">{{ $p->views }}</td>
          <td style="padding:12px 16px;text-align:right;white-space:nowrap;">
            <a href="{{ route('admin.blog.posts.preview', $p) }}" target="_blank" style="color:var(--fg-3);text-decoration:none;margin-right:12px;">Preview</a>
            <a href="{{ route('admin.blog.posts.edit', $p) }}" style="color:var(--brand-300);text-decoration:none;">Editar</a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @else
    <div style="padding:48px 20px;text-align:center;color:var(--fg-4);">
      Nenhum post encontrado. <a href="{{ route('admin.blog.posts.create') }}" style="color:var(--brand-300);">Criar o primeiro</a>.
    </div>
    @endif
  </div>

  <div style="margin-top:16px;">{{ $posts->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
