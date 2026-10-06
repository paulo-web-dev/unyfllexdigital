{{-- Marca ✓ / ✗ estilizada (SVG inline, sem emoji). --}}
@props(['ok' => true])
<span {{ $attributes->merge(['class' => 'vt-marca ' . ($ok ? 'vt-marca--sim' : 'vt-marca--nao')]) }} role="img" aria-label="{{ $ok ? 'Sim' : 'Não' }}">
  @if($ok)
    <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M3.5 8.5l3 3 6-7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
  @else
    <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M4.5 4.5l7 7m0-7l-7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
  @endif
</span>
