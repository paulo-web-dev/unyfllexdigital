<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class BlogPost extends Model
{
    protected $fillable = [
        'blog_category_id', 'title', 'slug', 'excerpt', 'content', 'featured_image',
        'meta_title', 'meta_description', 'focus_keyword', 'secondary_keywords',
        'faq', 'reading_time', 'views', 'status', 'published_at', 'author', 'source',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'reading_time' => 'integer',
        'views'        => 'integer',
    ];

    /* ───────────── slug + reading time automáticos ───────────── */

    protected static function booted()
    {
        static::saving(function (BlogPost $post) {
            if (empty($post->slug) && ! empty($post->title)) {
                $post->slug = static::uniqueSlug($post->title, $post->id);
            }
            $post->reading_time = static::calcReadingTime($post->content);
        });
    }

    public static function uniqueSlug(string $base, $ignoreId = null): string
    {
        $slug = Str::slug($base);
        $original = $slug ?: 'post';
        $i = 1;
        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $original . '-' . (++$i);
        }
        return $slug;
    }

    public static function calcReadingTime(?string $content): int
    {
        $words = str_word_count(strip_tags((string) $content));
        return max(1, (int) round($words / 200)); // ~200 palavras/min
    }

    /* ───────────── relations ───────────── */

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function tags()
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tag', 'blog_post_id', 'blog_tag_id');
    }

    /* ───────────── scopes ───────────── */

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', 'publicado')
                 ->where(function ($w) {
                     $w->whereNull('published_at')->orWhere('published_at', '<=', now());
                 });
    }

    /* ───────────── helpers de apresentação / SEO ───────────── */

    public function url(): string
    {
        return route('blog.show', $this->slug);
    }

    public function metaTitleOr(): string
    {
        return $this->meta_title ?: $this->title;
    }

    public function metaDescriptionOr(): string
    {
        return Str::limit($this->meta_description ?: $this->excerptOr(), 300, '');
    }

    public function excerptOr(): string
    {
        return $this->excerpt ?: Str::limit(strip_tags((string) $this->content), 160);
    }

    public function ogImageUrl(): string
    {
        if (! empty($this->featured_image)) {
            return Str::startsWith($this->featured_image, ['http://', 'https://'])
                ? $this->featured_image
                : asset(ltrim($this->featured_image, '/'));
        }
        return asset('img/og-image.png');
    }

    public function hasFeatured(): bool
    {
        return ! empty($this->featured_image);
    }

    public function featuredUrl(): ?string
    {
        return $this->hasFeatured() ? $this->ogImageUrl() : null;
    }

    public function publishedDate(): ?Carbon
    {
        return $this->published_at ?: $this->created_at;
    }

    public function faqItems(): array
    {
        if (empty($this->faq)) {
            return [];
        }
        $data = is_array($this->faq) ? $this->faq : json_decode($this->faq, true);
        return is_array($data) ? $data : [];
    }

    public function secondaryKeywordList(): array
    {
        return collect(explode(',', (string) $this->secondary_keywords))
            ->map(fn ($k) => trim($k))
            ->filter()
            ->values()
            ->all();
    }

    /** JSON-LD do tipo Article (string já pronta para a view). */
    public function jsonLdArticle(): string
    {
        $date = $this->publishedDate();
        $data = [
            '@context' => 'https://schema.org',
            '@type'    => 'Article',
            'headline' => $this->title,
            'description' => $this->metaDescriptionOr(),
            'image'    => $this->ogImageUrl(),
            'datePublished' => optional($date)->toIso8601String(),
            'dateModified'  => optional($this->updated_at)->toIso8601String(),
            'author'   => ['@type' => 'Organization', 'name' => $this->author ?: 'Unyflex Digital'],
            'publisher' => [
                '@type' => 'Organization',
                'name'  => 'Unyflex Digital',
                'logo'  => ['@type' => 'ImageObject', 'url' => asset('img/logo-unyflex.png')],
            ],
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $this->url()],
        ];
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /** JSON-LD FAQPage, ou string vazia se não houver FAQ. */
    public function jsonLdFaq(): string
    {
        $items = $this->faqItems();
        if (empty($items)) {
            return '';
        }
        $entities = [];
        foreach ($items as $it) {
            $q = $it['q'] ?? $it['question'] ?? null;
            $a = $it['a'] ?? $it['answer'] ?? null;
            if ($q && $a) {
                $entities[] = [
                    '@type' => 'Question',
                    'name'  => $q,
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a],
                ];
            }
        }
        if (empty($entities)) {
            return '';
        }
        return json_encode([
            '@context' => 'https://schema.org',
            '@type'    => 'FAQPage',
            'mainEntity' => $entities,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
