<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogTag extends Model
{
    protected $fillable = ['name', 'slug'];

    protected static function booted()
    {
        static::saving(function (BlogTag $tag) {
            if (empty($tag->slug) && ! empty($tag->name)) {
                $tag->slug = static::uniqueSlug($tag->name, $tag->id);
            }
        });
    }

    public static function uniqueSlug(string $base, $ignoreId = null): string
    {
        $slug = Str::slug($base);
        $original = $slug ?: 'tag';
        $i = 1;
        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $original . '-' . (++$i);
        }
        return $slug;
    }

    public function posts()
    {
        return $this->belongsToMany(BlogPost::class, 'blog_post_tag', 'blog_tag_id', 'blog_post_id');
    }

    public function url(): string
    {
        return route('blog.tag', $this->slug);
    }
}
