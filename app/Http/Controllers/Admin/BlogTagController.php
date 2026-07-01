<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogTagController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin.blog');

        $tags = BlogTag::orderBy('name')->withCount('posts')->get();
        $editing = $request->filled('edit') ? BlogTag::find($request->input('edit')) : null;

        return view('admin.blog.tags.index', compact('tags', 'editing'));
    }

    public function store(Request $request)
    {
        $this->authorize('admin.blog');
        $data = $this->validateData($request);

        $tag = new BlogTag();
        $tag->name = $data['name'];
        $tag->slug = ! empty($data['slug']) ? Str::slug($data['slug']) : null;
        $tag->save();

        return redirect()->route('admin.blog.tags.index')->with('success', 'Tag criada.');
    }

    public function update(Request $request, BlogTag $tag)
    {
        $this->authorize('admin.blog');
        $data = $this->validateData($request, $tag);

        $tag->name = $data['name'];
        if (! empty($data['slug'])) {
            $tag->slug = Str::slug($data['slug']);
        }
        $tag->save();

        return redirect()->route('admin.blog.tags.index')->with('success', 'Tag atualizada.');
    }

    public function destroy(BlogTag $tag)
    {
        $this->authorize('admin.blog');
        $tag->posts()->detach();
        $tag->delete();

        return redirect()->route('admin.blog.tags.index')->with('success', 'Tag excluída.');
    }

    private function validateData(Request $request, ?BlogTag $tag = null): array
    {
        $id = $tag?->id ?? 'NULL';
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:150', "unique:blog_tags,slug,{$id}"],
        ]);
    }
}
