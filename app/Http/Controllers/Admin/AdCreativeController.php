<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdCreative;
use App\Models\ModularCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Criativos de ADS (Meta feed/stories, WhatsApp, prova social).
 * O n8n lê o banco de imagens (CSV), o Claude compõe as artes usando as
 * fotos reais, o renderizador gera os PNGs e devolve via callback.
 */
class AdCreativeController extends Controller
{
    private const DIR_ADS = 'storage/ads-criativos';

    public function gerarAds(int $id)
    {
        $this->authorize('admin.cursos');
        $curso = ModularCourse::findOrFail($id);

        $versao = ((int) ($curso->adCreatives()->max('version') ?? 0)) + 1;

        foreach ($curso->adCreatives as $old) {
            $this->apagar($old);
        }
        $curso->adCreatives()->delete();

        AdCreative::create([
            'modular_course_id' => $curso->id,
            'slug'              => '__loading__',
            'title'             => 'Gerando...',
            'status'            => 'gerando',
            'version'           => $versao,
        ]);

        $ok = $this->dispararN8n([
            'course_id'    => $curso->id,
            'title'        => $curso->title,
            'callback_url' => url('/api/n8n/cursos-modulares/ads'),
            'version'      => $versao,
        ], $this->adsWebhook());

        return back()->with(
            $ok ? 'success' : 'warning',
            $ok ? 'Geração dos criativos disparada — chegam em 1-2 min (atualize a página).'
                : 'Não consegui acionar o n8n (ADS). Confira a URL do webhook.'
        );
    }

    public function adsCallback(Request $request)
    {
        $this->validarSecret($request);

        $data = $request->validate([
            'course_id'              => ['required', 'integer'],
            'creatives'              => ['required', 'array', 'min:1'],
            'creatives.*.slug'       => ['required', 'string', 'max:30'],
            'creatives.*.caption'    => ['nullable', 'string'],
            'creatives.*.image_base64' => ['nullable', 'string'],
        ]);

        $curso = ModularCourse::find($data['course_id']);
        if (! $curso) {
            return response()->json(['ok' => false, 'error' => 'curso nao encontrado'], 404);
        }

        foreach ($curso->adCreatives as $old) {
            $this->apagar($old);
        }
        $curso->adCreatives()->delete();

        $dir = public_path(self::DIR_ADS . '/' . $curso->id);

        $salvos = 0;
        foreach ($data['creatives'] as $c) {
            $bin = ! empty($c['image_base64']) ? base64_decode($c['image_base64'], true) : false;
            if ($bin === false || strlen($bin) === 0) {
                continue;
            }
            $slug = preg_replace('/[^a-z0-9_-]/i', '', $c['slug']) ?: 'peca';
            if (! File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            $fname = $slug . '-' . time() . '-' . Str::lower(Str::random(4)) . '.png';
            File::put($dir . '/' . $fname, $bin);
            AdCreative::create([
                'modular_course_id' => $curso->id,
                'slug'              => $slug,
                'title'             => AdCreative::SLUGS[$slug] ?? ucfirst($slug),
                'image_path'        => self::DIR_ADS . '/' . $curso->id . '/' . $fname,
                'caption'           => $c['caption'] ?? null,
                'status'            => 'pronto',
                'version'           => 1,
            ]);
            $salvos++;
        }

        if ($salvos === 0) {
            AdCreative::create([
                'modular_course_id' => $curso->id,
                'slug'              => 'erro',
                'title'             => 'Erro',
                'status'            => 'erro',
                'version'           => 1,
            ]);
        }

        return response()->json(['ok' => true, 'saved' => $salvos]);
    }

    public function adDestroy(int $id)
    {
        $this->authorize('admin.cursos');
        $curso = ModularCourse::findOrFail($id);
        foreach ($curso->adCreatives as $c) {
            $this->apagar($c);
        }
        $curso->adCreatives()->delete();

        return back()->with('success', 'Criativos removidos.');
    }

    // ───────────────────────────── HELPERS ─────────────────────────────

    private function adsWebhook(): string
    {
        return config('cursos_modulares.n8n_ads_webhook_url')
            ?: 'https://n8n.unyflex.com.br/webhook/cursos-modulares/ads-criativos';
    }

    private function validarSecret(Request $request): void
    {
        $secret = (string) config('cursos_modulares.n8n_secret');
        abort_unless(
            hash_equals($secret, (string) $request->header('X-Webhook-Secret')),
            401,
            'Secret invalido.'
        );
    }

    private function dispararN8n(array $payload, ?string $url): bool
    {
        if (empty($url)) {
            return false;
        }
        try {
            $resp = Http::withHeaders(['X-Webhook-Secret' => config('cursos_modulares.n8n_secret')])
                ->timeout(20)
                ->post($url, $payload);

            return $resp->successful();
        } catch (\Throwable $e) {
            Log::warning('n8n ads: ' . $e->getMessage());
            return false;
        }
    }

    private function apagar(AdCreative $c): void
    {
        if ($c->image_path) {
            $full = public_path($c->image_path);
            if (File::exists($full)) {
                File::delete($full);
            }
        }
    }
}
