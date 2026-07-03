@extends('layouts.admin')
@section('title', 'Calendário — Instagram')
@section('section', 'Instagram')

@section('content')
@include('admin.social._field-styles')
<div class="page">

  @include('admin.social._nav')

  @if(session('success'))
    <div style="padding:12px 16px;background:rgba(43,217,161,0.10);border:1px solid rgba(43,217,161,0.35);border-radius:var(--r-md);color:#6FE6BD;font-size:13px;font-weight:500;margin-bottom:20px;">✓ {{ session('success') }}</div>
  @endif
  @if(session('warning'))
    <div style="padding:12px 16px;background:rgba(255,181,71,0.10);border:1px solid rgba(255,181,71,0.35);border-radius:var(--r-md);color:#FFB547;font-size:13px;margin-bottom:20px;">{{ session('warning') }}</div>
  @endif
  @if($errors->any())
    <div style="padding:12px 16px;background:rgba(255,92,122,0.10);border:1px solid rgba(255,92,122,0.35);border-radius:var(--r-md);color:#FF5C7A;font-size:13px;margin-bottom:20px;"><strong>Corrija:</strong><ul style="margin:8px 0 0;padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  {{-- Cabeçalho: mês + navegação --}}
  @php
    $meses = [1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'];
    $hoje = \Illuminate\Support\Carbon::now()->format('Y-m-d');
  @endphp
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;gap:12px;flex-wrap:wrap;">
    <div style="display:flex;align-items:center;gap:12px;">
      <a href="{{ route('admin.social.calendar', ['mes' => $prevMes]) }}" class="btn btn-ghost" style="padding:6px 12px;">‹</a>
      <h1 style="font-family:var(--font-display);font-weight:700;font-size:20px;color:#fff;margin:0;min-width:190px;text-align:center;">{{ $meses[$ref->month] }} {{ $ref->year }}</h1>
      <a href="{{ route('admin.social.calendar', ['mes' => $nextMes]) }}" class="btn btn-ghost" style="padding:6px 12px;">›</a>
    </div>
    <button type="button" onclick="abrirGerar('{{ $hoje }}')" class="btn btn-primary">✨ Gerar posts do dia</button>
  </div>

  {{-- Dias da semana --}}
  <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:6px;margin-bottom:6px;">
    @foreach(['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'] as $d)
      <div style="text-align:center;font-size:11px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--fg-4);padding:4px 0;">{{ $d }}</div>
    @endforeach
  </div>

  {{-- Grade --}}
  @php $dia = $gridStart->copy(); @endphp
  <div style="display:flex;flex-direction:column;gap:6px;">
    @while($dia <= $gridEnd)
      <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:6px;">
        @for($i = 0; $i < 7; $i++)
          @php
            $key = $dia->format('Y-m-d');
            $noMes = $dia->month === $ref->month;
            $ehHoje = $key === $hoje;
            $doDia = $posts->get($key);
          @endphp
          <div onclick="abrirGerar('{{ $key }}')"
               style="min-height:112px;background:{{ $noMes ? 'var(--bg-2)' : 'var(--bg-1)' }};border:1px solid {{ $ehHoje ? 'var(--brand-500)' : 'var(--line-1)' }};border-radius:var(--r-sm);padding:8px;cursor:pointer;transition:border-color .15s;opacity:{{ $noMes ? '1' : '.5' }};overflow:hidden;"
               onmouseover="this.style.borderColor='var(--brand-500)'"
               onmouseout="this.style.borderColor='{{ $ehHoje ? 'var(--brand-500)' : 'var(--line-1)' }}'">
            <div style="font-size:12px;font-weight:600;color:{{ $ehHoje ? 'var(--brand-500)' : 'var(--fg-3)' }};margin-bottom:6px;">{{ $dia->day }}</div>
            @if($doDia)
              @foreach($doDia->take(3) as $p)
                <a href="{{ route('admin.social.posts.edit', $p) }}" onclick="event.stopPropagation();"
                   style="display:flex;align-items:center;gap:5px;margin-bottom:3px;padding:2px 5px;border-radius:5px;background:{{ $p->statusColor() }}1a;text-decoration:none;">
                  <span style="width:6px;height:6px;border-radius:50%;background:{{ $p->statusColor() }};flex-shrink:0;"></span>
                  <span style="font-size:10px;color:var(--fg-2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $p->scheduled_for->format('H:i') }} {{ $p->type === 'story' ? 'Story' : 'Feed' }}</span>
                </a>
              @endforeach
              @if($doDia->count() > 3)
                <div style="font-size:10px;color:var(--fg-4);padding-left:5px;">+{{ $doDia->count() - 3 }} mais</div>
              @endif
            @endif
          </div>
          @php $dia->addDay(); @endphp
        @endfor
      </div>
    @endwhile
  </div>

  <p style="font-size:12px;color:var(--fg-4);margin-top:16px;">Clique em qualquer dia para gerar posts com IA para aquela data. As artes usam suas fotos reais e já ficam pré-agendadas.</p>

</div>

{{-- ===================== MODAL DE GERAÇÃO (BRIEFING) ===================== --}}
<div id="gerar-modal" style="display:none;position:fixed;inset:0;background:rgba(5,8,15,.72);z-index:1000;align-items:flex-start;justify-content:center;padding:36px 16px;overflow:auto;">
  <div class="card" style="width:660px;max-width:95vw;padding:26px;">
    <form action="{{ route('admin.social.generate') }}" method="POST">
      @csrf
      <input type="hidden" name="scheduled_date" id="gerar-date">

      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
        <h2 style="font-family:var(--font-display);font-weight:700;font-size:17px;color:#fff;margin:0;">Gerar posts com IA</h2>
        <button type="button" onclick="fecharGerar()" style="background:none;border:none;color:var(--fg-3);font-size:20px;cursor:pointer;line-height:1;">✕</button>
      </div>
      <p style="font-size:13px;color:var(--fg-3);margin:0 0 18px;">Para o dia <strong id="gerar-date-label" style="color:var(--brand-500);"></strong>. Preencha o briefing de cada peça — quanto mais direção, melhor a arte.</p>

      <div id="pecas-container" style="display:flex;flex-direction:column;gap:16px;margin-bottom:14px;"></div>

      <button type="button" onclick="addPeca()" class="btn btn-ghost" style="width:100%;justify-content:center;margin-bottom:20px;">+ Adicionar peça</button>

      <div style="display:flex;gap:10px;justify-content:flex-end;">
        <button type="button" onclick="fecharGerar()" class="btn btn-ghost">Cancelar</button>
        <button type="submit" class="btn btn-primary">✨ Gerar</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
  var pcaIdx = 0;
  var CATEGORIAS = @json($categorias ?? []);

  function lbl(t) { return '<label style="display:block;font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--fg-4);margin-bottom:4px;">' + t + '</label>'; }
  function opt(v, t, sel) { return '<option value="' + v + '"' + (sel ? ' selected' : '') + '>' + t + '</option>'; }

  function toggleCat(sel) {
    var grid = sel.parentElement.parentElement;
    var wrap = grid.querySelector('.cat-wrap');
    if (wrap) wrap.style.opacity = (sel.value === 'nao') ? '.4' : '1';
  }

  function pecaCard(idx) {
    var n = 'items[' + idx + ']';
    var fi = 'class="field-input" style="padding:8px 10px;font-size:13px;"';
    var catOpts = CATEGORIAS.map(function (c) { return opt(c, c); }).join('');
    var wrap = document.createElement('div');
    wrap.style.cssText = 'border:1px solid var(--line-2);border-radius:var(--r-sm);padding:14px;background:var(--bg-1);';
    wrap.innerHTML =
      '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">' +
        '<span style="font-size:11px;font-weight:700;color:var(--brand-500);text-transform:uppercase;letter-spacing:.06em;">Briefing da peça</span>' +
        '<button type="button" class="rm-peca" style="background:none;border:none;color:#FF5C7A;font-size:12px;cursor:pointer;">✕ remover</button>' +
      '</div>' +
      '<div style="display:grid;grid-template-columns:1fr 120px;gap:8px;margin-bottom:8px;">' +
        '<div>' + lbl('Formato') + '<select name="' + n + '[tipo]" ' + fi + '>' + opt('feed', 'Feed (1080x1350)') + opt('story', 'Story (1080x1920)') + '</select></div>' +
        '<div>' + lbl('Horário') + '<input type="time" name="' + n + '[horario]" ' + fi + ' value="09:00" required></div>' +
      '</div>' +
      '<div style="margin-bottom:8px;">' + lbl('Pedido — o que você quer nesta arte') + '<textarea name="' + n + '[pedido]" rows="2" ' + fi + ' placeholder="Ex: divulgar a nova minissérie de Lei 14.133, com foco em urgência" required></textarea></div>' +
      '<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:8px;">' +
        '<div>' + lbl('Objetivo') + '<select name="' + n + '[objetivo]" ' + fi + '>' + opt('Divulgar curso', 'Divulgar curso') + opt('Depoimento', 'Depoimento') + opt('Dica educativa', 'Dica educativa') + opt('Institucional', 'Institucional') + opt('Data comemorativa', 'Data comemorativa') + opt('Bastidores', 'Bastidores') + '</select></div>' +
        '<div>' + lbl('Estilo') + '<select name="' + n + '[estilo]" ' + fi + '>' + opt('auto', 'Automático') + opt('dark', 'Dark') + opt('light', 'Light') + '</select></div>' +
        '<div>' + lbl('Tom') + '<select name="' + n + '[tom]" ' + fi + '>' + opt('Institucional', 'Institucional') + opt('Motivacional', 'Motivacional') + opt('Urgente', 'Urgente') + '</select></div>' +
      '</div>' +
      '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px;">' +
        '<div>' + lbl('Usar foto real?') + '<select name="' + n + '[foto_real]" ' + fi + ' onchange="toggleCat(this)">' + opt('sim', 'Sim') + opt('nao', 'Não (só gráfico)') + '</select></div>' +
        '<div class="cat-wrap">' + lbl('Categoria da foto') + '<select name="' + n + '[categoria]" ' + fi + '>' + opt('', '(qualquer)') + catOpts + '</select></div>' +
      '</div>' +
      '<div style="margin-bottom:8px;">' + lbl('Elementos') + '<div style="display:flex;gap:16px;flex-wrap:wrap;font-size:13px;color:var(--fg-2);padding-top:2px;">' +
        '<label style="display:flex;align-items:center;gap:5px;cursor:pointer;"><input type="checkbox" name="' + n + '[elementos][]" value="logo">Logo</label>' +
        '<label style="display:flex;align-items:center;gap:5px;cursor:pointer;"><input type="checkbox" name="' + n + '[elementos][]" value="selo">Selo</label>' +
        '<label style="display:flex;align-items:center;gap:5px;cursor:pointer;"><input type="checkbox" name="' + n + '[elementos][]" value="cta" checked>CTA</label>' +
      '</div></div>' +
      '<div>' + lbl('Texto do CTA (opcional)') + '<input type="text" name="' + n + '[cta]" ' + fi + ' placeholder="Ex: Garanta sua vaga"></div>';
    wrap.querySelector('.rm-peca').addEventListener('click', function () { wrap.remove(); });
    return wrap;
  }

  function addPeca() {
    document.getElementById('pecas-container').appendChild(pecaCard(pcaIdx++));
  }

  function abrirGerar(date) {
    document.getElementById('gerar-date').value = date;
    var parts = date.split('-');
    document.getElementById('gerar-date-label').textContent = parts[2] + '/' + parts[1] + '/' + parts[0];
    var box = document.getElementById('pecas-container');
    box.innerHTML = '';
    pcaIdx = 0;
    addPeca();
    document.getElementById('gerar-modal').style.display = 'flex';
  }

  function fecharGerar() {
    document.getElementById('gerar-modal').style.display = 'none';
  }

  document.getElementById('gerar-modal').addEventListener('click', function (e) {
    if (e.target === this) fecharGerar();
  });
</script>
@endpush
@endsection
