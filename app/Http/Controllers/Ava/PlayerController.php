<?php

namespace App\Http\Controllers\Ava;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\Material;
use App\Models\Panel;
use App\Models\VideoLesson;
use App\Models\ViewsMinisserie;
use App\Models\ViewsMaterialsMinisserie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlayerController extends Controller
{
    public function show(string $slug, ?int $videoId = null)
    {
        $user = Auth::user();

        $classe = Classes::where('slug', $slug)
            ->where('express', '1')
            ->where('status', 'able')
            ->firstOrFail();

        $matricula = Enrollment::where('student_id', $user->student_id)
            ->where('classes_id', $classe->id)
            ->first();

        abort_unless($matricula, 403, 'Você não tem acesso a esta minissérie.');

        // Carrega panels SEM eager load de relacionamentos
        $panels = Panel::where('classes_id', $classe->id)
            ->where('status', 'able')
            ->orderBy('start_time')
            ->orderBy('id')
            ->get();

        $panelIds = $panels->pluck('id');

        // Carrega TODOS os vídeos dos panels de uma vez — query separada totalmente independente
        $todosVideos = VideoLesson::whereIn('panel_id', $panelIds)
            ->where('status', '!=','acdvfdegvble')
            ->orderBy('panel_id')
            ->orderBy('id')
            ->get()
            ->groupBy('panel_id');

        // Carrega TODOS os materiais dos panels de uma vez — query separada
        $todosMateriais = DB::table('material_panels')
            ->join('materials', 'material_panels.material_id', '=', 'materials.id')
            ->whereIn('material_panels.panel_id', $panelIds)
            ->where('materials.status', 'able')
            ->select('materials.*', 'material_panels.panel_id as pivot_panel_id')
            ->orderBy('materials.id')
            ->get()
            ->groupBy('pivot_panel_id');

        // Injeta manualmente nos panels
        foreach ($panels as $panel) {
            $panel->setRelation('video_lesson', $todosVideos->get($panel->id, collect()));
            $panel->setRelation('material',     $todosMateriais->get($panel->id, collect()));
        }

        // Views já assistidas
        $viewsIds = ViewsMinisserie::where('id_user', $user->id)
            ->where('classes_id', $classe->id)
            ->pluck('video_id')
            ->toArray();

        // Materiais já baixados
        $materiaisVistos = ViewsMaterialsMinisserie::where('id_user', $user->id)
            ->pluck('material_id')
            ->toArray();

        // Monta cápsulas flat
        $capsulas = $panels->flatMap(function ($panel) use ($viewsIds) {
            return $panel->video_lesson->map(fn ($v) => [
                'id'       => $v->id,
                'panel_id' => $v->panel_id,
                'feita'    => in_array($v->id, $viewsIds),
            ]);
        });

        $totalCnt   = $capsulas->count();
        $watchedCnt = $capsulas->where('feita', true)->count();

        $allVideos            = $capsulas->values();
        $primeiroNaoAssistido = $allVideos->firstWhere('feita', false);

        $capsulaAtiva = null;
        if ($videoId) {
            $capsulaAtiva = $allVideos->firstWhere('id', $videoId);
        }
        $capsulaAtiva = $capsulaAtiva ?? $primeiroNaoAssistido ?? $allVideos->first();

        $capsula = null;
        if ($capsulaAtiva) {
            $videoAtivo = VideoLesson::find($capsulaAtiva['id']);
            $panelAtivo = Panel::find($capsulaAtiva['panel_id']);

            $pNum = 1; $vNum = 1;
            foreach ($panels as $pIdx => $p) {
                foreach ($p->video_lesson as $vIdx => $v) {
                    if ($v->id == $capsulaAtiva['id']) {
                        $pNum = $pIdx + 1;
                        $vNum = $vIdx + 1;
                        break 2;
                    }
                }
            }

            $capsula = [
                'video_id'  => $videoAtivo->id,
                'panel_id'  => $panelAtivo->id,
                'numero'    => $pNum . '.' . $vNum,
                'titulo'    => $videoAtivo->titulo ?? '',
                'link'      => $videoAtivo->link ?? '',
                'descricao' => $videoAtivo->subtitle ?? $panelAtivo->content ?? '',
                'resumo'    => $panelAtivo->content ?? '',
                'trecho'    => 'Temporada ' . $pNum . ': ' . $panelAtivo->title,
            ];

            $this->_registrarView($user->id, $classe->id, $capsulaAtiva['panel_id'], $capsulaAtiva['id']);
        }

        $totalVideos     = $totalCnt;
        $totalAssistidos = $watchedCnt;
        $progresso       = $totalVideos > 0 ? (int) round(($watchedCnt / $totalVideos) * 100) : 0;

        return view('pages.ava.player', compact(
            'classe', 'capsulas', 'capsula', 'capsulaAtiva',
            'panels', 'materiaisVistos',
            'totalCnt', 'watchedCnt',
            'totalVideos', 'totalAssistidos', 'progresso'
        ));
    }

    public function concluir(Request $request, string $slug, int $videoId)
    {
        $user  = Auth::user();
        $video = VideoLesson::findOrFail($videoId);
        $panel = Panel::findOrFail($video->panel_id);

        $temMatricula = Enrollment::where('student_id', $user->student_id)
            ->where('classes_id', $panel->classes_id)
            ->where('status', 'checked')
            ->exists();

        if (!$temMatricula) {
            return response()->json(['ok' => false, 'msg' => 'Sem acesso.'], 403);
        }

        $this->_registrarView($user->id, $panel->classes_id, $panel->id, $videoId);

        $totalVideos  = VideoLesson::whereIn('panel_id',
                Panel::where('classes_id', $panel->classes_id)->where('status', 'able')->pluck('id')
            )->where('status', 'able')->count();

        $assistidos   = ViewsMinisserie::where('id_user', $user->id)
            ->where('classes_id', $panel->classes_id)
            ->distinct('video_id')->count('video_id');

        $progressoPct = $totalVideos > 0 ? (int) round(($assistidos / $totalVideos) * 100) : 0;

        Log::info('[Player] Cápsula concluída', [
            'user_id'    => $user->id,
            'video_id'   => $videoId,
            'classes_id' => $panel->classes_id,
            'progresso'  => $progressoPct . '%',
        ]);

        return response()->json([
            'ok'         => true,
            'assistidos' => $assistidos,
            'total'      => $totalVideos,
            'progresso'  => $progressoPct,
        ]);
    }

    public function registrarMaterial(Request $request, string $slug, int $materialId)
    {
        $user     = Auth::user();
        $material = Material::findOrFail($materialId);
        $classe   = Classes::where('slug', $slug)->firstOrFail();

        $pertence = $material->panels()
            ->where('panels.classes_id', $classe->id)
            ->exists();

        if (!$pertence) {
            return response()->json(['ok' => false, 'msg' => 'Material não pertence a esta minissérie.'], 403);
        }

        ViewsMaterialsMinisserie::firstOrCreate([
            'id_user'     => $user->id,
            'material_id' => $materialId,
        ]);

        Log::info('[Player] Material acessado', [
            'user_id'     => $user->id,
            'material_id' => $materialId,
            'classes_id'  => $classe->id,
        ]);

        return response()->json(['ok' => true]);
    }

    private function _registrarView(int $userId, int $classesId, int $panelId, int $videoId): void
    {
        ViewsMinisserie::firstOrCreate([
            'id_user'    => $userId,
            'classes_id' => $classesId,
            'panel_id'   => $panelId,
            'video_id'   => $videoId,
        ]);
    }
}