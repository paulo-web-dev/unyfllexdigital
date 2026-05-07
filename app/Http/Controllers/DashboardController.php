<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Redireciona para o AVA (Ambiente Virtual de Aprendizagem).
     * Se o projeto AVA estiver no mesmo domínio, redireciona para /dashboard.
     * Caso contrário, ajuste a URL abaixo.
     */
    public function index()
    {
        return redirect('/dashboard');
    }
}
