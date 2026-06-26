@extends('layouts.admin')
@section('title', $curso->title)
@section('section', 'Cursos Modulares')

@section('content')
<div class="page">

  <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:12px;color:var(--fg-4);">
    <a href="{{ route('admin.cursos-modulares') }}" style="color:var(--fg-4);text-decoration:none;" onmouseover="this.style.color='var(--brand-300)'" onmouseout="this.style.color='var(--fg-4)'">Cursos Modulares</a>
    <span>/</span>
    <span style="color:var(--fg-2);">{{ $curso->title }}</span>
  </div>

  @if(session('success'))
    <div style="padding:12px 16px;background:rgba(43,217,161,0.10);border:1px solid rgba(43,217,161,0.35);border-radius:var(--r-md);color:#6FE6BD;font-size:13px;font-weight:500;margin-bottom:16px;">✓ {{ session('success') }}</div>
  @endif
  @if(session('warning'))
    <div style="padding:12px 16px;background:rgba(232,183,101,0.10);border:1px solid rgba(232,183,101,0.35);border-radius:var(--r-md);color:var(--gold-400);font-size:13px;font-weight:500;margin-bottom:16px;">⚠ {{ session('warning') }}</div>
  @endif
  @if($errors->any())
    <div style="padding:12px 16px;background:rgba(255,90,90,0.08);border:1px solid rgba(255,90,90,0.30);border-radius:var(--r-md);color:#ff9a9a;font-size:13px;margin-bottom:16px;">{{ $errors->first() }}</div>
  @endif

  @php
    $cb = [
      'rascunho'    => ['bg'=>'rgba(255,255,255,0.06)','bd'=>'var(--line-2)','fg'=>'var(--fg-3)'],
      'processando' => ['bg'=>'rgba(232,183,101,0.12)','bd'=>'rgba(232,183,101,0.35)','fg'=>'var(--gold-400)'],
      'publicado'   => ['bg'=>'rgba(43,217,161,0.12)','bd'=>'rgba(43,217,161,0.35)','fg'=>'#6FE6BD'],
    ][$curso->status] ?? ['bg'=>'rgba(255,255,255,0.06)','bd'=>'var(--line-2)','fg'=>'var(--fg-3)'];
    $sb = [
      'gerando'            => ['bg'=>'rgba(0,163,255,0.12)','bd'=>'rgba(0,163,255,0.30)','fg'=>'var(--brand-300)'],
      'aguardando_revisao' => ['bg'=>'rgba(232,183,101,0.12)','bd'=>'rgba(232,183,101,0.35)','fg'=>'var(--gold-400)'],
      'aprovado'           => ['bg'=>'rgba(43,217,161,0.12)','bd'=>'rgba(43,217,161,0.35)','fg'=>'#6FE6BD'],
      'reprovado'          => ['bg'=>'rgba(255,90,90,0.10)','bd'=>'rgba(255,90,90,0.30)','fg'=>'#ff9a9a'],
    ];
  @endphp

  <div class="page-header">
    <div>
      <h1 class="page-title">{{ $curso->title }}</h1>
      <p class="page-subtitle">
        <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600;background:{{ $cb['bg'] }};border:1px solid {{ $cb['bd'] }};color:{{ $cb['fg'] }};">{{ $curso->statusLabel() }}</span>
        <span style="margin-left:8px;color:var(--fg-4);">criado em {{ optional($curso->created_at)->format('d/m/Y H:i') ?? '—' }}</span>
      </p>
    </div>
    <div class="page-actions">
      @if($curso->hasApostila())
        <a href="{{ $curso->apostilaUrl() }}" target="_blank" class="btn" style="text-decoration:none;display:inline-flex;">Baixar apostila</a>
      @endif
      <form action="{{ route('admin.cursos-modulares.gerar-tudo', $curso->id) }}" method="POST" style="display:inline;"
            onsubmit="return confirm('Gerar TUDO? (Media kit a partir do título/descrição, e os roteiros a partir da apostila.)');">
        @csrf
        <button type="submit" class="btn btn-primary" style="display:inline-flex;">
          <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
          Gerar tudo
        </button>
      </form>
      <a href="{{ route('admin.cursos-modulares') }}" class="btn btn-ghost" style="text-decoration:none;display:inline-flex;">Voltar</a>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;margin-bottom:28px;">
    <div class="card" style="padding:0;">
      <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
        <h2 style="font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;margin:0;">Apostila (origem dos roteiros)</h2>
      </div>
      <div style="padding:20px;">
        @if($curso->hasApostila())
          <div style="display:flex;align-items:center;gap:14px;">
            <div style="width:48px;height:48px;border-radius:12px;background:rgba(0,163,255,0.10);border:1px solid rgba(0,163,255,0.20);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <svg viewBox="0 0 24 24" fill="none" stroke="var(--brand-300)" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" style="width:22px;height:22px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div style="min-width:0;">
              <div style="font-size:14px;font-weight:600;color:var(--fg-1);word-break:break-all;">{{ $curso->apostila_original_name }}</div>
              <div style="font-size:12px;color:var(--fg-4);margin-top:2px;">{{ $curso->sizeHuman() }} · PDF público</div>
            </div>
          </div>
        @else
          <p style="color:var(--fg-4);font-size:13px;margin:0;">Nenhuma apostila enviada. Sem apostila não dá pra gerar os roteiros (o media kit usa só o título e a descrição).</p>
        @endif
        @if($curso->description)
          <div style="margin-top:20px;padding-top:20px;border-top:1px solid var(--line-1);">
            <div style="font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--fg-4);margin-bottom:6px;">Descrição</div>
            <p style="font-size:13px;color:var(--fg-2);margin:0;line-height:1.6;">{{ $curso->description }}</p>
          </div>
        @endif
      </div>
    </div>

    <div class="card" style="padding:16px 18px;">
      <div style="font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--fg-4);margin-bottom:8px;">Como funciona</div>
      <p style="font-size:12px;color:var(--fg-3);margin:0;line-height:1.6;">
        <strong style="color:var(--fg-2);">Gerar tudo</strong> dispara o media kit (card + story, do título/descrição) e os roteiros (da apostila). Revise cada peça: <strong style="color:var(--fg-2);">Aprovar</strong> fixa, <strong style="color:var(--fg-2);">Reprovar</strong> manda refazer com seu comentário.
      </p>
    </div>
  </div>

  {{-- ══════════════════ MEDIA KIT ══════════════════ --}}
  <div style="display:flex;align-items:center;gap:12px;margin:0 0 14px;">
    <h2 style="font-family:var(--font-display);font-weight:700;font-size:18px;color:#fff;margin:0;flex:1;">Media Kit</h2>
    <form action="{{ route('admin.cursos-modulares.media.gerar', $curso->id) }}" method="POST" style="display:inline;"
          onsubmit="return confirm('Gerar (ou regenerar) o card e o story a partir do título e descrição?');">
      @csrf
      <button type="submit" class="btn btn-sm" style="display:inline-flex;font-size:12px;">Gerar media kit</button>
    </form>
  </div>

  @php $mPorTipo = $midia->keyBy('type'); @endphp
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:28px;">
    @foreach(['card'=>'Card (feed)','story'=>'Story (vertical)'] as $key => $label)
      @php $m = $mPorTipo[$key] ?? null; $mst = $m ? ($sb[$m->status] ?? $sb['aguardando_revisao']) : null; @endphp
      <div class="card" style="padding:0;">
        <div style="padding:14px 18px;border-bottom:1px solid var(--line-2);display:flex;align-items:center;gap:10px;">
          <h3 style="font-family:var(--font-display);font-weight:700;font-size:14px;color:var(--fg-1);margin:0;flex:1;">{{ $label }}</h3>
          @if($m)
            <span style="font-size:11px;color:var(--fg-4);">v{{ $m->version }}</span>
            <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600;background:{{ $mst['bg'] }};border:1px solid {{ $mst['bd'] }};color:{{ $mst['fg'] }};">{{ $m->statusLabel() }}</span>
          @else
            <span style="font-size:11px;color:var(--fg-4);">não gerado</span>
          @endif
        </div>

        <div style="padding:18px;">
          @if(!$m || (!$m->hasImage() && $m->status !== 'gerando'))
            <p style="color:var(--fg-4);font-size:13px;margin:0;">Ainda não gerado. Use “Gerar media kit”.</p>
          @elseif($m->status === 'gerando')
            <p style="color:var(--brand-300);font-size:13px;margin:0;">Gerando a arte… atualize a página em instantes.</p>
            @if($m->feedback)<div style="margin-top:10px;font-size:12px;color:var(--fg-4);">Refazendo com seu feedback: “{{ $m->feedback }}”</div>@endif
          @else
            @if($m->hasImage())
              <img src="{{ $m->imageUrl() }}" alt="{{ $label }}" style="max-height:380px;max-width:100%;height:auto;border-radius:8px;display:block;margin:0 auto 14px;border:1px solid var(--line-2);">
            @endif
            @if($m->status === 'reprovado' && $m->feedback)
              <div style="margin-bottom:12px;padding:10px 12px;background:rgba(255,90,90,0.06);border:1px solid rgba(255,90,90,0.20);border-radius:var(--r-md);font-size:12px;color:#ffb3b3;"><strong>Último feedback:</strong> {{ $m->feedback }}</div>
            @endif
            @if($m->caption)
              <div style="font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--fg-4);margin-bottom:6px;">Legenda</div>
              <div id="cap-{{ $m->id }}" style="font-size:12px;color:var(--fg-2);line-height:1.6;background:var(--bg-2);border:1px solid var(--line-1);border-radius:var(--r-sm);padding:10px 12px;white-space:pre-wrap;">{{ $m->caption }}</div>
              <button type="button" class="btn btn-sm" style="font-size:11px;margin-top:8px;" onclick="cmCopy('cap-{{ $m->id }}', this)">Copiar legenda</button>
            @endif
          @endif
        </div>

        @if($m && $m->status !== 'gerando' && $m->hasImage())
          <div style="padding:12px 18px;border-top:1px solid var(--line-1);display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
            @if($m->status !== 'aprovado')
              <form action="{{ route('admin.cursos-modulares.media.approve', [$curso->id, $m->id]) }}" method="POST" style="display:inline;">@csrf
                <button type="submit" class="btn btn-sm" style="font-size:12px;color:#6FE6BD;border-color:rgba(43,217,161,0.35);">✓ Aprovar</button>
              </form>
            @endif
            <button type="button" class="btn btn-sm" style="font-size:12px;color:#ff9a9a;border-color:rgba(255,90,90,0.30);" onclick="cmToggle('mrej-{{ $m->id }}')">↺ Reprovar / refazer</button>
            <a href="{{ $m->imageUrl() }}" download class="btn btn-sm" style="font-size:12px;text-decoration:none;">⤓ Baixar PNG</a>
            <button type="button" class="btn btn-sm" style="font-size:12px;" onclick="cmToggle('mcap-{{ $m->id }}')">✎ Legenda</button>
            <form action="{{ route('admin.cursos-modulares.media.destroy', [$curso->id, $m->id]) }}" method="POST" style="display:inline;margin-left:auto;" onsubmit="return confirm('Excluir esta peça?');">@csrf @method('DELETE')
              <button type="submit" class="btn btn-sm" style="font-size:12px;color:var(--fg-4);">Excluir</button>
            </form>
          </div>

          <div id="mrej-{{ $m->id }}" style="display:none;padding:14px 18px;border-top:1px solid var(--line-1);background:rgba(255,90,90,0.03);">
            <form action="{{ route('admin.cursos-modulares.media.reject', [$curso->id, $m->id]) }}" method="POST">@csrf
              <label class="cm-label">O que melhorar nesta arte?</label>
              <textarea name="feedback" class="cm-input" rows="2" required placeholder="Ex: aumente o título, use mais a cor de destaque, deixe o fundo mais claro…"></textarea>
              <div style="margin-top:10px;display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary btn-sm" style="font-size:12px;">Enviar para refazer</button>
                <button type="button" class="btn btn-ghost btn-sm" style="font-size:12px;" onclick="cmToggle('mrej-{{ $m->id }}')">Cancelar</button>
              </div>
            </form>
          </div>

          <div id="mcap-{{ $m->id }}" style="display:none;padding:14px 18px;border-top:1px solid var(--line-1);">
            <form action="{{ route('admin.cursos-modulares.media.caption', [$curso->id, $m->id]) }}" method="POST">@csrf @method('PUT')
              <label class="cm-label">Editar legenda</label>
              <textarea name="caption" class="cm-input" rows="4">{{ $m->caption }}</textarea>
              <div style="margin-top:10px;display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary btn-sm" style="font-size:12px;">Salvar legenda</button>
                <button type="button" class="btn btn-ghost btn-sm" style="font-size:12px;" onclick="cmToggle('mcap-{{ $m->id }}')">Cancelar</button>
              </div>
            </form>
          </div>
        @endif
      </div>
    @endforeach
  </div>

  {{-- ══════════════════ ROTEIROS ══════════════════ --}}
  <div style="display:flex;align-items:center;gap:12px;margin:0 0 14px;">
    <h2 style="font-family:var(--font-display);font-weight:700;font-size:18px;color:#fff;margin:0;flex:1;">Roteiros</h2>
    @if($curso->hasApostila())
      <form action="{{ route('admin.cursos-modulares.gerar', $curso->id) }}" method="POST" style="display:inline;"
            onsubmit="return confirm('Gerar (ou regenerar) os três roteiros a partir da apostila?');">
        @csrf
        <button type="submit" class="btn btn-sm" style="display:inline-flex;font-size:12px;">Gerar rascunhos</button>
      </form>
    @endif
  </div>

  @php $porTipo = $assets->keyBy('type'); $tipos = config('cursos_modulares.tipos'); @endphp
  <div style="display:flex;flex-direction:column;gap:14px;">
    @foreach($tipos as $key => $label)
      @php $a = $porTipo[$key] ?? null; $st = $a ? ($sb[$a->status] ?? $sb['aguardando_revisao']) : null; @endphp
      <div class="card" style="padding:0;">
        <div style="padding:14px 20px;border-bottom:1px solid var(--line-2);display:flex;align-items:center;gap:12px;">
          <h3 style="font-family:var(--font-display);font-weight:700;font-size:15px;color:var(--fg-1);margin:0;flex:1;">{{ $label }}</h3>
          @if($a)
            <span style="font-size:11px;color:var(--fg-4);">v{{ $a->version }}</span>
            <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600;background:{{ $st['bg'] }};border:1px solid {{ $st['bd'] }};color:{{ $st['fg'] }};">{{ $a->statusLabel() }}</span>
          @else
            <span style="font-size:11px;color:var(--fg-4);">não gerado</span>
          @endif
        </div>
        <div style="padding:18px 20px;">
          @if(!$a || (!$a->hasContent() && $a->status !== 'gerando'))
            <p style="color:var(--fg-4);font-size:13px;margin:0;">Ainda não gerado.</p>
          @elseif($a->status === 'gerando')
            <p style="color:var(--brand-300);font-size:13px;margin:0;">Gerando… atualize a página em instantes.</p>
            @if($a->feedback)<div style="margin-top:10px;font-size:12px;color:var(--fg-4);">Refazendo com seu feedback: “{{ $a->feedback }}”</div>@endif
          @else
            @if($a->status === 'reprovado' && $a->feedback)
              <div style="margin-bottom:12px;padding:10px 12px;background:rgba(255,90,90,0.06);border:1px solid rgba(255,90,90,0.20);border-radius:var(--r-md);font-size:12px;color:#ffb3b3;"><strong>Último feedback:</strong> {{ $a->feedback }}</div>
            @endif
            <button type="button" class="btn btn-sm" style="font-size:12px;" onclick="cmToggle('ver-{{ $a->id }}')">Ver / ocultar</button>
            <button type="button" class="btn btn-sm" style="font-size:12px;" onclick="cmCopy('content-{{ $a->id }}', this)">Copiar</button>
            <div id="ver-{{ $a->id }}" style="display:none;margin-top:12px;">
              <div id="content-{{ $a->id }}" style="max-height:380px;overflow:auto;padding:14px 16px;background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-md);font-size:13px;line-height:1.7;color:var(--fg-2);">{!! nl2br(e($a->content)) !!}</div>
            </div>
          @endif
        </div>
        @if($a && $a->status !== 'gerando' && $a->hasContent())
          <div style="padding:12px 20px;border-top:1px solid var(--line-1);display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
            @if($a->status !== 'aprovado')
              <form action="{{ route('admin.cursos-modulares.assets.approve', [$curso->id, $a->id]) }}" method="POST" style="display:inline;">@csrf
                <button type="submit" class="btn btn-sm" style="font-size:12px;color:#6FE6BD;border-color:rgba(43,217,161,0.35);">✓ Aprovar</button>
              </form>
            @endif
            <button type="button" class="btn btn-sm" style="font-size:12px;color:#ff9a9a;border-color:rgba(255,90,90,0.30);" onclick="cmToggle('rej-{{ $a->id }}')">↺ Reprovar / refazer</button>
            <button type="button" class="btn btn-sm" style="font-size:12px;" onclick="cmToggle('edit-{{ $a->id }}')">✎ Editar</button>
            <form action="{{ route('admin.cursos-modulares.assets.destroy', [$curso->id, $a->id]) }}" method="POST" style="display:inline;margin-left:auto;" onsubmit="return confirm('Excluir este item?');">@csrf @method('DELETE')
              <button type="submit" class="btn btn-sm" style="font-size:12px;color:var(--fg-4);">Excluir</button>
            </form>
          </div>
          <div id="rej-{{ $a->id }}" style="display:none;padding:14px 20px;border-top:1px solid var(--line-1);background:rgba(255,90,90,0.03);">
            <form action="{{ route('admin.cursos-modulares.assets.reject', [$curso->id, $a->id]) }}" method="POST">@csrf
              <label class="cm-label">O que você quer que melhore?</label>
              <textarea name="feedback" class="cm-input" rows="3" required placeholder="Ex: deixe mais informal, inclua exemplos de pregão eletrônico…"></textarea>
              <div style="margin-top:10px;display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary btn-sm" style="font-size:12px;">Enviar para refazer</button>
                <button type="button" class="btn btn-ghost btn-sm" style="font-size:12px;" onclick="cmToggle('rej-{{ $a->id }}')">Cancelar</button>
              </div>
            </form>
          </div>
          <div id="edit-{{ $a->id }}" style="display:none;padding:14px 20px;border-top:1px solid var(--line-1);">
            <form action="{{ route('admin.cursos-modulares.assets.update', [$curso->id, $a->id]) }}" method="POST">@csrf @method('PUT')
              <label class="cm-label">Editar conteúdo</label>
              <textarea name="content" class="cm-input" rows="12" required>{{ $a->content }}</textarea>
              <div style="margin-top:10px;display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary btn-sm" style="font-size:12px;">Salvar alterações</button>
                <button type="button" class="btn btn-ghost btn-sm" style="font-size:12px;" onclick="cmToggle('edit-{{ $a->id }}')">Cancelar</button>
              </div>
            </form>
          </div>
        @endif
      </div>
    @endforeach
  </div>

  {{-- ══════════════════ ÁUDIO DO PODCAST (mini-podcasts) ══════════════════ --}}
  @php
    $roteiroPodcast = $assets->firstWhere('type', 'podcast');
    $temRoteiro   = $roteiroPodcast && trim((string) $roteiroPodcast->content) !== '';
    $gerandoAudio = $audios->contains(fn ($x) => $x->status === 'gerando');
    $prontos      = $audios->where('status', 'pronto')->sortBy('part');
    $temAudio     = $prontos->isNotEmpty();
    $falhou       = $audios->where('status', 'erro')->count();
    $erroAudio    = ! $gerandoAudio && $prontos->isEmpty() && $audios->isNotEmpty();
  @endphp
  <div style="display:flex;align-items:center;gap:12px;margin:28px 0 14px;">
    <h2 style="font-family:var(--font-display);font-weight:700;font-size:18px;color:#fff;margin:0;flex:1;">Áudio do Podcast</h2>
    @if($temRoteiro)
      <form action="{{ route('admin.cursos-modulares.podcast.gerar', $curso->id) }}" method="POST" style="display:inline;"
            onsubmit="return confirm('Gerar o áudio do podcast em trechos? (Pode levar alguns minutos.)');">
        @csrf
        <button type="submit" class="btn btn-sm" style="display:inline-flex;font-size:12px;">{{ $temAudio ? 'Regerar áudios' : 'Gerar áudio do podcast' }}</button>
      </form>
    @endif
  </div>

  <div class="card" style="padding:18px 20px;">
    @if(!$temRoteiro)
      <p style="color:var(--fg-4);font-size:13px;margin:0;">Gere o <strong>roteiro de podcast</strong> ali em cima primeiro — o áudio é criado a partir dele.</p>
    @elseif($gerandoAudio)
      <p style="color:var(--brand-300);font-size:13px;margin:0;">Gerando os áudios em trechos… pode levar alguns minutos (são vários). Atualize a página para ouvir.</p>
    @elseif($erroAudio)
      <p style="color:#ff9a9a;font-size:13px;margin:0;">A última geração não retornou áudio. Tente “Regerar áudios”.</p>
    @elseif(!$temAudio)
      <p style="color:var(--fg-4);font-size:13px;margin:0;">Nenhum áudio gerado ainda. Clique em “Gerar áudio do podcast”.</p>
    @else
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
        <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600;background:rgba(43,217,161,0.12);border:1px solid rgba(43,217,161,0.35);color:#6FE6BD;">Pronto</span>
        <span style="font-size:11px;color:var(--fg-4);">{{ $prontos->count() }} {{ $prontos->count() === 1 ? 'trecho' : 'trechos' }}</span>
        <form action="{{ route('admin.cursos-modulares.podcast.destroy', $curso->id) }}" method="POST" style="display:inline;margin-left:auto;" onsubmit="return confirm('Excluir todos os áudios do podcast?');">@csrf @method('DELETE')
          <button type="submit" class="btn btn-sm" style="font-size:12px;color:var(--fg-4);">Excluir tudo</button>
        </form>
      </div>
      @if($falhou)
        <p style="color:#ffcf8f;font-size:12px;margin:0 0 12px;">⚠ {{ $falhou }} {{ $falhou === 1 ? 'trecho não foi gerado' : 'trechos não foram gerados' }} (provável limite do Gemini). Você pode clicar em “Regerar áudios” para tentar os que faltaram.</p>
      @endif
      <div style="display:flex;flex-direction:column;gap:14px;">
        @foreach($prontos as $au)
          <div style="padding:12px 14px;background:var(--bg-2);border:1px solid var(--line-1);border-radius:var(--r-md);">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
              <span style="font-size:13px;font-weight:600;color:var(--fg-1);flex:1;">{{ $au->title ?: ('Parte ' . $au->part) }}</span>
              <a href="{{ $au->audioUrl() }}" download class="btn btn-sm" style="font-size:11px;text-decoration:none;">⤓ Baixar</a>
            </div>
            <audio controls preload="none" src="{{ $au->audioUrl() }}" style="width:100%;"></audio>
          </div>
        @endforeach
      </div>
    @endif
  </div>

  {{-- ══════════════════ MATERIAIS DE ESTUDO — RESUMOS (PDF) ══════════════════ --}}
  @php
    $roteiroResumo  = $assets->firstWhere('type', 'resumo');
    $temResumoBase  = $roteiroResumo && trim((string) $roteiroResumo->content) !== '';
    $resumos        = $materiais->where('type', 'resumo');
    $resGerando     = $resumos->contains(fn ($x) => $x->status === 'gerando');
    $resProntos     = $resumos->where('status', 'pronto')->sortBy('sort_order');
    $resErro        = ! $resGerando && $resProntos->isEmpty() && $resumos->isNotEmpty();
  @endphp
  <div style="display:flex;align-items:center;gap:12px;margin:28px 0 14px;">
    <h2 style="font-family:var(--font-display);font-weight:700;font-size:18px;color:#fff;margin:0;flex:1;">Materiais de Estudo — Resumos (PDF)</h2>
    @if($temResumoBase)
      <form action="{{ route('admin.cursos-modulares.resumo-pdf.gerar', $curso->id) }}" method="POST" style="display:inline;"
            onsubmit="return confirm('Gerar os resumos em PDF a partir do roteiro de resumo? (Pode levar 1-2 min.)');">
        @csrf
        <button type="submit" class="btn btn-sm" style="display:inline-flex;font-size:12px;">{{ $resProntos->isNotEmpty() ? 'Regerar resumos' : 'Gerar resumos em PDF' }}</button>
      </form>
    @endif
  </div>

  <div class="card" style="padding:18px 20px;">
    @if(!$temResumoBase)
      <p style="color:var(--fg-4);font-size:13px;margin:0;">Gere o <strong>roteiro de resumo</strong> ali em cima primeiro — os PDFs são criados a partir dele.</p>
    @elseif($resGerando)
      <p style="color:var(--brand-300);font-size:13px;margin:0;">Gerando os resumos em PDF… pode levar 1-2 min. Atualize a página para baixar.</p>
    @elseif($resErro)
      <p style="color:#ff9a9a;font-size:13px;margin:0;">A última geração não retornou PDF. Tente “Regerar resumos”.</p>
    @elseif($resProntos->isEmpty())
      <p style="color:var(--fg-4);font-size:13px;margin:0;">Nenhum resumo gerado ainda. Clique em “Gerar resumos em PDF”.</p>
    @else
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
        <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600;background:rgba(43,217,161,0.12);border:1px solid rgba(43,217,161,0.35);color:#6FE6BD;">Pronto</span>
        <span style="font-size:11px;color:var(--fg-4);">{{ $resProntos->count() }} {{ $resProntos->count() === 1 ? 'resumo' : 'resumos' }}</span>
        <form action="{{ route('admin.cursos-modulares.materiais.destroy', [$curso->id, 'resumo']) }}" method="POST" style="display:inline;margin-left:auto;" onsubmit="return confirm('Excluir todos os resumos em PDF?');">@csrf @method('DELETE')
          <button type="submit" class="btn btn-sm" style="font-size:12px;color:var(--fg-4);">Excluir tudo</button>
        </form>
      </div>
      <div style="display:flex;flex-direction:column;gap:10px;">
        @foreach($resProntos as $mat)
          <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--bg-2);border:1px solid var(--line-1);border-radius:var(--r-md);">
            <span style="font-size:18px;line-height:1;">📄</span>
            <span style="font-size:13px;font-weight:600;color:var(--fg-1);flex:1;">{{ $mat->title }}</span>
            <a href="{{ $mat->pdfUrl() }}" target="_blank" rel="noopener" class="btn btn-sm" style="font-size:11px;text-decoration:none;">⤓ Baixar PDF</a>
          </div>
        @endforeach
      </div>
    @endif
  </div>

  {{-- ══════════════════ CARTÕES DIDÁTICOS (interativos) ══════════════════ --}}
  @php
    $roteiroBase    = $assets->firstWhere('type', 'resumo');
    $temBaseCartoes = $roteiroBase && trim((string) $roteiroBase->content) !== '';
    $cartoes        = $materiais->where('type', 'cartoes');
    $cartGerando    = $cartoes->contains(fn ($x) => $x->status === 'gerando');
    $cartProntos    = $cartoes->where('status', 'pronto')->sortBy('sort_order');
    $cartErro       = ! $cartGerando && $cartProntos->isEmpty() && $cartoes->isNotEmpty();
  @endphp
  <div style="display:flex;align-items:center;gap:12px;margin:28px 0 14px;">
    <h2 style="font-family:var(--font-display);font-weight:700;font-size:18px;color:#fff;margin:0;flex:1;">Cartões Didáticos</h2>
    @if($temBaseCartoes)
      <form action="{{ route('admin.cursos-modulares.cartoes.gerar', $curso->id) }}" method="POST" style="display:inline;"
            onsubmit="return confirm('Gerar os cartões didáticos a partir do roteiro de resumo?');">
        @csrf
        <button type="submit" class="btn btn-sm" style="display:inline-flex;font-size:12px;">{{ $cartProntos->isNotEmpty() ? 'Regerar cartões' : 'Gerar cartões' }}</button>
      </form>
    @endif
  </div>

  <div class="card" style="padding:18px 20px;">
    @if(!$temBaseCartoes)
      <p style="color:var(--fg-4);font-size:13px;margin:0;">Gere o <strong>roteiro de resumo</strong> ali em cima primeiro — os cartões são criados a partir dele.</p>
    @elseif($cartGerando)
      <p style="color:var(--brand-300);font-size:13px;margin:0;">Gerando os cartões… atualize a página em instantes.</p>
    @elseif($cartErro)
      <p style="color:#ff9a9a;font-size:13px;margin:0;">A última geração não retornou cartões. Tente “Regerar cartões”.</p>
    @elseif($cartProntos->isEmpty())
      <p style="color:var(--fg-4);font-size:13px;margin:0;">Nenhum cartão gerado ainda. Clique em “Gerar cartões”.</p>
    @else
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
        <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600;background:rgba(43,217,161,0.12);border:1px solid rgba(43,217,161,0.35);color:#6FE6BD;">Pronto</span>
        <span style="font-size:11px;color:var(--fg-4);">{{ $cartProntos->count() }} {{ $cartProntos->count() === 1 ? 'baralho' : 'baralhos' }}</span>
        <form action="{{ route('admin.cursos-modulares.materiais.destroy', [$curso->id, 'cartoes']) }}" method="POST" style="display:inline;margin-left:auto;" onsubmit="return confirm('Excluir todos os cartões?');">@csrf @method('DELETE')
          <button type="submit" class="btn btn-sm" style="font-size:12px;color:var(--fg-4);">Excluir tudo</button>
        </form>
      </div>
      <div style="display:flex;flex-direction:column;gap:18px;">
        @foreach($cartProntos as $deck)
          @php $cards = $deck->cards(); @endphp
          <div class="cm-deck" data-cards='@json($cards)' style="background:var(--bg-2);border:1px solid var(--line-1);border-radius:var(--r-md);padding:16px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
              <span style="font-size:18px;line-height:1;">🃏</span>
              <span style="font-size:13px;font-weight:600;color:var(--fg-1);flex:1;">{{ $deck->title }}</span>
              <span style="font-size:11px;color:var(--fg-4);">{{ count($cards) }} cartões</span>
            </div>
            <div class="flashcard" style="cursor:pointer;min-height:130px;display:flex;align-items:center;justify-content:center;text-align:center;padding:20px;border-radius:var(--r-sm);border:1px solid var(--line-2);background:var(--bg-1);user-select:none;">
              <div class="fc-front" style="font-size:15px;font-weight:600;color:#fff;width:100%;"></div>
              <div class="fc-back" style="font-size:14px;color:var(--fg-2);line-height:1.55;display:none;width:100%;"></div>
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
    @endif
  </div>

  {{-- ══════════════════ PROVA / SIMULADO (interativo) ══════════════════ --}}
  @php
    $roteiroProva = $assets->firstWhere('type', 'resumo');
    $temBaseProva = $roteiroProva && trim((string) $roteiroProva->content) !== '';
    $provas       = $materiais->where('type', 'prova');
    $provaGerando = $provas->contains(fn ($x) => $x->status === 'gerando');
    $provaPronta  = $provas->where('status', 'pronto')->sortBy('sort_order')->first();
    $provaErro    = ! $provaGerando && ! $provaPronta && $provas->isNotEmpty();
    $questions    = $provaPronta ? (json_decode((string) $provaPronta->content, true) ?: []) : [];
  @endphp
  <div style="display:flex;align-items:center;gap:12px;margin:28px 0 14px;">
    <h2 style="font-family:var(--font-display);font-weight:700;font-size:18px;color:#fff;margin:0;flex:1;">Prova / Simulado</h2>
    @if($temBaseProva)
      <form action="{{ route('admin.cursos-modulares.prova.gerar', $curso->id) }}" method="POST" style="display:inline;"
            onsubmit="return confirm('Gerar a prova (simulado) a partir do roteiro de resumo?');">
        @csrf
        <button type="submit" class="btn btn-sm" style="display:inline-flex;font-size:12px;">{{ (count($questions)) ? 'Regerar prova' : 'Gerar prova' }}</button>
      </form>
    @endif
  </div>

  <div class="card" style="padding:18px 20px;">
    @if(!$temBaseProva)
      <p style="color:var(--fg-4);font-size:13px;margin:0;">Gere o <strong>roteiro de resumo</strong> ali em cima primeiro — a prova é criada a partir dele.</p>
    @elseif($provaGerando)
      <p style="color:var(--brand-300);font-size:13px;margin:0;">Gerando a prova… atualize a página em instantes.</p>
    @elseif($provaErro)
      <p style="color:#ff9a9a;font-size:13px;margin:0;">A última geração não retornou questões. Tente “Regerar prova”.</p>
    @elseif(!$provaPronta || !count($questions))
      <p style="color:var(--fg-4);font-size:13px;margin:0;">Nenhuma prova gerada ainda. Clique em “Gerar prova”.</p>
    @else
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
        <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600;background:rgba(43,217,161,0.12);border:1px solid rgba(43,217,161,0.35);color:#6FE6BD;">Pronto</span>
        <span style="font-size:11px;color:var(--fg-4);">{{ count($questions) }} {{ count($questions) === 1 ? 'questão' : 'questões' }}</span>
        <form action="{{ route('admin.cursos-modulares.materiais.destroy', [$curso->id, 'prova']) }}" method="POST" style="display:inline;margin-left:auto;" onsubmit="return confirm('Excluir a prova?');">@csrf @method('DELETE')
          <button type="submit" class="btn btn-sm" style="font-size:12px;color:var(--fg-4);">Excluir</button>
        </form>
      </div>
      <div class="cm-prova" data-total="{{ count($questions) }}">
        @foreach($questions as $qi => $q)
          <div class="qz-q" data-correct="{{ (int) ($q['correta'] ?? 0) }}" style="padding:14px 16px;background:var(--bg-2);border:1px solid var(--line-1);border-radius:var(--r-md);margin-bottom:14px;">
            <div style="font-size:14px;font-weight:600;color:#fff;margin-bottom:10px;line-height:1.5;">{{ $qi + 1 }}. {{ $q['enunciado'] ?? '' }}</div>
            <div style="display:flex;flex-direction:column;gap:8px;">
              @foreach(($q['alternativas'] ?? []) as $ai => $alt)
                <button type="button" class="qz-alt" data-i="{{ $ai }}" style="text-align:left;padding:10px 12px;border:1px solid var(--line-2);border-radius:var(--r-sm);background:var(--bg-1);color:var(--fg-2);font-size:13px;cursor:pointer;line-height:1.45;">
                  <strong style="color:var(--brand-300);">{{ chr(65 + $ai) }})</strong> {{ $alt }}
                </button>
              @endforeach
            </div>
            <div class="qz-coment" style="display:none;margin-top:10px;padding:10px 12px;background:rgba(0,163,255,0.06);border-left:3px solid var(--brand-500);border-radius:4px;font-size:12.5px;color:var(--fg-3);line-height:1.5;">
              {!! nl2br(e($q['comentario'] ?? '')) !!}
            </div>
          </div>
        @endforeach
        <div style="display:flex;align-items:center;gap:12px;margin-top:6px;">
          <button type="button" class="qz-corrigir btn btn-sm" style="font-size:12px;">Corrigir prova</button>
          <button type="button" class="qz-refazer btn btn-sm" style="font-size:12px;color:var(--fg-4);display:none;">Refazer</button>
          <span class="qz-score" style="font-size:13px;font-weight:700;color:var(--fg-1);margin-left:auto;"></span>
        </div>
      </div>
    @endif
  </div>

  {{-- ══════════════════ CRIATIVOS DE ADS ══════════════════ --}}
  @php
    $criativosProntos = $criativos->where('status', 'pronto')->sortBy('id');
    $criativosGerando = $criativos->contains(fn ($x) => $x->status === 'gerando');
    $criativosErro    = ! $criativosGerando && $criativosProntos->isEmpty() && $criativos->isNotEmpty();
  @endphp
  <div style="display:flex;align-items:center;gap:12px;margin:28px 0 14px;">
    <h2 style="font-family:var(--font-display);font-weight:700;font-size:18px;color:#fff;margin:0;flex:1;">Criativos de ADS</h2>
    <form action="{{ route('admin.cursos-modulares.ads.gerar', $curso->id) }}" method="POST" style="display:inline;"
          onsubmit="return confirm('Gerar os criativos de ADS? O Claude vai usar as fotos do seu banco de imagens. (Pode levar 1-2 min.)');">
      @csrf
      <button type="submit" class="btn btn-sm" style="display:inline-flex;font-size:12px;">{{ $criativosProntos->isNotEmpty() ? 'Regerar criativos' : 'Gerar criativos' }}</button>
    </form>
  </div>

  <div class="card" style="padding:18px 20px;">
    @if($criativosGerando)
      <p style="color:var(--brand-300);font-size:13px;margin:0;">Gerando os criativos… o Claude está montando as artes com as fotos. Pode levar 1-2 min — atualize a página.</p>
    @elseif($criativosErro)
      <p style="color:#ff9a9a;font-size:13px;margin:0;">A última geração não retornou criativos. Verifique se as fotos do banco abrem no navegador e tente “Regerar criativos”.</p>
    @elseif($criativosProntos->isEmpty())
      <p style="color:var(--fg-4);font-size:13px;margin:0;">Nenhum criativo gerado ainda. Clique em “Gerar criativos” — o Claude usa as fotos do seu banco de imagens (planilha) como fundo das artes.</p>
    @else
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
        <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600;background:rgba(43,217,161,0.12);border:1px solid rgba(43,217,161,0.35);color:#6FE6BD;">Pronto</span>
        <span style="font-size:11px;color:var(--fg-4);">{{ $criativosProntos->count() }} {{ $criativosProntos->count() === 1 ? 'criativo' : 'criativos' }}</span>
        <form action="{{ route('admin.cursos-modulares.ads.destroy', $curso->id) }}" method="POST" style="display:inline;margin-left:auto;" onsubmit="return confirm('Excluir todos os criativos?');">@csrf @method('DELETE')
          <button type="submit" class="btn btn-sm" style="font-size:12px;color:var(--fg-4);">Excluir tudo</button>
        </form>
      </div>
      <div style="display:flex;flex-direction:column;gap:16px;">
        @foreach($criativosProntos as $cr)
          <div style="display:flex;gap:16px;align-items:flex-start;padding:14px;background:var(--bg-2);border:1px solid var(--line-1);border-radius:var(--r-md);flex-wrap:wrap;">
            <a href="{{ $cr->imageUrl() }}" target="_blank" rel="noopener" style="flex-shrink:0;">
              <img src="{{ $cr->imageUrl() }}" alt="{{ $cr->tituloLabel() }}" style="width:170px;height:auto;border-radius:8px;border:1px solid var(--line-2);display:block;" />
            </a>
            <div style="flex:1;min-width:220px;display:flex;flex-direction:column;gap:8px;">
              <div style="font-size:13px;font-weight:700;color:#fff;">{{ $cr->tituloLabel() }}</div>
              @if($cr->caption)
                <div id="cap-{{ $cr->id }}" style="font-size:12.5px;color:var(--fg-3);line-height:1.5;white-space:pre-wrap;background:var(--bg-1);border:1px solid var(--line-2);border-radius:var(--r-sm);padding:10px 12px;">{{ $cr->caption }}</div>
              @endif
              <div style="display:flex;gap:8px;flex-wrap:wrap;">
                @if($cr->caption)
                  <button type="button" class="btn btn-sm" style="font-size:11px;" onclick="cmCopy('cap-{{ $cr->id }}', this)">Copiar legenda</button>
                @endif
                <a href="{{ $cr->imageUrl() }}" download target="_blank" rel="noopener" class="btn btn-sm" style="font-size:11px;text-decoration:none;">⤓ Baixar imagem</a>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>

  {{-- ══════════════════ CAPA DO CURSO (site) ══════════════════ --}}
  @php
    $capaGerando = $capa->contains(fn ($x) => $x->status === 'gerando');
    $capaPronta  = $capa->where('status', 'pronto')->first();
    $capaErro    = ! $capaGerando && ! $capaPronta && $capa->isNotEmpty();
  @endphp
  <div style="display:flex;align-items:center;gap:12px;margin:28px 0 14px;">
    <h2 style="font-family:var(--font-display);font-weight:700;font-size:18px;color:#fff;margin:0;flex:1;">Capa do curso (site)</h2>
    <form action="{{ route('admin.cursos-modulares.capa.gerar', $curso->id) }}" method="POST" style="display:inline;"
          onsubmit="return confirm('Gerar a capa 16:9 do curso?');">
      @csrf
      <button type="submit" class="btn btn-sm" style="display:inline-flex;font-size:12px;">{{ $capaPronta ? 'Regerar capa' : 'Gerar capa' }}</button>
    </form>
  </div>

  <div class="card" style="padding:18px 20px;">
    @if($capaGerando)
      <p style="color:var(--brand-300);font-size:13px;margin:0;">Gerando a capa… atualize a página em instantes.</p>
    @elseif($capaErro)
      <p style="color:#ff9a9a;font-size:13px;margin:0;">A última geração não retornou imagem. Tente “Regerar capa”.</p>
    @elseif(!$capaPronta)
      <p style="color:var(--fg-4);font-size:13px;margin:0;">Nenhuma capa gerada ainda. Clique em “Gerar capa” — é a imagem 16:9 que vai no topo do card do curso no site.</p>
    @else
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
        <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600;background:rgba(43,217,161,0.12);border:1px solid rgba(43,217,161,0.35);color:#6FE6BD;">Pronto</span>
        <span style="font-size:11px;color:var(--fg-4);">1280 × 720 (16:9)</span>
        <form action="{{ route('admin.cursos-modulares.capa.destroy', $curso->id) }}" method="POST" style="display:inline;margin-left:auto;" onsubmit="return confirm('Excluir a capa?');">@csrf @method('DELETE')
          <button type="submit" class="btn btn-sm" style="font-size:12px;color:var(--fg-4);">Excluir</button>
        </form>
      </div>
      <a href="{{ $capaPronta->imageUrl() }}" target="_blank" rel="noopener" style="display:block;">
        <img src="{{ $capaPronta->imageUrl() }}" alt="Capa do curso" style="width:100%;max-width:640px;height:auto;border-radius:10px;border:1px solid var(--line-2);display:block;" />
      </a>
      <div style="margin-top:12px;">
        <a href="{{ $capaPronta->imageUrl() }}" download target="_blank" rel="noopener" class="btn btn-sm" style="font-size:11px;text-decoration:none;">⤓ Baixar imagem</a>
      </div>
    @endif
  </div>

  {{-- ══════════════════ VÍDEO DE RESUMO (estilo NotebookLM) ══════════════════ --}}
  @php
    $videoGerando = $videos->contains(fn ($x) => $x->status === 'gerando');
    $videoPronto  = $videos->first(fn ($x) => $x->status === 'pronto' && ! empty($x->video_url));
    $videoErroRow = $videos->first(fn ($x) => $x->status === 'erro');
    $videoErro    = ! $videoGerando && ! $videoPronto && $videoErroRow;
  @endphp
  <div style="display:flex;align-items:center;gap:12px;margin:28px 0 14px;">
    <h2 style="font-family:var(--font-display);font-weight:700;font-size:18px;color:#fff;margin:0;flex:1;">Vídeo de resumo</h2>
    <form action="{{ route('admin.cursos-modulares.video.gerar', $curso->id) }}" method="POST" style="display:inline;"
          onsubmit="return confirm('Gerar o vídeo de resumo? Isso leva alguns minutos (slides + narração + montagem).');">
      @csrf
      <button type="submit" class="btn btn-sm" style="display:inline-flex;font-size:12px;">{{ $videoPronto ? 'Regerar vídeo' : 'Gerar vídeo' }}</button>
    </form>
  </div>

  <div class="card" style="padding:18px 20px;">
    @if($videoGerando)
      <p style="color:var(--brand-300);font-size:13px;margin:0;">Gerando o vídeo… o n8n monta os slides, narra com IA e renderiza o .mp4. Isso costuma levar alguns minutos — atualize a página depois.</p>
    @elseif($videoErro)
      <p style="color:#ff9a9a;font-size:13px;margin:0 0 6px;">A última geração não concluiu.</p>
      @if($videoErroRow->feedback)
        <p style="color:var(--fg-4);font-size:12px;margin:0;">{{ $videoErroRow->feedback }}</p>
      @endif
    @elseif(!$videoPronto)
      <p style="color:var(--fg-4);font-size:13px;margin:0;">Nenhum vídeo gerado ainda. Clique em “Gerar vídeo” — a IA cria um resumo em slides narrados (estilo NotebookLM) a partir do título e da descrição do curso.</p>
    @else
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;flex-wrap:wrap;">
        <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600;background:rgba(43,217,161,0.12);border:1px solid rgba(43,217,161,0.35);color:#6FE6BD;">Pronto</span>
        @if($videoPronto->durationHuman())
          <span style="font-size:11px;color:var(--fg-4);">{{ $videoPronto->durationHuman() }}</span>
        @endif
        @if($videoPronto->slides)
          <span style="font-size:11px;color:var(--fg-4);">{{ $videoPronto->slides }} slides</span>
        @endif
        <form action="{{ route('admin.cursos-modulares.video.destroy', [$curso->id, $videoPronto->id]) }}" method="POST" style="display:inline;margin-left:auto;" onsubmit="return confirm('Remover este vídeo da listagem?');">@csrf @method('DELETE')
          <button type="submit" class="btn btn-sm" style="font-size:12px;color:var(--fg-4);">Excluir</button>
        </form>
      </div>
      <video controls preload="metadata" src="{{ $videoPronto->video_url }}" style="width:100%;max-width:720px;border-radius:10px;border:1px solid var(--line-2);display:block;background:#000;"></video>
      <div style="margin-top:12px;">
        <a href="{{ $videoPronto->video_url }}" target="_blank" rel="noopener" class="btn btn-sm" style="font-size:11px;text-decoration:none;">↗ Abrir em nova aba</a>
      </div>
    @endif
  </div>

  {{-- ══════════════════ ALUNOS MATRICULADOS ══════════════════ --}}
  @php
    $matAtivas = $matriculas->where('status', 'ativo');
  @endphp
  <div style="display:flex;align-items:center;gap:12px;margin:28px 0 14px;">
    <h2 style="font-family:var(--font-display);font-weight:700;font-size:18px;color:#fff;margin:0;flex:1;">Alunos matriculados</h2>
    <span style="font-size:11px;color:var(--fg-4);">{{ $matAtivas->count() }} {{ $matAtivas->count() === 1 ? 'ativo' : 'ativos' }}</span>
  </div>

  <div class="card" style="padding:18px 20px;">
    <form action="{{ route('admin.cursos-modulares.matriculas.store', $curso->id) }}" method="POST" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:16px;">
      @csrf
      <input type="text" name="ident" class="cm-input" placeholder="E-mail ou CPF do aluno" required style="flex:1;min-width:240px;max-width:360px;" />
      <button type="submit" class="btn btn-sm" style="font-size:12px;">Matricular aluno</button>
    </form>

    @if($matriculas->isEmpty())
      <p style="color:var(--fg-4);font-size:13px;margin:0;">Nenhum aluno matriculado ainda. Matricule pelo e-mail ou CPF acima.</p>
    @else
      <div style="display:flex;flex-direction:column;gap:8px;">
        @foreach($matriculas as $m)
          <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;background:var(--bg-2);border:1px solid var(--line-1);border-radius:var(--r-md);flex-wrap:wrap;{{ $m->status === 'cancelado' ? 'opacity:.55;' : '' }}">
            <div style="flex:1;min-width:200px;">
              <div style="font-size:13px;font-weight:600;color:var(--fg-1);">{{ optional($m->student)->name ?? '—' }}</div>
              <div style="font-size:11px;color:var(--fg-4);">{{ optional($m->student)->email ?? '' }}</div>
            </div>
            @php
              $cor = $m->status === 'ativo' ? ['rgba(43,217,161,0.12)','rgba(43,217,161,0.35)','#6FE6BD'] : ($m->status === 'pendente' ? ['rgba(255,193,90,0.12)','rgba(255,193,90,0.35)','#ffd27a'] : ['rgba(255,107,107,0.12)','rgba(255,107,107,0.35)','#ff9a9a']);
            @endphp
            <span style="font-size:10px;color:var(--fg-4);text-transform:uppercase;letter-spacing:.05em;">{{ $m->source === 'compra' ? 'Compra' : 'Manual' }}</span>
            <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600;background:{{ $cor[0] }};border:1px solid {{ $cor[1] }};color:{{ $cor[2] }};">{{ $m->statusLabel() }}</span>
            @if($m->status !== 'cancelado')
              <form action="{{ route('admin.cursos-modulares.matriculas.cancelar', [$curso->id, $m->id]) }}" method="POST" style="display:inline;" onsubmit="return confirm('Cancelar a matrícula de {{ addslashes(optional($m->student)->name ?? 'aluno') }}?');">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-sm" style="font-size:11px;color:var(--fg-4);">Cancelar</button>
              </form>
            @endif
          </div>
        @endforeach
      </div>
    @endif
  </div>

</div>
@endsection

@push('styles')
<style>
.cm-label { display:block;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--fg-3);margin-bottom:6px; }
.cm-input { width:100%;padding:10px 14px;background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-sm);color:var(--fg-2);font-family:var(--font-body);font-size:13px;line-height:1.6;outline:none;box-sizing:border-box;resize:vertical; }
.cm-input:focus { border-color:var(--brand-500);box-shadow:0 0 0 3px rgba(0,163,255,.18); }
</style>
@endpush

@push('scripts')
<script>
function cmToggle(id){ const el=document.getElementById(id); if(el) el.style.display=(el.style.display==='none'||!el.style.display)?'block':'none'; }
function cmCopy(id, btn){ const el=document.getElementById(id); if(!el) return; navigator.clipboard.writeText(el.innerText).then(()=>{ const o=btn.textContent; btn.textContent='Copiado!'; setTimeout(()=>{btn.textContent=o;},1500); }); }
function cmInitDecks(){
  document.querySelectorAll('.cm-deck').forEach(function(deck){
    if(deck.__init) return; deck.__init=true;
    var cards=[]; try{ cards=JSON.parse(deck.getAttribute('data-cards')||'[]'); }catch(e){ cards=[]; }
    if(!cards.length) return;
    var i=0, back=false;
    var elFront=deck.querySelector('.fc-front'), elBack=deck.querySelector('.fc-back');
    var elCount=deck.querySelector('.fc-counter'), card=deck.querySelector('.flashcard');
    function render(){
      elFront.textContent=cards[i].front||'';
      elBack.textContent=cards[i].back||'';
      elFront.style.display=back?'none':'block';
      elBack.style.display=back?'block':'none';
      elCount.textContent=(i+1)+' / '+cards.length;
    }
    card.addEventListener('click', function(){ back=!back; render(); });
    deck.querySelector('.fc-prev').addEventListener('click', function(e){ e.stopPropagation(); i=(i-1+cards.length)%cards.length; back=false; render(); });
    deck.querySelector('.fc-next').addEventListener('click', function(e){ e.stopPropagation(); i=(i+1)%cards.length; back=false; render(); });
    render();
  });
}
document.addEventListener('DOMContentLoaded', cmInitDecks);
function cmInitQuiz(){
  document.querySelectorAll('.cm-prova').forEach(function(quiz){
    if(quiz.__init) return; quiz.__init=true;
    var qs=Array.prototype.slice.call(quiz.querySelectorAll('.qz-q'));
    var btnC=quiz.querySelector('.qz-corrigir'), btnR=quiz.querySelector('.qz-refazer'), score=quiz.querySelector('.qz-score');
    function reset(alt){ alt.classList.remove('sel'); alt.style.borderColor='var(--line-2)'; alt.style.background='var(--bg-1)'; }
    qs.forEach(function(q){
      q.querySelectorAll('.qz-alt').forEach(function(alt){
        alt.addEventListener('click', function(){
          if(quiz.__done) return;
          q.querySelectorAll('.qz-alt').forEach(reset);
          alt.classList.add('sel'); alt.style.borderColor='var(--brand-500)'; alt.style.background='rgba(0,163,255,0.10)';
        });
      });
    });
    btnC.addEventListener('click', function(){
      if(quiz.__done) return; quiz.__done=true;
      var acertos=0;
      qs.forEach(function(q){
        var correct=parseInt(q.getAttribute('data-correct'),10);
        var sel=q.querySelector('.qz-alt.sel');
        q.querySelectorAll('.qz-alt').forEach(function(a){
          var i=parseInt(a.getAttribute('data-i'),10);
          if(i===correct){ a.style.borderColor='#2bd9a1'; a.style.background='rgba(43,217,161,0.14)'; }
          else if(sel&&a===sel){ a.style.borderColor='#ff6b6b'; a.style.background='rgba(255,107,107,0.12)'; }
        });
        if(sel&&parseInt(sel.getAttribute('data-i'),10)===correct) acertos++;
        var com=q.querySelector('.qz-coment'); if(com) com.style.display='block';
      });
      score.textContent='Acertos: '+acertos+' / '+qs.length;
      btnC.style.display='none'; btnR.style.display='inline-flex';
    });
    btnR.addEventListener('click', function(){
      quiz.__done=false;
      qs.forEach(function(q){
        q.querySelectorAll('.qz-alt').forEach(reset);
        var com=q.querySelector('.qz-coment'); if(com) com.style.display='none';
      });
      score.textContent=''; btnR.style.display='none'; btnC.style.display='inline-flex';
    });
  });
}
document.addEventListener('DOMContentLoaded', cmInitQuiz);
</script>
@endpush
