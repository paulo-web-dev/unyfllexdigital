<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Models\User;

class AuthController extends Controller
{
    /*
    |----------------------------------------------------------------------
    | Login
    |----------------------------------------------------------------------
    */

    /** Exibe a tela de login (GET /login) */
    public function showLogin()
    {
        // Se já estiver logado, vai direto pro dashboard
        if (Auth::check()) {
            $user = auth()->user();

        if ($user->power >= 13) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->intended(route('dashboard'));
        }

        return view('pages.login');
    }

    /** Processa o login (POST /login) */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Informe seu e-mail.',
            'email.email'       => 'E-mail inválido.',
            'password.required' => 'Informe sua senha.',
        ]);
    
        $throttleKey = Str::lower($request->email) . '|' . $request->ip();
    
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => "Muitas tentativas. Tente novamente em {$seconds} segundos."]);
        }
    
        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');
    
        if (Auth::attempt($credentials, $remember)) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
    
            // Admin (power >= 13) vai para o painel admin
            if (Auth::user()->power >= 13) {
                return redirect()->route('admin.dashboard');
            }
    
            // Aluno vai para o AVA
            return redirect()->intended(route('dashboard'));
        }
    
        RateLimiter::hit($throttleKey, 60);
    
        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'E-mail ou senha incorretos.']);
    }

    /*
    |----------------------------------------------------------------------
    | Logout
    |----------------------------------------------------------------------
    */

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    /*
    |----------------------------------------------------------------------
    | Esqueci a senha
    |----------------------------------------------------------------------
    */

    /** Exibe tela "esqueci minha senha" (GET /esqueci-senha) */
    public function showForgotPassword()
    {
        return view('pages.forgot-password');
    }

    /** Envia o link de reset (POST /esqueci-senha) */
    public function sendResetLink(Request $request)
    {
        $request->validate(
            ['email' => ['required', 'email']],
            ['email.required' => 'Informe seu e-mail.', 'email.email' => 'E-mail inválido.']
        );

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', 'Link de redefinição enviado! Verifique sua caixa de entrada.');
        }

        return back()
            ->withInput()
            ->withErrors(['email' => __($status)]);
    }

    /** Exibe o formulário de nova senha (GET /redefinir-senha/{token}) */
    public function showResetPassword(Request $request, string $token)
    {
        return view('pages.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /** Redefine a senha (POST /redefinir-senha) */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'                 => ['required'],
            'email'                 => ['required', 'email'],
            'password'              => ['required', 'min:8', 'confirmed'],
        ], [
            'password.min'       => 'A senha deve ter no mínimo 8 caracteres.',
            'password.confirmed' => 'As senhas não conferem.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
                Auth::login($user);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('dashboard')
                ->with('success', 'Senha redefinida com sucesso!');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
