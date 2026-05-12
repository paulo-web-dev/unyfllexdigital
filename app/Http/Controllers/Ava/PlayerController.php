<?php

namespace App\Http\Controllers\Ava;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Panel;
use App\Models\ViewsMinisserie;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    /**
     * Exibe o player de uma videoaula.
     *
     * Rota: GET /dashboard/player/{slug}/{videoId?}
     *
     * @param string   $slug    — slug da Classes
     * @param int|null $videoId — ID do video_lesson a exibir (opcional; padrão = primeiro)
     */
    public function show(string $slug, ?int $videoId = null)
    {
        $user   = auth()->user();
        $idUser = $user->id;

        // ── 1. Carrega a minissérie com todos os painéis e vídeos ──────────
        $classes = Classes::where('slug', $slug)
            ->with([
                'panels' => fn ($q) => $q
                    ->where('status', 'able')
                    ->with(['video_lesson', 'material'])
                    ->orderBy('start_time')
                    ->orderByRaw("CAST(horario AS TIME)")
            ])
            ->firstOrFail();

        $panels = $classes->panels;

        // ── 2. Monta lista flat de todos os vídeos com contexto ───────────
        // [ ['panel' => Panel, 'video' => VideoLesson, 'panelIndex' => int, 'videoIndex' => int] ]
        $todosVideos = collect();
        foreach ($panels as $pIdx => $panel) {
            foreach ($panel->video_lesson as $vIdx => $video) {
                $todosVideos->push([
                    'panel'      => $panel,
                    'video'      => $video,
                    'panelIndex' => $pIdx,
                    'videoIndex' => $vIdx,
                ]);
            }
        }

        // ── 3. Determina qual vídeo está ativo ────────────────────────────
        // Se vier $videoId, usa ele; senão pega o primeiro
        $ativoIndex = 0;
        if ($videoId) {
            $found = $todosVideos->search(fn ($v) => $v['video']->id === $videoId);
            if ($found !== false) $ativoIndex = $found;
        }

        $ativo      = $todosVideos->get($ativoIndex);
        $videoAtivo = $ativo['video'];
        $panelAtivo = $ativo['panel'];

        // ── 4. Registra a visualização ────────────────────────────────────
        ViewsMinisserie::updateOrCreate(
            [
                'id_user'    => $idUser,
                'video_id'   => $videoAtivo->id,
            ],
            [
                'classes_id' => $classes->id,
                'panel_id'   => $panelAtivo->id,
            ]
        );

        // ── 5. Visualizações do aluno nesta minissérie ────────────────────
        $videosVistos = ViewsMinisserie::where('id_user', $idUser)
            ->where('classes_id', $classes->id)
            ->pluck('video_id')
            ->unique();

        $totalVideos    = $todosVideos->count();
        $totalAssistidos = $videosVistos->count();
        $progresso      = $totalVideos > 0
            ? (int) round(($totalAssistidos / $totalVideos) * 100)
            : 0;

        // ── 6. Variável $curso (breadcrumb) ───────────────────────────────
        $curso = [
            'titulo' => $classes->title,
            'slug'   => $classes->slug,
        ];

        // ── 7. Variável $capsula (coluna esquerda) ────────────────────────
        $panelNumero = $ativo['panelIndex'] + 1;
        $videoNumero = $ativo['videoIndex'] + 1;

        // Materiais do painel ativo
        $materiais   = $panelAtivo->material ?? collect();

        $capsula = [
            'video_id'       => $videoAtivo->id,
            'numero'         => "{$panelNumero}.{$videoNumero}",
            'titulo'         => $videoAtivo->titulo ?? 'Sem título',
            'trecho'         => $panelAtivo->title ?? "Temporada {$panelNumero}",
            'descricao'      => $videoAtivo->subtitle ?? $panelAtivo->content ?? '',
            'link'           => $videoAtivo->link ?? '',
            'duracao'        => '~12 min',
            'posicaoAtual'   => '00:00',
            'progressoVideo' => 0,
            'qtdMateriais'   => $materiais->count(),
            'qtdMapas'       => 0,
            'meta'           => "Temporada {$panelNumero} · cápsula {$videoNumero} de " . $panelAtivo->video_lesson->count(),
            'atualizadoEm'   => optional($videoAtivo->updated_at)->format('d/m/Y') ?? '',
            'resumo'         => $panelAtivo->content
                ? '<p class="tp-p">' . e($panelAtivo->content) . '</p>'
                : '<p class="tp-p">Resumo não disponível para esta cápsula.</p>',
            'mapaConceitos'  => [],
            'progressoPodcast'  => 0,
            'duracaoPodcast'    => '~12 min',
            'posicaoPodcast'    => '00:00',
            'checklist'      => [],
        ];

        // ── 8. Temporada (cabeçalho da sidebar) ───────────────────────────
        $temporada = [
            'label'      => "Temporada {$panelNumero}",
            'titulo'     => $panelAtivo->title,
            'concluidas' => $videosVistos->count(),
            'total'      => $totalVideos,
            'progresso'  => $progresso,
        ];

        // ── 9. Lista de cápsulas (sidebar) ───────────────────────────────
        $capsulas = $todosVideos->map(function ($item, $idx) use ($videoAtivo, $videosVistos, $classes) {
            $p = $item['panel'];
            $v = $item['video'];
            $pN = $item['panelIndex'] + 1;
            $vN = $item['videoIndex'] + 1;

            return [
                'id'     => $v->id,
                'n'      => "{$pN}.{$vN}",
                'titulo' => $v->titulo ?? 'Sem título',
                'duracao'=> '~12 min',
                'feita'  => $videosVistos->contains($v->id),
                'ativa'  => $v->id === $videoAtivo->id,
                'slug'   => $classes->slug,
            ];
        });

        // ── 10. Navegação anterior/próxima ────────────────────────────────
        $idAnterior = $ativoIndex > 0
            ? $todosVideos->get($ativoIndex - 1)['video']->id
            : null;

        $idProximo = $ativoIndex < $todosVideos->count() - 1
            ? $todosVideos->get($ativoIndex + 1)['video']->id
            : null;

        // ── 11. Materiais para a aba (passados à view) ────────────────────
        $materiaisAtivos = $materiais;

        return view('pages.ava.player', compact(
            'classes',
            'panels',
            'curso',
            'capsula',
            'temporada',
            'capsulas',
            'idAnterior',
            'idProximo',
            'materiaisAtivos',
            'progresso',
            'totalVideos',
            'totalAssistidos',
        ));
    }

    /**
     * Marca cápsula como concluída via AJAX.
     * POST /dashboard/player/{slug}/{videoId}/concluir
     */
    public function concluir(Request $request, string $slug, int $videoId)
    {
        ViewsMinisserie::updateOrCreate(
            [
                'id_user'  => auth()->id(),
                'video_id' => $videoId,
            ],
            [
                'classes_id' => Classes::where('slug', $slug)->value('id'),
            ]
        );

        return response()->json(['ok' => true]);
    }
}
