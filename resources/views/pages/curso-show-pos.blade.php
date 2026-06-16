@extends('layouts.site')
@section('meta_title', 'Unyflex Digital')

@section('content')

<div style="padding-top:72px; min-height:100vh;">

  {{-- Header do curso --}}
  <div style="background:var(--bg-1);border-bottom:1px solid var(--line-1);padding:16px 0;">
    <div class="container">
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <a href="{{route('cursos')}}" class="btn-ux btn-ux-ghost btn-ux-sm">
          <i data-lucide="chevron-left" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;"></i>
          Cursos
        </a>
        <span style="color:var(--fg-4);">/</span>
        <span style="font-size:12px;font-weight:600;letter-spacing:0.12em;text-transform:uppercase;color:var(--brand-300);">{{ $classes->title }}</span>
        <span style="color:var(--fg-4);">·</span>
        <span id="header-progress-label" style="font-size:12px;color:var(--fg-3);">Cápsula 1</span>
        <div style="margin-left:auto;display:flex;align-items:center;gap:10px;">
          <div style="display:flex;align-items:center;gap:8px;">
            <div style="width:80px;height:4px;background:rgba(255,255,255,0.1);border-radius:2px;overflow:hidden;">
              <div id="header-progress-bar" style="height:100%;width:0%;background:var(--grad-brand);transition:width 0.4s;"></div>
            </div>
            <span id="header-progress-pct" style="font-family:var(--font-mono);font-size:11px;color:var(--brand-300);">0%</span>
          </div>
          <button
            class="btn-ux btn-ux-primary btn-ux-sm btn-add-to-cart"
            style="display:inline-flex;align-items:center;gap:8px;"
            data-course-id="{{ $classes->id }}"
            data-course-title="{{ $classes->title }}"
            data-course-price="{{ $classes->price ?? 0 }}"
            data-course-thumb="https://unyflex.com.br/storage/cursos/banner/{{ $classes->photo }}"
            data-course-slug="{{ $classes->slug }}"
            aria-label="Adicionar {{ $classes->title }} ao carrinho">
            <i data-lucide="award" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
            <span class="btn-cart-label">Garantir Acesso Completo</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- Layout player --}}
  <div id="player-layout" style="display:grid;grid-template-columns:1fr 360px;min-height:calc(100vh - 120px);">

    {{-- ═══════════════════════════════════
         COLUNA ESQUERDA
    ═══════════════════════════════════ --}}
    <div style="background:var(--bg-0);overflow-y:auto;padding:28px 32px 48px;">

      {{-- Player --}}
      <div style="position:relative;border-radius:var(--r-xl);overflow:hidden;background:#000;border:1px solid var(--line-2);box-shadow:var(--shadow-lg);aspect-ratio:16/9;margin-bottom:24px;">
        <iframe
          id="main-player"
          src="{{ $panel[0]->video_lesson[0]->link ?? '' }}"
          style="width:100%;height:100%;border:none;display:block;"
          allowfullscreen>
        </iframe>
      </div>

      {{-- Título e navegação --}}
      <div style="margin-bottom:24px;">
        <h2 id="current-title" style="font-family:var(--font-display);font-weight:800;font-size:clamp(20px,2.5vw,26px);color:#fff;letter-spacing:-0.02em;margin-bottom:16px;">
          1.1 {{ $panel[0]->video_lesson[0]->titulo ?? '' }}
        </h2>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
          <button id="btn-prev" class="btn-ux btn-ux-secondary" style="opacity:0.35;">
            <i data-lucide="chevron-left" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;"></i>
            Aula anterior
          </button>
          <button id="btn-next" class="btn-ux btn-ux-primary">
            Próxima aula
            <i data-lucide="chevron-right" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;"></i>
          </button>
        </div>
      </div>

      {{-- Abas --}}
      <div style="background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-lg);padding:6px;display:flex;gap:4px;margin-bottom:18px;">
        @foreach([['materiais','download','Materiais de Apoio'],['comentarios','message-square','Comentários']] as [$id,$ic,$lbl])
        <button class="player-tab-btn {{ $id==='materiais'?'active':'' }}"
                data-tab="{{ $id }}"
                style="flex:1;padding:10px 8px;border-radius:10px;background:{{ $id==='materiais'?'var(--bg-3)':'transparent' }};border:none;cursor:pointer;color:{{ $id==='materiais'?'#fff':'var(--fg-3)' }};font-size:12px;font-weight:600;font-family:inherit;display:inline-flex;align-items:center;justify-content:center;gap:6px;transition:all 0.2s;">
          <i data-lucide="{{ $ic }}" style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
          {{ $lbl }}
        </button>
        @endforeach
      </div>

      {{-- Painel: Materiais --}}
      <div class="player-tab-panel" data-panel="materiais" style="background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-lg);padding:24px 28px;">
        <h4 style="font-family:var(--font-display);font-weight:700;color:#fff;font-size:17px;margin-bottom:6px;">Materiais de Apoio</h4>
        <p id="materials-season-label" style="font-size:13px;color:var(--fg-3);margin-bottom:18px;"></p>
        <div id="materials-container" style="display:flex;flex-direction:column;gap:10px;">
          <div style="padding:24px;text-align:center;color:var(--fg-4);font-size:14px;">
            Selecione uma aula para ver os materiais disponíveis.
          </div>
        </div>
      </div>

      {{-- Painel: Comentários --}}
      <div class="player-tab-panel" data-panel="comentarios" style="display:none;background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-lg);padding:24px 28px;">
        <h4 style="font-family:var(--font-display);font-weight:700;color:#fff;font-size:18px;margin-bottom:18px;">Comentários</h4>
        <p style="color:var(--fg-3);font-size:14px;">Em breve você poderá comentar e tirar dúvidas por aqui.</p>
      </div>

    </div>

    {{-- ═══════════════════════════════════
         SIDEBAR
    ═══════════════════════════════════ --}}
    <div style="background:var(--bg-1);border-left:1px solid var(--line-1);display:flex;flex-direction:column;overflow:hidden;height:calc(100vh - 120px);position:sticky;top:120px;">

      {{-- Cabeçalho da sidebar --}}
      <div style="padding:18px 20px 14px;border-bottom:1px solid var(--line-1);flex-shrink:0;">
        <div style="font-size:10px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;color:var(--brand-300);margin-bottom:4px;">Minisérie</div>
        <h3 style="font-family:var(--font-display);font-weight:700;color:#fff;font-size:15px;line-height:1.3;margin-bottom:10px;">{{ $classes->title }}</h3>
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="flex:1;height:4px;background:rgba(255,255,255,0.07);border-radius:2px;overflow:hidden;">
            <div id="sidebar-progress-bar" style="height:100%;width:0%;background:var(--grad-brand);transition:width 0.4s;"></div>
          </div>
          @php
            $totalVideos = 0;
            foreach ($panel as $s) { $totalVideos += count($s->video_lesson); }
            $totalSeasons = count($panel);
          @endphp
          <span id="sidebar-progress-label" style="font-family:var(--font-mono);font-size:11px;color:var(--brand-300);">0 / {{ $totalVideos }}</span>
        </div>
      </div>

      {{-- Lista de temporadas --}}
      <div id="course-modules" style="overflow-y:auto;flex:1;padding:8px 0;">

        @foreach ($panel as $season)
        <div class="sidebar-season">

          <button class="sidebar-season-btn"
                  data-season="{{ $loop->index }}"
                  style="width:100%;text-align:left;background:transparent;border:none;padding:12px 20px 10px;cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:8px;border-top:1px solid var(--line-1);">
            <div>
              <div style="font-size:10px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--brand-400);margin-bottom:3px;">
                Temporada {{ $loop->iteration }}
              </div>
              <div style="font-size:13px;font-weight:600;color:var(--fg-2);line-height:1.3;">{{ $season->title }}</div>
              <div style="font-size:11px;color:var(--fg-4);margin-top:2px;">
                {{ count($season->video_lesson) }} episódios
                @if(count($season->material) > 0)· {{ count($season->material) }} materiais@endif
              </div>
            </div>
            <i data-lucide="chevron-down"
               class="season-chevron {{ $loop->first ? 'open' : '' }}"
               style="width:16px;height:16px;stroke:var(--fg-4);fill:none;stroke-width:2;flex-shrink:0;transition:transform 0.25s;{{ $loop->first ? 'transform:rotate(180deg);' : '' }}"></i>
          </button>

          <div class="sidebar-season-content"
               data-season="{{ $loop->index }}"
               style="{{ $loop->first ? '' : 'display:none;' }}">

            @foreach ($season->video_lesson as $video)
            @php
              $isFirst = $loop->parent->first && $loop->first;
            @endphp
            <div class="video-item {{ $isFirst ? 'active' : '' }}"
                 data-url="{{ $video->link }}"
                 data-title="{{ $loop->parent->iteration }}.{{ $loop->iteration }} {{ $video->titulo }}"
                 data-num="{{ $loop->parent->iteration }}.{{ $loop->iteration }}"
                 data-season-index="{{ $loop->parent->index }}"
                 data-season-label="Temporada {{ $loop->parent->iteration }}: {{ $season->title }}"
                 style="{{ $isFirst ? 'background:linear-gradient(90deg,rgba(0,163,255,0.12),transparent);border-left:2px solid var(--brand-400);' : '' }}padding:10px 14px 10px 20px;cursor:pointer;display:flex;align-items:flex-start;gap:10px;border-bottom:1px solid var(--line-1);transition:background 0.2s;">

              <div class="lesson-num"
                   style="width:26px;height:26px;border-radius:50%;border:1.5px solid {{ $isFirst ? 'var(--brand-400)' : 'var(--line-2)' }};background:{{ $isFirst ? 'rgba(0,163,255,0.15)' : 'transparent' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                @if($isFirst)
                  <svg viewBox="0 0 24 24" style="width:9px;height:9px;fill:var(--brand-300);"><polygon points="6 4 20 12 6 20 6 4"/></svg>
                @else
                  <span style="font-family:var(--font-mono);font-size:9px;color:var(--fg-4);">{{ $loop->parent->iteration }}.{{ $loop->iteration }}</span>
                @endif
              </div>

              <div style="flex:1;min-width:0;">
                <div class="lesson-title" style="font-size:13px;color:{{ $isFirst ? '#fff' : 'var(--fg-2)' }};font-weight:{{ $isFirst ? '600' : '400' }};line-height:1.4;">
                  {{ $loop->parent->iteration }}.{{ $loop->iteration }} {{ $video->titulo }}
                </div>
              </div>
            </div>
            @endforeach

          </div>
        </div>
        @endif
        @endforeach

      </div>
    </div>

  </div>
</div>

{{-- Dados ocultos de materiais por temporada --}}
<div id="materials-data" style="display:none;">
  @foreach ($panel as $season)
  <div data-season-index="{{ $loop->index }}">
    @foreach ($season->material as $material)
    <a href="https://unygov.com.br/storage/materials/{{ $material->file_name }}"
       target="_blank"
       data-type="{{ $material->type }}"
       data-name="{{ $material->name }}"
       data-index="{{ $loop->index }}"></a>
    @endforeach
  </div>
  @endforeach
</div>

@push('styles')
<style>
.player-tab-btn.active {
  background: var(--bg-3) !important;
  color: #fff !important;
  box-shadow: inset 0 0 0 1px rgba(0,163,255,0.3), 0 0 18px -8px rgba(0,163,255,0.5);
}
.video-item:hover { background: rgba(255,255,255,0.04); }
.video-item.active {
  background: linear-gradient(90deg,rgba(0,163,255,0.12),transparent) !important;
  border-left: 2px solid var(--brand-400) !important;
}
.sidebar-season-btn:hover { background: rgba(255,255,255,0.03); }
.season-chevron.open { transform: rotate(180deg) !important; }
@media(max-width:991px) {
  #player-layout { display: block !important; }
  #player-layout > div:last-child { height:auto !important; position:static !important; max-height:60vh; }
}
</style>
@endpush

@push('scripts')
<script>
(function () {

  const mainPlayer           = document.getElementById('main-player');
  const currentTitle         = document.getElementById('current-title');
  const btnPrev              = document.getElementById('btn-prev');
  const btnNext              = document.getElementById('btn-next');
  const headerBar            = document.getElementById('header-progress-bar');
  const headerPct            = document.getElementById('header-progress-pct');
  const headerLabel          = document.getElementById('header-progress-label');
  const sidebarBar           = document.getElementById('sidebar-progress-bar');
  const sidebarLabel         = document.getElementById('sidebar-progress-label');
  const materialsContainer   = document.getElementById('materials-container');
  const materialsSeasonLabel = document.getElementById('materials-season-label');
  const materialsData        = document.getElementById('materials-data');

  const videoItems  = Array.from(document.querySelectorAll('.video-item'));
  const totalVideos = videoItems.length;
  let currentIndex  = 0;

  // ── Abas ─────────────────────────────────────────────────────
  document.querySelectorAll('.player-tab-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.player-tab-btn').forEach(b => {
        b.classList.remove('active');
        b.style.background = 'transparent';
        b.style.color = 'var(--fg-3)';
      });
      document.querySelectorAll('.player-tab-panel').forEach(p => p.style.display = 'none');
      this.classList.add('active');
      this.style.background = 'var(--bg-3)';
      this.style.color = '#fff';
      document.querySelector(`.player-tab-panel[data-panel="${this.dataset.tab}"]`).style.display = 'block';
    });
  });

  // ── Accordion temporadas ─────────────────────────────────────
  document.querySelectorAll('.sidebar-season-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const idx     = this.dataset.season;
      const content = document.querySelector(`.sidebar-season-content[data-season="${idx}"]`);
      const chevron = this.querySelector('.season-chevron');
      if (!content) return;
      const isOpen = content.style.display !== 'none';
      content.style.display = isOpen ? 'none' : 'block';
      chevron.classList.toggle('open', !isOpen);
    });
  });

  // ── Materiais ─────────────────────────────────────────────────
  function loadMaterials(seasonIndex, seasonLabel) {
    const seasonData = materialsData.querySelector(`[data-season-index="${seasonIndex}"]`);
    const links      = seasonData ? Array.from(seasonData.querySelectorAll('a')) : [];
    materialsSeasonLabel.textContent = seasonLabel;

    if (links.length === 0) {
      materialsContainer.innerHTML = `
        <div style="padding:24px;text-align:center;color:var(--fg-4);font-size:14px;border:1px dashed var(--line-2);border-radius:10px;">
          Nenhum material disponível para esta temporada.
        </div>`;
      return;
    }

    const icons  = { PDF: '📄', PODCAST: '🎧' };
    const labels = { PDF: 'Mapa Mental / PDF', PODCAST: 'Áudiocast' };

    materialsContainer.innerHTML = links.map(a => {
      const type  = a.dataset.type  || 'OUTRO';
      const name  = a.dataset.name  || 'Material';
      const icon  = icons[type]     || '📁';
      const label = labels[type]    || 'Material de Apoio';

      return `
        <a href="${a.href}" target="_blank"
           style="display:flex;align-items:center;gap:14px;padding:14px 16px;background:var(--bg-1);border:1px solid var(--line-1);border-radius:10px;text-decoration:none;color:inherit;transition:border-color 0.2s;"
           onmouseover="this.style.borderColor='rgba(0,163,255,0.35)'"
           onmouseout="this.style.borderColor='var(--line-1)'">
          <div style="width:40px;height:40px;border-radius:10px;background:var(--bg-3);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">${icon}</div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--brand-300);margin-bottom:2px;">${label}</div>
            <div style="font-size:14px;color:var(--fg-2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${name}</div>
          </div>
          <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:var(--fg-4);fill:none;stroke-width:2;flex-shrink:0;">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="7 10 12 15 17 10"/>
            <line x1="12" y1="15" x2="12" y2="3"/>
          </svg>
        </a>`;
    }).join('');
  }

  // ── Carregar vídeo ────────────────────────────────────────────
  function carregarVideo(index) {
    if (index < 0 || index >= totalVideos) return;

    const item   = videoItems[index];
    currentIndex = index;

    // Reset todos
    videoItems.forEach(v => {
      v.classList.remove('active');
      const num = v.querySelector('.lesson-num');
      if (num) {
        num.style.background  = 'transparent';
        num.style.borderColor = 'var(--line-2)';
        num.innerHTML = `<span style="font-family:var(--font-mono);font-size:9px;color:var(--fg-4);">${v.dataset.num}</span>`;
      }
      const t = v.querySelector('.lesson-title');
      if (t) { t.style.color = 'var(--fg-2)'; t.style.fontWeight = '400'; }
    });

    // Ativar atual
    item.classList.add('active');
    const num = item.querySelector('.lesson-num');
    if (num) {
      num.style.background  = 'rgba(0,163,255,0.15)';
      num.style.borderColor = 'var(--brand-400)';
      num.innerHTML = `<svg viewBox="0 0 24 24" style="width:9px;height:9px;fill:var(--brand-300);"><polygon points="6 4 20 12 6 20 6 4"/></svg>`;
    }
    const t = item.querySelector('.lesson-title');
    if (t) { t.style.color = '#fff'; t.style.fontWeight = '600'; }

    // Abrir temporada se fechada
    const sIdx    = item.dataset.seasonIndex;
    const content = document.querySelector(`.sidebar-season-content[data-season="${sIdx}"]`);
    const chevron = document.querySelector(`.sidebar-season-btn[data-season="${sIdx}"] .season-chevron`);
    if (content && content.style.display === 'none') {
      content.style.display = 'block';
      if (chevron) chevron.classList.add('open');
    }

    item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });

    mainPlayer.src           = item.dataset.url;
    currentTitle.textContent = item.dataset.title;

    // Progresso sobre todas as aulas
    const pct = Math.round(((index + 1) / totalVideos) * 100);
    headerBar.style.width    = pct + '%';
    headerPct.textContent    = pct + '%';
    headerLabel.textContent  = `Cápsula ${index + 1} de ${totalVideos}`;
    sidebarBar.style.width   = pct + '%';
    sidebarLabel.textContent = `${index + 1} / ${totalVideos}`;

    // Botões prev/next
    btnPrev.style.opacity = index > 0 ? '1' : '0.35';
    btnNext.style.opacity = index < totalVideos - 1 ? '1' : '0.35';
    btnNext.style.pointerEvents = index < totalVideos - 1 ? 'auto' : 'none';

    loadMaterials(item.dataset.seasonIndex, item.dataset.seasonLabel);
  }

  btnPrev.addEventListener('click', () => carregarVideo(currentIndex - 1));
  btnNext.addEventListener('click', () => carregarVideo(currentIndex + 1));

  videoItems.forEach((item, idx) => item.addEventListener('click', () => carregarVideo(idx)));

  carregarVideo(0);
})();
</script>
<script>
  document.addEventListener('DOMContentLoaded', function () {

    const cart = UnyCart.getCart();

    document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
      const id = String(btn.dataset.courseId);
      if (cart.find(i => String(i.id) === id)) setInCart(btn);
    });

    document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        const item = {
          id:    this.dataset.courseId,
          title: this.dataset.courseTitle,
          price: parseFloat(this.dataset.coursePrice) || 0,
          thumb: this.dataset.courseThumb,
          slug:  this.dataset.courseSlug,
        };
        const result = UnyCart.addItem(item);
        if (result.added) setInCart(this);
        window.location.href = '/checkout';
      });
    });

    function setInCart(btn) {
      btn.classList.add('in-cart');
      const label = btn.querySelector('.btn-cart-label');
      if (label) label.textContent = 'No carrinho';
      btn.setAttribute('aria-label', btn.dataset.courseTitle + ' — já no carrinho');
    }

    document.addEventListener('cart:updated', function (e) {
      const ids = e.detail.cart.map(i => String(i.id));
      document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
        if (!ids.includes(String(btn.dataset.courseId))) {
          btn.classList.remove('in-cart');
          const label = btn.querySelector('.btn-cart-label');
          if (label) label.textContent = 'Adicionar';
        }
      });
    });

  });
</script>
@endpush

@endsection