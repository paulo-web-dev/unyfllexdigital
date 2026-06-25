<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModularCourse;
use App\Models\ModularCourseAsset;
use App\Models\MediaKitAsset;
use App\Models\PodcastAudio;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cursos Modulares — apostila PDF -> curso modular.
 *
 * FASE 2: roteiros (resumo / podcast / vídeo) gerados pelo n8n + Claude.
 * FASE 3: media kit (card + story) gerados pelo n8n + Claude + renderizador.
 *
 * Rotas do admin: grupo 'auth'+'admin', gate admin.cursos.
 * Callbacks do n8n entram por routes/api.php (sem CSRF), protegidos pelo
 * header X-Webhook-Secret (config cursos_modulares.n8n_secret).
 */
class ModularCourseController extends Controller
{
    private const DIR_APOSTILA = 'storage/cursos-modulares/apostilas';
    private const DIR_MEDIA    = 'storage/media-kit';
    private const DIR_PODCAST  = 'storage/podcast-audio';

    /** Tipos do media kit. */
    private const TIPOS_MIDIA = ['card' => 'Card (feed)', 'story' => 'Story (vertical)'];

    // ───────────────────────────── LISTAGEM ─────────────────────────────

    public function index()
    {
        $this->authorize('admin.cursos');

        $cursos = ModularCourse::with('mediaKitAssets')->orderByDesc('id')->paginate(20);

        $kpis = [
            'total'      => ModularCourse::count(),
            'rascunhos'  => ModularCourse::where('status', 'rascunho')->count(),
            'publicados' => ModularCourse::where('status', 'publicado')->count(),
            'apostilas'  => ModularCourse::whereNotNull('apostila_path')->count(),
        ];

        return view('pages.admin.cursos-modulares.index', compact('cursos', 'kpis'));
    }

    public function create()
    {
        $this->authorize('admin.cursos');
        return view('pages.admin.cursos-modulares.create');
    }

    public function store(Request $request)
    {
        $this->authorize('admin.cursos');

        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['required', 'in:rascunho,processando,publicado'],
            'apostila'    => ['nullable', 'file', 'mimes:pdf', 'max:65536'],
        ], [
            'title.required' => 'Informe o titulo do curso.',
            'apostila.mimes' => 'A apostila precisa ser um arquivo PDF.',
            'apostila.max'   => 'A apostila pode ter no maximo 64 MB.',
        ]);

        $curso = new ModularCourse();
        $curso->title       = $data['title'];
        $curso->slug        = Str::slug($data['title']) . '-' . Str::lower(Str::random(5));
        $curso->description = $data['description'] ?? null;
        $curso->status      = $data['status'];

        if ($request->hasFile('apostila')) {
            $this->guardarApostila($curso, $request->file('apostila'));
        }

        $curso->save();

        return redirect()
            ->route('admin.cursos-modulares.show', $curso->id)
            ->with('success', 'Curso modular criado com sucesso.');
    }

    public function show(int $id)
    {
        $this->authorize('admin.cursos');

        $curso  = ModularCourse::findOrFail($id);
        $assets = $curso->assets()
            ->orderByRaw("FIELD(`type`, 'resumo', 'podcast', 'video')")
            ->get();
        $midia = $curso->mediaKitAssets()
            ->orderByRaw("FIELD(`type`, 'card', 'story')")
            ->get();

        $audios = $curso->podcastAudios()->orderBy('part')->get();
        $materiais = $curso->courseMaterials()->orderBy('type')->orderBy('sort_order')->get();
        $criativos = $curso->adCreatives()->orderBy('id')->get();
        $capa = $curso->coverArt()->orderByDesc('id')->get();

        return view('pages.admin.cursos-modulares.show', compact('curso', 'assets', 'midia', 'audios', 'materiais', 'criativos', 'capa'));
    }

    public function download(int $id)
    {
        $this->authorize('admin.cursos');
        $curso = ModularCourse::findOrFail($id);
        abort_unless($curso->hasApostila(), 404);

        return redirect($curso->apostilaUrl());
    }

    public function destroy(int $id)
    {
        $this->authorize('admin.cursos');
        $curso = ModularCourse::findOrFail($id);

        $this->apagarApostila($curso);
        foreach ($curso->mediaKitAssets as $m) {
            $this->apagarImagem($m);
        }
        foreach ($curso->podcastAudios as $pa) {
            $this->apagarAudio($pa);
        }
        $curso->podcastAudios()->delete();
        $curso->mediaKitAssets()->delete();
        $curso->assets()->delete();
        $curso->delete();

        return redirect()
            ->route('admin.cursos-modulares')
            ->with('success', 'Curso modular removido.');
    }

    // ═══════════════════════ ROTEIROS (fase 2) ═════════════════════════

    public function gerar(int $id)
    {
        $this->authorize('admin.cursos');
        $curso = ModularCourse::findOrFail($id);
        abort_unless($curso->hasApostila(), 422, 'Envie a apostila antes de gerar.');

        foreach (array_keys(config('cursos_modulares.tipos')) as $tipo) {
            ModularCourseAsset::updateOrCreate(
                ['modular_course_id' => $curso->id, 'type' => $tipo],
                ['status' => 'gerando', 'feedback' => null]
            );
        }
        $curso->status = 'processando';
        $curso->save();

        $ok = $this->dispararN8n($this->payloadRoteiros($curso), config('cursos_modulares.n8n_webhook_url'));

        return back()->with(
            $ok ? 'success' : 'warning',
            $ok ? 'Geração dos roteiros disparada — atualize a página em instantes.'
                : 'Não consegui acionar o n8n (roteiros). Confira a URL do webhook.'
        );
    }

    public function assetApprove(int $id, int $assetId)
    {
        $this->authorize('admin.cursos');
        $asset = $this->asset($id, $assetId);
        $asset->update(['status' => 'aprovado', 'feedback' => null]);
        $this->talvezConcluir($asset->course);

        return back()->with('success', $asset->typeLabel() . ' aprovado.');
    }

    public function assetReject(Request $request, int $id, int $assetId)
    {
        $this->authorize('admin.cursos');
        $data  = $request->validate(['feedback' => ['required', 'string', 'max:2000']]);
        $asset = $this->asset($id, $assetId);

        $novaVersao = ((int) $asset->version) + 1;
        $asset->update(['status' => 'gerando', 'feedback' => $data['feedback'], 'version' => $novaVersao]);

        $curso = $asset->course;
        $curso->status = 'processando';
        $curso->save();

        $payload = $this->payloadRoteiros($curso);
        $payload['type']     = $asset->type;
        $payload['feedback'] = $data['feedback'];
        $payload['version']  = $novaVersao;

        $ok = $this->dispararN8n($payload, config('cursos_modulares.n8n_webhook_url'));

        return back()->with(
            $ok ? 'success' : 'warning',
            $ok ? $asset->typeLabel() . ' enviado para refação com seu feedback.'
                : 'Salvei o feedback, mas não consegui acionar o n8n.'
        );
    }

    public function assetUpdate(Request $request, int $id, int $assetId)
    {
        $this->authorize('admin.cursos');
        $data  = $request->validate(['content' => ['required', 'string']]);
        $asset = $this->asset($id, $assetId);
        $asset->update(['content' => $data['content']]);

        return back()->with('success', $asset->typeLabel() . ' atualizado.');
    }

    public function assetDestroy(int $id, int $assetId)
    {
        $this->authorize('admin.cursos');
        $this->asset($id, $assetId)->delete();

        return back()->with('success', 'Item removido.');
    }

    public function callback(Request $request)
    {
        $this->validarSecret($request);

        $data = $request->validate([
            'course_id'        => ['required', 'integer'],
            'assets'           => ['required', 'array', 'min:1'],
            'assets.*.type'    => ['required', 'in:resumo,podcast,video'],
            'assets.*.content' => ['nullable', 'string'],
            'assets.*.version' => ['nullable', 'integer'],
        ]);

        $curso = ModularCourse::find($data['course_id']);
        if (! $curso) {
            return response()->json(['ok' => false, 'error' => 'curso nao encontrado'], 404);
        }

        foreach ($data['assets'] as $a) {
            ModularCourseAsset::updateOrCreate(
                ['modular_course_id' => $curso->id, 'type' => $a['type']],
                ['content' => $a['content'] ?? '', 'version' => $a['version'] ?? 1, 'status' => 'aguardando_revisao']
            );
        }

        if ($curso->status === 'processando') {
            $curso->status = 'rascunho';
            $curso->save();
        }

        return response()->json(['ok' => true, 'saved' => count($data['assets'])]);
    }

    // ═══════════════════════ MEDIA KIT (fase 3) ════════════════════════

    public function gerarMediaKit(int $id)
    {
        $this->authorize('admin.cursos');
        $curso = ModularCourse::findOrFail($id);
        abort_unless(! empty($curso->title), 422, 'O curso precisa de um titulo.');

        foreach (array_keys(self::TIPOS_MIDIA) as $tipo) {
            MediaKitAsset::updateOrCreate(
                ['modular_course_id' => $curso->id, 'type' => $tipo],
                ['status' => 'gerando', 'feedback' => null]
            );
        }

        $ok = $this->dispararN8n($this->payloadMedia($curso), $this->mediaKitWebhook());

        return back()->with(
            $ok ? 'success' : 'warning',
            $ok ? 'Geração do media kit disparada — as artes chegam em instantes.'
                : 'Não consegui acionar o n8n (media kit). Confira a URL do webhook.'
        );
    }

    /** Dispara media kit E, se houver apostila, os roteiros — em sequência. */
    public function gerarTudo(int $id)
    {
        $this->authorize('admin.cursos');
        $curso = ModularCourse::findOrFail($id);

        // 1) Media kit (precisa de título)
        foreach (array_keys(self::TIPOS_MIDIA) as $tipo) {
            MediaKitAsset::updateOrCreate(
                ['modular_course_id' => $curso->id, 'type' => $tipo],
                ['status' => 'gerando', 'feedback' => null]
            );
        }
        $okMedia = $this->dispararN8n($this->payloadMedia($curso), $this->mediaKitWebhook());

        // 2) Roteiros (precisa de apostila)
        $okRot = null;
        if ($curso->hasApostila()) {
            foreach (array_keys(config('cursos_modulares.tipos')) as $tipo) {
                ModularCourseAsset::updateOrCreate(
                    ['modular_course_id' => $curso->id, 'type' => $tipo],
                    ['status' => 'gerando', 'feedback' => null]
                );
            }
            $curso->status = 'processando';
            $curso->save();
            $okRot = $this->dispararN8n($this->payloadRoteiros($curso), config('cursos_modulares.n8n_webhook_url'));
        }

        $msg = $okMedia ? 'Media kit disparado.' : 'Falha ao disparar o media kit.';
        if ($okRot === true)  $msg .= ' Roteiros disparados.';
        if ($okRot === false) $msg .= ' Falha ao disparar os roteiros.';
        if ($okRot === null)  $msg .= ' (Sem apostila, os roteiros não foram disparados.)';

        return back()->with(($okMedia && $okRot !== false) ? 'success' : 'warning', $msg . ' Atualize a página em instantes.');
    }

    public function mediaKitCallback(Request $request)
    {
        $this->validarSecret($request);

        $data = $request->validate([
            'course_id'             => ['required', 'integer'],
            'assets'                => ['required', 'array', 'min:1'],
            'assets.*.type'         => ['required', 'in:card,story'],
            'assets.*.image_base64' => ['nullable', 'string'],
            'assets.*.caption'      => ['nullable', 'string'],
            'assets.*.version'      => ['nullable', 'integer'],
        ]);

        $curso = ModularCourse::find($data['course_id']);
        if (! $curso) {
            return response()->json(['ok' => false, 'error' => 'curso nao encontrado'], 404);
        }

        $dir = public_path(self::DIR_MEDIA . '/' . $curso->id);
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        foreach ($data['assets'] as $a) {
            $payload = [
                'caption' => $a['caption'] ?? null,
                'version' => $a['version'] ?? 1,
                'status'  => 'aguardando_revisao',
            ];

            if (! empty($a['image_base64'])) {
                $bin = base64_decode($a['image_base64'], true);
                if ($bin !== false) {
                    // remove a imagem anterior, se houver
                    $old = MediaKitAsset::where('modular_course_id', $curso->id)->where('type', $a['type'])->first();
                    if ($old) {
                        $this->apagarImagem($old);
                    }
                    $fname = $a['type'] . '-' . time() . '-' . Str::lower(Str::random(4)) . '.png';
                    File::put($dir . '/' . $fname, $bin);
                    $payload['image_path'] = self::DIR_MEDIA . '/' . $curso->id . '/' . $fname;
                }
            }

            MediaKitAsset::updateOrCreate(
                ['modular_course_id' => $curso->id, 'type' => $a['type']],
                $payload
            );
        }

        return response()->json(['ok' => true, 'saved' => count($data['assets'])]);
    }

    public function mediaApprove(int $id, int $assetId)
    {
        $this->authorize('admin.cursos');
        $asset = $this->mediaAsset($id, $assetId);
        $asset->update(['status' => 'aprovado', 'feedback' => null]);

        return back()->with('success', $asset->typeLabel() . ' aprovado.');
    }

    public function mediaReject(Request $request, int $id, int $assetId)
    {
        $this->authorize('admin.cursos');
        $data  = $request->validate(['feedback' => ['required', 'string', 'max:2000']]);
        $asset = $this->mediaAsset($id, $assetId);

        $novaVersao = ((int) $asset->version) + 1;
        $asset->update(['status' => 'gerando', 'feedback' => $data['feedback'], 'version' => $novaVersao]);

        $payload = $this->payloadMedia($asset->course);
        $payload['type']     = $asset->type;
        $payload['feedback'] = $data['feedback'];
        $payload['version']  = $novaVersao;

        $ok = $this->dispararN8n($payload, $this->mediaKitWebhook());

        return back()->with(
            $ok ? 'success' : 'warning',
            $ok ? $asset->typeLabel() . ' enviado para refação com seu feedback.'
                : 'Salvei o feedback, mas não consegui acionar o n8n.'
        );
    }

    public function mediaUpdateCaption(Request $request, int $id, int $assetId)
    {
        $this->authorize('admin.cursos');
        $data  = $request->validate(['caption' => ['nullable', 'string', 'max:3000']]);
        $asset = $this->mediaAsset($id, $assetId);
        $asset->update(['caption' => $data['caption'] ?? '']);

        return back()->with('success', 'Legenda atualizada.');
    }

    public function mediaDestroy(int $id, int $assetId)
    {
        $this->authorize('admin.cursos');
        $asset = $this->mediaAsset($id, $assetId);
        $this->apagarImagem($asset);
        $asset->delete();

        return back()->with('success', 'Item removido.');
    }

    // ═══════════════════ PODCAST EM ÁUDIO (fase 4) ═════════════════════

    public function gerarPodcastAudio(int $id)
    {
        $this->authorize('admin.cursos');
        $curso = ModularCourse::findOrFail($id);

        $roteiro = $curso->assets()->where('type', 'podcast')->first();
        abort_unless(
            $roteiro && trim((string) $roteiro->content) !== '',
            422,
            'Gere o roteiro de podcast antes do audio.'
        );

        $versao = ((int) ($curso->podcastAudios()->max('version') ?? 0)) + 1;

        // limpa as partes anteriores (arquivos + registros) e cria um placeholder "gerando"
        foreach ($curso->podcastAudios as $old) {
            $this->apagarAudio($old);
        }
        $curso->podcastAudios()->delete();
        PodcastAudio::create([
            'modular_course_id' => $curso->id,
            'part'              => 1,
            'title'             => 'Gerando...',
            'status'            => 'gerando',
            'version'           => $versao,
        ]);

        $ok = $this->dispararN8n([
            'course_id'    => $curso->id,
            'title'        => $curso->title,
            'roteiro'      => $roteiro->content,
            'callback_url' => url('/api/n8n/cursos-modulares/podcast-audio'),
            'version'      => $versao,
        ], $this->podcastWebhook());

        return back()->with(
            $ok ? 'success' : 'warning',
            $ok ? 'Geracao dos audios disparada - os trechos chegam em instantes (atualize a pagina).'
                : 'Nao consegui acionar o n8n (audio). Confira a URL do webhook.'
        );
    }

    public function podcastAudioCallback(Request $request)
    {
        $this->validarSecret($request);

        // Recebe UM trecho por chamada (o n8n manda um POST por parte).
        $data = $request->validate([
            'course_id'    => ['required', 'integer'],
            'part'         => ['required', 'integer', 'min:1'],
            'total'        => ['nullable', 'integer'],
            'title'        => ['nullable', 'string', 'max:120'],
            'audio_base64' => ['nullable', 'string'],
            'version'      => ['nullable', 'integer'],
        ]);

        $curso = ModularCourse::find($data['course_id']);
        if (! $curso) {
            return response()->json(['ok' => false, 'error' => 'curso nao encontrado'], 404);
        }

        $parte = (int) $data['part'];

        // Acha a linha desse trecho (ou o placeholder "gerando" da parte 1) e atualiza.
        $registro = PodcastAudio::firstOrNew([
            'modular_course_id' => $curso->id,
            'part'              => $parte,
        ]);
        $registro->title   = $data['title'] ?? ('Parte ' . $parte);
        $registro->version = $data['version'] ?? ($registro->version ?? 1);

        $bin = ! empty($data['audio_base64']) ? base64_decode($data['audio_base64'], true) : false;

        if ($bin !== false && strlen($bin) > 0) {
            $dir = public_path(self::DIR_PODCAST . '/' . $curso->id);
            if (! File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            if ($registro->audio_path && File::exists(public_path($registro->audio_path))) {
                File::delete(public_path($registro->audio_path));
            }
            $fname = 'podcast-p' . $parte . '-' . time() . '-' . Str::lower(Str::random(4)) . '.wav';
            File::put($dir . '/' . $fname, $bin);
            $registro->audio_path = self::DIR_PODCAST . '/' . $curso->id . '/' . $fname;
            $registro->status     = 'pronto';
        } else {
            // trecho falhou (Gemini nao retornou audio) - marca erro, mas nao derruba os outros
            $registro->status = 'erro';
        }

        $registro->save();

        return response()->json(['ok' => true, 'part' => $parte, 'status' => $registro->status]);
    }

    public function podcastAudioDestroy(int $id)
    {
        $this->authorize('admin.cursos');
        $curso = ModularCourse::findOrFail($id);
        foreach ($curso->podcastAudios as $audio) {
            $this->apagarAudio($audio);
        }
        $curso->podcastAudios()->delete();

        return back()->with('success', 'Audios do podcast removidos.');
    }

    // ──────────────────────────── HELPERS ───────────────────────────────

    private function payloadRoteiros(ModularCourse $curso): array
    {
        return [
            'course_id'    => $curso->id,
            'title'        => $curso->title,
            'apostila_url' => $curso->apostilaUrl(),
            'callback_url' => url('/api/n8n/cursos-modulares/assets'),
            'type'         => null,
            'feedback'     => null,
            'version'      => 1,
        ];
    }

    private function payloadMedia(ModularCourse $curso): array
    {
        return [
            'course_id'    => $curso->id,
            'title'        => $curso->title,
            'description'  => $curso->description,
            'callback_url' => url('/api/n8n/cursos-modulares/media-kit'),
            'type'         => null,
            'feedback'     => null,
            'version'      => 1,
        ];
    }

    private function mediaKitWebhook(): string
    {
        return config('cursos_modulares.n8n_mediakit_webhook_url')
            ?: 'https://n8n.unyflex.com.br/webhook/cursos-modulares/media-kit';
    }

    private function podcastWebhook(): string
    {
        return config('cursos_modulares.n8n_podcast_webhook_url')
            ?: 'https://n8n.unyflex.com.br/webhook/cursos-modulares/podcast-audio';
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

    private function asset(int $courseId, int $assetId): ModularCourseAsset
    {
        return ModularCourseAsset::where('modular_course_id', $courseId)->where('id', $assetId)->firstOrFail();
    }

    private function mediaAsset(int $courseId, int $assetId): MediaKitAsset
    {
        return MediaKitAsset::where('modular_course_id', $courseId)->where('id', $assetId)->firstOrFail();
    }

    private function talvezConcluir(ModularCourse $curso): void
    {
        $tipos     = array_keys(config('cursos_modulares.tipos'));
        $aprovados = $curso->assets()->where('status', 'aprovado')->pluck('type')->all();

        if (count(array_intersect($tipos, $aprovados)) === count($tipos)) {
            $curso->status = 'publicado';
            $curso->save();
        }
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
            Log::warning('n8n cursos-modulares: ' . $e->getMessage());
            return false;
        }
    }

    private function guardarApostila(ModularCourse $curso, $file): void
    {
        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'apostila';
        $nome = $base . '-' . time() . '.' . $file->getClientOriginalExtension();
        $size = $file->getSize();

        $destino = public_path(self::DIR_APOSTILA);
        if (! File::isDirectory($destino)) {
            File::makeDirectory($destino, 0755, true);
        }
        $file->move($destino, $nome);

        $curso->apostila_path          = self::DIR_APOSTILA . '/' . $nome;
        $curso->apostila_original_name = $file->getClientOriginalName();
        $curso->apostila_mime          = 'application/pdf';
        $curso->apostila_size          = $size;
    }

    private function apagarApostila(ModularCourse $curso): void
    {
        if ($curso->apostila_path) {
            $full = public_path($curso->apostila_path);
            if (File::exists($full)) {
                File::delete($full);
            }
        }
    }

    private function apagarImagem(MediaKitAsset $asset): void
    {
        if ($asset->image_path) {
            $full = public_path($asset->image_path);
            if (File::exists($full)) {
                File::delete($full);
            }
        }
    }

    private function apagarAudio(PodcastAudio $audio): void
    {
        if ($audio->audio_path) {
            $full = public_path($audio->audio_path);
            if (File::exists($full)) {
                File::delete($full);
            }
        }
    }
}
