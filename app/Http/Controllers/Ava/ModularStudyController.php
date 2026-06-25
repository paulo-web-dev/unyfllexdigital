<?php

namespace App\Http\Controllers\Ava;

use App\Http\Controllers\Controller;
use App\Models\ModularCourse;
use App\Models\ModularEnrollment;
use App\Models\ModularProvaAttempt;
use Illuminate\Http\Request;

/**
 * Área do aluno (AVA) para CURSOS MODULARES.
 * Lista os cursos em que o aluno está matriculado (ativo) e a tela de
 * estudo (resumos em PDF, cartões interativos e prova com nota gravada).
 */
class ModularStudyController extends Controller
{
    public function index()
    {
        $sid = auth()->user()->student_id;

        $cursos = ModularCourse::whereHas('enrollments', fn ($q) => $q
            ->where('student_id', $sid)->where('status', 'ativo'))
            ->with(['coverArt' => fn ($q) => $q->where('status', 'pronto')])
            ->orderBy('title')
            ->get();

        return view('pages.ava.modulares', compact('cursos'));
    }

    public function show(string $slug)
    {
        $sid   = auth()->user()->student_id;
        $curso = ModularCourse::where('slug', $slug)->firstOrFail();

        abort_unless($this->matriculado($curso->id, $sid), 403, 'Você não tem acesso a este curso.');

        $resumos = $curso->courseMaterials()->where('type', 'resumo')->where('status', 'pronto')->orderBy('sort_order')->get();
        $cartoes = $curso->courseMaterials()->where('type', 'cartoes')->where('status', 'pronto')->orderBy('sort_order')->get();
        $prova   = $curso->courseMaterials()->where('type', 'prova')->where('status', 'pronto')->orderBy('sort_order')->first();
        $questions = $prova ? (json_decode((string) $prova->content, true) ?: []) : [];

        $audios = $curso->podcastAudios()->where('status', 'pronto')->orderBy('part')->get();

        $tentativas = ModularProvaAttempt::where('modular_course_id', $curso->id)
            ->where('student_id', $sid)
            ->orderByDesc('id')
            ->limit(10)
            ->get();
        $melhor = (int) ($tentativas->max('score') ?? 0);

        return view('pages.ava.modular-show', compact('curso', 'resumos', 'cartoes', 'prova', 'questions', 'tentativas', 'melhor', 'audios'));
    }

    public function provaResultado(Request $request, int $id)
    {
        $sid   = auth()->user()->student_id;
        $curso = ModularCourse::findOrFail($id);

        abort_unless($this->matriculado($curso->id, $sid), 403);

        $data = $request->validate([
            'score'   => ['required', 'integer', 'min:0'],
            'total'   => ['required', 'integer', 'min:1'],
            'answers' => ['nullable', 'array'],
        ]);

        $score = min((int) $data['score'], (int) $data['total']);

        $att = ModularProvaAttempt::create([
            'modular_course_id' => $curso->id,
            'student_id'        => $sid,
            'score'             => $score,
            'total'             => (int) $data['total'],
            'answers'           => isset($data['answers']) ? json_encode($data['answers']) : null,
        ]);

        return response()->json([
            'ok'      => true,
            'score'   => $att->score,
            'total'   => $att->total,
            'percent' => $att->percent(),
        ]);
    }

    private function matriculado(int $courseId, $sid): bool
    {
        return ModularEnrollment::where('modular_course_id', $courseId)
            ->where('student_id', $sid)
            ->where('status', 'ativo')
            ->exists();
    }
}
