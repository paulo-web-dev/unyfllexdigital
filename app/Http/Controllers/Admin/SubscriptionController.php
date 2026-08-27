<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessLog;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SubscriptionController extends Controller
{
    /** Lista as assinaturas com estado, validade e resumo de acesso. */
    public function index(Request $request)
    {
        $busca = trim((string) $request->get('q', ''));

        // reais(): esconde a conta de teste da área do assinante (plano TESTE) — ela continua ativa para acesso.
        $query = Subscription::reais()->with('student')->orderByDesc('id');

        if ($busca !== '') {
            $query->whereHas('student', function ($q) use ($busca) {
                $q->where('name', 'like', "%{$busca}%")
                  ->orWhere('email', 'like', "%{$busca}%")
                  ->orWhere('cpf', 'like', "%{$busca}%");
            });
        }

        $assinaturas = $query->paginate(20)->withQueryString();

        // Resumo de logins por aluno (uma query só, sem N+1)
        $ids = $assinaturas->pluck('student_id')->filter()->unique()->values()->all();
        $logins = collect();
        if (!empty($ids)) {
            $logins = AccessLog::whereIn('student_id', $ids)
                ->where('action', 'login')
                ->selectRaw('student_id, COUNT(*) as total, MAX(created_at) as ultimo')
                ->groupBy('student_id')
                ->get()
                ->keyBy('student_id');
        }

        $stats = [
            'vigentes' => Subscription::reais()->vigentes()->count(),
            'total'    => Subscription::reais()->count(),
            'amai'     => Subscription::amai()->vigentes()->count(),
        ];

        return view('pages.admin.assinaturas.index', compact('assinaturas', 'logins', 'busca', 'stats'));
    }

    /** Formulário de nova assinatura. */
    public function create()
    {
        return view('pages.admin.assinaturas.form', [
            'assinatura' => new Subscription([
                'start_date' => Carbon::now()->toDateString(),
                'end_date'   => Carbon::now()->addYear()->toDateString(),
                'status'     => 'ativo',
            ]),
            'modo' => 'create',
        ]);
    }

    /** Cria a assinatura para o aluno identificado pelo e-mail. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'email'      => ['required', 'email'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'plano'      => ['nullable', 'string', 'max:60'],
            'observacao' => ['nullable', 'string', 'max:1000'],
        ], [], ['email' => 'e-mail do aluno']);

        $user = User::where('email', $data['email'])->first();
        if (!$user || !$user->student_id) {
            return back()->withInput()->withErrors(['email' => 'Nenhum aluno encontrado com este e-mail (ou o usuário não está vinculado a um aluno).']);
        }

        Subscription::create([
            'student_id' => $user->student_id,
            'status'     => 'ativo',
            'start_date' => $data['start_date'],
            'end_date'   => $data['end_date'],
            'plano'      => $data['plano'] ?? null,
            'observacao' => $data['observacao'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.assinaturas.index')
            ->with('success', 'Assinatura criada para ' . ($user->name ?? $data['email']) . '.');
    }

    /** Formulário de edição. */
    public function edit($id)
    {
        $assinatura = Subscription::with('student')->findOrFail($id);
        return view('pages.admin.assinaturas.form', compact('assinatura') + ['modo' => 'edit']);
    }

    /** Atualiza validade/plano/status (renovar, ajustar). */
    public function update(Request $request, $id)
    {
        $assinatura = Subscription::findOrFail($id);

        $data = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'status'     => ['required', 'in:ativo,cancelado'],
            'plano'      => ['nullable', 'string', 'max:60'],
            'observacao' => ['nullable', 'string', 'max:1000'],
        ]);

        $assinatura->update($data);

        return redirect()->route('admin.assinaturas.index')
            ->with('success', 'Assinatura atualizada.');
    }

    /** Cancela a assinatura (mantém histórico). */
    public function cancel($id)
    {
        $assinatura = Subscription::findOrFail($id);
        $assinatura->status = 'cancelado';
        $assinatura->save();

        return back()->with('success', 'Assinatura cancelada.');
    }

    /** Relatório de acessos do aluno: logins e cursos assistidos. */
    public function acessos($id)
    {
        $assinatura = Subscription::with('student')->findOrFail($id);
        $sid = $assinatura->student_id;

        $totalLogins = AccessLog::where('student_id', $sid)->where('action', 'login')->count();

        $logins = AccessLog::where('student_id', $sid)
            ->where('action', 'login')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        // Cursos acessados, agrupados pelo nome (detail), com contagem e último acesso.
        $cursos = AccessLog::where('student_id', $sid)
            ->where('action', 'curso_view')
            ->whereNotNull('detail')
            ->selectRaw('detail, COUNT(*) as vezes, MAX(created_at) as ultimo')
            ->groupBy('detail')
            ->orderByDesc('ultimo')
            ->get();

        return view('pages.admin.assinaturas.acessos', compact('assinatura', 'logins', 'totalLogins', 'cursos'));
    }
}
