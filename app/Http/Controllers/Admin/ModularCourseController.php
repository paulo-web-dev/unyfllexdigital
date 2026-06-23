<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModularCourse;
use App\Models\ModularCourseAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cursos Modulares — apostila PDF -> curso modular.
 *
 * FASE 2: dispara a geração no n8n, recebe os rascunhos de volta (callback),
 * e gerencia os assets (resumo / podcast / vídeo) com aprovar / reprovar +
 * comentário / editar dentro da tela do curso.
 *
 * Rotas do admin: grupo 'auth'+'admin', gate admin.cursos.
 * O callback do n8n entra por routes/api.php (sem CSRF) e é protegido por
 * um segredo compartilhado no header X-Webhook-Secret.
 */
class ModularCourseController extends Controller
{
    /** Pasta pública da apostila (dentro de public/, servida em /storage/...). */
    private const DIR = 'storage/cursos-modulares/apostilas';

    // ───────────────────────────── LISTAGEM ─────────────────────────────

    public function index()
    {
        $this->authorize('admin.cursos');

        $cursos = ModularCourse::orderByDesc('id')->paginate(20);

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

        return view('pages.admin.cursos-modulares.show', compact('curso', 'assets'));
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
        $curso->assets()->delete();
        $curso->delete();

        return redirect()
            ->route('admin.cursos-modulares')
            ->with('success', 'Curso modular removido.');
    }

    // ─────────────────────── DISPARAR GERAÇÃO NO N8N ────────────────────

    public function gerar(int $id)
    {
        $this->authorize('admin.cursos');
        $curso = ModularCourse::findOrFail($id);

        abort_unless($curso->hasApostila(), 422, 'Envie a apostila antes de gerar.');

        // Marca os tipos como "gerando" (placeholders que o callback vai preencher).
        foreach (array_keys(config('cursos_modulares.tipos')) as $tipo) {
            ModularCourseAsset::updateOrCreate(
                ['modular_course_id' => $curso->id, 'type' => $tipo],
                ['status' => 'gerando', 'feedback' => null]
            );
        }

        $curso->status = 'processando';
        $curso->save();

        $ok = $this->dispararN8n([
            'course_id'    => $curso->id,
            'title'        => $curso->title,
            'apostila_url' => $curso->apostilaUrl(),
            'callback_url' => url('/api/n8n/cursos-modulares/assets'),
            'type'         => null,
            'feedback'     => null,
            'version'      => 1,
        ]);

        return back()->with(
            $ok ? 'success' : 'warning',
            $ok
                ? 'Geração disparada. Os rascunhos chegam em instantes — atualize a página.'
                : 'Não consegui acionar o n8n. Confira a URL do webhook em config/cursos_modulares.php.'
        );
    }

    // ───────────────────────── AÇÕES DOS ASSETS ─────────────────────────

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
        $asset->update([
            'status'   => 'gerando',
            'feedback' => $data['feedback'],
            'version'  => $novaVersao,
        ]);

        $curso = $asset->course;
        $curso->status = 'processando';
        $curso->save();

        $ok = $this->dispararN8n([
            'course_id'    => $curso->id,
            'title'        => $curso->title,
            'apostila_url' => $curso->apostilaUrl(),
            'callback_url' => url('/api/n8n/cursos-modulares/assets'),
            'type'         => $asset->type,
            'feedback'     => $data['feedback'],
            'version'      => $novaVersao,
        ]);

        return back()->with(
            $ok ? 'success' : 'warning',
            $ok
                ? $asset->typeLabel() . ' enviado para refação com seu feedback.'
                : 'Salvei o feedback, mas não consegui acionar o n8n. Confira o webhook.'
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

    // ─────────────────── CALLBACK DO N8N (rota de API) ──────────────────

    public function callback(Request $request)
    {
        $secret = (string) config('cursos_modulares.n8n_secret');
        abort_unless(
            hash_equals($secret, (string) $request->header('X-Webhook-Secret')),
            401,
            'Secret invalido.'
        );

        $data = $request->validate([
            'course_id'         => ['required', 'integer'],
            'assets'            => ['required', 'array', 'min:1'],
            'assets.*.type'     => ['required', 'in:resumo,podcast,video'],
            'assets.*.content'  => ['nullable', 'string'],
            'assets.*.version'  => ['nullable', 'integer'],
        ]);

        $curso = ModularCourse::find($data['course_id']);
        if (! $curso) {
            return response()->json(['ok' => false, 'error' => 'curso nao encontrado'], 404);
        }

        foreach ($data['assets'] as $a) {
            ModularCourseAsset::updateOrCreate(
                ['modular_course_id' => $curso->id, 'type' => $a['type']],
                [
                    'content' => $a['content'] ?? '',
                    'version' => $a['version'] ?? 1,
                    'status'  => 'aguardando_revisao',
                ]
            );
        }

        // Volta o curso para um estado neutro enquanto você revisa.
        if ($curso->status === 'processando') {
            $curso->status = 'rascunho';
            $curso->save();
        }

        return response()->json(['ok' => true, 'saved' => count($data['assets'])]);
    }

    // ──────────────────────────── HELPERS ───────────────────────────────

    private function asset(int $courseId, int $assetId): ModularCourseAsset
    {
        return ModularCourseAsset::where('modular_course_id', $courseId)
            ->where('id', $assetId)
            ->firstOrFail();
    }

    /** Se os 3 tipos estiverem aprovados, marca o curso como publicado. */
    private function talvezConcluir(ModularCourse $curso): void
    {
        $tipos     = array_keys(config('cursos_modulares.tipos'));
        $aprovados = $curso->assets()->where('status', 'aprovado')->pluck('type')->all();

        if (count(array_intersect($tipos, $aprovados)) === count($tipos)) {
            $curso->status = 'publicado';
            $curso->save();
        }
    }

    private function dispararN8n(array $payload): bool
    {
        try {
            $resp = Http::withHeaders(['X-Webhook-Secret' => config('cursos_modulares.n8n_secret')])
                ->timeout(20)
                ->post(config('cursos_modulares.n8n_webhook_url'), $payload);

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

        $destino = public_path(self::DIR);
        if (! File::isDirectory($destino)) {
            File::makeDirectory($destino, 0755, true);
        }
        $file->move($destino, $nome);

        $curso->apostila_path          = self::DIR . '/' . $nome; // ex: storage/cursos-modulares/apostilas/xxx.pdf
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
}
