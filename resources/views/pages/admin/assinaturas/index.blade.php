@extends('layouts.admin')
@section('title', 'Assinaturas')
@section('section', 'Operacional')

@section('content')
<div class="page">

  <div class="page-header">
    <div>
      <h1 class="page-title">Assinaturas</h1>
      <p class="page-subtitle">Acesso a todas as minisséries e cursos modulares · gestão manual</p>
    </div>
    <div class="page-actions">
      <a href="{{ route('admin.assinaturas.create') }}" class="btn btn-primary" style="text-decoration:none;">
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
        Nova assinatura
      </a>
    </div>
  </div>

  @if(session('success'))
    <div style="padding:12px 16px;background:rgba(43,217,161,0.10);border:1px solid rgba(43,217,161,0.35);border-radius:10px;color:#6FE6BD;font-size:13px;font-weight:500;margin-bottom:18px;">✓ {{ session('success') }}</div>
  @endif

  {{-- KPIs --}}
  <div style="display:flex;gap:14px;margin-bottom:22px;flex-wrap:wrap;">
    <div style="background:var(--bg-2,#0f1520);border:1px solid var(--line-1,#1e2836);border-radius:12px;padding:16px 20px;min-width:160px;">
      <div style="font-size:12px;color:var(--fg-4,#8A94A6);margin-bottom:4px;">Assinaturas ativas</div>
      <div style="font-size:26px;font-weight:700;color:#2BD9A1;">{{ $stats['vigentes'] }}</div>
    </div>
    <div style="background:var(--bg-2,#0f1520);border:1px solid var(--line-1,#1e2836);border-radius:12px;padding:16px 20px;min-width:160px;">
      <div style="font-size:12px;color:var(--fg-4,#8A94A6);margin-bottom:4px;">Total (histórico)</div>
      <div style="font-size:26px;font-weight:700;color:#fff;">{{ $stats['total'] }}</div>
    </div>
  </div>

  {{-- Busca --}}
  <form method="GET" style="margin-bottom:18px;display:flex;gap:8px;max-width:420px;">
    <input type="text" name="q" value="{{ $busca }}" placeholder="Buscar por nome, e-mail ou CPF"
           style="flex:1;padding:9px 12px;background:var(--bg-1,#0a0e15);border:1px solid var(--line-1,#1e2836);border-radius:8px;color:#fff;font-size:13px;">
    <button type="submit" class="btn">Buscar</button>
    @if($busca)<a href="{{ route('admin.assinaturas.index') }}" class="btn btn-ghost" style="text-decoration:none;">Limpar</a>@endif
  </form>

  @if($assinaturas->isEmpty())
    <div style="text-align:center;padding:50px 20px;color:var(--fg-4,#8A94A6);">
      <p style="font-size:14px;margin:0;">Nenhuma assinatura {{ $busca ? 'encontrada' : 'cadastrada' }}.</p>
    </div>
  @else
    <div style="overflow-x:auto;border:1px solid var(--line-1,#1e2836);border-radius:12px;">
      <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <thead>
          <tr style="background:var(--bg-2,#0f1520);text-align:left;color:var(--fg-4,#8A94A6);">
            <th style="padding:12px 16px;font-weight:600;">Aluno</th>
            <th style="padding:12px 16px;font-weight:600;">Plano</th>
            <th style="padding:12px 16px;font-weight:600;">Validade</th>
            <th style="padding:12px 16px;font-weight:600;">Estado</th>
            <th style="padding:12px 16px;font-weight:600;">Logins</th>
            <th style="padding:12px 16px;font-weight:600;text-align:right;">Ações</th>
          </tr>
        </thead>
        <tbody>
          @foreach($assinaturas as $a)
            @php $log = $logins->get($a->student_id); @endphp
            <tr style="border-top:1px solid var(--line-1,#1e2836);">
              <td style="padding:12px 16px;">
                <div style="color:#fff;font-weight:500;">{{ $a->student->name ?? '—' }}</div>
                <div style="color:var(--fg-4,#8A94A6);font-size:11px;">{{ $a->student->email ?? '' }}</div>
              </td>
              <td style="padding:12px 16px;color:var(--fg-2,#c9d1dc);">{{ $a->plano ?: '—' }}</td>
              <td style="padding:12px 16px;color:var(--fg-2,#c9d1dc);">
                {{ optional($a->end_date)->format('d/m/Y') }}
                @php $dias = $a->diasRestantes(); @endphp
                @if($a->status === 'ativo')
                  <div style="font-size:11px;color:{{ $dias < 0 ? '#FF5C7A' : ($dias <= 7 ? '#FFB547' : 'var(--fg-4,#8A94A6)') }};">
                    {{ $dias < 0 ? 'expirada há ' . abs($dias) . 'd' : ($dias . ' dias restantes') }}
                  </div>
                @endif
              </td>
              <td style="padding:12px 16px;">
                <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;background:{{ $a->estadoColor() }}1a;color:{{ $a->estadoColor() }};">{{ $a->estadoLabel() }}</span>
              </td>
              <td style="padding:12px 16px;color:var(--fg-2,#c9d1dc);">
                {{ $log->total ?? 0 }}
                @if($log && $log->ultimo)
                  <div style="font-size:11px;color:var(--fg-4,#8A94A6);">último: {{ \Illuminate\Support\Carbon::parse($log->ultimo)->format('d/m H:i') }}</div>
                @endif
              </td>
              <td style="padding:12px 16px;text-align:right;white-space:nowrap;">
                <a href="{{ route('admin.assinaturas.edit', $a->id) }}" class="btn btn-ghost" style="text-decoration:none;padding:6px 12px;font-size:12px;">Editar</a>
                @if($a->status === 'ativo')
                  <form action="{{ route('admin.assinaturas.cancel', $a->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Cancelar esta assinatura?');">
                    @csrf
                    <button type="submit" class="btn btn-ghost" style="padding:6px 12px;font-size:12px;color:#FF5C7A;">Cancelar</button>
                  </form>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div style="margin-top:18px;">{{ $assinaturas->links() }}</div>
  @endif

</div>
@endsection
