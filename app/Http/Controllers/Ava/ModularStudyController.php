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

        // Acesso: matrícula no curso OU assinatura ativa.
        $assinante = $this->assinaturaVigente($sid);
        abort_unless($this->matriculado($curso->id, $sid) || $assinante, 403, 'Você não tem acesso a este curso.');

        // Registra o curso acessado (relatórios).
        \App\Models\AccessLog::registrar('curso_view', $sid, auth()->id(), 'Modular: ' . $curso->title);

        $resumos = $curso->courseMaterials()->where('type', 'resumo')->where('status', 'pronto')->orderBy('sort_order')->get();
        $cartoes = $curso->courseMaterials()->where('type', 'cartoes')->where('status', 'pronto')->orderBy('sort_order')->get();
        $prova   = $curso->courseMaterials()->where('type', 'prova')->where('status', 'pronto')->orderBy('sort_order')->first();
        $questions = $prova ? (json_decode((string) $prova->content, true) ?: []) : [];

        $audios = $curso->podcastAudios()->where('status', 'pronto')->orderBy('part')->get();

        $video = $curso->videos()
            ->where('status', 'pronto')
            ->whereNotNull('video_url')
            ->orderByDesc('id')
            ->first();

        $tentativas = ModularProvaAttempt::where('modular_course_id', $curso->id)
            ->where('student_id', $sid)
            ->orderByDesc('id')
            ->limit(10)
            ->get();
        $melhor = (int) ($tentativas->max('score') ?? 0);

        $layout = $assinante ? 'layouts.assinante' : 'layouts.app';

        return view('pages.ava.modular-show', compact('curso', 'resumos', 'cartoes', 'prova', 'questions', 'tentativas', 'melhor', 'audios', 'video', 'layout'));
    }

    public function provaResultado(Request $request, int $id)
    {
        $sid   = auth()->user()->student_id;
        $curso = ModularCourse::findOrFail($id);

        // Acesso: matrícula ativa OU assinatura vigente (mesma regra do show()).
        abort_unless($this->matriculado($curso->id, $sid) || $this->assinaturaVigente($sid), 403);

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

    /** True se o aluno (students.id) tem assinatura ativa e dentro da validade. */
    private function assinaturaVigente($sid): bool
    {
        if (!$sid) {
            return false;
        }
        $student = \App\Models\Student::find($sid);
        return $student ? $student->isAssinante() : false;
    }

    private function matriculado(int $courseId, $sid): bool
    {
        return ModularEnrollment::where('modular_course_id', $courseId)
            ->where('student_id', $sid)
            ->where('status', 'ativo')
            ->exists();
    }
}
