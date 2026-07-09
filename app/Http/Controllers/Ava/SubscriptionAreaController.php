<?php

namespace App\Http\Controllers\Ava;

use App\Http\Controllers\Controller;
use App\Models\AccessLog;
use App\Models\Classes;
use App\Models\ModularCourse;
use App\Models\ViewsMinisserie;
use Illuminate\Support\Facades\DB;

class SubscriptionAreaController extends Controller
{
    /** Catálogo do assinante: minisséries + cursos gravados + modulares, com marcação de assistidos. */
    public function home()
    {
        $uid = auth()->id();
        $sid = auth()->user()->student_id;

        // Minisséries (express = 1, publicadas) — mais recentes primeiro
        $minisseries = Classes::where('express', '1')
            ->where('status', 'able')
            ->orderByDesc('start_date')
            ->orderBy('title')
            ->get(['id', 'title', 'subtitle', 'slug', 'photo']);

        // Cursos Gravados: turmas (unyflex = 1, express = '0', able) com vídeo com link — recentes primeiro
        $gravados = Classes::where('unyflex', 1)
            ->where('express', '0')
            ->where('status', 'able')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('video_lessons as vl')
                    ->join('panels as p', 'vl.panel_id', '=', 'p.id')
                    ->whereColumn('p.classes_id', 'classes.id')
                    ->whereNotNull('vl.link')
                    ->where('vl.link', '<>', '');
            })
            ->orderByDesc('start_date')
            ->orderBy('title')
            ->get(['id', 'title', 'subtitle', 'slug', 'photo']);

        // Cursos modulares (publicados)
        $modulares = ModularCourse::where('status', 'publicado')
            ->with(['coverArt' => fn ($q) => $q->where('status', 'pronto')])
            ->orderBy('title')
            ->get();

        // ── Assistidos ────────────────────────────────────────────────────
        // Minisséries e gravados assistidos: via views_minisseries (classes_id).
        $assistidasClasses = ViewsMinisserie::where('id_user', $uid)
            ->pluck('classes_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        // Modulares assistidos: via access_logs (detail = "Modular: <título>").
        $modularesAssistidos = AccessLog::where('student_id', $sid)
            ->where('action', 'curso_view')
            ->where('detail', 'like', 'Modular: %')
            ->pluck('detail')
            ->map(fn ($d) => trim(str_replace('Modular:', '', $d)))
            ->unique()
            ->values()
            ->all();

        return view('assinante.home', compact(
            'minisseries', 'gravados', 'modulares',
            'assistidasClasses', 'modularesAssistidos'
        ));
    }
}
