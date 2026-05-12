<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Enrollment;
use App\Models\Classes;
use App\Models\Student;
use App\Models\ViewsMaterialsMinisserie;
use App\Models\ViewsMinisserie;

class DashboardController extends Controller
{
    public function index()
    {
        $user    = auth()->user();
        $idAluno = $user->student_id;
        $idUser = $user->id;
        // ══════════════════════════════════════════════════════════════
        // 1. MATRÍCULAS DO ALUNO (minisséries)
        //    Uma única query com eager loading de tudo que precisamos.
        // ══════════════════════════════════════════════════════════════
        $matriculas = Enrollment::where('student_id', $idAluno)
            ->where('modality', 'minisserie')
            ->with([
                'classes' => fn ($q) => $q->with([
                    'panels' => fn ($q) => $q->with('video_lesson')
                                             ->where('status', 'able')
                ])
            ])
            ->get();

        // IDs das classes matriculadas (para recomendados)
        $classesIds = $matriculas->pluck('classes_id')->filter()->values();

        // ══════════════════════════════════════════════════════════════
        // 2. VISUALIZAÇÕES DO ALUNO
        //    Uma query só — usamos em stats, última cápsula e progresso.
        // ══════════════════════════════════════════════════════════════
        $views = ViewsMinisserie::where('id_user', $idUser)
            ->with(['video' => fn ($q) => $q->with('panels')])
            ->orderByDesc('updated_at')
            ->get();

        $videosAssistidosIds = $views->pluck('video_id')->unique()->filter()->values();
        $totalCapsulas       = $videosAssistidosIds->count();

        // ══════════════════════════════════════════════════════════════
        // 3. ESTATÍSTICAS
        // ══════════════════════════════════════════════════════════════

        // — Sequência de dias consecutivos —
        $sequenciaDias = $this->calcularSequencia($idUser);

        // — Tempo assistido (12 min médios por vídeo) —
        $minutos        = $totalCapsulas * 12;
        $tempoAssistido = $this->formatarTempo($minutos);
        $tempoDelta     = $totalCapsulas > 0
            ? '+' . $this->formatarTempo(min($minutos, 60)) . ' esta semana'
            : 'Comece a assistir!';

        // — Certificados (mockado até existir a tabela) —
        $certificados      = 0;
        $certificadosDelta = $matriculas->isNotEmpty()
            ? 'Próximo: ' . (optional($matriculas->first()->classes)->title ?? 'Em breve')
            : 'Nenhum curso iniciado';

        $stats = [
            'sequencia' => $sequenciaDias > 0 ? "{$sequenciaDias} " . ($sequenciaDias === 1 ? 'dia' : 'dias') : '—',            'sequenciaDelta'     => $sequenciaDias > 1 ? "🔥 {$sequenciaDias} dias seguidos" : 'Comece hoje!',
            'tempoAssistido'     => $tempoAssistido,
            'tempoDelta'         => $tempoDelta,
            'capsulasConcluidas' => (string) $totalCapsulas,
            'capsulasDelta'      => $matriculas->isNotEmpty()
                ? 'de ' . $this->totalVideosDasMatriculas($matriculas) . ' disponíveis'
                : 'nenhum curso iniciado',
            'certificados'       => (string) $certificados,
            'certificadosDelta'  => $certificadosDelta,
        ];

        // ══════════════════════════════════════════════════════════════
        // 4. ÚLTIMA CÁPSULA ASSISTIDA
        // ══════════════════════════════════════════════════════════════
        $ultimaView = $views->first(); // já ordenado por updated_at desc

        if ($ultimaView && $ultimaView->video) {
            $video  = $ultimaView->video;
            $panel  = optional($video->panels);
            $classe = $matriculas->firstWhere('classes_id', $ultimaView->classes_id)?->classes;

            // Número da cápsula (posição no panel) — fallback para panel_id
            $numeroPainel = optional($panel)->title ?? "Painel {$ultimaView->panel_id}";

            // Progresso da minissérie para essa última view
            $totalNaCurso   = $this->totalVideosDaClasse($matriculas, $ultimaView->classes_id);
            $assistidosCurso = $views->where('classes_id', $ultimaView->classes_id)
                                     ->pluck('video_id')->unique()->count();
            $progressoCurso  = $totalNaCurso > 0
                ? (int) round(($assistidosCurso / $totalNaCurso) * 100)
                : 0;

            $ultimaCapsula = [
                'video_id'      => $video->id,
                'titulo'        => $video->titulo ?? 'Sem título',
                'numero'        => $numeroPainel,
                'descricao'     => $video->subtitle ?? 'Continue de onde parou.',
                'tempoRestante' => '~' . (12 - min(12, (int) (now()->diffInMinutes($ultimaView->updated_at) / 5))) . ' min',
                'meta'          => optional($classe)->title ?? 'Minissérie',
                'progresso'     => $progressoCurso,
                'player_id'     => $ultimaView->video_id,
            ];
        } else {
            // Fallback elegante — sem histórico
            $primeiroCurso  = $matriculas->first();
            $primeiroVideo  = optional(optional($primeiroCurso?->classes)->panels->first())
                                ->video_lesson->first();

            $ultimaCapsula = [
                'video_id'      => optional($primeiroVideo)->id ?? 1,
                'titulo'        => $primeiroVideo
                    ? ($primeiroVideo->titulo ?? 'Primeira cápsula')
                    : 'Nenhuma cápsula iniciada',
                'numero'        => $primeiroVideo ? 'Cápsula 1' : '—',
                'descricao'     => $primeiroVideo
                    ? 'Comece sua primeira cápsula agora!'
                    : 'Você ainda não assistiu nenhuma aula. Que tal começar agora?',
                'tempoRestante' => '~12 min',
                'meta'          => optional($primeiroCurso?->classes)->title ?? 'Explore o catálogo',
                'progresso'     => 0,
                'player_id'     => optional($primeiroVideo)->id ?? 1,
            ];
        }

        // ══════════════════════════════════════════════════════════════
        // 5. CURSOS EM ANDAMENTO
        //    Para cada matrícula: calcula progresso real.
        // ══════════════════════════════════════════════════════════════
        $tones = [1, 2, 3, 4]; // paleta de cores cíclica

        $cursosEmAndamento = $matriculas
            ->filter(fn ($m) => $m->classes !== null)
            ->map(function ($matricula, $i) use ($views, $tones) {
                $classe   = $matricula->classes;
                $panels   = $classe->panels ?? collect();

                // Todos os video IDs da minissérie
                $todosVideos = $panels->flatMap(fn ($p) => $p->video_lesson)->pluck('id');
                $total       = $todosVideos->count();

                // Quantos o aluno assistiu
                $assistidos = $views->where('classes_id', $classe->id)
                                    ->pluck('video_id')
                                    ->unique()
                                    ->intersect($todosVideos)
                                    ->count();

                $progresso = $total > 0 ? (int) round(($assistidos / $total) * 100) : 0;

                // Badge inteligente
                $badge = match (true) {
                    $progresso === 0    => 'INICIANDO',
                    $progresso <= 30    => 'INICIANDO',
                    $progresso <= 80    => 'EM ANDAMENTO',
                    default             => 'QUASE LÁ',
                };

                // Duração estimada
                $duracao = $this->formatarTempo($total * 12);

                // Primeiro video disponível para o player
                $primeiroVideoId = optional($panels->first()?->video_lesson->first())->id ?? 1;

                return [
                    'tone'      => $tones[$i % 4],
                    'badge'     => $badge,
                    'duracao'   => $duracao,
                    'eyebrow' => 'MINISSÉRIE · ' . $total . ' ' . ($total === 1 ? 'CÁPSULA' : 'CÁPSULAS'),
                    'titulo'    => $classe->title,
                    'descricao' => $classe->subtitle ?? $classe->title,
                    'progresso' => $progresso,
                    'player_id' => $primeiroVideoId,
                    'classes_id'=> $classe->id,
                ];
            })
            ->filter(fn ($c) => $c['progresso'] < 100) // remove concluídos
            ->values()
            ->take(4); // máx 4 cards

        // ══════════════════════════════════════════════════════════════
        // 6. CURSOS RECOMENDADOS
        //    Classes que o aluno NÃO está matriculado + status able
        //    Ordenado pelas mais populares (mais matrículas no geral)
        // ══════════════════════════════════════════════════════════════
        $recomendados = Classes::whereNotIn('id', $classesIds)
            ->where('status', 'able')
            ->withCount('panels as total_panels')
            ->orderByDesc('total_panels') // mais ricas em conteúdo primeiro
            ->take(4)
            ->get();

        $cursosRecomendados = $recomendados->map(function ($classe, $i) use ($tones) {
            $totalVideos = $classe->panels_sum_video_lesson
                ?? $classe->total_panels * 3; // estimativa
            $duracao = $this->formatarTempo($totalVideos > 0 ? $totalVideos * 12 : $classe->total_panels * 12);

            return [
                'tone'      => $tones[$i % 4],
                'badge'     => null,
                'duracao'   => $duracao ?: '~30 min',
                'eyebrow'   => 'MINISSÉRIE · ' . $classe->total_panels . ' CÁPSULAS',
                'titulo'    => $classe->title,
                'descricao' => $classe->subtitle ?? $classe->title,
                'player_id' => null,
                'classes_id'=> $classe->id,
            ];
        });

        // Nome do aluno para saudação
        $nomeAluno = explode(' ', trim($user->name ?? 'Aluno'))[0];

        return view('pages.ava.dashboard', compact(
            'nomeAluno',
            'stats',
            'ultimaCapsula',
            'cursosEmAndamento',
            'cursosRecomendados',
        ));
    }

    // ──────────────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ──────────────────────────────────────────────────────────────────

    /**
     * Calcula dias consecutivos assistindo (sequência).
     * Conta dias distintos com views, de hoje para trás, sem lacuna.
     */
    private function calcularSequencia(int $idUser): int
    {
        $dias = ViewsMinisserie::where('id_user', $idUser)
            ->selectRaw('DATE(updated_at) as dia')
            ->groupBy('dia')
            ->orderByDesc('dia')
            ->pluck('dia')
            ->map(fn ($d) => \Carbon\Carbon::parse($d)->startOfDay());

        $sequencia = 0;
        $esperado  = now()->startOfDay();

        foreach ($dias as $dia) {
            if ($dia->eq($esperado) || $dia->eq($esperado->copy()->subDay())) {
                $sequencia++;
                $esperado = $dia->copy()->subDay();
            } else {
                break;
            }
        }

        return $sequencia;
    }

    /**
     * Formata minutos em "Xh Ymin" ou "Ymin".
     */
    private function formatarTempo(int $minutos): string
    {
        if ($minutos <= 0) return '0 min';
        $horas = intdiv($minutos, 60);
        $resto = $minutos % 60;
        if ($horas > 0 && $resto > 0) return "{$horas}h {$resto}min";
        if ($horas > 0)               return "{$horas}h";
        return "{$resto}min";
    }

    /**
     * Total de videoaulas de todas as matrículas.
     */
    private function totalVideosDasMatriculas($matriculas): int
    {
        return $matriculas->sum(function ($m) {
            return optional($m->classes)->panels
                ->flatMap(fn ($p) => $p->video_lesson)
                ->count() ?? 0;
        });
    }

    /**
     * Total de videoaulas de uma classe específica dentro das matrículas.
     */
    private function totalVideosDaClasse($matriculas, int $classesId): int
    {
        $matricula = $matriculas->firstWhere('classes_id', $classesId);
        if (!$matricula || !$matricula->classes) return 0;

        return $matricula->classes->panels
            ->flatMap(fn ($p) => $p->video_lesson)
            ->count();
    }
}
