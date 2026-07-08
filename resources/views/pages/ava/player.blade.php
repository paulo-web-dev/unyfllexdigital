@extends($layout ?? 'layouts.app')
@section('title',$classe->title . ' — Unyflex Digital')

@section('content')
<div class="scroll" style="padding:0;">

  <div style="background:var(--bg-1);border-bottom:1px solid var(--line-1);padding:12px 24px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;position:sticky;top:0;z-index:10;">
    <a href="{{ route('ava.cursos') }}" class="btn btn-ghost" style="padding:7px 12px;font-size:12px;">
      <i data-lucide="chevron-left" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;"></i>
      <span>Cursos</span>
    </a>
    <span style="color:var(--fg-4);">/</span>
    <span style="font-size:12px;font-weight:600;letter-spacing:0.12em;text-transform:uppercase;color:var(--brand-300);">
      {{$classe->title }}
    </span>
    <span style="color:var(--fg-4);">·</span>
    <span id="header-cap-label" style="font-size:12px;color:var(--fg-3);">Cápsula {{ $capsula['numero'] }}</span>
    <div style="margin-left:auto;display:flex;align-items:center;gap:10px;">
      <div style="display:flex;align-items:center;gap:8px;">
        <div style="width:80px;height:4px;background:rgba(255,255,255,0.08);border-radius:2px;overflow:hidden;">
          <div id="header-progress-bar" style="height:100%;width:{{ $progresso }}%;background:var(--grad-brand);transition:width 0.4s;"></div>
        </div>
        <span id="header-progress-pct" style="font-family:var(--font-mono);font-size:11px;color:var(--brand-300);">{{ $progresso }}%</span>
      </div>
      <span id="header-watched-label" style="font-size:11px;color:var(--fg-4);">{{ $totalAssistidos }}/{{ $totalVideos }} assistidas</span>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 360px;min-height:calc(100vh - 120px);">

    <div style="background:var(--bg-0);overflow-y:auto;padding:28px 32px 48px;">

      <div style="position:relative;border-radius:var(--r-xl);overflow:hidden;background:#000;border:1px solid var(--line-2);box-shadow:var(--shadow-lg);aspect-ratio:16/9;margin-bottom:24px;">
        <iframe
          id="main-player"
          src="{{ $capsula['link'] }}"
          style="width:100%;height:100%;border:none;display:block;"
          allowfullscreen
          allow="autoplay; encrypted-media">
        </iframe>
      </div>

      <div style="margin-bottom:24px;">
        <h2 id="current-title"
            style="font-family:var(--font-display);font-weight:800;font-size:clamp(18px,2.5vw,24px);color:#fff;letter-spacing:-0.02em;margin-bottom:12px;">
          {{ $capsula['numero'] }} {{ $capsula['titulo'] }}
        </h2>
        <p id="current-desc" style="color:var(--fg-3);font-size:14px;line-height:1.6;margin-bottom:16px;">
          {{ $capsula['descricao'] }}
        </p>
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
          <button id="btn-prev" class="btn btn-secondary" onclick="navegarPrev()" style="opacity:0.35;pointer-events:none;">
            <i data-lucide="chevron-left" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;"></i>
            Aula anterior
          </button>
          <button id="btn-next" class="btn btn-primary" onclick="navegarNext()">
            Próxima aula
            <i data-lucide="chevron-right" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;"></i>
          </button>
          <button class="btn btn-ghost" type="button">
            <i data-lucide="bookmark" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
            <span>Salvar</span>
          </button>
        </div>
      </div>

      <div style="background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-lg);padding:6px;display:flex;gap:4px;margin-bottom:18px;">
        @foreach([['resumo','file-text','Resumo'],['materiais','download','Materiais'],['podcast','mic','Podcast']] as [$tabId,$tabIcon,$tabLabel])
          <button class="player-tab-btn {{ $tabId === 'resumo' ? 'active' : '' }}"
                  data-tab="{{ $tabId }}" type="button"
                  style="flex:1;padding:10px 8px;border-radius:10px;border:none;cursor:pointer;font-size:12px;font-weight:600;font-family:inherit;display:inline-flex;align-items:center;justify-content:center;gap:6px;transition:all 0.2s;
                  background:{{ $tabId==='resumo'?'var(--bg-3)':'transparent' }};color:{{ $tabId==='resumo'?'#fff':'var(--fg-3)' }};">
            <i data-lucide="{{ $tabIcon }}" style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
            {{ $tabLabel }}
          </button>
        @endforeach
      </div>

      <div class="player-tab-panel" data-panel="resumo"
           style="background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-lg);padding:24px 28px;">
        <h4 style="font-family:var(--font-display);font-weight:700;color:#fff;font-size:17px;margin-bottom:14px;">Resumo da cápsula</h4>
        <div id="panel-resumo-content">{!! html_entity_decode($capsula['resumo'], ENT_QUOTES | ENT_HTML5, 'UTF-8') !!}</div>
      </div>

      <div class="player-tab-panel" data-panel="materiais"
           style="display:none;background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-lg);padding:24px 28px;">
        <h4 style="font-family:var(--font-display);font-weight:700;color:#fff;font-size:17px;margin-bottom:6px;">Materiais de Apoio</h4>
        <p id="materiais-season-label" style="font-size:13px;color:var(--fg-3);margin-bottom:18px;">{{ $capsula['trecho'] }}</p>
        <div id="materiais-container"></div>
      </div>

      <div class="player-tab-panel" data-panel="podcast"
           style="display:none;background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-lg);padding:24px 28px;">
        <h4 style="font-family:var(--font-display);font-weight:700;color:#fff;font-size:17px;margin-bottom:8px;">Versão em áudio</h4>
        <div id="podcast-container"></div>
      </div>

    </div>

    <div style="background:var(--bg-1);border-left:1px solid var(--line-1);display:flex;flex-direction:column;overflow:hidden;height:calc(100vh - 68px);position:sticky;top:68px;">

      <div style="padding:18px 20px 14px;border-bottom:1px solid var(--line-1);flex-shrink:0;">
        <div style="font-size:10px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;color:var(--brand-300);margin-bottom:4px;">Minissérie</div>
        <h3 style="font-family:var(--font-display);font-weight:700;color:#fff;font-size:15px;line-height:1.3;margin-bottom:10px;">{{$classe->title }}</h3>
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="flex:1;height:4px;background:rgba(255,255,255,0.07);border-radius:2px;overflow:hidden;">
            <div id="sidebar-progress-bar" style="height:100%;width:{{ $progresso }}%;background:var(--grad-brand);transition:width 0.4s;"></div>
          </div>
          <span id="sidebar-progress-label" style="font-family:var(--font-mono);font-size:11px;color:var(--brand-300);">{{ $totalAssistidos }} / {{ $totalVideos }}</span>
        </div>
      </div>

      <div style="overflow-y:auto;flex:1;padding:8px 0;">
        @foreach($panels as $panelItem)
          @php $pNum = $loop->iteration; $isFirst = $loop->first; @endphp

          <button class="sidebar-season-btn" data-season="{{ $loop->index }}"
                  style="width:100%;text-align:left;background:transparent;border:none;padding:12px 20px 10px;cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:8px;border-top:1px solid var(--line-1);">
            <div>
              <div style="font-size:10px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--brand-400);margin-bottom:3px;">Temporada {{ $pNum }}</div>
              <div style="font-size:13px;font-weight:600;color:var(--fg-2);line-height:1.3;">{{ $panelItem->title }}</div>
              <div style="font-size:11px;color:var(--fg-4);margin-top:2px;">{{ $panelItem->video_lesson->count() }} cápsulas @if($panelItem->material->count()>0) · {{ $panelItem->material->count() }} materiais @endif</div>
            </div>
            <i data-lucide="chevron-down" class="season-chevron {{ $isFirst?'open':'' }}"
               style="width:16px;height:16px;stroke:var(--fg-4);fill:none;stroke-width:2;flex-shrink:0;transition:transform 0.25s;{{ $isFirst?'transform:rotate(180deg);':'' }}"></i>
          </button>

          <div class="sidebar-season-content" data-season="{{ $loop->index }}" style="{{ $isFirst?'':'display:none;' }}">
            @foreach($panelItem->video_lesson as $video)
              @php
                $vNum    = $loop->iteration;
                $isAtivo = $video->id === $capsula['video_id'];
                $isFeito = $capsulas->firstWhere('id', $video->id)['feita'] ?? false;
              @endphp
              <div class="video-item {{ $isAtivo?'is-active':'' }} {{ $isFeito?'is-done':'' }}"
                   data-id="{{ $video->id }}"
                   data-panel-id="{{ $video->panel_id }}"
                   data-num="{{ $pNum }}.{{ $vNum }}"
                   data-titulo="{{ addslashes($video->titulo ?? '') }}"
                   data-desc="{{ addslashes($video->subtitle ?? $panelItem->content ?? '') }}"
                   data-link="{{ $video->link ?? '' }}"
                   data-resumo="{{ addslashes($panelItem->content ?? 'Resumo não disponível.') }}"
                   data-season-index="{{ $loop->parent->index }}"
                   data-season-label="{{ addslashes('Temporada '.$pNum.': '.$panelItem->title) }}"
data-materiais="{{ $panelItem->material->map(fn($m) => ['id' => $m->id, 'type' => $m->type, 'name' => $m->name ?? $m->file_name, 'file' => $m->file_name])->toJson() }}"
                   style="display:flex;align-items:flex-start;gap:10px;padding:10px 14px 10px 20px;border-bottom:1px solid var(--line-1);cursor:pointer;transition:background 0.2s;
                   {{ $isAtivo?'background:linear-gradient(90deg,rgba(0,163,255,0.12),transparent);border-left:2px solid var(--brand-400);':'' }}">

                <div class="lesson-num"
                     style="width:26px;height:26px;border-radius:50%;border:1.5px solid {{ $isAtivo?'var(--brand-400)':'var(--line-2)' }};background:{{ $isAtivo?'rgba(0,163,255,0.15)':'transparent' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                  @if($isFeito && !$isAtivo)
                    <svg viewBox="0 0 24 24" style="width:11px;height:11px;stroke:var(--brand-300);fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round;"><polyline points="20 6 9 17 4 12"/></svg>
                  @elseif($isAtivo)
                    <svg viewBox="0 0 24 24" style="width:9px;height:9px;fill:var(--brand-300);"><polygon points="6 4 20 12 6 20 6 4"/></svg>
                  @else
                    <span style="font-family:var(--font-mono);font-size:9px;color:var(--fg-4);">{{ $pNum }}.{{ $vNum }}</span>
                  @endif
                </div>

                <div style="flex:1;min-width:0;">
                  <div class="lesson-title"
                       style="font-size:13px;line-height:1.4;color:{{ $isAtivo?'#fff':'var(--fg-2)' }};font-weight:{{ $isAtivo?'600':'400' }};">
                    {{ $pNum }}.{{ $vNum }} {{ $video->titulo }}
                  </div>
                  <div style="font-size:11px;color:var(--fg-4);margin-top:2px;">~12 min</div>
                </div>
              </div>
            @endforeach
          </div>
        @endforeach
      </div>
    </div>

  </div>
</div>

<script id="watched-data"  type="application/json">{!! json_encode($capsulas->pluck('feita','id')) !!}</script>
<script id="totals-data"   type="application/json">{"total":{{ $totalVideos }},"watched":{{ $totalAssistidos }}}</script>
<script id="active-id"     type="application/json">{{ $capsula['video_id'] }}</script>
@endsection

@push('styles')
<style>
.player-tab-btn.active          { background:var(--bg-3)!important;color:#fff!important;box-shadow:inset 0 0 0 1px rgba(0,163,255,.3),0 0 18px -8px rgba(0,163,255,.5); }
.season-chevron.open            { transform:rotate(180deg)!important; }
.sidebar-season-btn:hover       { background:rgba(255,255,255,.03); }
.video-item:hover               { background:rgba(255,255,255,.03); }
.video-item.is-active           { background:linear-gradient(90deg,rgba(0,163,255,.12),transparent)!important;border-left:2px solid var(--brand-400)!important; }
@media(max-width:991px){
  div[style*="grid-template-columns:1fr 360px"]{ display:block!important; }
  div[style*="position:sticky;top:68px"]       { height:auto!important;position:static!important;max-height:60vh; }
}
</style>
@endpush

@push('scripts')
<script>
(function(){

  const SLUG             = @json($classe->slug);
  const CSRF             = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  const MATERIAIS_VISTOS = new Set(@json($materiaisVistos));
  const watched          = JSON.parse(document.getElementById('watched-data').textContent);
  const totals           = JSON.parse(document.getElementById('totals-data').textContent);
  const activeId         = JSON.parse(document.getElementById('active-id').textContent);

  let watchedCnt   = totals.watched;
  const totalCnt   = totals.total;

  const allItems   = Array.from(document.querySelectorAll('.video-item'));
  let   activeIdx  = allItems.findIndex(i => +i.dataset.id === +activeId);
  if   (activeIdx < 0) activeIdx = 0;

  const mainPlayer    = document.getElementById('main-player');
  const currentTitle  = document.getElementById('current-title');
  const currentDesc   = document.getElementById('current-desc');
  const headerCapLbl  = document.getElementById('header-cap-label');
  const headerBar     = document.getElementById('header-progress-bar');
  const headerPct     = document.getElementById('header-progress-pct');
  const headerWatched = document.getElementById('header-watched-label');
  const sidebarBar    = document.getElementById('sidebar-progress-bar');
  const sidebarLbl    = document.getElementById('sidebar-progress-label');
  const resumoContent = document.getElementById('panel-resumo-content');
  const materiaisLbl  = document.getElementById('materiais-season-label');
  const materiaisBox  = document.getElementById('materiais-container');
  const podcastBox    = document.getElementById('podcast-container');
  const btnPrev       = document.getElementById('btn-prev');
  const btnNext       = document.getElementById('btn-next');

  function registrarView(videoId){
    fetch(`/dashboard/player/${SLUG}/${videoId}/concluir`,{
      method:'POST',
      headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'},
      body:'{}'
    }).catch(()=>{});
  }

  function registrarMaterial(materialId){
    if(!materialId || MATERIAIS_VISTOS.has(parseInt(materialId))) return;
    MATERIAIS_VISTOS.add(parseInt(materialId));
    fetch(`/dashboard/player/${SLUG}/material/${materialId}/registrar`,{
      method:'POST',
      headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'},
      body:'{}'
    }).catch(()=>{});
  }

  function atualizarProgresso(){
    const pct = totalCnt > 0 ? Math.round((watchedCnt/totalCnt)*100) : 0;
    headerBar.style.width      = pct+'%';
    headerPct.textContent      = pct+'%';
    sidebarBar.style.width     = pct+'%';
    headerWatched.textContent  = `${watchedCnt}/${totalCnt} assistidas`;
    sidebarLbl.textContent     = `${watchedCnt} / ${totalCnt}`;
  }

  function renderMateriais(matJson, seasonLabel){
    if(materiaisLbl) materiaisLbl.textContent = seasonLabel;
    let mats = [];
    try{ mats = JSON.parse(matJson||'[]'); }catch(e){}
    if(!mats.length){
      materiaisBox.innerHTML = `<div style="padding:24px;text-align:center;color:var(--fg-4);font-size:14px;border:1px dashed var(--line-2);border-radius:10px;">Nenhum material disponível para esta cápsula.</div>`;
      return;
    }
    const icons  = {PDF:'📄',PODCAST:'🎧'};
    const labels = {PDF:'Mapa Mental / PDF',PODCAST:'Áudiocast'};
    materiaisBox.innerHTML = mats.map(m=>{
      const ic  = icons[m.type]  || '📁';
      const lb  = labels[m.type] || 'Material';
      const url = `https://unygov.com.br/storage/materials/${m.file}`;
      const jaBaixou = m.id && MATERIAIS_VISTOS.has(parseInt(m.id));
      const badge = jaBaixou
        ? `<span style="font-size:10px;font-weight:700;color:var(--success);background:rgba(43,217,161,0.10);border:1px solid rgba(43,217,161,0.25);border-radius:4px;padding:2px 6px;flex-shrink:0;">✓ Baixado</span>`
        : '';
      return `<a href="${url}" target="_blank" onclick="registrarMaterial(${m.id||0})"
        style="display:flex;align-items:center;gap:14px;padding:14px 16px;background:var(--bg-1);border:1px solid ${jaBaixou?'rgba(43,217,161,0.2)':'var(--line-1)'};border-radius:10px;text-decoration:none;color:inherit;margin-bottom:10px;transition:border-color .2s;"
        onmouseover="this.style.borderColor='rgba(0,163,255,.35)'" onmouseout="this.style.borderColor='${jaBaixou?'rgba(43,217,161,0.2)':'var(--line-1)'}'">
        <div style="width:40px;height:40px;border-radius:10px;background:var(--bg-3);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">${ic}</div>
        <div style="flex:1;min-width:0;">
          <div style="font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--brand-300);margin-bottom:2px;">${lb}</div>
          <div style="font-size:14px;color:var(--fg-2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${m.name}</div>
        </div>
        ${badge}
        <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:var(--fg-4);fill:none;stroke-width:2;flex-shrink:0;">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
        </svg>
      </a>`;
    }).join('');
  }

  function renderPodcast(matJson, num, titulo){
    let mats = [];
    try{ mats = JSON.parse(matJson||'[]'); }catch(e){}
    const pod = mats.find(m=>m.type==='PODCAST');
    podcastBox.innerHTML = pod
      ? `<div class="podcast" style="margin-top:16px;">
          <div class="cover"><svg viewBox="0 0 24 24" style="width:30px;height:30px;fill:currentColor;"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/></svg></div>
          <div class="meta"><div class="eyebrow">Cápsula ${num}</div><h4>${titulo}</h4></div>
          <a href="https://unygov.com.br/storage/materials/${pod.file}" target="_blank" class="play-mini" onclick="registrarMaterial(${pod.id||0})">
            <svg viewBox="0 0 24 24"><polygon points="6 4 20 12 6 20 6 4"/></svg>
          </a>
        </div>`
      : `<p style="color:var(--fg-4);font-size:14px;padding:24px;border:1px dashed var(--line-2);border-radius:10px;margin-top:16px;text-align:center;">Áudiocast não disponível para esta cápsula.</p>`;
  }

  function carregarVideo(idx){
    if(idx<0||idx>=allItems.length) return;
    const item  = allItems[idx];
    const id    = item.dataset.id;
    const num   = item.dataset.num;
    const title = item.dataset.titulo;
    const desc  = item.dataset.desc;
    const link  = item.dataset.link;
    const res   = item.dataset.resumo;
    const slbl  = item.dataset.seasonLabel;
    const mats  = item.dataset.materiais;

    mainPlayer.src = link;
    currentTitle.textContent   = `${num} ${title}`;
    currentDesc.textContent    = desc;
    headerCapLbl.textContent   = `Cápsula ${num}`;
    resumoContent.innerHTML    = `<p class="tp-p">${res.replace(/\\n/g,'<br>')}</p>`;
    renderMateriais(mats, slbl);
    renderPodcast(mats, num, title);
    history.pushState({idx}, '', `/dashboard/player/${SLUG}/${id}`);

    if(!watched[id]){
      watched[id] = true;
      watchedCnt++;
      atualizarProgresso();
      registrarView(id);
    }

    allItems.forEach((el,i)=>{
      const done = !!watched[el.dataset.id];
      const now  = i===idx;
      el.classList.toggle('is-active', now);
      el.style.background = now ? 'linear-gradient(90deg,rgba(0,163,255,.12),transparent)' : '';
      el.style.borderLeft = now ? '2px solid var(--brand-400)' : '';
      const numEl   = el.querySelector('.lesson-num');
      const titleEl = el.querySelector('.lesson-title');
      if(numEl){
        numEl.style.background  = now ? 'rgba(0,163,255,.15)' : 'transparent';
        numEl.style.borderColor = now ? 'var(--brand-400)'    : 'var(--line-2)';
        numEl.innerHTML = now
          ? `<svg viewBox="0 0 24 24" style="width:9px;height:9px;fill:var(--brand-300);"><polygon points="6 4 20 12 6 20 6 4"/></svg>`
          : done
            ? `<svg viewBox="0 0 24 24" style="width:11px;height:11px;stroke:var(--brand-300);fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round;"><polyline points="20 6 9 17 4 12"/></svg>`
            : `<span style="font-family:var(--font-mono);font-size:9px;color:var(--fg-4);">${el.dataset.num}</span>`;
      }
      if(titleEl){ titleEl.style.color = now ? '#fff' : 'var(--fg-2)'; titleEl.style.fontWeight = now ? '600' : '400'; }
    });

    const sc   = document.querySelector(`.sidebar-season-content[data-season="${item.dataset.seasonIndex}"]`);
    const chev = document.querySelector(`.sidebar-season-btn[data-season="${item.dataset.seasonIndex}"] .season-chevron`);
    if(sc && sc.style.display==='none'){ sc.style.display='block'; chev?.classList.add('open'); }
    item.scrollIntoView({block:'nearest',behavior:'smooth'});

    const hasPrev = idx > 0;
    const hasNext = idx < allItems.length-1;
    btnPrev.style.opacity       = hasPrev ? '1'    : '0.35';
    btnPrev.style.pointerEvents = hasPrev ? 'auto' : 'none';
    btnNext.style.opacity       = hasNext ? '1'    : '0.5';
    btnNext.style.pointerEvents = hasNext ? 'auto' : 'none';
    btnNext.textContent         = hasNext ? 'Próxima aula' : 'Última aula ✓';
    activeIdx = idx;
    if(window.lucide) lucide.createIcons();
  }

  window.navegarPrev = ()=> carregarVideo(activeIdx-1);
  window.navegarNext = ()=> carregarVideo(activeIdx+1);
  allItems.forEach((item,idx)=> item.addEventListener('click', ()=> carregarVideo(idx)));

  document.querySelectorAll('.sidebar-season-btn').forEach(btn=>{
    btn.addEventListener('click',function(){
      const idx  = this.dataset.season;
      const sc   = document.querySelector(`.sidebar-season-content[data-season="${idx}"]`);
      const chev = this.querySelector('.season-chevron');
      if(!sc) return;
      const open = sc.style.display!=='none';
      sc.style.display = open ? 'none' : 'block';
      chev?.classList.toggle('open', !open);
    });
  });

  document.querySelectorAll('.player-tab-btn').forEach(btn=>{
    btn.addEventListener('click',function(){
      document.querySelectorAll('.player-tab-btn').forEach(b=>{ b.classList.remove('active'); b.style.background='transparent'; b.style.color='var(--fg-3)'; });
      document.querySelectorAll('.player-tab-panel').forEach(p=> p.style.display='none');
      this.classList.add('active'); this.style.background='var(--bg-3)'; this.style.color='#fff';
      document.querySelector(`.player-tab-panel[data-panel="${this.dataset.tab}"]`).style.display='block';
    });
  });

  window.addEventListener('popstate', e=> { if(e.state?.idx!=null) carregarVideo(e.state.idx); });

  atualizarProgresso();
  btnPrev.style.opacity       = activeIdx>0 ? '1'    : '0.35';
  btnPrev.style.pointerEvents = activeIdx>0 ? 'auto' : 'none';

  const initItem = allItems[activeIdx];
  if(initItem){
    renderMateriais(initItem.dataset.materiais, initItem.dataset.seasonLabel);
    renderPodcast(initItem.dataset.materiais, initItem.dataset.num, initItem.dataset.titulo);
    setTimeout(()=> initItem.scrollIntoView({block:'nearest',behavior:'smooth'}), 300);
  }

})();
</script>
@endpush