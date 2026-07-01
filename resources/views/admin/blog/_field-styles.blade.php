@push('styles')
<style>
  .field-label { display:block;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--fg-3);margin-bottom:6px; }
  .field-input { width:100%;padding:10px 14px;background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-sm);color:var(--fg-2);font-family:var(--font-body);font-size:14px;outline:none;transition:border-color .2s,box-shadow .2s;box-sizing:border-box; }
  .field-input:focus { border-color:var(--brand-500);box-shadow:0 0 0 3px rgba(0,163,255,.18); }
  .field-input::placeholder { color:var(--fg-4); }
  .field-error { font-size:11px;color:var(--danger);margin-top:4px;display:block; }
  select.field-input { appearance:none;-webkit-appearance:none;cursor:pointer; }
  .field-input option { background:var(--bg-2);color:var(--fg-2); }
  textarea.field-input { resize:vertical; }
</style>
@endpush
