<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Panel;
use App\Models\PanelProva;
use App\Services\PanelProvaService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

/**
 * Prova por painel ("Curso Modular" do assinante), gerada pelo mesmo workflow
 * n8n das provas dos cursos modulares. Ver App\Services\PanelProvaService.
 */
class PanelProvaController extends Controller
{
    // ───────────────────────────── GERAR (admin) ─────────────────────────────

    public function gerar(int $id, PanelProvaService $service)
    {
        $this->authorize('admin.cursos');
        $panel = Panel::findOrFail($id);

        [$ok, $msg] = $service->gerar($panel);

        return back()->with($ok ? 'success' : 'warning', $ok
            ? 'Geração da prova disparada — chega em instantes (atualize a página).'
            : $msg);
    }

    // ───────────────────────────── CALLBACK DO n8n ─────────────────────────────

    /**
     * POST /api/n8n/paineis/prova — mesmo shape do callback de materiais dos
     * modulares (course_id, type, materials[].content), com course_id = id do painel.
     */
    public function provaCallback(Request $request)
    {
        $this->validarSecret($request);

        $data = $request->validate([
            'course_id'           => ['required', 'integer'],
            'type'                => ['nullable', 'string', 'max:20'],
            'materials'           => ['required', 'array', 'min:1'],
            'materials.*.title'   => ['nullable', 'string', 'max:160'],
            'materials.*.content' => ['nullable', 'string'],
        ]);

        $panel = Panel::find($data['course_id']);
        if (! $panel) {
            return response()->json(['ok' => false, 'error' => 'painel nao encontrado'], 404);
        }

        // Primeiro material cujo content seja um JSON válido de questões.
        $conteudo = null;
        $titulo   = null;
        foreach ($data['materials'] as $m) {
            $questoes = json_decode((string) ($m['content'] ?? ''), true);
            if (is_array($questoes) && count($questoes) > 0 && isset($questoes[0]['enunciado'])) {
                $conteudo = $m['content'];
                $titulo   = $m['title'] ?? 'Prova';
                break;
            }
        }

        try {
            $versao = ((int) PanelProva::where('panel_id', $panel->id)->max('version')) ?: 1;
            PanelProva::where('panel_id', $panel->id)->delete();

            PanelProva::create([
                'panel_id' => $panel->id,
                'title'    => $conteudo ? $titulo : 'Erro',
                'content'  => $conteudo,
                'status'   => $conteudo ? 'pronto' : 'erro',
                'version'  => $versao,
            ]);
        } catch (QueryException $e) {
            return response()->json(['ok' => false, 'error' => 'tabela panel_provas ausente — rode database/panel_provas.sql'], 500);
        }

        return response()->json(['ok' => true, 'panel_id' => $panel->id, 'status' => $conteudo ? 'pronto' : 'erro']);
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
}
