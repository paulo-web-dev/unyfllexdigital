@extends('layouts.admin')
@section('title', 'Cursos & Minisséries')
@section('section', 'Operacional')

@section('content')
<div class="page">

  <div class="page-header">
    <div>
      <h1 class="page-title">Cursos &amp; Minisséries</h1>
      <p class="page-subtitle">Catálogo completo · cápsulas, podcasts, PDFs e flashcards</p>
    </div>
    <div class="page-actions">
      <button class="btn">Upload em lote</button>
      <button class="btn btn-primary"><svg class="ico" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12 5v14M5 12h14"/></svg> Nova minissérie</button>
    </div>
  </div>

  <div class="kpi-row">
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Minisséries</span></div><div class="kpi-value">26</div><div class="kpi-delta positive">↑ 2 publicadas</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Cápsulas totais</span></div><div class="kpi-value">184</div><div class="kpi-delta positive">↑ 8 vs. mês ant.</div></div>
    <div class="kpi-card kpi-gold"><div class="kpi-top"><span class="kpi-label">Conclusão média</span></div><div class="kpi-value" style="color:var(--gold-400);">62%</div><div class="kpi-delta positive">↑ 3,2%</div></div>
    <div class="kpi-card"><div class="kpi-top"><span class="kpi-label">Receita do catálogo</span></div><div class="kpi-value">R$ 2,84M</div><div class="kpi-delta positive">↑ 12,7% 30d</div></div>
  </div>

  <div class="filter-bar" style="border-radius:14px;border:1px solid var(--line-2);background:var(--bg-2);margin-bottom:14px;">
    <div class="search-mini">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:14px;height:14px;"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>
      <input type="search" placeholder="Buscar minisséries…">
    </div>
    <button class="chip-filter active">Categoria: todas</button>
    <button class="chip-filter">Status: publicado</button>
    <div style="flex:1;"></div>
  </div>

  {{-- Grid de cursos --}}
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;">
    {{--
      TODO: @foreach($cursos as $curso)
      <div class="card" style="padding:0;overflow:hidden;">
        <div style="aspect-ratio:16/9;background:linear-gradient(135deg,#00A3FF,#002C4D);"></div>
        <div style="padding:14px;">
          <h4>{{ $curso->title }}</h4>
          ...
        </div>
      </div>
      @endforeach
    --}}
    <div class="card" style="padding:40px;text-align:center;color:var(--fg-4);font-size:13px;grid-column:1/-1;">
      Conecte o model <code>Classes</code> para exibir as minisséries aqui.
    </div>
  </div>

</div>
@endsection
