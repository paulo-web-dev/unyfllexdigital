<?php

namespace App\Http\Controllers\Ava;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\ModularCourse;

class SubscriptionAreaController extends Controller
{
    /** Catálogo do assinante: TODAS as minisséries + TODOS os cursos modulares. */
    public function home()
    {
        $minisseries = Classes::where('express', '1')
            ->where('status', 'able')
            ->orderBy('title')
            ->get(['id', 'title', 'subtitle', 'slug', 'photo']);

        $modulares = ModularCourse::where('status', 'publicado')
            ->with(['coverArt' => fn ($q) => $q->where('status', 'pronto')])
            ->orderBy('title')
            ->get();

        return view('assinante.home', compact('minisseries', 'modulares'));
    }
}
