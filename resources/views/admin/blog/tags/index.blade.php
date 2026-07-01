@extends('layouts.admin')
@section('title', 'Tags do Blog')
@section('section', 'Blog')

@section('content')
@include('admin.blog._field-styles')
<div class="page">

  <div style="margin-bottom:20px;">
    <h1 style="font-family:var(--font-display);font-weight:800;font-size:22px;color:#fff;margin:0;">Tags</h1>
    <p style="color:var(--fg-4);font-size:13px;margin:4px 0 0;">Conectam artigos de clusters diferentes pelo mesmo assunto.</p>
  </div>

  @if(session('success'))
    <div style="padding:12px 16px;background:rgba(43,217,161,0.10);border:1px solid rgba(43,217,161,0.35);border-radius:var(--r-md);color:#6FE6BD;font-size:13px;font-weight:500;margin-bottom:16px;">✓ {{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div style="padding:12px 16px;background:rgba(255,92,122,0.10);border:1px solid rgba(255,92,122,0.35);border-radius:var(--r-md);color:#FF5C7A;font-size:13px;margin-bottom:16px;"><ul style="margin:0;padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  <div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start;">

    <div class="card" style="padding:0;overflow:hidden;">
      <table style="width:100%;border-collapse:collapse;font-size:13.5px;">
        <thead>
          <tr style="text-align:left;color:var(--fg-4);font-size:12px;">
            <th style="padding:12px 16px;font-weight:600;">Tag</th>
            <th style="padding:12px 16px;font-weight:600;text-align:center;">Posts</th>
            <th style="padding:12px 16px;font-weight:600;text-align:right;">Ações</th>
          </tr>
        </thead>
        <tbody>
          @forelse($tags as $t)
          <tr style="border-top:1px solid var(--line-1);">
            <td style="padding:12px 16px;">
              <div style="color:#fff;font-weight:500;">#{{ $t->name }}</div>
              <div style="color:var(--fg-4);font-size:11px;">/tag/{{ $t->slug }}</div>
            </td>
            <td style="padding:12px 16px;color:var(--fg-3);text-align:center;">{{ $t->posts_count }}</td>
            <td style="padding:12px 16px;text-align:right;white-space:nowrap;">
              <a href="{{ route('admin.blog.tags.index', ['edit' => $t->id]) }}" style="color:var(--brand-300);text-decoration:none;margin-right:12px;">Editar</a>
              <form action="{{ route('admin.blog.tags.destroy', $t) }}" method="POST" style="display:inline;" onsubmit="return confirm('Excluir esta tag?')">
                @csrf @method('DELETE')
                <button type="submit" style="background:none;border:none;color:#FF5C7A;cursor:pointer;font-size:13.5px;padding:0;">Excluir</button>
              </form>
            </td>
          </tr>
          @empty
          <tr><td colspan="3" style="padding:40px;text-align:center;color:var(--fg-4);">Nenhuma tag ainda. Elas também nascem sozinhas ao salvar um post.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="card" style="padding:20px;">
      <h2 style="font-family:var(--font-display);font-weight:700;font-size:15px;color:#fff;margin:0 0 16px;">{{ $editing ? 'Editar tag' : 'Nova tag' }}</h2>
      <form action="{{ $editing ? route('admin.blog.tags.update', $editing) : route('admin.blog.tags.store') }}" method="POST" style="display:flex;flex-direction:column;gap:14px;">
        @csrf
        @if($editing) @method('PUT') @endif
        <div>
          <label class="field-label">Nome *</label>
          <input type="text" name="name" class="field-input" value="{{ old('name', $editing->name ?? '') }}" required>
        </div>
        <div>
          <label class="field-label">Slug</label>
          <input type="text" name="slug" class="field-input" value="{{ old('slug', $editing->slug ?? '') }}" placeholder="gerado do nome">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">{{ $editing ? 'Salvar' : 'Adicionar' }}</button>
        @if($editing)
          <a href="{{ route('admin.blog.tags.index') }}" class="btn btn-ghost" style="width:100%;justify-content:center;display:flex;text-decoration:none;">Cancelar edição</a>
        @endif
      </form>
    </div>

  </div>
</div>
@endsection
