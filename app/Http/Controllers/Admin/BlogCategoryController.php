<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogCategoryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin.blog');

        $categories = BlogCategory::orderBy('sort_order')->orderBy('name')
            ->withCount('posts')
            ->get();

        // categoria em edição (via ?edit=ID)
        $editing = $request->filled('edit')
            ? BlogCategory::find($request->input('edit'))
            : null;

        return view('admin.blog.categories.index', compact('categories', 'editing'));
    }

    public function store(Request $request)
    {
        $this->authorize('admin.blog');
        $data = $this->validateData($request);

        $cat = new BlogCategory();
        $this->fill($cat, $data);
        $cat->save();

        return redirect()
            ->route('admin.blog.categories.index')
            ->with('success', 'Categoria criada.');
    }

    public function update(Request $request, BlogCategory $category)
    {
        $this->authorize('admin.blog');
        $data = $this->validateData($request, $category);

        $this->fill($category, $data);
        $category->save();

        return redirect()
            ->route('admin.blog.categories.index')
            ->with('success', 'Categoria atualizada.');
    }

    public function destroy(BlogCategory $category)
    {
        $this->authorize('admin.blog');

        // não apaga se houver posts vinculados (evita órfãos)
        if ($category->posts()->exists()) {
            return back()->with('warning', 'Há posts nesta categoria. Mova-os antes de excluir.');
        }

        $category->delete();
        return redirect()
            ->route('admin.blog.categories.index')
            ->with('success', 'Categoria excluída.');
    }

    private function validateData(Request $request, ?BlogCategory $category = null): array
    {
        $id = $category?->id ?? 'NULL';
        return $request->validate([
            'name'           => ['required', 'string', 'max:150'],
            'slug'           => ['nullable', 'string', 'max:180', "unique:blog_categories,slug,{$id}"],
            'description'    => ['nullable', 'string', 'max:500'],
            'minisserie_url' => ['nullable', 'url', 'max:500'],
            'color'          => ['nullable', 'string', 'max:20'],
            'sort_order'     => ['nullable', 'integer'],
        ]);
    }

    private function fill(BlogCategory $cat, array $data): void
    {
        $cat->name           = $data['name'];
        $cat->slug           = ! empty($data['slug']) ? Str::slug($data['slug']) : ($cat->slug ?: null);
        $cat->description    = $data['description'] ?? null;
        $cat->minisserie_url = $data['minisserie_url'] ?? null;
        $cat->color          = $data['color'] ?? null;
        $cat->sort_order     = $data['sort_order'] ?? 0;
        // slug vazio -> model gera no saving
    }
}
