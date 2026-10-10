<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function formEntrar()
    {
        return view('auth.entrar');
    }

    public function entrar(Request $request): RedirectResponse
    {
        $credenciais = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Informe o e-mail.',
            'email.email' => 'Esse e-mail não parece válido. Confira se não falta o @.',
            'password.required' => 'Informe a senha.',
        ]);

        if (! Auth::attempt($credenciais, $request->boolean('lembrar'))) {
            throw ValidationException::withMessages(['email' => 'E-mail ou senha incorretos. Confira e tente de novo.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('painel'));
    }

    public function formCadastrar()
    {
        return view('auth.cadastrar');
    }

    public function cadastrar(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'name.required' => 'Informe seu nome.',
            'email.required' => 'Informe o e-mail.',
            'email.email' => 'Esse e-mail não parece válido. Confira se não falta o @.',
            'email.unique' => 'Já existe uma conta com esse e-mail. Tente entrar.',
            'password.required' => 'Crie uma senha.',
            'password.min' => 'A senha precisa ter pelo menos 8 caracteres.',
            'password.confirmed' => 'As senhas não são iguais. Digite de novo.',
        ]);

        Auth::login(User::create($dados));
        $request->session()->regenerate();

        return redirect()->route('painel')->with('ok', 'Conta criada. Bem-vindo(a) ao Balcão!');
    }

    public function sair(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('entrar');
    }
}
