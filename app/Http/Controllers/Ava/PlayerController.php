<?php

namespace App\Http\Controllers\Ava;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function show($id = 1)
    {
        // TODO: buscar dados reais
        // $capsula = Panel::with('video_lesson','material')->findOrFail($id);

        $curso = ['titulo' => 'Patrimônio e Frotas Públicas com I.A.'];

        $capsula = [
            'numero'         => '1.4',
            'titulo'         => 'Controles Preventivos e Documentação',
            'trecho'         => 'Trecho 1 — Controles Preventivos e Documentação',
            'descricao'      => 'Nesta cápsula você vai estruturar pontos de controle internos para o setor de patrimônio e gerar documentação auditável a partir dos modelos prontos da minissérie.',
            'duracao'        => '14:32',
            'posicaoAtual'   => '05:31',
            'progressoVideo' => 38,
            'qtdMateriais'   => 3,
            'qtdMapas'       => 1,
            'meta'           => 'Temporada 1 · cápsula 4 de 12',
            'atualizadoEm'   => '06/05/2026',
            'resumo'         => '<p class="tp-p">Controles preventivos são pontos do processo onde a probabilidade de um erro virar problema é alta. Identificá-los exige mapear o fluxo do bem patrimonial — da entrada à baixa — e marcar os momentos em que falta documentação ou confirmação humana.</p>
                                 <p class="tp-p">A documentação auditável segue três princípios: <strong style="color:#fff">rastreabilidade</strong> (quem, quando, com base em quê), <strong style="color:#fff">imutabilidade</strong> (não se edita, se anexa correção) e <strong style="color:#fff">recuperação</strong> (qualquer auditor encontra em menos de 5 min).</p>',
            'mapaConceitos'  => ['Rastreabilidade', 'Imutabilidade', 'Recuperação'],
            'progressoPodcast'  => 22,
            'duracaoPodcast'    => '22:30',
            'posicaoPodcast'    => '04:48',
            'checklist' => [
                ['id' => 1, 'texto' => 'Mapeei o fluxo do bem patrimonial da entrada à baixa',           'feito' => true],
                ['id' => 2, 'texto' => 'Identifiquei os 3 pontos críticos do meu setor',                  'feito' => true],
                ['id' => 3, 'texto' => 'Adicionei rastreabilidade (quem / quando / base) em cada ponto',  'feito' => false],
                ['id' => 4, 'texto' => 'Defini formato imutável de registro (anexo, não edição)',          'feito' => false],
                ['id' => 5, 'texto' => 'Testei recuperação: auditor encontra documento em menos de 5 min', 'feito' => false],
            ],
        ];

        $temporada = [
            'label'      => 'Temporada 1',
            'titulo'     => 'Levantamento de Infraestrutura e Recursos',
            'concluidas' => 3,
            'total'      => 10,
            'progresso'  => 30,
        ];

        $capsulas = [
            ['n' => '1.1',  'id' => 1,  'titulo' => 'Introdução e Apresentação do Curso',    'duracao' => '12:48', 'feita' => true],
            ['n' => '1.2',  'id' => 2,  'titulo' => 'Fundamentos da Auditoria Automatizada', 'duracao' => '15:20', 'feita' => true],
            ['n' => '1.3',  'id' => 3,  'titulo' => 'Gestão por Riscos e Priorização',       'duracao' => '18:04', 'feita' => true],
            ['n' => '1.4',  'id' => 4,  'titulo' => 'Controles Preventivos e Documentação',  'duracao' => '14:32', 'feita' => false],
            ['n' => '1.5',  'id' => 5,  'titulo' => 'Cruzamento de Dados entre Setores',     'duracao' => '17:11', 'feita' => false],
            ['n' => '1.6',  'id' => 6,  'titulo' => 'Gestão de Pessoas e Desafios',          'duracao' => '13:45', 'feita' => false],
            ['n' => '1.7',  'id' => 7,  'titulo' => 'Integração com Sistemas de TI',         'duracao' => '16:08', 'feita' => false],
            ['n' => '1.8',  'id' => 8,  'titulo' => 'Indicadores de Performance',            'duracao' => '11:50', 'feita' => false],
            ['n' => '1.9',  'id' => 9,  'titulo' => 'Painéis e Visualização de Dados',       'duracao' => '19:22', 'feita' => false],
            ['n' => '1.10', 'id' => 10, 'titulo' => 'Revisão e Próximos Passos',             'duracao' => '09:36', 'feita' => false],
        ];

        // Navegação anterior/próxima baseada no $id
        $idsOrdenados   = array_column($capsulas, 'id');
        $posicao        = array_search((int) $id, $idsOrdenados);
        $idAnterior     = $posicao > 0 ? $idsOrdenados[$posicao - 1] : null;
        $idProximo      = ($posicao !== false && $posicao < count($idsOrdenados) - 1) ? $idsOrdenados[$posicao + 1] : null;

        // Marca a cápsula ativa
        foreach ($capsulas as &$c) {
            $c['ativa'] = $c['id'] === (int) $id;
        }

        return view('pages.ava.player', compact(
            'curso', 'capsula', 'temporada', 'capsulas', 'idAnterior', 'idProximo'
        ));
    }

    public function concluir(Request $request, $id)
    {
        // TODO: Progresso::updateOrCreate(['user_id' => auth()->id(), 'capsula_id' => $id], ['concluida' => true]);
        return response()->json(['ok' => true]);
    }
}
