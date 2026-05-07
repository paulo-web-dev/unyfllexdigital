<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CursoController extends Controller
{
    public function index()
    {
        return view('pages.cursos');
    }

    public function show(string $slug)
    {
        // Futuramente: buscar curso do banco pelo slug
        // $curso = Curso::where('slug', $slug)->firstOrFail();
        return view('pages.curso-show', compact('slug'));
    }
}
