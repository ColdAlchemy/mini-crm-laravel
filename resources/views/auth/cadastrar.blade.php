@extends('layout')
@section('titulo', 'Criar conta')
@section('erros-no-campo', '1')
@section('entrada')
<div class="entrada">
    <div class="entrada-cartao">
        <span class="marca" translate="no"><span class="marca-ponto" aria-hidden="true"></span><span>Balcão<small>mini CRM do comércio</small></span></span>
        <h1>Criar conta</h1>
        <form method="POST" action="{{ route('cadastrar') }}" novalidate>
            @csrf
            <div class="campo">
                <label for="name">Seu nome</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required
                       @error('name') aria-invalid="true" aria-describedby="name-erro" @enderror>
                @error('name') <p class="erro-campo" id="name-erro">{{ $message }}</p> @enderror
            </div>
            <div class="campo">
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" spellcheck="false" required
                       @error('email') aria-invalid="true" aria-describedby="email-erro" @enderror>
                @error('email') <p class="erro-campo" id="email-erro">{{ $message }}</p> @enderror
            </div>
            <div class="campo">
                <label for="password">Senha</label>
                <input id="password" name="password" type="password" autocomplete="new-password" required
                       aria-describedby="password-dica @error('password') password-erro @enderror" @error('password') aria-invalid="true" @enderror>
                <p class="dica" id="password-dica">Pelo menos 8 caracteres.</p>
                @error('password') <p class="erro-campo" id="password-erro">{{ $message }}</p> @enderror
            </div>
            <div class="campo">
                <label for="password_confirmation">Repita a senha</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            </div>
            <button type="submit" class="botao" style="width:100%">Criar conta</button>
        </form>
        <p class="pequeno" style="margin:1rem 0 0">Já tem conta? <a href="{{ route('entrar') }}">Entrar</a></p>
    </div>
</div>
@endsection
