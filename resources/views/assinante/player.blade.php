@extends('layouts.assinante')
@section('title', $painel->title . ' — ' . $classe->title . ' — Assinatura Unyflex')
@section('section', 'Player')

@section('content')
@php
  $totalAulas  = $aulas->count();
  $vistosPainel = $aulas->filter(fn ($a) => in_array($a->id, $feitas))->count();
  $pct = $totalAulas > 0 ? (int) round($vistosPainel / $totalAulas * 100) : 0;
  $podcast = collect($materiais)->firstWhere('type', 'PODCAST');
@endphp

<div class="as-player">

  {{-- Barra superior: voltar + contexto do painel + progresso do painel --}}
  <div class="as-player__top">
    <a href="{{ $urlVoltar }}" class="as-btn as-btn--ghost">
      <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.2;"><polyline points="15 18 9 12 15 6"/></svg>
      Voltar ao catálogo
    </a>
    <div class="as-player__crumb">
      <span class="as-player__crumb-turma">{{ $classe->title }}</span>
      <span class="as-player__crumb-sep">·</span>
      <span>Curso {{ $numero }} de {{ $totalPaineis }}</span>
    </div>
    <div class="as-player__prog">
      <div class="as-player__prog-bar"><i id="pl-bar" style="width:{{ $pct }}%"></i></div>
      <span id="pl-prog-label">{{ $vistosPainel }}/{{ $totalAulas }} aulas · {{ $pct }}%</span>
    </div>
  </div>

  <div class="as-player__grid">

    {{-- ══ Coluna principal ══ --}}
    <div class="as-player__main">
      <div class="as-player__video">
        <iframe id="pl-iframe" src="{{ $capsula['link'] }}" allowfullscreen allow="autoplay; encrypted-media"></iframe>
      </div>

      <p class="as-player__eyebrow" id="pl-num">Aula {{ $numero }}.{{ $aulas->search(fn ($a) => $a->id === $capsula['video_id']) + 1 }}</p>
      <h2 class="as-player__title" id="pl-title">{{ $capsula['titulo'] }}</h2>
      <p class="as-player__desc" id="pl-desc">{{ $capsula['descricao'] }}</p>

      <div class="as-player__nav">
        <button type="button" id="pl-prev" class="as-btn as-btn--ghost">
          <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.2;"><polyline points="15 18 9 12 15 6"/></svg>
          Aula anterior
        </button>
        <button type="button" id="pl-next" class="as-btn as-btn--primary">
          Próxima aula
          <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.2;"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
        @if($proximo)
          <a id="pl-next-panel" class="as-btn as-btn--primary" href="{{ $proximo['url'] }}" hidden>
            Próximo curso: {{ \Illuminate\Support\Str::limit($proximo['titulo'], 40) }}
            <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.2;"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
        @else
          <a id="pl-back-end" class="as-btn as-btn--primary" href="{{ $urlVoltar }}" hidden>Voltar ao catálogo</a>
        @endif
      </div>

      {{-- Abas --}}
      <div class="as-tabs" role="tablist">
        <button type="button" class="as-tab active" data-tab="resumo">Resumo</button>
        <button type="button" class="as-tab" data-tab="materiais">Materiais <span class="as-tab__cnt">{{ count($materiais) }}</span></button>
        <button type="button" class="as-tab" data-tab="podcast">Podcast</button>
      </div>

      <section class="as-tabpanel" data-panel="resumo">
        <h4>Resumo do curso</h4>
        <div class="as-tabpanel__body">{!! html_entity_decode((string) $painel->content, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?: '<p class="as-muted">Resumo não disponível.</p>' !!}</div>
      </section>

      <section class="as-tabpanel" data-panel="materiais" hidden>
        <h4>Materiais deste curso</h4>
        <p class="as-muted" style="margin:0 0 14px;">Curso {{ $numero }}: {{ $painel->title }}</p>
        <div id="pl-materiais"></div>
      </section>

      <section class="as-tabpanel" data-panel="podcast" hidden>
        <h4>Versão em áudio</h4>
        <div id="pl-podcast"></div>
      </section>
    </div>

    {{-- ══ Lateral: só o painel em contexto ══ --}}
    <aside class="as-player__side">
      <div class="as-player__panelcard">
        <p class="as-player__eyebrow">Curso {{ $numero }} de {{ $totalPaineis }}</p>
        <h3>{{ $painel->title }}</h3>
        <p class="as-muted">{{ $totalAulas }} {{ $totalAulas === 1 ? 'aula' : 'aulas' }} @if(count($materiais)) · {{ count($materiais) }} {{ count($materiais) === 1 ? 'material' : 'materiais' }} @endif</p>
      </div>

      <ol class="as-aulas" id="pl-aulas">
        @foreach($aulas as $i => $aula)
          @php $ativa = $aula->id === $capsula['video_id']; $feita = in_array($aula->id, $feitas); @endphp
          <li class="as-aula {{ $ativa ? 'is-active' : '' }} {{ $feita ? 'is-done' : '' }}" data-idx="{{ $i }}" data-id="{{ $aula->id }}">
            <span class="as-aula__num">
              <span class="as-aula__num-txt">{{ $numero }}.{{ $i + 1 }}</span>
              <svg class="as-aula__check" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
              <svg class="as-aula__play" viewBox="0 0 24 24"><polygon points="6 4 20 12 6 20 6 4"/></svg>
            </span>
            <span class="as-aula__txt">
              <span class="as-aula__title">{{ $aula->titulo ?: 'Sem título' }}</span>
              <span class="as-aula__sub">~12 min</span>
            </span>
          </li>
        @endforeach
      </ol>

      <div class="as-player__side-foot">
        @if($proximo)
          <p class="as-muted">Próximo curso desta turma</p>
          <a class="as-player__nextpanel" href="{{ $proximo['url'] }}">
            <span class="as-player__eyebrow">Curso {{ $proximo['numero'] }}</span>
            <strong>{{ $proximo['titulo'] }}</strong>
          </a>
        @else
          <p class="as-muted">Este é o último curso desta turma.</p>
          <a class="as-btn as-btn--ghost" href="{{ $urlVoltar }}">Voltar ao catálogo</a>
        @endif
      </div>
    </aside>
  </div>
</div>

<script id="pl-data" type="application/json">{!! json_encode([
  'slug'      => $classe->slug,
  'numero'    => $numero,
  'contexto'  => $queryContexto,
  'ativa'     => (int) $capsula['video_id'],
  'feitas'    => array_map('intval', $feitas),
  'materiais' => $materiais,
  'materiaisVistos' => array_map('intval', $materiaisVistos),
  'aulas'     => $aulas->map(fn ($a, $i) => [
      'id'     => (int) $a->id,
      'num'    => $numero . '.' . ($i + 1),
      'titulo' => (string) ($a->titulo ?: 'Sem título'),
      'desc'   => (string) ($a->subtitle ?: $painel->content ?: ''),
      'link'   => (string) $a->link,
  ])->values()->all(),
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endsection

@push('scripts')
<script>
(function () {
  const D     = JSON.parse(document.getElementById('pl-data').textContent);
  const CSRF  = '{{ csrf_token() }}';
  const feitas = new Set(D.feitas);
  const matsVistos = new Set(D.materiaisVistos);

  const iframe  = document.getElementById('pl-iframe');
  const elNum   = document.getElementById('pl-num');
  const elTitle = document.getElementById('pl-title');
  const elDesc  = document.getElementById('pl-desc');
  const elBar   = document.getElementById('pl-bar');
  const elProg  = document.getElementById('pl-prog-label');
  const btnPrev = document.getElementById('pl-prev');
  const btnNext = document.getElementById('pl-next');
  const nextPanel = document.getElementById('pl-next-panel');
  const backEnd   = document.getElementById('pl-back-end');
  const items   = Array.from(document.querySelectorAll('.as-aula'));
  let idx = D.aulas.findIndex(a => a.id === D.ativa);
  if (idx < 0) idx = 0;

  function registrarView(id) {
    fetch(`/dashboard/player/${D.slug}/${id}/concluir`, {
      method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' }, body: '{}'
    }).catch(() => {});
  }
  function registrarMaterial(id) {
    if (!id || matsVistos.has(id)) return;
    matsVistos.add(id);
    fetch(`/dashboard/player/${D.slug}/material/${id}/registrar`, {
      method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' }, body: '{}'
    }).catch(() => {});
  }
  window.plRegistrarMaterial = registrarMaterial;

  function progresso() {
    const total = D.aulas.length;
    const vistos = D.aulas.filter(a => feitas.has(a.id)).length;
    const pct = total ? Math.round(vistos / total * 100) : 0;
    elBar.style.width = pct + '%';
    elProg.textContent = `${vistos}/${total} aulas · ${pct}%`;
  }

  function renderNav() {
    const last = idx >= D.aulas.length - 1;
    btnPrev.disabled = idx <= 0;
    btnNext.hidden = last;
    if (nextPanel) nextPanel.hidden = !last;
    if (backEnd)   backEnd.hidden   = !last;
  }

  function renderMateriais() {
    const box = document.getElementById('pl-materiais');
    const mats = D.materiais.filter(m => m.type !== 'PODCAST');
    if (!mats.length) {
      box.innerHTML = '<p class="as-muted as-empty">Nenhum material disponível para este curso.</p>';
      return;
    }
    const icons = { PDF: '📄', PowerPoint: '📊', Word: '📝', Excel: '📈', Link: '🔗' };
    box.innerHTML = mats.map(m => {
      const visto = matsVistos.has(m.id);
      return `<a class="as-material ${visto ? 'is-visto' : ''}" href="https://unygov.com.br/storage/materials/${encodeURIComponent(m.file)}" target="_blank" rel="noopener" onclick="plRegistrarMaterial(${m.id})">
        <span class="as-material__ico">${icons[m.type] || '📁'}</span>
        <span class="as-material__txt"><span class="as-material__type">${m.type || 'Material'}</span><span class="as-material__name">${m.name}</span></span>
        ${visto ? '<span class="as-material__badge">✓ Baixado</span>' : ''}
      </a>`;
    }).join('');
  }

  function renderPodcast() {
    const box = document.getElementById('pl-podcast');
    const pod = D.materiais.find(m => m.type === 'PODCAST');
    box.innerHTML = pod
      ? `<a class="as-material" href="https://unygov.com.br/storage/materials/${encodeURIComponent(pod.file)}" target="_blank" rel="noopener" onclick="plRegistrarMaterial(${pod.id})">
           <span class="as-material__ico">🎧</span>
           <span class="as-material__txt"><span class="as-material__type">Áudiocast</span><span class="as-material__name">${pod.name}</span></span>
         </a>`
      : '<p class="as-muted as-empty">Áudiocast não disponível para este curso.</p>';
  }

  function carregar(i, push) {
    if (i < 0 || i >= D.aulas.length) return;
    idx = i;
    const a = D.aulas[i];
    iframe.src = a.link;
    elNum.textContent = 'Aula ' + a.num;
    elTitle.textContent = a.titulo;
    elDesc.textContent = a.desc;
    items.forEach((el, k) => {
      el.classList.toggle('is-active', k === i);
      el.classList.toggle('is-done', feitas.has(D.aulas[k].id));
    });
    if (!feitas.has(a.id)) { feitas.add(a.id); registrarView(a.id); items[i].classList.add('is-done'); }
    progresso();
    renderNav();
    items[i].scrollIntoView({ block: 'nearest' });
    if (push) history.pushState({ idx: i }, '', `/dashboard/player/${D.slug}/${a.id}?${D.contexto}`);
  }

  btnPrev.addEventListener('click', () => carregar(idx - 1, true));
  btnNext.addEventListener('click', () => carregar(idx + 1, true));
  items.forEach(el => el.addEventListener('click', () => carregar(+el.dataset.idx, true)));
  window.addEventListener('popstate', e => { if (e.state && typeof e.state.idx === 'number') carregar(e.state.idx, false); });

  document.querySelectorAll('.as-tab').forEach(t => t.addEventListener('click', () => {
    document.querySelectorAll('.as-tab').forEach(x => x.classList.toggle('active', x === t));
    document.querySelectorAll('.as-tabpanel').forEach(p => p.hidden = p.dataset.panel !== t.dataset.tab);
  }));

  renderMateriais();
  renderPodcast();
  progresso();
  renderNav();
  if (items[idx]) items[idx].scrollIntoView({ block: 'nearest' });
})();
</script>
@endpush
