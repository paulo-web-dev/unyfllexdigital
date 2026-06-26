@extends('layouts.app')
@section('title', $curso->title . ' — Unyflex Digital')

@section('content')
<style>
  .ms-sec-title { font-family:var(--font-display);font-weight:700;font-size:18px;color:#fff;margin:28px 0 14px; }
  .ms-card { background:var(--bg-2);border:1px solid var(--line-1);border-radius:var(--r-lg);padding:18px 20px; }
  .ms-row { display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--bg-1);border:1px solid var(--line-2);border-radius:var(--r-md); }
  .flashcard { cursor:pointer;min-height:130px;display:flex;align-items:center;justify-content:center;text-align:center;padding:20px;border-radius:var(--r-sm);border:1px solid var(--line-2);background:var(--bg-1);user-select:none; }
  .fc-front { font-size:15px;font-weight:600;color:#fff;width:100%; }
  .fc-back  { font-size:14px;color:var(--fg-2);line-height:1.55;width:100%; }
  .qz-q { padding:14px 16px;background:var(--bg-1);border:1px solid var(--line-2);border-radius:var(--r-md);margin-bottom:14px; }
  .qz-alt { text-align:left;padding:10px 12px;border:1px solid var(--line-2);border-radius:var(--r-sm);background:var(--bg-2);color:var(--fg-2);font-size:13px;cursor:pointer;line-height:1.45;width:100%;display:block;margin-bottom:8px; }
  .badge-pill { display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600;background:rgba(43,217,161,0.12);border:1px solid rgba(43,217,161,0.35);color:#6FE6BD; }
</style>

<div class="scroll">

    {{-- Cabeçalho --}}
    <div class="page-head">
        <div>
            <a href="{{ route('ava.modulares') }}" style="color:var(--brand-300);font-size:12px;text-decoration:none;">‹ Cursos modulares</a>
            <h1 style="margin-top:6px;">{{ $curso->title }}</h1>
            <p>Estude pelos resumos, revise com os cartões e teste-se no simulado.</p>
        </div>
    </div>

    @if($resumos->isEmpty() && $cartoes->isEmpty() && !count($questions) && $audios->isEmpty() && !$video && empty($curso->apostila_path))
        <div class="ms-card">
            <p style="color:var(--fg-4);font-size:14px;margin:0;">Os materiais deste curso ainda estão sendo preparados. Volte em breve.</p>
        </div>
    @endif

    {{-- ───────── Apostila ───────── --}}
    @if(!empty($curso->apostila_path))
        <h2 class="ms-sec-title">Apostila</h2>
        <div class="ms-card">
            <div class="ms-row">
                <span style="font-size:18px;line-height:1;">📕</span>
                <span style="font-size:13px;font-weight:600;color:var(--fg-1);flex:1;">{{ $curso->apostila_original_name ?: 'Apostila do curso' }}</span>
                <a href="{{ $curso->apostilaUrl() }}" target="_blank" rel="noopener" class="btn btn-sm" style="font-size:11px;text-decoration:none;">⤓ Abrir apostila</a>
            </div>
        </div>
    @endif

    {{-- ───────── Vídeo de resumo ───────── --}}
    @if($video)
        <h2 class="ms-sec-title">Vídeo de resumo</h2>
        <div class="ms-card">
            <video controls preload="metadata" src="{{ $video->video_url }}" style="width:100%;border-radius:var(--r-md);border:1px solid var(--line-2);display:block;background:#000;"></video>
            <p style="font-size:11px;color:var(--fg-4);margin:10px 0 0;">Resumo em slides narrados — uma visão geral rápida do curso.</p>
        </div>
    @endif

    {{-- ───────── Resumos ───────── --}}
    @if($resumos->isNotEmpty())
        <h2 class="ms-sec-title">Resumos de estudo</h2>
        <div class="ms-card">
            <div style="display:flex;flex-direction:column;gap:10px;">
                @foreach($resumos as $r)
                    <div class="ms-row">
                        <span style="font-size:18px;line-height:1;">📄</span>
                        <span style="font-size:13px;font-weight:600;color:var(--fg-1);flex:1;">{{ $r->title }}</span>
                        <a href="{{ $r->pdfUrl() }}" target="_blank" rel="noopener" class="btn btn-sm" style="font-size:11px;text-decoration:none;">⤓ Abrir PDF</a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ───────── Podcast ───────── --}}
    @if($audios->isNotEmpty())
        <h2 class="ms-sec-title">Podcast</h2>
        <div class="ms-card">
            <div style="display:flex;flex-direction:column;gap:16px;">
                @foreach($audios as $au)
                    <div>
                        <div style="font-size:13px;font-weight:600;color:var(--fg-1);margin-bottom:6px;">{{ $au->title ?: ('Parte ' . $au->part) }}</div>
                        <audio controls preload="none" src="{{ $au->audioUrl() }}" style="width:100%;"></audio>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ───────── Cartões ───────── --}}
    @if($cartoes->isNotEmpty())
        <h2 class="ms-sec-title">Cartões de revisão</h2>
        <div class="ms-card">
            <div style="display:flex;flex-direction:column;gap:18px;">
                @foreach($cartoes as $deck)
                    @php $cards = $deck->cards(); @endphp
                    <div class="cm-deck" data-cards='@json($cards)' style="background:var(--bg-1);border:1px solid var(--line-2);border-radius:var(--r-md);padding:16px;">
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                            <span style="font-size:18px;line-height:1;">🃏</span>
                            <span style="font-size:13px;font-weight:600;color:var(--fg-1);flex:1;">{{ $deck->title }}</span>
                            <span style="font-size:11px;color:var(--fg-4);">{{ count($cards) }} cartões</span>
                        </div>
                        <div class="flashcard">
                            <div class="fc-front"></div>
                            <div class="fc-back" style="display:none;"></div>
                        </div>
                        <div style="display:flex;align-items:center;gap:12px;margin-top:10px;justify-content:center;">
                            <button type="button" class="fc-prev btn btn-sm" style="font-size:12px;">‹ Anterior</button>
                            <span class="fc-counter" style="font-size:12px;color:var(--fg-4);min-width:56px;text-align:center;"></span>
                            <button type="button" class="fc-next btn btn-sm" style="font-size:12px;">Próximo ›</button>
                        </div>
                        <p style="font-size:11px;color:var(--fg-4);text-align:center;margin:8px 0 0;">Clique no cartão para virar (pergunta ⇄ resposta)</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ───────── Prova ───────── --}}
    @if(count($questions))
        <h2 class="ms-sec-title">Simulado</h2>
        <div class="ms-card">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;flex-wrap:wrap;">
                <span style="font-size:11px;color:var(--fg-4);">{{ count($questions) }} {{ count($questions) === 1 ? 'questão' : 'questões' }}</span>
                @if($tentativas->isNotEmpty())
                    <span class="badge-pill">Melhor nota: {{ $melhor }} / {{ count($questions) }}</span>
                    <span style="font-size:11px;color:var(--fg-4);">{{ $tentativas->count() }} {{ $tentativas->count() === 1 ? 'tentativa' : 'tentativas' }}</span>
                @endif
            </div>

            <div class="cm-prova" data-total="{{ count($questions) }}"
                 data-url="{{ route('ava.modulares.prova', $curso->id) }}" data-token="{{ csrf_token() }}">
                @foreach($questions as $qi => $q)
                    <div class="qz-q" data-correct="{{ (int) ($q['correta'] ?? 0) }}">
                        <div style="font-size:14px;font-weight:600;color:#fff;margin-bottom:10px;line-height:1.5;">{{ $qi + 1 }}. {{ $q['enunciado'] ?? '' }}</div>
                        @foreach(($q['alternativas'] ?? []) as $ai => $alt)
                            <button type="button" class="qz-alt" data-i="{{ $ai }}">
                                <strong style="color:var(--brand-300);">{{ chr(65 + $ai) }})</strong> {{ $alt }}
                            </button>
                        @endforeach
                        <div class="qz-coment" style="display:none;margin-top:10px;padding:10px 12px;background:rgba(0,163,255,0.06);border-left:3px solid var(--brand-500);border-radius:4px;font-size:12.5px;color:var(--fg-3);line-height:1.5;">
                            {!! nl2br(e($q['comentario'] ?? '')) !!}
                        </div>
                    </div>
                @endforeach
                <div style="display:flex;align-items:center;gap:12px;margin-top:6px;flex-wrap:wrap;">
                    <button type="button" class="qz-corrigir btn btn-primary" style="font-size:12px;">Finalizar prova</button>
                    <button type="button" class="qz-refazer btn btn-sm" style="font-size:12px;color:var(--fg-4);display:none;">Refazer</button>
                    <span class="qz-score" style="font-size:14px;font-weight:700;color:var(--fg-1);"></span>
                    <span class="qz-saved" style="font-size:12px;color:#6FE6BD;display:none;"></span>
                </div>
            </div>
        </div>
    @endif

</div>

<script>
(function(){
  function cmInitDecks(){
    document.querySelectorAll('.cm-deck').forEach(function(deck){
      if(deck.__init) return; deck.__init=true;
      var cards=[]; try{ cards=JSON.parse(deck.getAttribute('data-cards')||'[]'); }catch(e){ cards=[]; }
      if(!cards.length) return;
      var i=0, back=false;
      var elFront=deck.querySelector('.fc-front'), elBack=deck.querySelector('.fc-back');
      var elCount=deck.querySelector('.fc-counter'), card=deck.querySelector('.flashcard');
      function render(){
        elFront.textContent=cards[i].front||''; elBack.textContent=cards[i].back||'';
        elFront.style.display=back?'none':'block'; elBack.style.display=back?'block':'none';
        elCount.textContent=(i+1)+' / '+cards.length;
      }
      card.addEventListener('click', function(){ back=!back; render(); });
      deck.querySelector('.fc-prev').addEventListener('click', function(e){ e.stopPropagation(); i=(i-1+cards.length)%cards.length; back=false; render(); });
      deck.querySelector('.fc-next').addEventListener('click', function(e){ e.stopPropagation(); i=(i+1)%cards.length; back=false; render(); });
      render();
    });
  }
  function cmInitQuiz(){
    document.querySelectorAll('.cm-prova').forEach(function(quiz){
      if(quiz.__init) return; quiz.__init=true;
      var qs=[].slice.call(quiz.querySelectorAll('.qz-q'));
      var btnC=quiz.querySelector('.qz-corrigir'), btnR=quiz.querySelector('.qz-refazer');
      var score=quiz.querySelector('.qz-score'), saved=quiz.querySelector('.qz-saved');
      var url=quiz.getAttribute('data-url'), token=quiz.getAttribute('data-token');
      function resetAlt(a){ a.classList.remove('sel'); a.style.borderColor='var(--line-2)'; a.style.background='var(--bg-2)'; }
      qs.forEach(function(q){
        q.querySelectorAll('.qz-alt').forEach(function(alt){
          alt.addEventListener('click', function(){
            if(quiz.__done) return;
            q.querySelectorAll('.qz-alt').forEach(resetAlt);
            alt.classList.add('sel'); alt.style.borderColor='var(--brand-500)'; alt.style.background='rgba(0,163,255,0.10)';
          });
        });
      });
      btnC.addEventListener('click', function(){
        if(quiz.__done) return; quiz.__done=true;
        var acertos=0, answers=[];
        qs.forEach(function(q){
          var correct=parseInt(q.getAttribute('data-correct'),10);
          var sel=q.querySelector('.qz-alt.sel');
          var selIdx = sel ? parseInt(sel.getAttribute('data-i'),10) : -1;
          answers.push(selIdx);
          q.querySelectorAll('.qz-alt').forEach(function(a){
            var i=parseInt(a.getAttribute('data-i'),10);
            if(i===correct){ a.style.borderColor='#2bd9a1'; a.style.background='rgba(43,217,161,0.14)'; }
            else if(sel&&a===sel){ a.style.borderColor='#ff6b6b'; a.style.background='rgba(255,107,107,0.12)'; }
          });
          if(selIdx===correct) acertos++;
          var com=q.querySelector('.qz-coment'); if(com) com.style.display='block';
        });
        score.textContent='Acertos: '+acertos+' / '+qs.length;
        btnC.style.display='none'; btnR.style.display='inline-flex';
        if(url){
          fetch(url, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':token,'Accept':'application/json'}, body:JSON.stringify({score:acertos,total:qs.length,answers:answers}) })
            .then(function(r){ return r.json(); })
            .then(function(){ if(saved){ saved.textContent='✓ Nota registrada'; saved.style.display='inline'; } })
            .catch(function(){ if(saved){ saved.textContent='(não consegui registrar a nota)'; saved.style.display='inline'; } });
        }
      });
      btnR.addEventListener('click', function(){
        quiz.__done=false;
        qs.forEach(function(q){ q.querySelectorAll('.qz-alt').forEach(resetAlt); var com=q.querySelector('.qz-coment'); if(com) com.style.display='none'; });
        score.textContent=''; if(saved) saved.style.display='none';
        btnR.style.display='none'; btnC.style.display='inline-flex';
      });
    });
  }
  cmInitDecks(); cmInitQuiz();
})();
</script>
@endsection
