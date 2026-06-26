<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModularCourse;
use App\Models\CourseVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cursos Modulares — FASE 9: Vídeo de resumo (estilo NotebookLM).
 *
 * Fluxo:
 *   admin clica "Gerar vídeo"  ->  dispara webhook do n8n
 *   n8n: Claude (roteiro+slides) -> renderizador (PNG) -> Gemini TTS (WAV)
 *        -> video-assembler (MP4) -> callback aqui com a URL pública
 *
 * O .mp4 fica no video-assembler (VPS) e é servido por HTTPS; aqui só a URL.
 * Callback entra por routes/api.php (sem CSRF), protegido pelo header
 * X-Webhook-Secret (config cursos_modulares.n8n_secret).
 */
class CourseVideoController extends Controller
{
    /** Fallback caso a chave não exista no config/cursos_modulares.php do usuário. */
    private const WEBHOOK_FALLBACK = 'https://n8n.unyflex.com.br/webhook/cursos-modulares/video';

    // ───────────────────────────── ADMIN ─────────────────────────────

    /** Dispara a geração do vídeo de resumo no n8n. */
    public function gerar(int $id)
    {
        $this->authorize('admin.cursos');

        $curso = ModularCourse::findOrFail($id);

        // Um vídeo por curso: marca como "gerando" (cria ou regenera).
        $registro = CourseVideo::updateOrCreate(
            ['modular_course_id' => $curso->id],
            ['status' => 'gerando', 'feedback' => null, 'video_url' => null]
        );
        // Mantém versão crescente a cada (re)geração.
        $registro->increment('version');

        $ok = $this->dispararN8n($this->payload($curso), $this->webhookUrl());

        return back()->with(
            $ok ? 'success' : 'warning',
            $ok ? 'Geração do vídeo de resumo disparada — isso leva alguns minutos. Atualize a página depois.'
                : 'Não consegui acionar o n8n (vídeo). Confira a URL do webhook em config/cursos_modulares.php (video_webhook_url).'
        );
    }

    /** Remove o vídeo do curso (apaga só o registro; o .mp4 fica no VPS). */
    public function videoDestroy(int $id, int $videoId)
    {
        $this->authorize('admin.cursos');

        $video = CourseVideo::where('modular_course_id', $id)->where('id', $videoId)->firstOrFail();
        $video->delete();

        return back()->with('success', 'Vídeo removido da listagem. (O arquivo .mp4 permanece no servidor de vídeos.)');
    }

    // ───────────────────────── CALLBACK (n8n) ─────────────────────────

    /**
     * Recebe do n8n o resultado do vídeo.
     *
     * Corpo esperado (JSON):
     *   { course_id, video_url, title?, duration?, slides?, status?, error? }
     *
     * - Com video_url            -> status "pronto"
     * - Com error (e sem URL)    -> status "erro" + feedback
     */
    public function videoCallback(Request $request)
    {
        $this->validarSecret($request);

        $data = $request->validate([
            'course_id' => ['required', 'integer'],
            'video_url' => ['nullable', 'string', 'max:1024'],
            'title'     => ['nullable', 'string', 'max:255'],
            'duration'  => ['nullable', 'integer'],
            'slides'    => ['nullable', 'integer'],
            'status'    => ['nullable', 'string', 'max:20'],
            'error'     => ['nullable', 'string'],
        ]);

        $curso = ModularCourse::find($data['course_id']);
        if (! $curso) {
            return response()->json(['ok' => false, 'error' => 'curso nao encontrado'], 404);
        }

        $temUrl  = ! empty($data['video_url']);
        $status  = $temUrl ? 'pronto' : 'erro';
        // Permite o n8n forçar um status específico, se quiser.
        if (! empty($data['status'])) {
            $status = $data['status'];
        }

        $registro = CourseVideo::firstOrNew(['modular_course_id' => $curso->id]);
        $registro->fill([
            'title'     => $data['title'] ?? $registro->title ?? ('Resumo em vídeo — ' . $curso->title),
            'video_url' => $temUrl ? $data['video_url'] : $registro->video_url,
            'duration'  => $data['duration'] ?? $registro->duration,
            'slides'    => $data['slides'] ?? $registro->slides,
            'status'    => $status,
            'feedback'  => $temUrl ? null : ($data['error'] ?? 'O fluxo não retornou a URL do vídeo.'),
            'version'   => $registro->version ?: 1,
        ]);
        $registro->save();

        return response()->json([
            'ok'     => true,
            'status' => $registro->status,
            'url'    => $registro->video_url,
        ]);
    }

    // ───────────────────────────── HELPERS ─────────────────────────────

    /** Payload enviado ao n8n para gerar o roteiro/slides do vídeo. */
    private function payload(ModularCourse $curso): array
    {
        return [
            'course_id'    => $curso->id,
            'slug'         => $curso->slug,
            'title'        => $curso->title,
            'description'  => (string) $curso->description,
            'apostila_url' => $curso->apostilaUrl(),
        ];
    }

    /** URL do webhook do n8n para o vídeo (config com fallback). */
    private function webhookUrl(): string
    {
        $url = config('cursos_modulares.video_webhook_url');
        return is_string($url) && $url !== '' ? $url : self::WEBHOOK_FALLBACK;
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
            Log::warning('n8n cursos-modulares (video): ' . $e->getMessage());
            return false;
        }
    }
}
