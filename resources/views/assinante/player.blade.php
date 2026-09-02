@extends('layouts.assinante')
@section('title', $painel->title . ' — ' . $classe->title . ' — Assinatura Unyflex')
@section('section', 'Player')

@section('content')
@php
  $totalAulas  = $aulas->count();
  $vistosPainel = $aulas->filter(fn ($a) => in_array($a->id, $feitas))->count();
  $pct = $totalAulas > 0 ? (int) round($vistosPainel / $totalAulas * 100) : 0;
  $podcast = collect($materiais)->firstWhere('type', 'PODCAST');
  // Descrição da aula como texto puro: o fallback é panels.content, que vem com
  // HTML + entidades do banco (mesma limpeza de PanelProvaService::fonte()).
  $descAula = function ($html) {
      $txt = html_entity_decode((string) $html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
      $txt = preg_replace('/<(br|\/p|\/div|\/li|\/h[1-6])[^>]*>/i', ' ', $txt); // quebras viram espaço
      $txt = trim(preg_replace('/\s+/u', ' ', strip_tags($txt)));
      return $txt === '-' ? '' : $txt;
  };
@endphp

<div class="as-player">

  {{-- Barra superior: voltar + contexto do painel + progresso do painel --}}
  <div class="as-player__top">
    <a href="{{ $urlVoltar }}" class="as-btn as-btn--ghost">
      <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.2;"><polyline points="15 18 9 12 15 6"/></svg>
      Voltar ao catálogo
    </a>
    <div class="as-player__crumb">
      {{-- Nomenclatura de produto: a turma gravada inteira é o "Curso Livre Aprofundado". --}}
      <span class="as-player__crumb-turma">{{ $classe->express ? '' : 'Curso Livre Aprofundado: ' }}{{ $classe->title }}</span>
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
      <p class="as-player__desc" id="pl-desc">{{ $descAula($capsula['descricao']) }}</p>

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
        @if(count($questoesProva))
          <button type="button" class="as-tab" data-tab="prova">Prova <span class="as-tab__cnt">{{ count($questoesProva) }}</span></button>
        @endif
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

      @if(count($questoesProva))
      {{-- Aba Prova: mesmo padrão do simulado dos modulares, visual as-*. A correção
           exibida é local; a nota gravada é recalculada no servidor (player.prova.resultado). --}}
      <section class="as-tabpanel" data-panel="prova" hidden>
        <h4>Prova deste curso</h4>
        <p class="as-muted" style="margin:0 0 14px;">
          {{ count($questoesProva) }} {{ count($questoesProva) === 1 ? 'questão' : 'questões' }}
          @if($tentativasProva->isNotEmpty())
            · Melhor nota: {{ $melhorProva }}/{{ count($questoesProva) }}
            · {{ $tentativasProva->count() }} {{ $tentativasProva->count() === 1 ? 'tentativa' : 'tentativas' }}
          @endif
        </p>

        <div class="as-prova" id="pl-prova"
             data-url="{{ route('player.prova.resultado', [$classe->slug, $painel->id]) }}">
          @foreach($questoesProva as $qi => $q)
            <div class="as-prova__q" data-correct="{{ (int) ($q['correta'] ?? 0) }}">
              <p class="as-prova__enunciado">{{ $qi + 1 }}. {{ $q['enunciado'] ?? '' }}</p>
              @foreach(($q['alternativas'] ?? []) as $ai => $alt)
                <button type="button" class="as-prova__alt" data-i="{{ $ai }}">
                  <strong>{{ chr(65 + $ai) }})</strong> {{ $alt }}
                </button>
              @endforeach
              <div class="as-prova__coment" hidden>{!! nl2br(e($q['comentario'] ?? '')) !!}</div>
            </div>
          @endforeach

          <div class="as-prova__acoes">
            <button type="button" class="as-btn as-btn--primary" id="pl-prova-corrigir">Finalizar prova</button>
            <button type="button" class="as-btn as-btn--ghost" id="pl-prova-refazer" hidden>Refazer</button>
            <span class="as-prova__score" id="pl-prova-score"></span>
            <span class="as-prova__saved" id="pl-prova-saved" hidden></span>
          </div>
        </div>

        {{-- Certificado: melhor nota >= mínimo na prova (carga horária pelo tipo da turma).
             Aprovado nesta sessão, o JS revela o botão sem recarregar. --}}
        @if($certificado)
          <div class="as-cert" id="pl-cert" data-minimo="{{ $certificado['minimo'] }}" style="margin-top:16px; padding-top:14px; border-top:1px solid rgba(255,255,255,.07);">
            <a href="{{ $certificado['url'] }}" id="pl-cert-link" class="as-btn as-btn--primary" @unless($certificado['aprovado']) hidden @endunless>Emitir certificado ({{ $certificado['horas'] }}h)</a>
            <p class="as-muted" id="pl-cert-falta" style="margin:0;" @if($certificado['aprovado']) hidden @endif>
              Certificado ({{ $certificado['horas'] }}h): acerte pelo menos
              {{ $certificado['minimo'] }}/{{ count($questoesProva) }} na prova para liberar{{ $melhorProva > 0 ? ' — sua melhor nota: ' . $melhorProva . '/' . count($questoesProva) : '' }}.
            </p>
          </div>
        @endif
      </section>
      @endif
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
          <p class="as-muted">Próximo curso {{ $classe->express ? 'desta minissérie' : 'deste Curso Livre Aprofundado' }}</p>
          <a class="as-player__nextpanel" href="{{ $proximo['url'] }}">
            <span class="as-player__eyebrow">Curso {{ $proximo['numero'] }}</span>
            <strong>{{ $proximo['titulo'] }}</strong>
          </a>
        @else
          <p class="as-muted">Este é o último curso {{ $classe->express ? 'desta minissérie' : 'deste Curso Livre Aprofundado' }}.</p>
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
      'desc'   => $descAula($a->subtitle ?: $painel->content ?: ''),
      'link'   => (string) $a->link,
  ])->values()->all(),
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endsection

@push('styles')
<style>
  .as-prova__q { padding:14px 0; border-bottom:1px solid rgba(255,255,255,.07); }
  .as-prova__q:last-of-type { border-bottom:0; }
  .as-prova__enunciado { font-size:14px; font-weight:600; color:var(--as-fg-1, #fff); line-height:1.5; margin:0 0 10px; }
  .as-prova__alt {
    display:block; width:100%; text-align:left; margin-bottom:8px; padding:10px 12px;
    background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.10); border-radius:10px;
    color:var(--as-fg-2, #cdd6e4); font-size:13px; line-height:1.45; cursor:pointer;
  }
  .as-prova__alt strong { color:var(--as-blue-2, #58b6ff); margin-right:4px; }
  .as-prova__alt.is-sel { border-color:var(--as-blue, #0088F4); background:rgba(0,136,244,.12); }
  .as-prova__alt.is-certa { border-color:#2bd9a1; background:rgba(43,217,161,.14); }
  .as-prova__alt.is-errada { border-color:#ff6b6b; background:rgba(255,107,107,.12); }
  .as-prova__coment {
    margin-top:10px; padding:10px 12px; background:rgba(0,136,244,.07);
    border-left:3px solid var(--as-blue, #0088F4); border-radius:4px;
    font-size:12.5px; color:var(--as-fg-3, #9aa7ba); line-height:1.5;
  }
  .as-prova__acoes { display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-top:14px; }
  .as-prova__score { font-size:14px; font-weight:700; color:var(--as-fg-1, #fff); }
  .as-prova__saved { font-size:12px; color:#6FE6BD; }
</style>
@endpush

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

  // ── Aba Prova (correção local para exibição; nota recalculada no servidor) ──
  (function () {
    const quiz = document.getElementById('pl-prova');
    if (!quiz) return;
    const qs    = Array.from(quiz.querySelectorAll('.as-prova__q'));
    const btnC  = document.getElementById('pl-prova-corrigir');
    const btnR  = document.getElementById('pl-prova-refazer');
    const score = document.getElementById('pl-prova-score');
    const saved = document.getElementById('pl-prova-saved');
    let done = false;

    qs.forEach(q => q.querySelectorAll('.as-prova__alt').forEach(alt => {
      alt.addEventListener('click', () => {
        if (done) return;
        q.querySelectorAll('.as-prova__alt').forEach(a => a.classList.remove('is-sel'));
        alt.classList.add('is-sel');
      });
    }));

    btnC.addEventListener('click', () => {
      if (done) return;
      done = true;
      let acertos = 0;
      const answers = [];
      qs.forEach(q => {
        const correct = parseInt(q.getAttribute('data-correct'), 10);
        const sel = q.querySelector('.as-prova__alt.is-sel');
        const selIdx = sel ? parseInt(sel.getAttribute('data-i'), 10) : -1;
        answers.push(selIdx);
        q.querySelectorAll('.as-prova__alt').forEach(a => {
          const i = parseInt(a.getAttribute('data-i'), 10);
          if (i === correct) a.classList.add('is-certa');
          else if (sel && a === sel) a.classList.add('is-errada');
        });
        if (selIdx === correct) acertos++;
        const com = q.querySelector('.as-prova__coment');
        if (com && com.textContent.trim()) com.hidden = false;
      });
      score.textContent = 'Acertos: ' + acertos + ' / ' + qs.length;
      btnC.hidden = true;
      btnR.hidden = false;

      fetch(quiz.dataset.url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ score: acertos, total: qs.length, answers: answers })
      }).then(r => r.json())
        .then(r => {
          saved.textContent = r && r.ok ? '✓ Nota registrada' : '(não consegui registrar a nota)';
          saved.hidden = false;
          // Nota (do servidor) atingiu o mínimo: revela o botão do certificado.
          const certBox = document.getElementById('pl-cert');
          const certLink = document.getElementById('pl-cert-link');
          if (r && r.ok && certBox && certLink && r.score >= parseInt(certBox.dataset.minimo, 10)) {
            certLink.hidden = false;
            const falta = document.getElementById('pl-cert-falta');
            if (falta) falta.hidden = true;
          }
        })
        .catch(() => { saved.textContent = '(não consegui registrar a nota)'; saved.hidden = false; });
    });

    btnR.addEventListener('click', () => {
      done = false;
      qs.forEach(q => {
        q.querySelectorAll('.as-prova__alt').forEach(a => a.classList.remove('is-sel', 'is-certa', 'is-errada'));
        const com = q.querySelector('.as-prova__coment');
        if (com) com.hidden = true;
      });
      score.textContent = '';
      saved.hidden = true;
      btnR.hidden = true;
      btnC.hidden = false;
    });
  })();
})();
</script>
@endpush
