<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModularCourse;
use App\Models\ModularEnrollment;
use App\Models\Student;
use Illuminate\Http\Request;

/**
 * Matrícula MANUAL de alunos em cursos modulares (feita pelo admin).
 * A matrícula por compra é tratada à parte (checkout/Asaas), gravando
 * na MESMA tabela modular_enrollments com source='compra'.
 */
class ModularEnrollmentController extends Controller
{
    public function matricular(Request $request, int $id)
    {
        $this->authorize('admin.cursos');
        $curso = ModularCourse::findOrFail($id);

        $data  = $request->validate(['ident' => ['required', 'string', 'max:160']]);
        $ident = trim($data['ident']);
        $digits = preg_replace('/\D/', '', $ident);

        $student = Student::where('email', $ident)->first();
        if (! $student && $digits !== '') {
            $student = Student::where('cpf', $digits)->orWhere('cpf', $ident)->first();
        }
        if (! $student) {
            return back()->with('warning', 'Aluno não encontrado por e-mail ou CPF.');
        }

        ModularEnrollment::updateOrCreate(
            ['modular_course_id' => $curso->id, 'student_id' => $student->id],
            ['status' => 'ativo', 'source' => 'manual', 'start_date' => now()]
        );

        return back()->with('success', 'Aluno matriculado: ' . $student->name . '.');
    }

    public function cancelar(int $id, int $matricula)
    {
        $this->authorize('admin.cursos');
        $curso = ModularCourse::findOrFail($id);

        $m = ModularEnrollment::where('modular_course_id', $curso->id)
            ->where('id', $matricula)
            ->firstOrFail();

        $m->status = 'cancelado';
        $m->save();

        return back()->with('success', 'Matrícula cancelada.');
    }
}
