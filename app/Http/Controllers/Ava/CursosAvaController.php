<?php

namespace App\Http\Controllers\Ava;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CursosAvaController extends Controller
{
    public function index(Request $request)
    {
        // TODO: substituir por Classes::where('express', 1)->get() com filtro por categoria

        $filtros = [
            ['valor' => 'todos',        'label' => 'Todos'],
            ['valor' => 'em-andamento', 'label' => 'Em andamento'],
            ['valor' => 'pregao',       'label' => 'Pregão'],
            ['valor' => 'patrimonio',   'label' => 'Patrimônio'],
            ['valor' => 'contratos',    'label' => 'Contratos'],
            ['valor' => 'lgpd',         'label' => 'LGPD'],
            ['valor' => 'ia-aplicada',  'label' => 'I.A. aplicada'],
        ];

        $categoriaAtiva   = $request->get('categoria', 'todos');
        $totalMinisseries = 26;
        $totalCapsulas    = 184;

        $spotlight = [
            'titulo'    => 'Auditoria contínua com dashboards',
            'descricao' => 'Aprenda a montar indicadores que apontam riscos antes que virem problema, com 6 cápsulas curtas e um dashboard pronto para clonar.',
            'player_id' => 4,
        ];

        $cursos = [
            ['tone' => 1, 'badge' => 'EM ANDAMENTO', 'duracao' => '2h 48min', 'eyebrow' => 'MINISSÉRIE · 12 CÁPSULAS', 'titulo' => 'Patrimônio e Frotas Públicas com I.A.',         'descricao' => 'Levantamento, auditoria e controle de bens patrimoniais com apoio de I.A.',         'progresso' => 42, 'player_id' => 1],
            ['tone' => 2, 'badge' => 'EM ANDAMENTO', 'duracao' => '1h 52min', 'eyebrow' => 'MINISSÉRIE · 8 CÁPSULAS',  'titulo' => 'Lei 14.133 na prática',                          'descricao' => 'Como aplicar a Nova Lei de Licitações nos pregões do dia a dia.',                  'progresso' => 18, 'player_id' => 2],
            ['tone' => 3, 'badge' => 'NOVO',          'duracao' => '1h 22min', 'eyebrow' => 'MINISSÉRIE · 6 CÁPSULAS',  'titulo' => 'Auditoria contínua com dashboards',              'descricao' => 'Indicadores que apontam riscos antes que virem problema.',                         'player_id' => 4],
            ['tone' => 4,                             'duracao' => '2h 10min', 'eyebrow' => 'MINISSÉRIE · 9 CÁPSULAS',  'titulo' => 'Gestão de contratos públicos',                   'descricao' => 'Do recebimento à fiscalização contínua, passando por aditivos.',                   'player_id' => 5],
            ['tone' => 1,                             'duracao' => '58min',    'eyebrow' => 'CÁPSULA AVULSA',            'titulo' => 'Como redigir um Termo de Referência sem retrabalho', 'descricao' => 'Modelo comentado + checklist final.',                                         'player_id' => 6],
            ['tone' => 2,                             'duracao' => '46min',    'eyebrow' => 'CÁPSULA AVULSA',            'titulo' => 'LGPD para servidores municipais',                'descricao' => 'Aplicação prática nos processos administrativos.',                                 'player_id' => 7],
            ['tone' => 3, 'badge' => 'QUASE LÁ',     'duracao' => '3h 04min', 'eyebrow' => 'MINISSÉRIE · 14 CÁPSULAS', 'titulo' => 'Pregão eletrônico avançado',                     'descricao' => 'Estratégias de condução, análise e diligências bem documentadas.',               'progresso' => 88, 'player_id' => 3],
            ['tone' => 4,                             'duracao' => '1h 18min', 'eyebrow' => 'MINISSÉRIE · 5 CÁPSULAS',  'titulo' => 'Pesquisa de preços com inteligência',            'descricao' => 'Como construir preços de referência defensáveis em 4 fontes.',                    'player_id' => 8],
        ];

        return view('pages.ava.cursos', compact(
            'filtros', 'categoriaAtiva', 'totalMinisseries', 'totalCapsulas', 'spotlight', 'cursos'
        ));
    }
}
