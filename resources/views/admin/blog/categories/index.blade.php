@extends('layouts.admin')
@section('title', 'Categorias do Blog')
@section('section', 'Blog')

@section('content')
@include('admin.blog._field-styles')
<div class="page">

  <div style="margin-bottom:20px;">
    <h1 style="font-family:var(--font-display);font-weight:800;font-size:22px;color:#fff;margin:0;">Categorias</h1>
    <p style="color:var(--fg-4);font-size:13px;margin:4px 0 0;">Os clusters do blog. Cada um aponta para a minissérie do CTA.</p>
  </div>

  @if(session('success'))
    <div style="padding:12px 16px;background:rgba(43,217,161,0.10);border:1px solid rgba(43,217,161,0.35);border-radius:var(--r-md);color:#6FE6BD;font-size:13px;font-weight:500;margin-bottom:16px;">✓ {{ session('success') }}</div>
  @endif
  @if(session('warning'))
    <div style="padding:12px 16px;background:rgba(255,181,71,0.10);border:1px solid rgba(255,181,71,0.35);border-radius:var(--r-md);color:#FFB547;font-size:13px;margin-bottom:16px;">{{ session('warning') }}</div>
  @endif
  @if($errors->any())
    <div style="padding:12px 16px;background:rgba(255,92,122,0.10);border:1px solid rgba(255,92,122,0.35);border-radius:var(--r-md);color:#FF5C7A;font-size:13px;margin-bottom:16px;"><ul style="margin:0;padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  <div style="display:grid;grid-template-columns:1fr 330px;gap:20px;align-items:start;">

    {{-- Tabela --}}
    <div class="card" style="padding:0;overflow:hidden;">
      <table style="width:100%;border-collapse:collapse;font-size:13.5px;">
        <thead>
          <tr style="text-align:left;color:var(--fg-4);font-size:12px;">
            <th style="padding:12px 16px;font-weight:600;">Categoria</th>
            <th style="padding:12px 16px;font-weight:600;text-align:center;">Posts</th>
            <th style="padding:12px 16px;font-weight:600;text-align:right;">Ações</th>
          </tr>
        </thead>
        <tbody>
          @forelse($categories as $c)
          <tr style="border-top:1px solid var(--line-1);">
            <td style="padding:12px 16px;">
              <div style="display:flex;align-items:center;gap:8px;">
                <span style="width:10px;height:10px;border-radius:50%;background:{{ $c->color ?: 'var(--brand-500)' }};flex:none;"></span>
                <div>
                  <div style="color:#fff;font-weight:500;">{{ $c->name }}</div>
                  <div style="color:var(--fg-4);font-size:11px;">/categoria/{{ $c->slug }}</div>
                </div>
              </div>
            </td>
            <td style="padding:12px 16px;color:var(--fg-3);text-align:center;">{{ $c->posts_count }}</td>
            <td style="padding:12px 16px;text-align:right;white-space:nowrap;">
              <a href="{{ route('admin.blog.categories.index', ['edit' => $c->id]) }}" style="color:var(--brand-300);text-decoration:none;margin-right:12px;">Editar</a>
              <form action="{{ route('admin.blog.categories.destroy', $c) }}" method="POST" style="display:inline;" onsubmit="return confirm('Excluir esta categoria?')">
                @csrf @method('DELETE')
                <button type="submit" style="background:none;border:none;color:#FF5C7A;cursor:pointer;font-size:13.5px;padding:0;">Excluir</button>
              </form>
            </td>
          </tr>
          @empty
          <tr><td colspan="3" style="padding:40px;text-align:center;color:var(--fg-4);">Nenhuma categoria ainda.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Form criar/editar --}}
    <div class="card" style="padding:20px;">
      <h2 style="font-family:var(--font-display);font-weight:700;font-size:15px;color:#fff;margin:0 0 16px;">{{ $editing ? 'Editar categoria' : 'Nova categoria' }}</h2>
      <form action="{{ $editing ? route('admin.blog.categories.update', $editing) : route('admin.blog.categories.store') }}" method="POST" style="display:flex;flex-direction:column;gap:14px;">
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
        <div>
          <label class="field-label">Descrição</label>
          <textarea name="description" class="field-input" rows="2">{{ old('description', $editing->description ?? '') }}</textarea>
        </div>
        <div>
          <label class="field-label">URL da minissérie (CTA)</label>
          <input type="url" name="minisserie_url" class="field-input" value="{{ old('minisserie_url', $editing->minisserie_url ?? '') }}" placeholder="https://digital.unyflex.com.br/view/minisseries/...">
        </div>
        <div style="display:flex;gap:10px;">
          <div style="flex:1;">
            <label class="field-label">Cor</label>
            <input type="text" name="color" class="field-input" value="{{ old('color', $editing->color ?? '#00A3FF') }}">
          </div>
          <div style="width:90px;">
            <label class="field-label">Ordem</label>
            <input type="number" name="sort_order" class="field-input" value="{{ old('sort_order', $editing->sort_order ?? 0) }}">
          </div>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">{{ $editing ? 'Salvar' : 'Adicionar' }}</button>
        @if($editing)
          <a href="{{ route('admin.blog.categories.index') }}" class="btn btn-ghost" style="width:100%;justify-content:center;display:flex;text-decoration:none;">Cancelar edição</a>
        @endif
      </form>
    </div>

  </div>
</div>
@endsection
