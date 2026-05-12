<?php

namespace App\Http\Controllers\Ava;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\ViewsMinisserie;
use Illuminate\Http\Request;

class CursosAvaController extends Controller
{
    public function index(Request $request)
    {
        $user    = auth()->user();
        $idAluno = $user->student_id;
        $idUser  = $user->id;

        // ── 1. Matrículas do aluno ────────────────────────────────────────
        $matriculas = Enrollment::where('student_id', $idAluno)
            ->where('modality', 'minisserie')
            ->with([
                'classes' => fn ($q) => $q->with([
                    'panels' => fn ($q) => $q
                        ->where('status', 'able')
                        ->with('video_lesson')
                ])
            ])
            ->get();

        $classesMatriculadasIds = $matriculas->pluck('classes_id')->filter()->values();

        // ── 2. Visualizações do aluno (todas de uma vez) ──────────────────
        $views = ViewsMinisserie::where('id_user', $idUser)
            ->select('classes_id', 'video_id')
            ->get();

        // ── 3. Filtros disponíveis ────────────────────────────────────────
        $filtros = [
            ['valor' => 'todos',        'label' => 'Todos'],
            ['valor' => 'em-andamento', 'label' => 'Em andamento'],
            ['valor' => 'concluido',    'label' => 'Concluídos'],
            ['valor' => 'nao-iniciado', 'label' => 'Não iniciados'],
        ];

        $categoriaAtiva = $request->get('categoria', 'todos');

        // ── 4. Spotlight — minissérie mais recente com matrícula ──────────
        $spotlightClasse = $matriculas
            ->sortByDesc('created_at')
            ->first()?->classes;

        $spotlight = $spotlightClasse ? [
            'titulo'    => $spotlightClasse->title,
            'descricao' => $spotlightClasse->subtitle ?? $spotlightClasse->title,
            'slug'      => $spotlightClasse->slug,
            'photo'     => $spotlightClasse->photo ?? null,
        ] : null;

        // ── 5. Monta cards de cursos matriculados ─────────────────────────
        $tones = [1, 2, 3, 4];

        $cursos = $matriculas
            ->filter(fn ($m) => $m->classes !== null)
            ->map(function ($matricula, $i) use ($views, $tones, $categoriaAtiva) {
                $classe  = $matricula->classes;
                $panels  = $classe->panels ?? collect();

                // Total de vídeos da minissérie
                $todosVideos = $panels->flatMap(fn ($p) => $p->video_lesson)->pluck('id');
                $total       = $todosVideos->count();

                // Quantos o aluno assistiu
                $assistidos = $views
                    ->where('classes_id', $classe->id)
                    ->pluck('video_id')
                    ->unique()
                    ->intersect($todosVideos)
                    ->count();

                $progresso = $total > 0 ? (int) round(($assistidos / $total) * 100) : 0;

                // Badge inteligente
                $badge = match (true) {
                    $progresso === 0  => null,
                    $progresso <= 30  => 'INICIANDO',
                    $progresso <= 80  => 'EM ANDAMENTO',
                    $progresso < 100  => 'QUASE LÁ',
                    default           => 'CONCLUÍDO',
                };

                // Duração estimada
                $duracao = $this->formatarTempo($total * 12);

                // Primeiro vídeo para o player
                $primeiroVideo = $panels->first()?->video_lesson->first();

                // Filtro por categoria
                if ($categoriaAtiva !== 'todos') {
                    $passaFiltro = match ($categoriaAtiva) {
                        'em-andamento' => $progresso > 0 && $progresso < 100,
                        'concluido'    => $progresso === 100,
                        'nao-iniciado' => $progresso === 0,
                        default        => true,
                    };
                    if (!$passaFiltro) return null;
                }

                return [
                    'tone'      => $tones[$i % 4],
                    'badge'     => $badge,
                    'duracao'   => $duracao ?: '~12 min',
                    'eyebrow'   => 'MINISSÉRIE · ' . $total . ' ' . ($total === 1 ? 'CÁPSULA' : 'CÁPSULAS'),
                    'titulo'    => $classe->title,
                    'descricao' => $classe->subtitle ?? $classe->title,
                    'progresso' => $progresso > 0 ? $progresso : null,
                    'photo'     => 'https://unyflex.com.br/storage/cursos/banner/'.$classe->photo,
                    'slug'      => $classe->slug,
                    'player_id' => optional($primeiroVideo)->id,
                ];
            })
            ->filter() // remove os nulls (filtro de categoria)
            ->values();

        // ── 6. Totais para o cabeçalho ────────────────────────────────────
        $totalMinisseries = $cursos->count();
        $totalCapsulas    = $matriculas->sum(function ($m) {
            return optional($m->classes)->panels
                ->flatMap(fn ($p) => $p->video_lesson)
                ->count() ?? 0;
        });

        return view('pages.ava.cursos', compact(
            'filtros',
            'categoriaAtiva',
            'totalMinisseries',
            'totalCapsulas',
            'spotlight',
            'cursos',
        ));
    }

    private function formatarTempo(int $minutos): string
    {
        if ($minutos <= 0) return '0 min';
        $h = intdiv($minutos, 60);
        $m = $minutos % 60;
        if ($h > 0 && $m > 0) return "{$h}h {$m}min";
        if ($h > 0)            return "{$h}h";
        return "{$m}min";
    }
}
