<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()  { return view('pages.admin.dashboard'); }
    public function alunos()     { return view('pages.admin.alunos'); }
    public function matriculas() { return view('pages.admin.matriculas'); }
    public function cursos()     { return view('pages.admin.cursos'); }
    public function financeiro() { return view('pages.admin.financeiro'); }
    public function analytics()  { return view('pages.admin.analytics'); }

    // Telas "em breve" — mesma view genérica com o nome da seção
    public function vendas()     { return view('pages.admin.em-breve', ['titulo' => 'Vendas']); }
    public function cupons()     { return view('pages.admin.em-breve', ['titulo' => 'Cupons']); }
    public function certif()     { return view('pages.admin.em-breve', ['titulo' => 'Certificados']); }
    public function relatorios() { return view('pages.admin.em-breve', ['titulo' => 'Relatórios']); }
    public function suporte()    { return view('pages.admin.em-breve', ['titulo' => 'Suporte']); }
    public function equipe()     { return view('pages.admin.em-breve', ['titulo' => 'Equipe']); }
    public function permissoes() { return view('pages.admin.em-breve', ['titulo' => 'Permissões']); }
    public function logs()       { return view('pages.admin.em-breve', ['titulo' => 'Logs de atividade']); }
    public function integ()      { return view('pages.admin.em-breve', ['titulo' => 'Integrações']); }
    public function config()     { return view('pages.admin.em-breve', ['titulo' => 'Configurações']); }
}
