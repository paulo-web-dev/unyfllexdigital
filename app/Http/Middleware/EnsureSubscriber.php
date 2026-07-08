<?php

namespace App\Http\Middleware;

use App\Models\Student;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSubscriber
{
    /**
     * Libera o acesso apenas para alunos com assinatura ativa.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $student = ($user && $user->student_id) ? Student::find($user->student_id) : null;

        if (!$student || !$student->isAssinante()) {
            return redirect()->route('dashboard')
                ->with('warning', 'Você não tem uma assinatura ativa.');
        }

        return $next($request);
    }
}
