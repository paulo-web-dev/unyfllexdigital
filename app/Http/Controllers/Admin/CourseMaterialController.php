<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseMaterial;
use App\Models\ModularCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Materiais de estudo em PDF (resumo / cartões / notas / prova).
 *
 * FASE 5 (parte 1): RESUMOS em PDF.
 *   - dispara o n8n (Claude gera os resumos -> renderizador vira PDF)
 *   - callback do n8n entra por routes/api.php, protegido pelo X-Webhook-Secret.
 *
 * Mesma tabela (course_materials) serve para os próximos tipos (cartões, prova).
 */
class CourseMaterialController extends Controller
{
    private const DIR_MATERIAIS = 'storage/materiais';

    // ───────────────────────────── GERAR (RESUMO) ─────────────────────────────

    public function gerarResumoPdf(int $id)
    {
        $this->authorize('admin.cursos');
        $curso = ModularCourse::findOrFail($id);

        $resumo = $curso->assets()->where('type', 'resumo')->first();
        abort_unless(
            $resumo && trim((string) $resumo->content) !== '',
            422,
            'Gere o roteiro de resumo antes de criar os PDFs.'
        );

        $versao = ((int) ($curso->courseMaterials()->where('type', 'resumo')->max('version') ?? 0)) + 1;

        // limpa os resumos anteriores (arquivos + registros) e cria um placeholder "gerando"
        foreach ($curso->courseMaterials()->where('type', 'resumo')->get() as $old) {
            $this->apagarMaterial($old);
        }
        $curso->courseMaterials()->where('type', 'resumo')->delete();

        CourseMaterial::create([
            'modular_course_id' => $curso->id,
            'type'              => 'resumo',
            'title'             => 'Gerando...',
            'status'            => 'gerando',
            'sort_order'        => 1,
            'version'           => $versao,
        ]);

        $ok = $this->dispararN8n([
            'course_id'    => $curso->id,
            'title'        => $curso->title,
            'resumo'       => $resumo->content,
            'callback_url' => url('/api/n8n/cursos-modulares/materiais'),
            'version'      => $versao,
        ], $this->resumoPdfWebhook());

        return back()->with(
            $ok ? 'success' : 'warning',
            $ok ? 'Geração dos resumos em PDF disparada — os arquivos chegam em instantes (atualize a página).'
                : 'Não consegui acionar o n8n (resumos). Confira a URL do webhook.'
        );
    }

    // ───────────────────────────── GERAR (CARTÕES) ─────────────────────────────

    public function gerarCartoes(int $id)
    {
        $this->authorize('admin.cursos');
        $curso = ModularCourse::findOrFail($id);

        $resumo = $curso->assets()->where('type', 'resumo')->first();
        abort_unless(
            $resumo && trim((string) $resumo->content) !== '',
            422,
            'Gere o roteiro de resumo antes de criar os cartões.'
        );

        $versao = ((int) ($curso->courseMaterials()->where('type', 'cartoes')->max('version') ?? 0)) + 1;

        foreach ($curso->courseMaterials()->where('type', 'cartoes')->get() as $old) {
            $this->apagarMaterial($old);
        }
        $curso->courseMaterials()->where('type', 'cartoes')->delete();

        CourseMaterial::create([
            'modular_course_id' => $curso->id,
            'type'              => 'cartoes',
            'title'             => 'Gerando...',
            'status'            => 'gerando',
            'sort_order'        => 1,
            'version'           => $versao,
        ]);

        $ok = $this->dispararN8n([
            'course_id'    => $curso->id,
            'title'        => $curso->title,
            'resumo'       => $resumo->content,
            'callback_url' => url('/api/n8n/cursos-modulares/materiais'),
            'version'      => $versao,
        ], $this->cartoesWebhook());

        return back()->with(
            $ok ? 'success' : 'warning',
            $ok ? 'Geração dos cartões disparada — chegam em instantes (atualize a página).'
                : 'Não consegui acionar o n8n (cartões). Confira a URL do webhook.'
        );
    }

    // ───────────────────────────── GERAR (PROVA / SIMULADO) ─────────────────────────────

    public function gerarProva(int $id)
    {
        $this->authorize('admin.cursos');
        $curso = ModularCourse::findOrFail($id);

        $resumo = $curso->assets()->where('type', 'resumo')->first();
        abort_unless(
            $resumo && trim((string) $resumo->content) !== '',
            422,
            'Gere o roteiro de resumo antes de criar a prova.'
        );

        $versao = ((int) ($curso->courseMaterials()->where('type', 'prova')->max('version') ?? 0)) + 1;

        foreach ($curso->courseMaterials()->where('type', 'prova')->get() as $old) {
            $this->apagarMaterial($old);
        }
        $curso->courseMaterials()->where('type', 'prova')->delete();

        CourseMaterial::create([
            'modular_course_id' => $curso->id,
            'type'              => 'prova',
            'title'             => 'Gerando...',
            'status'            => 'gerando',
            'sort_order'        => 1,
            'version'           => $versao,
        ]);

        $ok = $this->dispararN8n([
            'course_id'    => $curso->id,
            'title'        => $curso->title,
            'resumo'       => $resumo->content,
            'callback_url' => url('/api/n8n/cursos-modulares/materiais'),
            'version'      => $versao,
        ], $this->provaWebhook());

        return back()->with(
            $ok ? 'success' : 'warning',
            $ok ? 'Geração da prova disparada — chega em instantes (atualize a página).'
                : 'Não consegui acionar o n8n (prova). Confira a URL do webhook.'
        );
    }

    // ───────────────────────────── CALLBACK DO n8n ─────────────────────────────

    public function materialCallback(Request $request)
    {
        $this->validarSecret($request);

        $data = $request->validate([
            'course_id'              => ['required', 'integer'],
            'type'                   => ['required', 'string', 'max:20'],
            'materials'              => ['required', 'array', 'min:1'],
            'materials.*.title'      => ['nullable', 'string', 'max:160'],
            'materials.*.sort_order' => ['nullable', 'integer'],
            'materials.*.pdf_base64' => ['nullable', 'string'],
            'materials.*.content'    => ['nullable', 'string'],
        ]);

        $curso = ModularCourse::find($data['course_id']);
        if (! $curso) {
            return response()->json(['ok' => false, 'error' => 'curso nao encontrado'], 404);
        }

        $type = $data['type'];

        // limpa o que existia desse tipo (inclui o placeholder "gerando")
        foreach ($curso->courseMaterials()->where('type', $type)->get() as $old) {
            $this->apagarMaterial($old);
        }
        $curso->courseMaterials()->where('type', $type)->delete();

        $dir = public_path(self::DIR_MATERIAIS . '/' . $curso->id . '/' . $type);

        $salvos = 0;
        foreach ($data['materials'] as $i => $m) {
            $ordem  = (int) ($m['sort_order'] ?? ($i + 1));
            $titulo = $m['title'] ?? ((CourseMaterial::TIPOS[$type] ?? 'Material') . ' ' . $ordem);

            $pdfPath = null;
            $content = null;

            $bin = ! empty($m['pdf_base64']) ? base64_decode($m['pdf_base64'], true) : false;
            if ($bin !== false && strlen($bin) > 0) {
                if (! File::isDirectory($dir)) {
                    File::makeDirectory($dir, 0755, true);
                }
                $fname = $type . '-' . $ordem . '-' . time() . '-' . Str::lower(Str::random(4)) . '.pdf';
                File::put($dir . '/' . $fname, $bin);
                $pdfPath = self::DIR_MATERIAIS . '/' . $curso->id . '/' . $type . '/' . $fname;
            } elseif (! empty($m['content'])) {
                $content = $m['content'];
            } else {
                continue; // sem PDF e sem conteúdo -> ignora
            }

            CourseMaterial::create([
                'modular_course_id' => $curso->id,
                'type'              => $type,
                'title'             => $titulo,
                'pdf_path'          => $pdfPath,
                'content'           => $content,
                'status'            => 'pronto',
                'sort_order'        => $ordem,
                'version'           => 1,
            ]);
            $salvos++;
        }

        if ($salvos === 0) {
            CourseMaterial::create([
                'modular_course_id' => $curso->id,
                'type'              => $type,
                'title'             => 'Erro',
                'status'            => 'erro',
                'sort_order'        => 1,
                'version'           => 1,
            ]);
        }

        return response()->json(['ok' => true, 'type' => $type, 'saved' => $salvos]);
    }

    // ───────────────────────────── EXCLUIR ─────────────────────────────

    public function materialDestroy(int $id, string $type)
    {
        $this->authorize('admin.cursos');
        $curso = ModularCourse::findOrFail($id);

        foreach ($curso->courseMaterials()->where('type', $type)->get() as $m) {
            $this->apagarMaterial($m);
        }
        $curso->courseMaterials()->where('type', $type)->delete();

        return back()->with('success', 'Materiais removidos.');
    }

    // ───────────────────────────── HELPERS ─────────────────────────────

    private function resumoPdfWebhook(): string
    {
        return config('cursos_modulares.n8n_resumo_pdf_webhook_url')
            ?: 'https://n8n.unyflex.com.br/webhook/cursos-modulares/resumo-pdf';
    }

    private function cartoesWebhook(): string
    {
        return config('cursos_modulares.n8n_cartoes_webhook_url')
            ?: 'https://n8n.unyflex.com.br/webhook/cursos-modulares/cartoes';
    }

    private function provaWebhook(): string
    {
        return config('cursos_modulares.n8n_prova_webhook_url')
            ?: 'https://n8n.unyflex.com.br/webhook/cursos-modulares/prova';
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
            Log::warning('n8n materiais: ' . $e->getMessage());
            return false;
        }
    }

    private function apagarMaterial(CourseMaterial $m): void
    {
        if ($m->pdf_path) {
            $full = public_path($m->pdf_path);
            if (File::exists($full)) {
                File::delete($full);
            }
        }
    }
}
