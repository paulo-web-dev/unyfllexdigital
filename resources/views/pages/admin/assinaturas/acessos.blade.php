@extends('layouts.admin')
@section('title', 'Acessos do aluno')
@section('section', 'Operacional')

@section('content')
<div class="page">

  <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:12px;color:var(--fg-4,#8A94A6);">
    <a href="{{ route('admin.assinaturas.index') }}" style="color:var(--fg-4,#8A94A6);text-decoration:none;">Assinaturas</a>
    <span>/</span>
    <span style="color:var(--fg-2,#c9d1dc);">Acessos</span>
  </div>

  <div style="margin-bottom:22px;">
    <h1 class="page-title" style="margin:0;">{{ $assinatura->student->name ?? 'Aluno' }}</h1>
    <p class="page-subtitle" style="margin:6px 0 0;">
      {{ $assinatura->student->email ?? '' }}
      · assinatura {{ strtolower($assinatura->estadoLabel()) }}
      @if($assinatura->end_date) · válida até {{ $assinatura->end_date->format('d/m/Y') }} @endif
    </p>
  </div>

  {{-- KPIs --}}
  <div style="display:flex;gap:14px;margin-bottom:26px;flex-wrap:wrap;">
    <div style="background:var(--bg-2,#0f1520);border:1px solid var(--line-1,#1e2836);border-radius:12px;padding:16px 20px;min-width:170px;">
      <div style="font-size:12px;color:var(--fg-4,#8A94A6);margin-bottom:4px;">Total de logins</div>
      <div style="font-size:26px;font-weight:700;color:#fff;">{{ $totalLogins }}</div>
    </div>
    <div style="background:var(--bg-2,#0f1520);border:1px solid var(--line-1,#1e2836);border-radius:12px;padding:16px 20px;min-width:170px;">
      <div style="font-size:12px;color:var(--fg-4,#8A94A6);margin-bottom:4px;">Cursos acessados</div>
      <div style="font-size:26px;font-weight:700;color:#00a3ff;">{{ $cursos->count() }}</div>
    </div>
    <div style="background:var(--bg-2,#0f1520);border:1px solid var(--line-1,#1e2836);border-radius:12px;padding:16px 20px;min-width:200px;">
      <div style="font-size:12px;color:var(--fg-4,#8A94A6);margin-bottom:4px;">Último login</div>
      <div style="font-size:16px;font-weight:600;color:#fff;padding-top:6px;">{{ optional($logins->first())->created_at?->format('d/m/Y H:i') ?? '—' }}</div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1.3fr 1fr;gap:20px;align-items:start;">

    {{-- Cursos acessados --}}
    <div>
      <h2 style="font-size:15px;font-weight:700;color:#fff;margin:0 0 12px;">Cursos acessados</h2>
      @if($cursos->isEmpty())
        <p style="color:var(--fg-4,#8A94A6);font-size:13px;">Este aluno ainda não abriu nenhum curso.</p>
      @else
        <div style="border:1px solid var(--line-1,#1e2836);border-radius:12px;overflow:hidden;">
          <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
              <tr style="background:var(--bg-2,#0f1520);text-align:left;color:var(--fg-4,#8A94A6);">
                <th style="padding:10px 14px;font-weight:600;">Curso</th>
                <th style="padding:10px 14px;font-weight:600;">Acessos</th>
                <th style="padding:10px 14px;font-weight:600;">Último</th>
              </tr>
            </thead>
            <tbody>
              @foreach($cursos as $c)
                <tr style="border-top:1px solid var(--line-1,#1e2836);">
                  <td style="padding:10px 14px;color:#fff;">{{ $c->detail }}</td>
                  <td style="padding:10px 14px;color:var(--fg-2,#c9d1dc);">{{ $c->vezes }}</td>
                  <td style="padding:10px 14px;color:var(--fg-2,#c9d1dc);">{{ \Illuminate\Support\Carbon::parse($c->ultimo)->format('d/m H:i') }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>

    {{-- Últimos logins --}}
    <div>
      <h2 style="font-size:15px;font-weight:700;color:#fff;margin:0 0 12px;">Logins recentes</h2>
      @if($logins->isEmpty())
        <p style="color:var(--fg-4,#8A94A6);font-size:13px;">Nenhum login registrado ainda.</p>
      @else
        <div style="border:1px solid var(--line-1,#1e2836);border-radius:12px;overflow:hidden;">
          <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
              <tr style="background:var(--bg-2,#0f1520);text-align:left;color:var(--fg-4,#8A94A6);">
                <th style="padding:10px 14px;font-weight:600;">Data</th>
                <th style="padding:10px 14px;font-weight:600;">IP</th>
              </tr>
            </thead>
            <tbody>
              @foreach($logins as $l)
                <tr style="border-top:1px solid var(--line-1,#1e2836);">
                  <td style="padding:10px 14px;color:#fff;">{{ optional($l->created_at)->format('d/m/Y H:i') }}</td>
                  <td style="padding:10px 14px;color:var(--fg-4,#8A94A6);">{{ $l->ip ?: '—' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <p style="font-size:11px;color:var(--fg-4,#8A94A6);margin-top:8px;">Mostrando os últimos {{ $logins->count() }} de {{ $totalLogins }} logins.</p>
      @endif
    </div>

  </div>

</div>
@endsection
