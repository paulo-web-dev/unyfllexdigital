<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function sobre()
    {
        return view('pages.sobre');
    }

    public function contato()
    {
        return view('pages.contato');
    }

    public function redirect()
    {
        return redirect()->away('https://unyflexincompany.pages.net.br/unyflexdigitalicitacoes');
        }


    public function checkout()
    {
        return view('pages.checkout');
    }

    public function login()
    {
        return view('pages.login');
    }

    public function contatoEnviar(Request $request)
    {
        $request->validate([
            'nome'     => 'required|string|max:120',
            'email'    => 'required|email',
            'assunto'  => 'required|string',
            'mensagem' => 'required|string|max:2000',
        ]);

        // TODO: Mail::to('contato@unyflexdigital.com.br')->send(new ContatoMail($request->all()));

        return back()->with('success', 'Mensagem enviada com sucesso! Retornaremos em até 24h.');
    }
}
