@extends('layouts.app')
@section('title', 'Perfil — Unyflex Digital')

@section('content')
<div class="scroll">

    <div class="page-head">
        <div>
            <div class="eyebrow">Conta</div>
            <h1>Perfil</h1>
        </div>
    </div>

    @if(session('success'))
        <div style="padding:14px 18px; background:rgba(43,217,161,0.12); border:1px solid rgba(43,217,161,0.35); border-radius:var(--r-md); color:var(--success); margin-bottom:24px; font-weight:600;">
            {{ session('success') }}
        </div>
    @endif

    <div style="max-width:640px;">

        {{-- Card do aluno --}}
        <div style="display:flex; align-items:center; gap:20px; padding:20px 24px; background:var(--bg-2); border:1px solid var(--line-2); border-radius:var(--r-lg); margin-bottom:28px;">
            <div style="width:56px; height:56px; border-radius:50%; background:var(--grad-brand); color:var(--fg-on-brand); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:18px; flex:0 0 56px;">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}{{ strtoupper(substr(explode(' ', auth()->user()->name ?? 'U X')[1] ?? 'X', 0, 1)) }}
            </div>
            <div>
                <h3 style="color:#fff; margin:0 0 4px; font-size:18px;">{{ auth()->user()->name ?? 'Usuário' }}</h3>
                <p style="color:var(--fg-3); margin:0; font-size:13px;">{{ auth()->user()->email ?? '' }}</p>
                <span style="display:inline-flex; margin-top:6px; padding:4px 10px; border-radius:var(--r-pill); background:rgba(0,163,255,0.10); border:1px solid rgba(0,163,255,0.25); font-size:11px; font-weight:600; color:var(--brand-300); letter-spacing:0.06em;">
                    {{ auth()->user()->role ?? 'Servidor público' }}
                </span>
            </div>
        </div>

        {{-- Formulário de dados --}}
        <form action="{{ route('perfil.update') }}" method="POST" style="display:flex; flex-direction:column; gap:16px;">
            @csrf
            <div style="display:flex; flex-direction:column; gap:6px;">
                <label style="font-size:12px; font-weight:600; letter-spacing:0.08em; text-transform:uppercase; color:var(--fg-3);">Nome completo</label>
                <input type="text" name="name" value="{{ old('name', auth()->user()->name ?? '') }}"
                    style="width:100%; padding:11px 14px; background:var(--bg-2); border:1px solid var(--line-2); border-radius:var(--r-sm); color:var(--fg-2); font-family:var(--font-body); font-size:14px; outline:none;">
            </div>
            <div style="display:flex; flex-direction:column; gap:6px;">
                <label style="font-size:12px; font-weight:600; letter-spacing:0.08em; text-transform:uppercase; color:var(--fg-3);">E-mail</label>
                <input type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}"
                    style="width:100%; padding:11px 14px; background:var(--bg-2); border:1px solid var(--line-2); border-radius:var(--r-sm); color:var(--fg-2); font-family:var(--font-body); font-size:14px; outline:none;">
            </div>
            <div style="display:flex; flex-direction:column; gap:6px;">
                <label style="font-size:12px; font-weight:600; letter-spacing:0.08em; text-transform:uppercase; color:var(--fg-3);">Cargo</label>
                <input type="text" name="cargo" value="{{ old('cargo') }}"
                    style="width:100%; padding:11px 14px; background:var(--bg-2); border:1px solid var(--line-2); border-radius:var(--r-sm); color:var(--fg-2); font-family:var(--font-body); font-size:14px; outline:none;">
            </div>
            <div style="display:flex; gap:12px; margin-top:4px;">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="save" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
                    <span>Salvar alterações</span>
                </button>
                <a href="{{ route('dashboard') }}" class="btn btn-ghost">Cancelar</a>
            </div>
        </form>

        {{-- Alterar senha --}}
        <div style="margin-top:36px; padding-top:28px; border-top:1px solid var(--line-1);">
            <h4 style="color:#fff; margin:0 0 16px;">Alterar senha</h4>
            <form action="{{ route('perfil.update') }}" method="POST" style="display:flex; flex-direction:column; gap:16px;">
                @csrf
                <input type="hidden" name="action" value="password">
                <div style="display:flex; flex-direction:column; gap:6px;">
                    <label style="font-size:12px; font-weight:600; letter-spacing:0.08em; text-transform:uppercase; color:var(--fg-3);">Senha atual</label>
                    <input type="password" name="password_current" style="width:100%; padding:11px 14px; background:var(--bg-2); border:1px solid var(--line-2); border-radius:var(--r-sm); color:var(--fg-2); font-family:var(--font-body); font-size:14px; outline:none;">
                </div>
                <div style="display:flex; flex-direction:column; gap:6px;">
                    <label style="font-size:12px; font-weight:600; letter-spacing:0.08em; text-transform:uppercase; color:var(--fg-3);">Nova senha</label>
                    <input type="password" name="password" style="width:100%; padding:11px 14px; background:var(--bg-2); border:1px solid var(--line-2); border-radius:var(--r-sm); color:var(--fg-2); font-family:var(--font-body); font-size:14px; outline:none;">
                </div>
                <div style="display:flex; flex-direction:column; gap:6px;">
                    <label style="font-size:12px; font-weight:600; letter-spacing:0.08em; text-transform:uppercase; color:var(--fg-3);">Confirmar nova senha</label>
                    <input type="password" name="password_confirmation" style="width:100%; padding:11px 14px; background:var(--bg-2); border:1px solid var(--line-2); border-radius:var(--r-sm); color:var(--fg-2); font-family:var(--font-body); font-size:14px; outline:none;">
                </div>
                <div>
                    <button type="submit" class="btn btn-secondary">
                        <i data-lucide="lock" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
                        <span>Atualizar senha</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
input:focus { border-color: var(--brand-500) !important; box-shadow: 0 0 0 3px rgba(0,163,255,0.18) !important; }
</style>
@endpush
