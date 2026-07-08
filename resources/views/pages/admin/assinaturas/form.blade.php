@extends('layouts.admin')
@section('title', $modo === 'create' ? 'Nova assinatura' : 'Editar assinatura')
@section('section', 'Operacional')

@section('content')
<div class="page">

  <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:12px;color:var(--fg-4,#8A94A6);">
    <a href="{{ route('admin.assinaturas.index') }}" style="color:var(--fg-4,#8A94A6);text-decoration:none;">Assinaturas</a>
    <span>/</span>
    <span style="color:var(--fg-2,#c9d1dc);">{{ $modo === 'create' ? 'Nova' : 'Editar' }}</span>
  </div>

  <h1 class="page-title" style="margin-bottom:22px;">{{ $modo === 'create' ? 'Nova assinatura' : 'Editar assinatura' }}</h1>

  @if($errors->any())
    <div style="padding:12px 16px;background:rgba(255,92,122,0.10);border:1px solid rgba(255,92,122,0.35);border-radius:10px;color:#FF5C7A;font-size:13px;margin-bottom:20px;">
      <strong>Corrija:</strong>
      <ul style="margin:8px 0 0;padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  @php
    $lbl = 'display:block;font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--fg-4,#8A94A6);margin-bottom:6px;';
    $inp = 'width:100%;padding:10px 12px;background:var(--bg-1,#0a0e15);border:1px solid var(--line-1,#1e2836);border-radius:8px;color:#fff;font-size:14px;';
  @endphp

  <form action="{{ $modo === 'create' ? route('admin.assinaturas.store') : route('admin.assinaturas.update', $assinatura->id) }}" method="POST" style="max-width:560px;">
    @csrf
    @if($modo === 'edit') @method('PUT') @endif

    <div style="background:var(--bg-2,#0f1520);border:1px solid var(--line-1,#1e2836);border-radius:12px;padding:22px;">

      @if($modo === 'create')
        <div style="margin-bottom:18px;">
          <label style="{{ $lbl }}">E-mail do aluno</label>
          <input type="email" name="email" value="{{ old('email') }}" style="{{ $inp }}" placeholder="aluno@exemplo.com" required>
          <p style="font-size:11px;color:var(--fg-4,#8A94A6);margin:6px 0 0;">O aluno já precisa ter cadastro/login. Buscamos pelo e-mail.</p>
        </div>
      @else
        <div style="margin-bottom:18px;">
          <label style="{{ $lbl }}">Aluno</label>
          <div style="{{ $inp }}background:var(--bg-3,#141b26);color:var(--fg-2,#c9d1dc);">{{ $assinatura->student->name ?? '—' }} · {{ $assinatura->student->email ?? '' }}</div>
        </div>
      @endif

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px;">
        <div>
          <label style="{{ $lbl }}">Início</label>
          <input type="date" name="start_date" value="{{ old('start_date', optional($assinatura->start_date)->format('Y-m-d')) }}" style="{{ $inp }}" required>
        </div>
        <div>
          <label style="{{ $lbl }}">Válida até</label>
          <input type="date" name="end_date" value="{{ old('end_date', optional($assinatura->end_date)->format('Y-m-d')) }}" style="{{ $inp }}" required>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:{{ $modo === 'edit' ? '1fr 1fr' : '1fr' }};gap:14px;margin-bottom:18px;">
        <div>
          <label style="{{ $lbl }}">Plano (opcional)</label>
          <input type="text" name="plano" value="{{ old('plano', $assinatura->plano) }}" style="{{ $inp }}" placeholder="Ex: Anual, Institucional">
        </div>
        @if($modo === 'edit')
          <div>
            <label style="{{ $lbl }}">Status</label>
            <select name="status" style="{{ $inp }}">
              <option value="ativo" {{ old('status', $assinatura->status) === 'ativo' ? 'selected' : '' }}>Ativo</option>
              <option value="cancelado" {{ old('status', $assinatura->status) === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
            </select>
          </div>
        @endif
      </div>

      <div>
        <label style="{{ $lbl }}">Observação (opcional)</label>
        <textarea name="observacao" rows="3" style="{{ $inp }}">{{ old('observacao', $assinatura->observacao) }}</textarea>
      </div>
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
      <a href="{{ route('admin.assinaturas.index') }}" class="btn btn-ghost" style="text-decoration:none;">Cancelar</a>
      <button type="submit" class="btn btn-primary">{{ $modo === 'create' ? 'Criar assinatura' : 'Salvar' }}</button>
    </div>
  </form>

</div>
@endsection
