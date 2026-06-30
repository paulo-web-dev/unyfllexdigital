<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /** Listagem principal /blog */
    public function index(Request $request)
    {
        $posts = BlogPost::published()
            ->with(['category', 'tags'])
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(9)
            ->withQueryString();

        $categories = BlogCategory::orderBy('sort_order')->orderBy('name')
            ->withCount(['posts as published_count' => fn ($q) => $q->published()])
            ->get();

        $destaque = BlogPost::published()->with('category')
            ->orderByDesc('views')->orderByDesc('published_at')
            ->first();

        return view('blog.index', compact('posts', 'categories', 'destaque'));
    }

    /** Post individual /blog/{slug} */
    public function show(string $slug)
    {
        $post = BlogPost::published()->with(['category', 'tags'])->where('slug', $slug)->firstOrFail();

        // incrementa views sem mexer no updated_at
        BlogPost::where('id', $post->id)->update(['views' => $post->views + 1]);

        $relacionados = BlogPost::published()
            ->where('blog_category_id', $post->blog_category_id)
            ->where('id', '!=', $post->id)
            ->with('category')
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('blog.show', compact('post', 'relacionados'));
    }

    /** Arquivo por categoria /categoria/{slug} */
    public function category(string $slug)
    {
        $category = BlogCategory::where('slug', $slug)->firstOrFail();

        $posts = $category->publishedPosts()
            ->with(['category', 'tags'])
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(9)
            ->withQueryString();

        $categories = BlogCategory::orderBy('sort_order')->orderBy('name')
            ->withCount(['posts as published_count' => fn ($q) => $q->published()])
            ->get();

        return view('blog.category', compact('category', 'posts', 'categories'));
    }

    /** Arquivo por tag /tag/{slug} */
    public function tag(string $slug)
    {
        $tag = BlogTag::where('slug', $slug)->firstOrFail();

        $posts = $tag->posts()
            ->published()
            ->with(['category', 'tags'])
            ->orderByDesc('published_at')
            ->orderByDesc('blog_posts.id')
            ->paginate(9)
            ->withQueryString();

        return view('blog.tag', compact('tag', 'posts'));
    }
}
