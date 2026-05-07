@extends('layouts.site')
@section('meta_title', 'Patrimônio e Frotas Públicas com I.A. — Unyflex Digital')

@section('content')

<div style="padding-top:72px; min-height:100vh;">

  {{-- Header do curso (fora do player) --}}
  <div style="background:var(--bg-1);border-bottom:1px solid var(--line-1);padding:16px 0;">
    <div class="container">
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <a href="{{ route('cursos') }}" class="btn-ux btn-ux-ghost btn-ux-sm">
          <i data-lucide="chevron-left" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;"></i>
          Cursos
        </a>
        <span style="color:var(--fg-4);">/</span>
        <span style="font-size:12px;font-weight:600;letter-spacing:0.12em;text-transform:uppercase;color:var(--brand-300);">Patrimônio e Frotas Públicas com I.A.</span>
        <span style="color:var(--fg-4);">·</span>
        <span style="font-size:12px;color:var(--fg-3);">Temporada 1 · Cápsula 4 de 12</span>
        <div style="margin-left:auto;display:flex;align-items:center;gap:10px;">
          <div style="display:flex;align-items:center;gap:8px;">
            <div style="width:80px;height:4px;background:rgba(255,255,255,0.1);border-radius:2px;overflow:hidden;">
              <div style="height:100%;width:30%;background:var(--grad-brand);"></div>
            </div>
            <span style="font-family:var(--font-mono);font-size:11px;color:var(--brand-300);">30%</span>
          </div>
          <button class="btn-ux btn-ux-primary btn-ux-sm">
            <i data-lucide="award" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
            Certificado
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- Layout player --}}
  <div style="display:grid;grid-template-columns:1fr 360px;min-height:calc(100vh - 120px);">

    {{-- COLUNA ESQUERDA --}}
    <div style="background:var(--bg-0);border-right:1px solid var(--line-1);overflow-y:auto;padding:28px 32px 48px;">

      {{-- Vídeo fake --}}
      <div style="position:relative;border-radius:var(--r-xl);overflow:hidden;background:radial-gradient(70% 100% at 50% 40%, rgba(0,163,255,0.18), transparent 65%),linear-gradient(180deg,#0A1428,#03060D);border:1px solid var(--line-2);box-shadow:var(--shadow-lg);aspect-ratio:16/9;margin-bottom:24px;">
        {{-- Header interno --}}
        <div style="position:absolute;top:16px;left:18px;right:18px;display:flex;align-items:center;gap:10px;">
          <div style="width:28px;height:28px;border-radius:50%;overflow:hidden;background:#000;box-shadow:0 0 0 1px rgba(0,163,255,0.3);">
            <img src="{{ asset('img/logo-unyflex.png') }}" alt="Unyflex" style="width:100%;height:100%;object-fit:cover;">
          </div>
          <div>
            <div style="color:#fff;font-weight:600;font-size:13px;">Trecho 1 — Controles Preventivos e Documentação</div>
            <div style="color:var(--fg-3);font-size:10px;font-family:var(--font-mono);">UNYFLEX DIGITAL</div>
          </div>
        </div>

        {{-- Play --}}
        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:84px;height:84px;border-radius:50%;background:var(--grad-brand);color:#061224;display:flex;align-items:center;justify-content:center;box-shadow:0 0 0 10px rgba(0,163,255,0.10),0 16px 60px -10px rgba(0,163,255,0.55);cursor:pointer;">
          <svg viewBox="0 0 24 24" style="width:28px;height:28px;fill:currentColor;margin-left:4px;"><polygon points="6 4 20 12 6 20 6 4"/></svg>
        </div>

        {{-- Controles --}}
        <div style="position:absolute;bottom:18px;left:18px;right:18px;display:flex;align-items:center;gap:12px;">
          <span style="font-family:var(--font-mono);font-size:12px;color:#fff;">05:31</span>
          <div style="flex:1;height:4px;background:rgba(255,255,255,0.14);border-radius:2px;cursor:pointer;position:relative;">
            <div style="height:100%;width:38%;background:var(--grad-brand);border-radius:2px;"></div>
            <div style="position:absolute;left:38%;top:50%;transform:translate(-50%,-50%);width:12px;height:12px;border-radius:50%;background:#fff;box-shadow:0 0 12px var(--brand-400);"></div>
          </div>
          <span style="font-family:var(--font-mono);font-size:12px;color:var(--fg-3);">14:32</span>
          <div style="display:flex;gap:6px;">
            @foreach([['zap','Velocidade'],['volume-2','Volume'],['maximize','Tela cheia']] as [$ic,$tt])
            <button title="{{ $tt }}" style="background:rgba(5,8,15,0.6);border:1px solid var(--line-2);border-radius:8px;width:32px;height:32px;display:flex;align-items:center;justify-content:center;color:#fff;cursor:pointer;">
              <i data-lucide="{{ $ic }}" style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2;"></i>
            </button>
            @endforeach
          </div>
        </div>
      </div>

      {{-- Info da aula --}}
      <div style="margin-bottom:24px;">
        <h2 style="font-family:var(--font-display);font-weight:800;font-size:clamp(20px,2.5vw,26px);color:#fff;letter-spacing:-0.02em;margin-bottom:10px;">1.4 Controles Preventivos e Documentação</h2>
        <div style="display:flex;gap:16px;flex-wrap:wrap;font-size:13px;color:var(--fg-3);margin-bottom:14px;">
          @foreach([['clock','14:32 minutos'],['file-text','3 materiais'],['brain-circuit','1 mapa mental']] as [$ic,$txt])
          <span style="display:inline-flex;align-items:center;gap:5px;">
            <i data-lucide="{{ $ic }}" style="width:13px;height:13px;stroke:var(--brand-300);fill:none;stroke-width:1.75;"></i>
            {{ $txt }}
          </span>
          @endforeach
        </div>
        <p style="color:var(--fg-2);font-size:15px;line-height:1.7;margin-bottom:18px;">
          Nesta cápsula você vai estruturar pontos de controle internos para o setor de patrimônio e gerar
          documentação auditável a partir dos modelos prontos da minisérie. Foco em aplicação imediata:
          ao final, você sai com um checklist preenchível e um modelo de relatório.
        </p>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
          <button class="btn-ux btn-ux-secondary">
            <i data-lucide="chevron-left" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;"></i>
            Aula anterior
          </button>
          <button class="btn-ux btn-ux-primary">
            Próxima aula
            <i data-lucide="chevron-right" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;"></i>
          </button>
          <button class="btn-ux btn-ux-ghost">
            <i data-lucide="bookmark" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
            Salvar
          </button>
          <button class="btn-ux btn-ux-ghost">
            <i data-lucide="download" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
            Materiais
          </button>
        </div>
      </div>

      {{-- Abas --}}
      <div class="player-tab-group" style="background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-lg);padding:6px;display:flex;gap:4px;margin-bottom:18px;">
        @foreach([['resumo','file-text','Resumo'],['mapa','brain-circuit','Mapa Mental'],['podcast','mic','Podcast'],['checklist','check-square','Checklist'],['comentarios','message-square','Comentários']] as [$id,$ic,$lbl])
        <button class="player-tab-btn {{ $id==='resumo'?'active':'' }}" data-tab="{{ $id }}" style="flex:1;padding:10px 8px;border-radius:10px;background:{{ $id==='resumo'?'var(--bg-3)':'transparent' }};border:none;cursor:pointer;color:{{ $id==='resumo'?'#fff':'var(--fg-3)' }};font-size:12px;font-weight:600;font-family:inherit;display:inline-flex;align-items:center;justify-content:center;gap:6px;transition:all 0.2s;">
          <i data-lucide="{{ $ic }}" style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
          {{ $lbl }}
        </button>
        @endforeach
      </div>

      {{-- Painel: Resumo --}}
      <div class="player-tab-panel" data-panel="resumo" style="background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-lg);padding:24px 28px;">
        <h4 style="font-family:var(--font-display);font-weight:700;color:#fff;font-size:18px;margin-bottom:12px;">Resumo da cápsula</h4>
        <p style="color:var(--fg-2);line-height:1.75;margin-bottom:14px;">Controles preventivos são pontos do processo onde a probabilidade de um erro virar problema é alta. Identificá-los exige mapear o fluxo do bem patrimonial — da entrada à baixa — e marcar os momentos em que falta documentação ou confirmação humana.</p>
        <p style="color:var(--fg-2);line-height:1.75;margin-bottom:14px;">A documentação auditável segue três princípios: <strong style="color:#fff;">rastreabilidade</strong> (quem, quando, com base em quê), <strong style="color:#fff;">imutabilidade</strong> (não se edita, se anexa correção) e <strong style="color:#fff;">recuperação</strong> (qualquer auditor encontra em &lt; 5 minutos).</p>
        <p style="color:var(--fg-4);font-size:13px;">Tempo de leitura: 4 min · Atualizado em 06/05/2026</p>
      </div>

      {{-- Painel: Mapa --}}
      <div class="player-tab-panel" data-panel="mapa" style="display:none;background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-lg);padding:24px 28px;">
        <h4 style="font-family:var(--font-display);font-weight:700;color:#fff;font-size:18px;margin-bottom:12px;">Mapa mental</h4>
        <div style="background:radial-gradient(50% 70% at 50% 50%, rgba(0,163,255,0.10), transparent 70%);border:1px dashed var(--line-2);border-radius:var(--r-md);padding:36px;text-align:center;min-height:200px;display:flex;flex-direction:column;align-items:center;justify-content:center;">
          <div style="font-family:var(--font-mono);font-size:11px;color:var(--brand-300);letter-spacing:0.14em;margin-bottom:8px;">MAPA MENTAL</div>
          <div style="font-family:var(--font-display);font-size:22px;color:#fff;font-weight:700;margin-bottom:20px;">Controles Preventivos</div>
          <div style="display:flex;gap:12px;flex-wrap:wrap;justify-content:center;margin-bottom:20px;">
            @foreach(['Rastreabilidade','Imutabilidade','Recuperação'] as $k)
            <div style="padding:10px 16px;background:var(--bg-3);border:1px solid rgba(0,163,255,0.3);border-radius:10px;font-size:13px;color:#fff;">{{ $k }}</div>
            @endforeach
          </div>
          <button class="btn-ux btn-ux-ghost btn-ux-sm">
            <i data-lucide="download" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
            Baixar PDF
          </button>
        </div>
      </div>

      {{-- Painel: Podcast --}}
      <div class="player-tab-panel" data-panel="podcast" style="display:none;background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-lg);padding:24px 28px;">
        <h4 style="font-family:var(--font-display);font-weight:700;color:#fff;font-size:18px;margin-bottom:12px;">Versão em áudio</h4>
        <div style="background:linear-gradient(135deg,rgba(0,163,255,0.10),transparent);border:1px solid var(--line-2);border-radius:var(--r-md);padding:20px;display:flex;align-items:center;gap:18px;">
          <div style="width:72px;height:72px;border-radius:14px;background:var(--grad-brand);display:flex;align-items:center;justify-content:center;color:#061224;box-shadow:0 12px 32px -8px rgba(0,163,255,0.5);flex-shrink:0;">
            <i data-lucide="mic" style="width:28px;height:28px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
          </div>
          <div style="flex:1;">
            <div style="font-size:10px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--brand-300);margin-bottom:4px;">Episódio 4 · 22 min</div>
            <div style="font-family:var(--font-display);font-weight:600;font-size:15px;color:#fff;margin-bottom:10px;">Controles Preventivos e Documentação</div>
            <div style="display:flex;align-items:center;gap:10px;">
              <div style="flex:1;height:4px;background:rgba(255,255,255,0.10);border-radius:2px;overflow:hidden;"><div style="height:100%;width:22%;background:var(--grad-brand);"></div></div>
              <span style="font-family:var(--font-mono);font-size:11px;color:var(--brand-200);">04:48 / 22:30</span>
            </div>
          </div>
          <button style="width:44px;height:44px;border-radius:50%;background:rgba(255,255,255,0.95);color:#0072FF;display:flex;align-items:center;justify-content:center;border:none;cursor:pointer;flex-shrink:0;">
            <svg viewBox="0 0 24 24" style="width:16px;height:16px;fill:currentColor;margin-left:2px;"><polygon points="6 4 20 12 6 20 6 4"/></svg>
          </button>
        </div>
      </div>

      {{-- Painel: Checklist --}}
      <div class="player-tab-panel" data-panel="checklist" style="display:none;background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-lg);padding:24px 28px;">
        <h4 style="font-family:var(--font-display);font-weight:700;color:#fff;font-size:18px;margin-bottom:8px;">Checklist da cápsula</h4>
        <p style="color:var(--fg-3);font-size:14px;margin-bottom:18px;">Marque conforme aplicar no seu setor.</p>
        <div style="display:flex;flex-direction:column;gap:10px;">
          @foreach([
            [true,'Mapeei o fluxo do bem patrimonial da entrada à baixa'],
            [true,'Identifiquei os 3 pontos críticos do meu setor'],
            [false,'Adicionei rastreabilidade (quem / quando / base) em cada ponto'],
            [false,'Defini formato imutável de registro (anexo, não edição)'],
            [false,'Testei recuperação: auditor encontra documento em < 5 min'],
          ] as [$done,$lbl])
          <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--bg-1);border:1px solid var(--line-1);border-radius:10px;">
            <div style="width:18px;height:18px;border-radius:5px;background:{{ $done ? 'var(--grad-brand)' : 'transparent' }};border:{{ $done ? 'none' : '1.5px solid var(--line-2)' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              @if($done)<i data-lucide="check" style="width:11px;height:11px;stroke:#061224;fill:none;stroke-width:3;"></i>@endif
            </div>
            <span style="font-size:14px;color:{{ $done ? 'var(--fg-3)' : 'var(--fg-2)' }};{{ $done ? 'text-decoration:line-through;text-decoration-color:rgba(255,255,255,0.2);' : '' }}">{{ $lbl }}</span>
          </div>
          @endforeach
        </div>
      </div>

      {{-- Painel: Comentários --}}
      <div class="player-tab-panel" data-panel="comentarios" style="display:none;background:var(--bg-2);border:1px solid var(--line-2);border-radius:var(--r-lg);padding:24px 28px;">
        <h4 style="font-family:var(--font-display);font-weight:700;color:#fff;font-size:18px;margin-bottom:18px;">Comentários <span style="font-size:14px;font-weight:500;color:var(--fg-3);">(12)</span></h4>
        <div style="display:flex;gap:10px;margin-bottom:24px;">
          <div style="width:36px;height:36px;border-radius:50%;background:var(--grad-brand);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;color:var(--fg-on-brand);flex-shrink:0;">MA</div>
          <textarea placeholder="Deixe seu comentário ou dúvida…" class="checkout-input" style="resize:none;height:80px;border-radius:var(--r-md);"></textarea>
        </div>
        @foreach([
          ['RC','Rafael C.','Pregoeiro · RJ','Excelente cápsula! A parte da imutabilidade foi um divisor de águas para nosso setor.','2h atrás'],
          ['PS','Patrícia S.','Auditora · TCU','Apliquei o checklist hoje mesmo. Achei 2 pontos críticos que nunca tinha percebido.','5h atrás'],
          ['TM','Thiago M.','Gestor · SP','Tem como exportar o checklist para PDF? Quero usar na reunião de equipe.','1 dia atrás'],
        ] as [$init,$nome,$cargo,$txt,$time])
        <div style="display:flex;gap:12px;padding:16px 0;border-bottom:1px solid var(--line-1);">
          <div style="width:36px;height:36px;border-radius:50%;background:var(--bg-4);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;color:var(--fg-2);flex-shrink:0;">{{ $init }}</div>
          <div style="flex:1;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
              <span style="font-weight:600;font-size:14px;color:#fff;">{{ $nome }}</span>
              <span style="font-size:11px;color:var(--fg-4);">{{ $cargo }}</span>
              <span style="font-size:11px;color:var(--fg-4);margin-left:auto;">{{ $time }}</span>
            </div>
            <p style="font-size:14px;color:var(--fg-2);line-height:1.6;margin:0;">{{ $txt }}</p>
          </div>
        </div>
        @endforeach
      </div>

    </div>

    {{-- SIDEBAR DE AULAS --}}
    <div style="background:var(--bg-1);border-left:1px solid var(--line-1);display:flex;flex-direction:column;overflow:hidden;height:calc(100vh - 120px);position:sticky;top:120px;">
      <div style="padding:18px 20px 14px;border-bottom:1px solid var(--line-1);">
        <div style="font-size:10px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;color:var(--brand-300);margin-bottom:4px;">Temporada 1</div>
        <h3 style="font-family:var(--font-display);font-weight:700;color:#fff;font-size:15px;line-height:1.3;margin-bottom:10px;">Levantamento de Infraestrutura e Recursos</h3>
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="flex:1;height:4px;background:rgba(255,255,255,0.07);border-radius:2px;overflow:hidden;"><div style="height:100%;width:30%;background:var(--grad-brand);"></div></div>
          <span style="font-family:var(--font-mono);font-size:11px;color:var(--brand-300);">3 / 10</span>
        </div>
      </div>

      <div style="overflow-y:auto;flex:1;padding:8px;">
        @php
        $lessons = [
          ['n'=>'1.1','title'=>'Introdução e Apresentação do Curso','dur'=>'12:48','done'=>true,'active'=>false],
          ['n'=>'1.2','title'=>'Fundamentos da Auditoria Automatizada','dur'=>'15:20','done'=>true,'active'=>false],
          ['n'=>'1.3','title'=>'Gestão por Riscos e Priorização','dur'=>'18:04','done'=>true,'active'=>false],
          ['n'=>'1.4','title'=>'Controles Preventivos e Documentação','dur'=>'14:32','done'=>false,'active'=>true],
          ['n'=>'1.5','title'=>'Cruzamento de Dados entre Setores','dur'=>'17:11','done'=>false,'active'=>false],
          ['n'=>'1.6','title'=>'Gestão de Pessoas e Desafios','dur'=>'13:45','done'=>false,'active'=>false],
          ['n'=>'1.7','title'=>'Integração com Sistemas de TI','dur'=>'16:08','done'=>false,'active'=>false],
          ['n'=>'1.8','title'=>'Indicadores de Performance','dur'=>'11:50','done'=>false,'active'=>false],
          ['n'=>'1.9','title'=>'Painéis e Visualização de Dados','dur'=>'19:22','done'=>false,'active'=>false],
          ['n'=>'1.10','title'=>'Revisão e Próximos Passos','dur'=>'09:36','done'=>false,'active'=>false],
        ];
        @endphp
        @foreach($lessons as $l)
        <div class="player-lesson-item {{ $l['active']?'active':'' }}" style="{{ $l['active']?'background:linear-gradient(90deg,rgba(0,163,255,0.12),transparent);border-left:2px solid var(--brand-400);':'' }}padding:12px;border-radius:10px;cursor:pointer;display:flex;align-items:flex-start;gap:10px;border-bottom:1px solid var(--line-1);">
          <div class="player-lesson-num {{ $l['done']?'done':'' }} {{ $l['active']?'active':'' }}">
            @if($l['done'])<i data-lucide="check" style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2.5;"></i>
            @elseif($l['active'])<svg viewBox="0 0 24 24" style="width:10px;height:10px;fill:currentColor;"><polygon points="6 4 20 12 6 20 6 4"/></svg>
            @else{{ $l['n'] }}@endif
          </div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:13px;color:{{ $l['active']?'#fff':($l['done']?'var(--fg-3)':'var(--fg-2)') }};font-weight:500;line-height:1.4;margin-bottom:3px;">{{ $l['n'] }} {{ $l['title'] }}</div>
            <div style="font-family:var(--font-mono);font-size:11px;color:var(--fg-4);">{{ $l['dur'] }}</div>
          </div>
        </div>
        @endforeach
      </div>

      {{-- Download de materiais --}}
      <div style="padding:16px;border-top:1px solid var(--line-1);">
        <button class="btn-ux btn-ux-ghost" style="width:100%;justify-content:center;font-size:13px;">
          <i data-lucide="download" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
          Baixar todos os materiais
        </button>
      </div>
    </div>

  </div>
</div>

@push('styles')
<style>
.player-tab-btn.active {
  background: var(--bg-3) !important;
  color: #fff !important;
  box-shadow: inset 0 0 0 1px rgba(0,163,255,0.3), 0 0 18px -8px rgba(0,163,255,0.5);
}
@media(max-width:991px) {
  div[style*="grid-template-columns:1fr 360px"] {
    display:block !important;
  }
}
</style>
@endpush

@endsection
