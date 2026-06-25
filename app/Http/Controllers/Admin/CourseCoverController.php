<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseCover;
use App\Models\ModularCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Capa (banner 16:9) do curso para o site.
 * O Claude monta só os textos; o layout (aprovado) é fixo no n8n; o
 * renderizador gera o PNG 1280x720 e devolve via callback.
 */
class CourseCoverController extends Controller
{
    private const DIR_CAPAS = 'storage/capas';

    public function gerarCapa(int $id)
    {
        $this->authorize('admin.cursos');
        $curso = ModularCourse::findOrFail($id);

        $versao = ((int) ($curso->coverArt()->max('version') ?? 0)) + 1;

        foreach ($curso->coverArt as $old) {
            $this->apagar($old);
        }
        $curso->coverArt()->delete();

        CourseCover::create([
            'modular_course_id' => $curso->id,
            'status'            => 'gerando',
            'version'           => $versao,
        ]);

        $ok = $this->dispararN8n([
            'course_id'    => $curso->id,
            'title'        => $curso->title,
            'description'  => $curso->description,
            'callback_url' => url('/api/n8n/cursos-modulares/capa'),
            'version'      => $versao,
        ], $this->capaWebhook());

        return back()->with(
            $ok ? 'success' : 'warning',
            $ok ? 'Geração da capa disparada — chega em instantes (atualize a página).'
                : 'Não consegui acionar o n8n (capa). Confira a URL do webhook.'
        );
    }

    public function capaCallback(Request $request)
    {
        $this->validarSecret($request);

        $data = $request->validate([
            'course_id'    => ['required', 'integer'],
            'image_base64' => ['nullable', 'string'],
        ]);

        $curso = ModularCourse::find($data['course_id']);
        if (! $curso) {
            return response()->json(['ok' => false, 'error' => 'curso nao encontrado'], 404);
        }

        foreach ($curso->coverArt as $old) {
            $this->apagar($old);
        }
        $curso->coverArt()->delete();

        $bin = ! empty($data['image_base64']) ? base64_decode($data['image_base64'], true) : false;

        if ($bin === false || strlen($bin) === 0) {
            CourseCover::create([
                'modular_course_id' => $curso->id,
                'status'            => 'erro',
                'version'           => 1,
            ]);
            return response()->json(['ok' => false, 'error' => 'sem imagem'], 200);
        }

        $dir = public_path(self::DIR_CAPAS . '/' . $curso->id);
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        $fname = 'capa-' . time() . '-' . Str::lower(Str::random(4)) . '.png';
        File::put($dir . '/' . $fname, $bin);

        CourseCover::create([
            'modular_course_id' => $curso->id,
            'image_path'        => self::DIR_CAPAS . '/' . $curso->id . '/' . $fname,
            'status'            => 'pronto',
            'version'           => 1,
        ]);

        return response()->json(['ok' => true]);
    }

    public function capaDestroy(int $id)
    {
        $this->authorize('admin.cursos');
        $curso = ModularCourse::findOrFail($id);
        foreach ($curso->coverArt as $c) {
            $this->apagar($c);
        }
        $curso->coverArt()->delete();

        return back()->with('success', 'Capa removida.');
    }

    // ───────────────────────────── HELPERS ─────────────────────────────

    private function capaWebhook(): string
    {
        return config('cursos_modulares.n8n_capa_webhook_url')
            ?: 'https://n8n.unyflex.com.br/webhook/cursos-modulares/capa';
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
            Log::warning('n8n capa: ' . $e->getMessage());
            return false;
        }
    }

    private function apagar(CourseCover $c): void
    {
        if ($c->image_path) {
            $full = public_path($c->image_path);
            if (File::exists($full)) {
                File::delete($full);
            }
        }
    }
}
