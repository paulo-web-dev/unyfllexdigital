<?php

namespace App\Http\Controllers\Ava;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\ModularCourse;
use Illuminate\Support\Facades\DB;

class SubscriptionAreaController extends Controller
{
    /** Catálogo do assinante: minisséries + cursos gravados + cursos modulares. */
    public function home()
    {
        // Minisséries (express = 1, publicadas)
        $minisseries = Classes::where('express', '1')
            ->where('status', 'able')
            ->orderBy('title')
            ->get(['id', 'title', 'subtitle', 'slug', 'photo']);

        // Cursos Gravados: turmas (unyflex = 1, express = 0) que possuem vídeo com link.
        $gravados = Classes::where('express', 0)
            ->where('unyflex', 1)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('video_lessons as vl')
                    ->join('panels as p', 'vl.panel_id', '=', 'p.id')
                    ->whereColumn('p.classes_id', 'classes.id')
                    ->whereNotNull('vl.link')
                    ->where('vl.link', '<>', '');
            })
            ->orderBy('title')
            ->get(['id', 'title', 'subtitle', 'slug', 'photo']);

        // Cursos modulares (publicados)
        $modulares = ModularCourse::where('status', 'publicado')
            ->with(['coverArt' => fn ($q) => $q->where('status', 'pronto')])
            ->orderBy('title')
            ->get();

        return view('assinante.home', compact('minisseries', 'gravados', 'modulares'));
    }
}
