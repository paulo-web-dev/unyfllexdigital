<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Services\AssinaturaVitrineService;

class SitemapController extends Controller
{
    /** /sitemap.xml — inclui a vitrine da assinatura (e suas categorias) e o blog: categorias, tags e posts publicados. */
    public function index()
    {
        $urls = [];

        // vitrine da Assinatura Premium
        $urls[] = ['loc' => route('assinatura.home'), 'changefreq' => 'weekly', 'priority' => '0.9'];
        $urls[] = ['loc' => route('assinatura.planos'), 'changefreq' => 'monthly', 'priority' => '0.9'];
        $urls[] = ['loc' => route('assinatura.categorias'), 'changefreq' => 'weekly', 'priority' => '0.8'];
        foreach (app(AssinaturaVitrineService::class)->categorias() as $cat) {
            $urls[] = ['loc' => $cat->url, 'changefreq' => 'weekly', 'priority' => '0.7'];
        }

        // raiz do blog
        $urls[] = ['loc' => route('blog.index'), 'changefreq' => 'daily', 'priority' => '0.8'];

        // categorias
        foreach (BlogCategory::all() as $cat) {
            $urls[] = ['loc' => $cat->url(), 'changefreq' => 'weekly', 'priority' => '0.6'];
        }

        // tags
        foreach (BlogTag::all() as $tag) {
            $urls[] = ['loc' => $tag->url(), 'changefreq' => 'weekly', 'priority' => '0.4'];
        }

        // posts publicados
        BlogPost::published()->orderByDesc('published_at')->get()->each(function ($post) use (&$urls) {
            $urls[] = [
                'loc'     => $post->url(),
                'lastmod' => optional($post->updated_at)->toAtomString(),
                'changefreq' => 'monthly',
                'priority'   => '0.7',
            ];
        });

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= "  <url>\n    <loc>" . e($u['loc']) . "</loc>\n";
            if (! empty($u['lastmod'])) {
                $xml .= "    <lastmod>" . $u['lastmod'] . "</lastmod>\n";
            }
            $xml .= "    <changefreq>" . $u['changefreq'] . "</changefreq>\n";
            $xml .= "    <priority>" . $u['priority'] . "</priority>\n  </url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }
}
