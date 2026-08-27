@extends('layouts.header')

@section('content')

@if(session('success'))
<div style="background:#E7F4EC;border-left:4px solid #2D8659;border-radius:0 8px 8px 0;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#15703D;display:flex;align-items:center;gap:8px;">
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 15.01 9 12.01"/></svg>
  {{ session('success') }}
</div>
@endif

{{-- Page header --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
  <div>
    <div style="font-size:11px;font-weight:600;letter-spacing:0.12em;text-transform:uppercase;color:#2D8659;margin-bottom:6px;">ARP — Gestão de Colaboradores</div>
    <h1 style="font-size:24px;font-weight:700;letter-spacing:-0.02em;color:#0F1A14;margin:0;">{{ $empresa->nome }}</h1>
  </div>
  <a href="{{ route('arp.colaboradores.create', $empresa->id) }}"
     style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#1F6B43;color:#fff;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
    Novo colaborador
  </a>
</div>

{{-- KPI Cards --}}
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:24px;">

  <div style="background:#fff;border:1px solid #ECF0EE;border-radius:12px;padding:16px 18px;border-top:3px solid #3B82F6;">
    <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;color:#6B7B72;margin-bottom:8px;">Colaboradores</div>
    <div style="font-size:28px;font-weight:800;letter-spacing:-0.02em;color:#0F1A14;line-height:1;">{{ $kpis['colaboradores'] }}</div>
    <div style="font-size:11px;color:#94A199;margin-top:4px;">cadastrados e ativos</div>
  </div>

  <div style="background:#fff;border:1px solid #ECF0EE;border-radius:12px;padding:16px 18px;border-top:3px solid #8B5CF6;">
    <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;color:#6B7B72;margin-bottom:8px;">Enviados</div>
    <div style="font-size:28px;font-weight:800;letter-spacing:-0.02em;color:#0F1A14;line-height:1;">{{ $kpis['enviados'] }}</div>
    <div style="font-size:11px;color:#94A199;margin-top:4px;">e-mail entregue</div>
  </div>

  <div style="background:#fff;border:1px solid #ECF0EE;border-radius:12px;padding:16px 18px;border-top:3px solid #10B981;">
    <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;color:#6B7B72;margin-bottom:8px;">Respondidos</div>
    <div style="font-size:28px;font-weight:800;letter-spacing:-0.02em;color:#10B981;line-height:1;">{{ $kpis['respondidos'] }}</div>
    <div style="font-size:11px;color:#94A199;margin-top:4px;">formulário preenchido</div>
  </div>

  <div style="background:#fff;border:1px solid #ECF0EE;border-radius:12px;padding:16px 18px;border-top:3px solid #F59E0B;">
    <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;color:#6B7B72;margin-bottom:8px;">Sem resposta</div>
    <div style="font-size:28px;font-weight:800;letter-spacing:-0.02em;color:#B45309;line-height:1;">{{ $kpis['sem_resposta'] }}</div>
    <div style="font-size:11px;color:#94A199;margin-top:4px;">recebeu mas não respondeu</div>
  </div>

  <div style="background:#fff;border:1px solid #ECF0EE;border-radius:12px;padding:16px 18px;border-top:3px solid #1F6B43;">
    <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;color:#6B7B72;margin-bottom:8px;">Taxa</div>
    <div style="font-size:28px;font-weight:800;letter-spacing:-0.02em;color:#1F6B43;line-height:1;">{{ $kpis['taxa'] }}%</div>
    <div style="font-size:11px;color:#94A199;margin-top:4px;">de conclusão</div>
  </div>

</div>

{{-- Progress bar --}}
@if($kpis['convidados'] > 0)
<div style="background:#fff;border:1px solid #ECF0EE;border-radius:12px;padding:16px 20px;margin-bottom:24px;">
  <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:8px;">
    <span style="font-weight:600;color:#0F1A14;">Progresso da pesquisa</span>
    <span style="color:#6B7B72;">{{ $kpis['respondidos'] }} / {{ $kpis['convidados'] }} respondidos</span>
  </div>
  <div style="height:8px;background:#ECF0EE;border-radius:4px;overflow:hidden;">
    <div style="height:100%;width:{{ $kpis['taxa'] }}%;background:linear-gradient(90deg,#2D8659,#5FB894);border-radius:4px;transition:width 0.8s ease;"></div>
  </div>
  <div style="display:flex;gap:16px;margin-top:10px;font-size:11.5px;color:#6B7B72;">
    <span style="display:flex;align-items:center;gap:4px;"><span style="width:8px;height:8px;border-radius:50%;background:#10B981;display:inline-block;"></span> {{ $kpis['respondidos'] }} responderam</span>
    <span style="display:flex;align-items:center;gap:4px;"><span style="width:8px;height:8px;border-radius:50%;background:#F59E0B;display:inline-block;"></span> {{ $kpis['sem_resposta'] }} receberam mas não responderam</span>
    @if($kpis['nao_enviados'] > 0)
    <span style="display:flex;align-items:center;gap:4px;"><span style="width:8px;height:8px;border-radius:50%;background:#EF4444;display:inline-block;"></span> {{ $kpis['nao_enviados'] }} aguardando envio</span>
    @endif
  </div>
</div>
@endif

{{-- Action panels --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:24px;">

  <div style="background:#fff;border:1px solid #ECF0EE;border-radius:12px;padding:20px;">
    <div style="font-size:13.5px;font-weight:600;color:#0F1A14;margin-bottom:4px;">📋 Importar em lote</div>
    <div style="font-size:12px;color:#6B7B72;margin-bottom:14px;">Cole uma lista de colaboradores (um por linha)</div>
    <form method="POST" action="{{ route('arp.colaboradores.importar', $empresa->id) }}">
      @csrf
      <textarea name="lista" rows="5"
        style="width:100%;padding:10px 12px;border:1px solid #DBE2DD;border-radius:8px;font-family:monospace;font-size:12px;resize:vertical;outline:none;color:#2A3D33;"
        placeholder="João Silva joao@email.com&#10;Maria Souza maria@email.com&#10;&#10;Ou CSV: Nome,email,cargo,setor&#10;Pedro Santos,pedro@email.com,Analista,TI"></textarea>
      <button type="submit" style="margin-top:10px;width:100%;padding:9px;background:#0F3D2A;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
        Importar colaboradores
      </button>
    </form>
  </div>

  <div style="background:#fff;border:1px solid #ECF0EE;border-radius:12px;padding:20px;display:flex;flex-direction:column;gap:10px;">
    <div style="font-size:13.5px;font-weight:600;color:#0F1A14;margin-bottom:4px;">✉️ Envio de convites</div>
    <div style="font-size:12px;color:#6B7B72;margin-bottom:6px;">Gerencie os envios de e-mail para esta pesquisa</div>

    <form method="POST" action="{{ route('arp.convites.disparar', $empresa->id) }}">
      @csrf
      <button type="submit" onclick="return confirm('Enviar e-mail para todos os colaboradores ativos?')"
        style="width:100%;padding:10px;background:#1F6B43;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2 11 13"/><path d="m22 2-7 20-4-9-9-4 20-7z"/></svg>
        Enviar para todos os ativos
      </button>
    </form>

    <form method="POST" action="{{ route('arp.convites.reenviar', $empresa->id) }}">
      @csrf
      <button type="submit" onclick="return confirm('Reenviar lembrete para quem ainda não respondeu?')"
        style="width:100%;padding:10px;background:#fff;color:#B45309;border:1px solid #FBBF24;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4.95"/></svg>
        Lembrete para sem resposta ({{ $kpis['sem_resposta'] }})
      </button>
    </form>

    <div style="padding:10px 12px;background:#F5F8F6;border-radius:8px;font-size:11.5px;color:#6B7B72;line-height:1.5;">
      💡 Os e-mails são enviados em segundo plano via fila. Verifique os logs caso algum envio falhe.
    </div>
  </div>

</div>

{{-- Collaborators table --}}
<div style="background:#fff;border:1px solid #ECF0EE;border-radius:12px;overflow:hidden;">

  <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #ECF0EE;">
    <span style="font-size:14px;font-weight:600;color:#0F1A14;">Colaboradores cadastrados</span>
    <div style="display:flex;align-items:center;gap:8px;background:#F5F8F6;border:1px solid #DBE2DD;border-radius:8px;padding:7px 12px;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94A199" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-3.5-3.5"/></svg>
      <input type="text" id="searchColabs" placeholder="Buscar por nome ou e-mail..."
        style="background:none;border:none;outline:none;font-size:13px;color:#2A3D33;width:200px;"
        onkeyup="filterTable()">
    </div>
  </div>

  @if($colaboradores->isEmpty())
  <div style="text-align:center;padding:48px;color:#6B7B72;">
    <div style="font-size:40px;margin-bottom:12px;">👥</div>
    <div style="font-size:15px;font-weight:600;margin-bottom:4px;color:#0F1A14;">Nenhum colaborador cadastrado</div>
    <div style="font-size:13px;">Importe uma lista ou adicione individualmente para começar.</div>
  </div>
  @else

  <div style="display:flex;border-bottom:1px solid #ECF0EE;">
    <button onclick="filterStatus('todos')" data-filter="todos"
      style="padding:10px 16px;border:none;background:none;font-size:12.5px;font-weight:600;color:#1F6B43;cursor:pointer;border-bottom:2px solid #1F6B43;">
      Todos ({{ $colaboradores->total() }})
    </button>
    <button onclick="filterStatus('enviado')" data-filter="enviado"
      style="padding:10px 16px;border:none;background:none;font-size:12.5px;font-weight:600;color:#6B7B72;cursor:pointer;border-bottom:2px solid transparent;">
      Enviados ({{ $kpis['enviados'] }})
    </button>
    <button onclick="filterStatus('respondido')" data-filter="respondido"
      style="padding:10px 16px;border:none;background:none;font-size:12.5px;font-weight:600;color:#6B7B72;cursor:pointer;border-bottom:2px solid transparent;">
      Respondidos ({{ $kpis['respondidos'] }})
    </button>
    <button onclick="filterStatus('pendente')" data-filter="pendente"
      style="padding:10px 16px;border:none;background:none;font-size:12.5px;font-weight:600;color:#6B7B72;cursor:pointer;border-bottom:2px solid transparent;">
      Aguardando envio ({{ $kpis['nao_enviados'] }})
    </button>
  </div>

  <table style="width:100%;border-collapse:collapse;" id="colabTable">
    <thead>
      <tr style="background:#F9FAFB;">
        <th style="padding:11px 18px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#6B7B72;border-bottom:1px solid #ECF0EE;">Colaborador</th>
        <th style="padding:11px 18px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#6B7B72;border-bottom:1px solid #ECF0EE;">Cargo / Setor</th>
        <th style="padding:11px 18px;text-align:center;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#6B7B72;border-bottom:1px solid #ECF0EE;">Status</th>
        <th style="padding:11px 18px;text-align:center;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#6B7B72;border-bottom:1px solid #ECF0EE;">Enviado em</th>
        <th style="padding:11px 18px;text-align:center;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#6B7B72;border-bottom:1px solid #ECF0EE;">Respondido em</th>
        <th style="padding:11px 18px;text-align:center;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#6B7B72;border-bottom:1px solid #ECF0EE;">Ações</th>
      </tr>
    </thead>
    <tbody>
      @foreach($colaboradores as $c)
      @php
        $convite = $c->conviteAtivo;
        $statusConvite = $convite?->status ?? 'sem_convite';
        $badgeMap = [
          'respondido'  => ['✅ Respondido',       '#E7F4EC', '#15703D'],
          'enviado'     => ['✉️ Enviado',           '#EEF2FF', '#4338CA'],
          'pendente'    => ['⏳ Aguardando envio',  '#FEF2F2', '#B91C1C'],
          'expirado'    => ['⏰ Expirado',          '#FFF7ED', '#B45309'],
          'sem_convite' => ['— Sem convite',        '#F9FAFB', '#6B7280'],
        ];
        [$label, $bg, $fg] = $badgeMap[$statusConvite] ?? ['—', '#F9FAFB', '#6B7280'];
      @endphp
      <tr style="border-bottom:1px solid #ECF0EE;transition:background 0.12s;"
          onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background='transparent'"
          data-status="{{ $statusConvite }}"
          data-search="{{ strtolower($c->nome . ' ' . $c->email) }}">
        <td style="padding:13px 18px;">
          <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#1F6B43,#0F3D2A);color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;flex-shrink:0;">
              {{ strtoupper(substr($c->nome, 0, 1)) }}
            </div>
            <div>
              <div style="font-size:14px;font-weight:600;color:#0F1A14;">{{ $c->nome }}</div>
              <div style="font-size:12px;color:#6B7B72;">{{ $c->email }}</div>
            </div>
          </div>
        </td>
        <td style="padding:13px 18px;font-size:13px;color:#4A5D53;">
          {{ $c->funcao_doc ?? '—' }}
          @if($c->setor_doc)<span style="color:#94A199;"> · {{ $c->setor_doc }}</span>@endif
        </td>
        <td style="padding:13px 18px;text-align:center;">
          <span style="display:inline-block;padding:3px 10px;border-radius:20px;background:{{ $bg }};color:{{ $fg }};font-size:11.5px;font-weight:600;">
            {{ $label }}
          </span>
        </td>
        <td style="padding:13px 18px;text-align:center;font-size:12px;color:#6B7B72;">
          {{ $convite?->enviado_em?->format('d/m/Y H:i') ?? '—' }}
        </td>
        <td style="padding:13px 18px;text-align:center;font-size:12px;color:#6B7B72;">
          {{ $convite?->respondido_em?->format('d/m/Y H:i') ?? '—' }}
        </td>
        <td style="padding:13px 18px;text-align:center;">
          <div style="display:flex;align-items:center;justify-content:center;gap:6px;flex-wrap:wrap;">

            {{-- Editar --}}
            <a href="{{ route('arp.colaboradores.edit', $c->id) }}" title="Editar colaborador"
              style="padding:5px 10px;background:#EFF6FF;border:1px solid #93C5FD;border-radius:6px;font-size:12px;color:#1D4ED8;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              Editar
            </a>

            {{-- Envio individual (apenas se não respondeu) --}}
            @if($statusConvite !== 'respondido')
            <form method="POST" action="{{ route('arp.colaboradores.enviar', $c->id) }}" style="display:inline;">
              @csrf
              <button type="submit" title="Enviar e-mail agora"
                style="padding:5px 10px;background:#E7F4EC;border:1px solid #5FB894;border-radius:6px;font-size:12px;cursor:pointer;color:#15703D;display:inline-flex;align-items:center;gap:4px;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2 11 13"/><path d="m22 2-7 20-4-9-9-4 20-7z"/></svg>
                Enviar
              </button>
            </form>
            @endif

            {{-- Link --}}
            <button onclick="copiarLink({{ $c->id }}, this)" title="Copiar link"
              style="padding:5px 10px;background:#F5F8F6;border:1px solid #DBE2DD;border-radius:6px;font-size:12px;cursor:pointer;color:#2A3D33;display:inline-flex;align-items:center;gap:4px;">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
              Link
            </button>

            {{-- Ativar/Inativar --}}
            <form method="POST" action="{{ route('arp.colaboradores.status', $c->id) }}" style="display:inline;">
              @csrf @method('PATCH')
              <button type="submit"
                style="padding:5px 10px;background:{{ $c->status === 'ativo' ? '#FEF2F2' : '#E7F4EC' }};border:1px solid {{ $c->status === 'ativo' ? '#FCA5A5' : '#5FB894' }};border-radius:6px;font-size:11px;cursor:pointer;color:{{ $c->status === 'ativo' ? '#B91C1C' : '#15703D' }};">
                {{ $c->status === 'ativo' ? 'Inativar' : 'Ativar' }}
              </button>
            </form>

            {{-- Excluir --}}
            <form method="POST" action="{{ route('arp.colaboradores.destroy', $c->id) }}" style="display:inline;">
              @csrf @method('DELETE')
              <button type="submit" onclick="return confirm('Remover {{ addslashes($c->nome) }}?')"
                style="padding:5px 8px;background:#FEF2F2;border:1px solid #FCA5A5;border-radius:6px;cursor:pointer;color:#B91C1C;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
              </button>
            </form>
          </div>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div style="padding:14px 20px;border-top:1px solid #ECF0EE;display:flex;align-items:center;justify-content:space-between;">
    <span style="font-size:12.5px;color:#6B7B72;">{{ $colaboradores->total() }} colaborador(es) no total</span>
    {{ $colaboradores->links() }}
  </div>
  @endif
</div>

@push('custom-scripts')
<script>
function filterTable() {
    const q = document.getElementById('searchColabs').value.toLowerCase().trim();
    document.querySelectorAll('#colabTable tbody tr').forEach(row => {
        row.style.display = !q || row.dataset.search?.includes(q) ? '' : 'none';
    });
}

function filterStatus(status) {
    document.querySelectorAll('[data-filter]').forEach(btn => {
        const active = btn.dataset.filter === status;
        btn.style.borderBottomColor = active ? '#1F6B43' : 'transparent';
        btn.style.color = active ? '#1F6B43' : '#6B7B72';
    });
    document.querySelectorAll('#colabTable tbody tr').forEach(row => {
        row.style.display = (status === 'todos' || row.dataset.status === status) ? '' : 'none';
    });
}

async function copiarLink(colaboradorId, btn) {
    try {
        const res = await fetch(`/arp/colaboradores/${colaboradorId}/link`);
        const data = await res.json();
        await navigator.clipboard.writeText(data.url);
        const orig = btn.innerHTML;
        btn.innerHTML = '✓ Copiado!';
        btn.style.background = '#E7F4EC';
        btn.style.color = '#15703D';
        setTimeout(() => { btn.innerHTML = orig; btn.style.background = ''; btn.style.color = ''; }, 2000);
    } catch(e) { alert('Erro ao copiar link.'); }
}
</script>
@endpush

@endsection
