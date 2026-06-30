<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogCategory extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'minisserie_url', 'color', 'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    protected static function booted()
    {
        static::saving(function (BlogCategory $cat) {
            if (empty($cat->slug) && ! empty($cat->name)) {
                $cat->slug = static::uniqueSlug($cat->name, $cat->id);
            }
        });
    }

    public static function uniqueSlug(string $base, $ignoreId = null): string
    {
        $slug = Str::slug($base);
        $original = $slug ?: 'categoria';
        $i = 1;
        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $original . '-' . (++$i);
        }
        return $slug;
    }

    public function posts()
    {
        return $this->hasMany(BlogPost::class, 'blog_category_id');
    }

    public function publishedPosts()
    {
        return $this->posts()->published();
    }

    public function url(): string
    {
        return route('blog.category', $this->slug);
    }
}
