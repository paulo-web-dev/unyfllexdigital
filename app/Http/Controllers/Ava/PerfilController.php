<?php

namespace App\Http\Controllers\Ava;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PerfilController extends Controller
{
    public function index()
    {
        return view('pages.ava.perfil');
    }

    public function update(Request $request)
    {
        if ($request->input('action') === 'password') {
            $request->validate([
                'password_current'     => ['required'],
                'password'             => ['required', 'min:8', 'confirmed'],
            ]);
            // TODO: verificar senha atual e atualizar
            return back()->with('success', 'Senha atualizada com sucesso.');
        }

        $request->validate([
            'name'  => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'cargo' => ['nullable', 'string', 'max:120'],
            'orgao' => ['nullable', 'string', 'max:180'],
        ]);

        // TODO: auth()->user()->update($request->only(['name','email','cargo','orgao']));

        return back()->with('success', 'Perfil atualizado com sucesso.');
    }
}
