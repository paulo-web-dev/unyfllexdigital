<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Panel;
use App\Models\VideoLesson;
use Illuminate\Support\Facades\DB;

class CursoLandingController extends Controller
{
    public function show(string $slug)
    {
        // Busca o curso pela slug
        $curso = Classes::where('slug', $slug)
            ->where('status', 'able')
            ->firstOrFail();

        // Carrega painéis (temporadas) — query separada
        $panels = Panel::where('classes_id', $curso->id)
            ->where('status', '!=','acble')
            ->orderBy('start_time')
            ->orderBy('id')
            ->get();

        $panelIds = $panels->pluck('id');

        // Carrega todos os vídeos agrupados por painel
        $videosPorPanel = VideoLesson::whereIn('panel_id', $panelIds)
            ->where('status', '!=' ,'afvvble')
            ->orderBy('panel_id')
            ->orderBy('id')
            ->get()
            ->groupBy('panel_id');

        // Carrega materiais agrupados por painel
        $materiaisPorPanel = DB::table('material_panels')
            ->join('materials', 'material_panels.material_id', '=', 'materials.id')
            ->whereIn('material_panels.panel_id', $panelIds)
            ->where('materials.status', 'able')
            ->select('materials.*', 'material_panels.panel_id as pivot_panel_id')
            ->get()
            ->groupBy('pivot_panel_id');

        // Injeta nos painéis
        foreach ($panels as $panel) {
            $panel->setRelation('video_lesson', $videosPorPanel->get($panel->id, collect()));
            $panel->setRelation('material',     $materiaisPorPanel->get($panel->id, collect()));
        }

        // Totais
        $totalVideos    = $videosPorPanel->flatten()->count();
        $totalMateriais = $materiaisPorPanel->flatten()->count();
        $totalTemporadas = $panels->count();

        return view('pages.curso-lp', compact(
            'curso', 'panels', 
            'totalVideos', 'totalMateriais', 'totalTemporadas'
        ));
    }
}
