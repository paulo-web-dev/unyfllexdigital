@extends('layouts.admin')
@section('title', $curso->title)
@section('section', 'Cursos Modulares')

@section('content')
<div class="page">

  {{-- Breadcrumb --}}
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
  @endphp

  {{-- Cabeçalho --}}
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
        <form action="{{ route('admin.cursos-modulares.gerar', $curso->id) }}" method="POST" style="display:inline;"
              onsubmit="return confirm('Gerar (ou regenerar) os três rascunhos a partir da apostila?');">
          @csrf
          <button type="submit" class="btn btn-primary" style="display:inline-flex;">
            <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
            Gerar rascunhos
          </button>
        </form>
      @endif
      <a href="{{ route('admin.cursos-modulares') }}" class="btn btn-ghost" style="text-decoration:none;display:inline-flex;">Voltar</a>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;margin-bottom:24px;">
    {{-- Apostila --}}
    <div class="card" style="padding:0;">
      <div style="padding:16px 20px;border-bottom:1px solid var(--line-2);">
        <h2 style="font-family:var(--font-display);font-weight:700;font-size:16px;color:#fff;margin:0;">Apostila (origem do curso)</h2>
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
          <p style="color:var(--fg-4);font-size:13px;margin:0;">Nenhuma apostila enviada. Sem apostila não dá pra gerar os rascunhos.</p>
        @endif
        @if($curso->description)
          <div style="margin-top:20px;padding-top:20px;border-top:1px solid var(--line-1);">
            <div style="font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--fg-4);margin-bottom:6px;">Descrição</div>
            <p style="font-size:13px;color:var(--fg-2);margin:0;line-height:1.6;">{{ $curso->description }}</p>
          </div>
        @endif
      </div>
    </div>

    {{-- Como funciona --}}
    <div class="card" style="padding:16px 18px;">
      <div style="font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--fg-4);margin-bottom:8px;">Como funciona</div>
      <p style="font-size:12px;color:var(--fg-3);margin:0;line-height:1.6;">
        Clique em <strong style="color:var(--fg-2);">Gerar rascunhos</strong> e o n8n + Claude leem a apostila e devolvem os três conteúdos abaixo. Você revisa cada um: <strong style="color:var(--fg-2);">Aprovar</strong> fixa, <strong style="color:var(--fg-2);">Reprovar</strong> manda refazer com o seu comentário. Quando os três estiverem aprovados, o curso vira <strong style="color:var(--fg-2);">Publicado</strong>.
      </p>
    </div>
  </div>

  {{-- ══ Conteúdo gerado ══════════════════════════════════════════════ --}}
  <h2 style="font-family:var(--font-display);font-weight:700;font-size:18px;color:#fff;margin:0 0 14px;">Conteúdo gerado</h2>

  @php
    $porTipo = $assets->keyBy('type');
    $tipos   = config('cursos_modulares.tipos');
    $sb = [
      'gerando'            => ['bg'=>'rgba(0,163,255,0.12)','bd'=>'rgba(0,163,255,0.30)','fg'=>'var(--brand-300)'],
      'aguardando_revisao' => ['bg'=>'rgba(232,183,101,0.12)','bd'=>'rgba(232,183,101,0.35)','fg'=>'var(--gold-400)'],
      'aprovado'           => ['bg'=>'rgba(43,217,161,0.12)','bd'=>'rgba(43,217,161,0.35)','fg'=>'#6FE6BD'],
      'reprovado'          => ['bg'=>'rgba(255,90,90,0.10)','bd'=>'rgba(255,90,90,0.30)','fg'=>'#ff9a9a'],
    ];
  @endphp

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
            <p style="color:var(--fg-4);font-size:13px;margin:0;">Ainda não gerado. Use o botão “Gerar rascunhos” acima.</p>
          @elseif($a->status === 'gerando')
            <p style="color:var(--brand-300);font-size:13px;margin:0;">Gerando… isso leva de alguns segundos a ~1 min. Atualize a página para ver o resultado.</p>
            @if($a->feedback)
              <div style="margin-top:10px;font-size:12px;color:var(--fg-4);">Refazendo com seu feedback: “{{ $a->feedback }}”</div>
            @endif
          @else
            @if($a->status === 'reprovado' && $a->feedback)
              <div style="margin-bottom:12px;padding:10px 12px;background:rgba(255,90,90,0.06);border:1px solid rgba(255,90,90,0.20);border-radius:var(--r-md);font-size:12px;color:#ffb3b3;">
                <strong>Seu último feedback:</strong> {{ $a->feedback }}
              </div>
            @endif

            <button type="button" class="btn btn-sm" style="font-size:12px;" onclick="cmToggle('ver-{{ $a->id }}')">Ver / ocultar conteúdo</button>
            <button type="button" class="btn btn-sm" style="font-size:12px;" onclick="cmCopy('content-{{ $a->id }}', this)">Copiar</button>

            <div id="ver-{{ $a->id }}" style="display:none;margin-top:12px;">
              <div id="content-{{ $a->id }}" style="max-height:380px;overflow:auto;padding:14px 16px;background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-md);font-size:13px;line-height:1.7;color:var(--fg-2);white-space:normal;">{!! nl2br(e($a->content)) !!}</div>
            </div>
          @endif
        </div>

        @if($a && $a->status !== 'gerando' && $a->hasContent())
          <div style="padding:12px 20px;border-top:1px solid var(--line-1);display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
            {{-- Aprovar --}}
            @if($a->status !== 'aprovado')
              <form action="{{ route('admin.cursos-modulares.assets.approve', [$curso->id, $a->id]) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-sm" style="font-size:12px;color:#6FE6BD;border-color:rgba(43,217,161,0.35);">✓ Aprovar</button>
              </form>
            @endif
            {{-- Reprovar (abre textarea) --}}
            <button type="button" class="btn btn-sm" style="font-size:12px;color:#ff9a9a;border-color:rgba(255,90,90,0.30);" onclick="cmToggle('rej-{{ $a->id }}')">↺ Reprovar / refazer</button>
            {{-- Editar (abre textarea) --}}
            <button type="button" class="btn btn-sm" style="font-size:12px;" onclick="cmToggle('edit-{{ $a->id }}')">✎ Editar</button>
            {{-- Excluir --}}
            <form action="{{ route('admin.cursos-modulares.assets.destroy', [$curso->id, $a->id]) }}" method="POST" style="display:inline;margin-left:auto;"
                  onsubmit="return confirm('Excluir este item gerado?');">
              @csrf @method('DELETE')
              <button type="submit" class="btn btn-sm" style="font-size:12px;color:var(--fg-4);">Excluir</button>
            </form>
          </div>

          {{-- Form reprovar --}}
          <div id="rej-{{ $a->id }}" style="display:none;padding:14px 20px;border-top:1px solid var(--line-1);background:rgba(255,90,90,0.03);">
            <form action="{{ route('admin.cursos-modulares.assets.reject', [$curso->id, $a->id]) }}" method="POST">
              @csrf
              <label class="cm-label">O que você quer que melhore?</label>
              <textarea name="feedback" class="cm-input" rows="3" required placeholder="Ex: deixe mais informal, inclua exemplos de pregão eletrônico, encurte a abertura…"></textarea>
              <div style="margin-top:10px;display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary btn-sm" style="font-size:12px;">Enviar para refazer</button>
                <button type="button" class="btn btn-ghost btn-sm" style="font-size:12px;" onclick="cmToggle('rej-{{ $a->id }}')">Cancelar</button>
              </div>
            </form>
          </div>

          {{-- Form editar --}}
          <div id="edit-{{ $a->id }}" style="display:none;padding:14px 20px;border-top:1px solid var(--line-1);">
            <form action="{{ route('admin.cursos-modulares.assets.update', [$curso->id, $a->id]) }}" method="POST">
              @csrf @method('PUT')
              <label class="cm-label">Editar conteúdo manualmente</label>
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
function cmToggle(id) {
  const el = document.getElementById(id);
  if (el) el.style.display = (el.style.display === 'none' || !el.style.display) ? 'block' : 'none';
}
function cmCopy(id, btn) {
  const el = document.getElementById(id);
  if (!el) return;
  const txt = el.innerText;
  navigator.clipboard.writeText(txt).then(() => {
    const orig = btn.textContent;
    btn.textContent = 'Copiado!';
    setTimeout(() => { btn.textContent = orig; }, 1500);
  });
}
</script>
@endpush
