<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\ViewsMinisserie;

class SearchController extends Controller
{
    /**
     * GET /busca?q=termo
     * Retorna JSON com resultados para o dropdown em tempo real.
     */
    public function index(Request $request)
    {
        $q       = trim($request->get('q', ''));
        $idAluno = auth()->user()->student_id;
        $idUser  = auth()->id();

        if (strlen($q) < 2) {
            return response()->json(['results' => [], 'total' => 0]);
        }

        // IDs das minisséries que o aluno tem acesso (matriculado)
        $classesIds = Enrollment::where('student_id', $idAluno)
            ->where('modality', 'minisserie')
            ->pluck('classes_id');

        // Busca nas minisséries matriculadas
        $classes = Classes::whereIn('id', $classesIds)
            ->where(function ($q2) use ($q) {
                $q2->where('title',    'like', "%{$q}%")
                   ->orWhere('subtitle', 'like', "%{$q}%");
            })
            ->with([
                'panels' => fn ($p) => $p
                    ->where('status', 'able')
                    ->with([
                        'video_lesson' => fn ($v) => $v
                            ->where('titulo', 'like', "%{$q}%")
                            ->limit(3)
                    ])
                    ->limit(5)
            ])
            ->limit(5)
            ->get();

        // Visualizações do aluno (para marcar assistidos)
        $vistos = ViewsMinisserie::where('id_user', $idUser)
            ->pluck('video_id')
            ->flip(); // transforma em lookup O(1)

        $results = [];

        foreach ($classes as $classe) {
            // Resultado do tipo "minissérie"
            $results[] = [
                'type'    => 'minisserie',
                'titulo'  => $classe->title,
                'sub'     => $classe->subtitle ?? '',
                'photo'   => $classe->photo
                    ? "https://unyflex.com.br/storage/cursos/banner/{$classe->photo}"
                    : null,
                'url'     => route('player', $classe->slug),
            ];

            // Resultados do tipo "cápsula" dentro da minissérie
            foreach ($classe->panels as $panel) {
                foreach ($panel->video_lesson as $video) {
                    $results[] = [
                        'type'    => 'capsula',
                        'titulo'  => $video->titulo ?? 'Sem título',
                        'sub'     => $classe->title,
                        'visto'   => isset($vistos[$video->id]),
                        'url'     => route('player.video', [$classe->slug, $video->id]),
                    ];
                }
            }
        }

        return response()->json([
            'results' => array_slice($results, 0, 8),
            'total'   => count($results),
            'query'   => $q,
        ]);
    }
}
