@extends('layouts.admin')
@section('title', 'Posts — Instagram')
@section('section', 'Instagram')

@section('content')
<div class="page">

  @include('admin.social._nav')

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;gap:12px;flex-wrap:wrap;">
    <h1 style="font-family:var(--font-display);font-weight:700;font-size:20px;color:#fff;margin:0;">Posts do Instagram</h1>
    <div style="display:flex;gap:10px;">
      <a href="{{ route('admin.social.accounts.index') }}" class="btn btn-ghost">Conta</a>
      <a href="{{ route('admin.social.posts.create') }}" class="btn btn-primary">+ Novo post</a>
    </div>
  </div>

  @if(session('success'))
    <div style="padding:12px 16px;background:rgba(43,217,161,0.10);border:1px solid rgba(43,217,161,0.35);border-radius:var(--r-md);color:#6FE6BD;font-size:13px;font-weight:500;margin-bottom:20px;">✓ {{ session('success') }}</div>
  @endif

  {{-- KPIs --}}
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px;">
    @foreach(['rascunho'=>['Rascunhos','#97A3B8'], 'agendado'=>['Agendados','#00A3FF'], 'publicado'=>['Publicados','#2BD9A1']] as $k => $v)
      <div class="card" style="padding:16px 18px;">
        <div style="font-size:11px;color:var(--fg-4);text-transform:uppercase;letter-spacing:.08em;">{{ $v[0] }}</div>
        <div style="font-family:var(--font-display);font-size:26px;font-weight:700;color:{{ $v[1] }};">{{ $counts[$k] }}</div>
      </div>
    @endforeach
  </div>

  {{-- Filtro por status --}}
  <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
    <a href="{{ route('admin.social.posts.index') }}" class="btn btn-ghost" style="{{ !$status ? 'border-color:var(--brand-500);color:var(--brand-500);' : '' }}">Todos</a>
    @foreach(\App\Models\SocialPost::STATUSES as $val => $lbl)
      <a href="{{ route('admin.social.posts.index', ['status' => $val]) }}" class="btn btn-ghost" style="{{ $status === $val ? 'border-color:var(--brand-500);color:var(--brand-500);' : '' }}">{{ $lbl }}</a>
    @endforeach
  </div>

  <div class="card" style="padding:0;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
      <thead>
        <tr style="background:var(--bg-2);color:var(--fg-3);text-align:left;">
          <th style="padding:12px 16px;font-weight:600;">Prévia</th>
          <th style="padding:12px 16px;font-weight:600;">Legenda</th>
          <th style="padding:12px 16px;font-weight:600;">Tipo</th>
          <th style="padding:12px 16px;font-weight:600;">Status</th>
          <th style="padding:12px 16px;font-weight:600;">Agendado</th>
          <th style="padding:12px 16px;font-weight:600;"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($posts as $p)
          <tr style="border-top:1px solid var(--line-1);">
            <td style="padding:10px 16px;">
              @if($p->thumb())
                <img src="{{ $p->thumb() }}" style="width:44px;height:44px;object-fit:cover;border-radius:8px;border:1px solid var(--line-2);">
              @else
                <div style="width:44px;height:44px;border-radius:8px;background:var(--bg-3);border:1px solid var(--line-2);"></div>
              @endif
            </td>
            <td style="padding:10px 16px;color:var(--fg-2);max-width:320px;">{{ \Illuminate\Support\Str::limit($p->caption, 70) ?: '—' }}</td>
            <td style="padding:10px 16px;color:var(--fg-3);">{{ $p->typeLabel() }}</td>
            <td style="padding:10px 16px;"><span style="display:inline-block;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600;color:{{ $p->statusColor() }};background:{{ $p->statusColor() }}1a;">{{ $p->statusLabel() }}</span></td>
            <td style="padding:10px 16px;color:var(--fg-3);">{{ $p->scheduled_for ? $p->scheduled_for->format('d/m/Y H:i') : '—' }}</td>
            <td style="padding:10px 16px;text-align:right;"><a href="{{ route('admin.social.posts.edit', $p) }}" style="color:var(--brand-500);text-decoration:none;">Editar</a></td>
          </tr>
        @empty
          <tr><td colspan="6" style="padding:30px;text-align:center;color:var(--fg-4);">Nenhum post ainda. Clique em "Novo post" para começar.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div style="margin-top:16px;">{{ $posts->links() }}</div>

</div>
@endsection
