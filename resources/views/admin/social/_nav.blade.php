@php
  $revCount = rescue(fn () => \App\Models\SocialArtDraft::where('status', 'revisao')->count(), 0, false);
  $tabs = [
    ['Calendário', route('admin.social.calendar'), request()->routeIs('admin.social.calendar'), 0],
    ['Aprovação', route('admin.social.review.index'), request()->routeIs('admin.social.review.*'), $revCount],
    ['Posts', route('admin.social.posts.index'), request()->routeIs('admin.social.posts.*'), 0],
    ['Conta', route('admin.social.accounts.index'), request()->routeIs('admin.social.accounts.*'), 0],
  ];
@endphp
<div style="display:flex;gap:4px;margin-bottom:22px;border-bottom:1px solid var(--line-1);">
  @foreach($tabs as $t)
    <a href="{{ $t[1] }}" style="padding:10px 16px;font-size:13px;text-decoration:none;border-bottom:2px solid {{ $t[2] ? 'var(--brand-500)' : 'transparent' }};color:{{ $t[2] ? 'var(--brand-500)' : 'var(--fg-3)' }};font-weight:{{ $t[2] ? '600' : '500' }};margin-bottom:-1px;display:flex;align-items:center;gap:6px;">
      {{ $t[0] }}
      @if($t[3] > 0)<span style="background:var(--brand-500);color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:10px;line-height:1.5;">{{ $t[3] }}</span>@endif
    </a>
  @endforeach
</div>
