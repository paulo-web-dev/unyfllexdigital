<?php

namespace App\Http\Controllers\Ava;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Perfil do usuário logado (aluno matriculado e assinante usam a mesma rota).
 * Atualiza a tabela users (login) e mantém students em sincronia, que é a
 * tabela usada pelo checkout, pelo admin e pelos relatórios.
 */
class PerfilController extends Controller
{
    public function index()
    {
        // Assinante: view/layout próprios da área do assinante. Aluno matriculado: AVA.
        if (auth()->user()->assinaturaVigente()) {
            return view('assinante.perfil');
        }

        return view('pages.ava.perfil');
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        // ── Troca de senha ──────────────────────────────────────────────
        if ($request->input('action') === 'password') {
            $request->validate([
                'password_current' => ['required', 'string'],
                'password'         => ['required', 'string', 'min:8', 'confirmed'],
            ], [
                'password_current.required' => 'Informe sua senha atual.',
                'password.required'         => 'Informe a nova senha.',
                'password.min'              => 'A nova senha deve ter no mínimo 8 caracteres.',
                'password.confirmed'        => 'A confirmação não confere com a nova senha.',
            ]);

            if (! Hash::check($request->input('password_current'), $user->password)) {
                return back()
                    ->withErrors(['password_current' => 'Senha atual incorreta.'])
                    ->with('perfil_aba', 'senha');
            }

            $user->forceFill(['password' => Hash::make($request->input('password'))])->save();

            return back()->with('success', 'Senha atualizada com sucesso.');
        }

        // ── Dados cadastrais ────────────────────────────────────────────
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180', Rule::unique('users', 'email')->ignore($user->id)],
            'cargo' => ['nullable', 'string', 'max:120'],
            'orgao' => ['nullable', 'string', 'max:180'],
        ], [
            'name.required'  => 'Informe seu nome.',
            'email.required' => 'Informe seu e-mail.',
            'email.email'    => 'E-mail inválido.',
            'email.unique'   => 'Este e-mail já está em uso por outra conta.',
        ]);

        $email = strtolower(trim($data['email']));

        $user->name  = trim($data['name']);
        $user->email = $email;
        // Só sobrescreve cargo/órgão quando o campo veio preenchido, para não
        // apagar dados existentes se o formulário enviar o campo vazio.
        if ($request->filled('cargo')) {
            $user->funcao = trim($data['cargo']);
        }
        if ($request->filled('orgao')) {
            $user->setor = trim($data['orgao']);
        }
        $user->save();

        // Mantém students alinhado (mesma regra que o checkout usa ao reutilizar aluno).
        if ($user->student_id && ($student = Student::find($user->student_id))) {
            $student->name  = $user->name;
            $student->email = $email;
            if ($request->filled('cargo')) {
                $student->cargo = $user->funcao;
            }
            if ($request->filled('orgao')) {
                $student->entidade = $user->setor;
            }
            $student->save();
        }

        return back()->with('success', 'Perfil atualizado com sucesso.');
    }
}
