@php
  $tabs = [
    ['Calendário', route('admin.social.calendar'), request()->routeIs('admin.social.calendar')],
    ['Posts', route('admin.social.posts.index'), request()->routeIs('admin.social.posts.*')],
    ['Conta', route('admin.social.accounts.index'), request()->routeIs('admin.social.accounts.*')],
  ];
@endphp
<div style="display:flex;gap:4px;margin-bottom:22px;border-bottom:1px solid var(--line-1);">
  @foreach($tabs as $t)
    <a href="{{ $t[1] }}" style="padding:10px 16px;font-size:13px;text-decoration:none;border-bottom:2px solid {{ $t[2] ? 'var(--brand-500)' : 'transparent' }};color:{{ $t[2] ? 'var(--brand-500)' : 'var(--fg-3)' }};font-weight:{{ $t[2] ? '600' : '500' }};margin-bottom:-1px;">{{ $t[0] }}</a>
  @endforeach
</div>
