<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // TODO: substituir pelos dados reais do aluno autenticado
        // Exemplo: $user = auth()->user();
        //          $matriculas = $user->matriculas()->with('curso')->get();

        $nomeAluno = explode(' ', auth()->user()->name ?? 'Servidor')[0];

        $stats = [
            'sequencia'          => '7 dias',
            'sequenciaDelta'     => '+2 vs semana passada',
            'tempoAssistido'     => '4h 32m',
            'tempoDelta'         => '+38m esta semana',
            'capsulasConcluidas' => '12',
            'capsulasDelta'      => 'de 26 disponíveis',
            'certificados'       => '2',
            'certificadosDelta'  => 'Próximo: Patrimônio',
        ];

        $ultimaCapsula = [
            'numero'        => '1.4',
            'titulo'        => 'Controles Preventivos e Documentação',
            'descricao'     => 'Como estruturar pontos de controle internos e gerar documentação auditável usando os modelos prontos da minissérie.',
            'tempoRestante' => '14 min',
            'meta'          => 'Temporada 1 · cápsula 4 de 12',
            'progresso'     => 42,
        ];

        $cursosEmAndamento = [
            ['tone' => 1, 'badge' => 'EM ANDAMENTO', 'duracao' => '2h 48min', 'eyebrow' => 'MINISSÉRIE · 12 CÁPSULAS', 'titulo' => 'Patrimônio e Frotas Públicas com I.A.',         'descricao' => 'Levantamento, auditoria e controle de bens patrimoniais com apoio de inteligência artificial.', 'progresso' => 42, 'player_id' => 1],
            ['tone' => 2, 'badge' => 'EM ANDAMENTO', 'duracao' => '1h 52min', 'eyebrow' => 'MINISSÉRIE · 8 CÁPSULAS',  'titulo' => 'Lei 14.133 na prática',                          'descricao' => 'Como aplicar a Nova Lei de Licitações nos pregões eletrônicos do dia a dia da prefeitura.',  'progresso' => 18, 'player_id' => 2],
            ['tone' => 3, 'badge' => 'QUASE LÁ',     'duracao' => '3h 04min', 'eyebrow' => 'MINISSÉRIE · 14 CÁPSULAS', 'titulo' => 'Pregão eletrônico avançado',                      'descricao' => 'Estratégias de condução, análise de propostas e diligências bem documentadas.',               'progresso' => 88, 'player_id' => 3],
        ];

        $cursosRecomendados = [
            ['tone' => 4, 'badge' => 'NOVO', 'duracao' => '1h 22min', 'eyebrow' => 'MINISSÉRIE · 6 CÁPSULAS', 'titulo' => 'Auditoria contínua com dashboards',                    'descricao' => 'Construa indicadores que apontam riscos antes que virem problema.'],
            ['tone' => 1,                    'duracao' => '58min',     'eyebrow' => 'CÁPSULA AVULSA',           'titulo' => 'Como redigir um Termo de Referência sem retrabalho',  'descricao' => 'Modelo comentado + checklist final.'],
            ['tone' => 2,                    'duracao' => '2h 10min',  'eyebrow' => 'MINISSÉRIE · 9 CÁPSULAS', 'titulo' => 'Gestão de contratos públicos',                         'descricao' => 'Do recebimento à fiscalização contínua, passando por aditivos.'],
            ['tone' => 3,                    'duracao' => '46min',     'eyebrow' => 'CÁPSULA AVULSA',           'titulo' => 'LGPD para servidores municipais',                      'descricao' => 'Aplicação prática nos processos administrativos.'],
        ];

        return view('pages.ava.dashboard', compact(
            'nomeAluno',
            'stats',
            'ultimaCapsula',
            'cursosEmAndamento',
            'cursosRecomendados',
        ));
    }
}
