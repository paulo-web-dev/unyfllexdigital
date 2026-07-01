<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

class BlogPostController extends Controller
{
    private const DIR_BLOG = 'storage/blog';

    /** Lista de posts com filtro por status e busca. */
    public function index(Request $request)
    {
        $this->authorize('admin.blog');

        $status = $request->input('status');
        $q      = trim((string) $request->input('q'));

        $posts = BlogPost::with('category')
            ->when(in_array($status, ['rascunho', 'agendado', 'publicado'], true),
                   fn ($qb) => $qb->where('status', $status))
            ->when($q !== '', fn ($qb) => $qb->where('title', 'like', "%{$q}%"))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'todos'     => BlogPost::count(),
            'publicado' => BlogPost::where('status', 'publicado')->count(),
            'agendado'  => BlogPost::where('status', 'agendado')->count(),
            'rascunho'  => BlogPost::where('status', 'rascunho')->count(),
        ];

        return view('admin.blog.posts.index', compact('posts', 'status', 'q', 'counts'));
    }

    public function create()
    {
        $this->authorize('admin.blog');
        $categories = BlogCategory::orderBy('sort_order')->orderBy('name')->get();
        return view('admin.blog.posts.form', [
            'categories' => $categories,
            'post'       => null,
            'tagsValue'  => '',
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('admin.blog');
        $data = $this->validateData($request);

        $post = new BlogPost();
        $post->source = 'manual';
        $this->fill($post, $request, $data);
        $post->save();

        $this->syncTags($post, $request);

        return redirect()
            ->route('admin.blog.posts.edit', $post)
            ->with('success', 'Post criado com sucesso.');
    }

    public function edit(BlogPost $post)
    {
        $this->authorize('admin.blog');
        $categories = BlogCategory::orderBy('sort_order')->orderBy('name')->get();
        $tagsValue  = $post->tags->pluck('name')->implode(', ');

        return view('admin.blog.posts.form', compact('categories', 'post', 'tagsValue'));
    }

    public function update(Request $request, BlogPost $post)
    {
        $this->authorize('admin.blog');
        $data = $this->validateData($request, $post);

        $this->fill($post, $request, $data);
        $post->save();

        $this->syncTags($post, $request);

        return redirect()
            ->route('admin.blog.posts.edit', $post)
            ->with('success', 'Post atualizado.');
    }

    public function destroy(BlogPost $post)
    {
        $this->authorize('admin.blog');
        $this->apagarImagem($post);
        $post->tags()->detach();
        $post->delete();

        return redirect()
            ->route('admin.blog.posts.index')
            ->with('success', 'Post excluído.');
    }

    /** Pré-visualização: renderiza o post (mesmo não publicado) na view pública. */
    public function preview(BlogPost $post)
    {
        $this->authorize('admin.blog');
        $post->load(['category', 'tags']);

        $relacionados = BlogPost::published()
            ->where('blog_category_id', $post->blog_category_id)
            ->where('id', '!=', $post->id)
            ->with('category')
            ->limit(3)
            ->get();

        return view('blog.show', compact('post', 'relacionados'));
    }

    // ───────────────────────────── helpers ─────────────────────────────

    private function validateData(Request $request, ?BlogPost $post = null): array
    {
        return $request->validate([
            'title'              => ['required', 'string', 'max:255'],
            'slug'               => ['nullable', 'string', 'max:280'],
            'blog_category_id'   => ['nullable', 'integer', 'exists:blog_categories,id'],
            'excerpt'            => ['nullable', 'string', 'max:500'],
            'content'            => ['required', 'string'],
            'meta_title'         => ['nullable', 'string', 'max:255'],
            'meta_description'   => ['nullable', 'string', 'max:320'],
            'focus_keyword'      => ['nullable', 'string', 'max:180'],
            'secondary_keywords' => ['nullable', 'string', 'max:500'],
            'author'             => ['nullable', 'string', 'max:150'],
            'status'             => ['required', 'in:rascunho,agendado,publicado'],
            'published_at'       => ['nullable', 'date'],
            'featured'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'faq_q'              => ['nullable', 'array'],
            'faq_a'              => ['nullable', 'array'],
            'tags_input'         => ['nullable', 'string', 'max:500'],
        ]);
    }

    private function fill(BlogPost $post, Request $request, array $data): void
    {
        $post->title              = $data['title'];
        $post->blog_category_id   = $data['blog_category_id'] ?? null;
        $post->excerpt            = $data['excerpt'] ?? null;
        $post->content            = $data['content'];
        $post->meta_title         = $data['meta_title'] ?? null;
        $post->meta_description   = $data['meta_description'] ?? null;
        $post->focus_keyword      = $data['focus_keyword'] ?? null;
        $post->secondary_keywords = $data['secondary_keywords'] ?? null;
        $post->author             = $data['author'] ?? optional($request->user())->name;

        // slug: no create gera do título se vazio; no update só troca se preenchido
        if (! empty($data['slug'])) {
            $post->slug = Str::slug($data['slug']);
        } elseif (! $post->exists) {
            $post->slug = null; // model gera no saving
        }

        // status + agendamento
        $status = $data['status'];
        $pub    = $data['published_at'] ?? null;
        if ($status === 'publicado') {
            $post->published_at = $pub ? Carbon::parse($pub) : ($post->published_at ?: now());
        } elseif ($status === 'agendado') {
            $post->published_at = $pub ? Carbon::parse($pub) : now()->addDay();
        } else {
            $post->published_at = $pub ? Carbon::parse($pub) : null;
        }
        $post->status = $status;

        // FAQ -> JSON
        $faq = [];
        $qs = $request->input('faq_q', []);
        $as = $request->input('faq_a', []);
        foreach ((array) $qs as $i => $qv) {
            $qv = trim((string) $qv);
            $av = trim((string) ($as[$i] ?? ''));
            if ($qv !== '' && $av !== '') {
                $faq[] = ['q' => $qv, 'a' => $av];
            }
        }
        $post->faq = ! empty($faq)
            ? json_encode($faq, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : null;

        // imagem destacada
        if ($request->hasFile('featured')) {
            $this->salvarImagem($post, $request);
        }
    }

    private function salvarImagem(BlogPost $post, Request $request): void
    {
        $dir = public_path(self::DIR_BLOG);
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        $file  = $request->file('featured');
        $ext   = $file->getClientOriginalExtension() ?: 'jpg';
        $fname = 'post-' . time() . '-' . Str::lower(Str::random(5)) . '.' . $ext;
        $file->move($dir, $fname);

        $this->apagarImagem($post);
        $post->featured_image = self::DIR_BLOG . '/' . $fname;
    }

    private function apagarImagem(BlogPost $post): void
    {
        if ($post->featured_image && ! Str::startsWith($post->featured_image, ['http://', 'https://'])) {
            $full = public_path($post->featured_image);
            if (File::exists($full)) {
                File::delete($full);
            }
        }
    }

    private function syncTags(BlogPost $post, Request $request): void
    {
        $names = collect(explode(',', (string) $request->input('tags_input')))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->unique();

        $ids = [];
        foreach ($names as $name) {
            $tag = BlogTag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
            $ids[] = $tag->id;
        }
        $post->tags()->sync($ids);
    }
}
