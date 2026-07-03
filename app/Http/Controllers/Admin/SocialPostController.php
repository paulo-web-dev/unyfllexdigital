<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\SocialPost;
use App\Models\SocialPostMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class SocialPostController extends Controller
{
    /** Subpasta pública onde as mídias ficam (servidas via asset()). */
    private const DIR = 'storage/social';

    /** Calendário mensal com os posts plotados por dia. */
    public function calendar(Request $request)
    {
        $this->authorize('admin.social');

        $mesParam = $request->input('mes');
        try {
            $ref = $mesParam
                ? Carbon::createFromFormat('Y-m', $mesParam)->startOfMonth()
                : Carbon::now()->startOfMonth();
        } catch (\Throwable $e) {
            $ref = Carbon::now()->startOfMonth();
        }

        $inicio = $ref->copy()->startOfMonth();
        $fim = $ref->copy()->endOfMonth();

        $posts = SocialPost::with('media')
            ->whereNotNull('scheduled_for')
            ->whereBetween('scheduled_for', [$inicio->copy()->startOfDay(), $fim->copy()->endOfDay()])
            ->orderBy('scheduled_for')
            ->get()
            ->groupBy(fn ($p) => $p->scheduled_for->format('Y-m-d'));

        return view('admin.social.calendar', [
            'ref'       => $ref,
            'gridStart' => $inicio->copy()->startOfWeek(Carbon::SUNDAY),
            'gridEnd'   => $fim->copy()->endOfWeek(Carbon::SATURDAY),
            'posts'     => $posts,
            'prevMes'   => $ref->copy()->subMonth()->format('Y-m'),
            'nextMes'   => $ref->copy()->addMonth()->format('Y-m'),
            'categorias'=> $this->categoriasCatalogo(),
        ]);
    }

    /**
     * Lê as categorias distintas do catálogo (planilha CSV), com cache de 10 min.
     * Usado para popular o briefing. Se falhar, devolve uma lista padrão.
     */
    private function categoriasCatalogo(): array
    {
        $fallback = ['Fotos de aula', 'Identidade Visual', 'Ícones'];
        $url = config('social.catalogo_url');
        if (empty($url)) {
            return $fallback;
        }

        return Cache::remember('social_catalogo_categorias', 600, function () use ($url, $fallback) {
            try {
                $resp = Http::timeout(12)->get($url);
                if (!$resp->successful()) {
                    return $fallback;
                }
                $csv = preg_replace('/^\xEF\xBB\xBF/', '', $resp->body()); // remove BOM
                $linhas = preg_split('/\r?\n/', trim($csv));
                if (count($linhas) < 2) {
                    return $fallback;
                }
                $header = array_map(fn ($h) => strtolower(trim($h, " \"")), explode(',', array_shift($linhas)));
                $idx = array_search('categoria', $header, true);
                if ($idx === false) {
                    return $fallback;
                }
                $cats = [];
                foreach ($linhas as $l) {
                    if (trim($l) === '') {
                        continue;
                    }
                    $cols = str_getcsv($l); // lida com aspas/vírgulas
                    $c = isset($cols[$idx]) ? trim($cols[$idx]) : '';
                    if ($c !== '') {
                        $cats[$c] = true;
                    }
                }
                $lista = array_keys($cats);
                sort($lista);
                return $lista ?: $fallback;
            } catch (\Throwable $e) {
                return $fallback;
            }
        });
    }

    public function index(Request $request)
    {
        $this->authorize('admin.social');

        $status = $request->input('status');

        $posts = SocialPost::with('media')
            ->when(in_array($status, array_keys(SocialPost::STATUSES), true),
                   fn ($q) => $q->where('status', $status))
            ->orderByRaw('COALESCE(scheduled_for, created_at) DESC')
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'rascunho'  => SocialPost::where('status', 'rascunho')->count(),
            'agendado'  => SocialPost::where('status', 'agendado')->count(),
            'publicado' => SocialPost::where('status', 'publicado')->count(),
        ];

        return view('admin.social.posts.index', compact('posts', 'status', 'counts'));
    }

    public function create()
    {
        $this->authorize('admin.social');
        $account = SocialAccount::where('platform', 'instagram')->first();
        $post = null;
        return view('admin.social.posts.form', compact('account', 'post'));
    }

    public function store(Request $request)
    {
        $this->authorize('admin.social');
        $data = $this->validateData($request);

        $post = new SocialPost();
        $post->source     = 'manual';
        $post->created_by = optional($request->user())->id;
        $this->fill($post, $data);
        $post->save();

        $this->uploadMedia($post, $request);

        return redirect()
            ->route('admin.social.posts.edit', $post)
            ->with('success', 'Post criado.');
    }

    public function edit(SocialPost $post)
    {
        $this->authorize('admin.social');
        $account = SocialAccount::where('platform', 'instagram')->first();
        $post->load('media');
        return view('admin.social.posts.form', compact('account', 'post'));
    }

    public function update(Request $request, SocialPost $post)
    {
        $this->authorize('admin.social');
        $data = $this->validateData($request);

        $this->fill($post, $data);
        $post->save();

        $this->uploadMedia($post, $request);

        return redirect()
            ->route('admin.social.posts.edit', $post)
            ->with('success', 'Post atualizado.');
    }

    public function destroy(SocialPost $post)
    {
        $this->authorize('admin.social');

        foreach ($post->media as $m) {
            File::delete(public_path($m->path));
        }
        $post->media()->delete();
        $post->delete();

        return redirect()
            ->route('admin.social.posts.index')
            ->with('success', 'Post excluído.');
    }

    /** Remove uma mídia específica do post. */
    public function destroyMedia(SocialPost $post, SocialPostMedia $media)
    {
        $this->authorize('admin.social');

        if ((int) $media->social_post_id === (int) $post->id) {
            File::delete(public_path($media->path));
            $media->delete();
        }

        return redirect()
            ->route('admin.social.posts.edit', $post)
            ->with('success', 'Mídia removida.');
    }

    // -------------------- helpers --------------------

    private function validateData(Request $request): array
    {
        return $request->validate([
            'social_account_id' => ['required', 'integer'],
            'type'              => ['required', 'in:feed_image,carousel,reel,story'],
            'status'            => ['required', 'in:rascunho,aprovado,agendado,publicando,publicado,falhou'],
            'caption'           => ['nullable', 'string'],
            'first_comment'     => ['nullable', 'string'],
            'scheduled_for'     => ['nullable', 'date'],
            'media'             => ['nullable', 'array'],
            'media.*'           => ['nullable', 'image', 'max:8192'], // 8MB por imagem
        ]);
    }

    private function fill(SocialPost $post, array $data): void
    {
        $post->social_account_id = $data['social_account_id'];
        $post->type          = $data['type'];
        $post->caption       = $data['caption'] ?? null;
        $post->first_comment = $data['first_comment'] ?? null;

        $status = $data['status'];
        $sched  = $data['scheduled_for'] ?? null;

        // "agendado" exige data; sem data, vira rascunho.
        if ($status === 'agendado' && $sched) {
            $post->scheduled_for = Carbon::parse($sched);
        } elseif ($status === 'agendado' && !$sched) {
            $status = 'rascunho';
            $post->scheduled_for = null;
        } else {
            $post->scheduled_for = $sched ? Carbon::parse($sched) : null;
        }

        $post->status = $status;
    }

    private function uploadMedia(SocialPost $post, Request $request): void
    {
        if (!$request->hasFile('media')) {
            return;
        }

        $dir = public_path(self::DIR);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $start = (int) $post->media()->max('sort_order');
        $i = 0;
        foreach ($request->file('media') as $file) {
            if (!$file) {
                continue;
            }
            $i++;
            $ext  = $file->getClientOriginalExtension() ?: 'jpg';
            $nome = 'post-' . $post->id . '-' . time() . '-' . $i . '.' . $ext;
            $file->move($dir, $nome);

            SocialPostMedia::create([
                'social_post_id' => $post->id,
                'path'           => self::DIR . '/' . $nome,
                'media_type'     => 'image',
                'sort_order'     => $start + $i,
                'source'         => 'upload',
                'created_at'     => now(),
            ]);
        }
    }
}
