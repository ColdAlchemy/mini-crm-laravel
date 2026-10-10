@extends('layout')
@section('titulo', 'Entrar')
@section('erros-no-campo', '1')
@section('entrada')
<div class="entrada">
    <div class="entrada-cartao">
        <span class="marca" translate="no"><span class="marca-ponto" aria-hidden="true"></span><span>Balcão<small>mini CRM do comércio</small></span></span>
        <h1>Entrar</h1>
        @if (session('ok'))<div class="aviso" role="status">{{ session('ok') }}</div>@endif
        <form method="POST" action="{{ route('entrar') }}" novalidate>
            @csrf
            <div class="campo">
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" spellcheck="false" required
                       @error('email') aria-invalid="true" aria-describedby="email-erro" @enderror>
                @error('email') <p class="erro-campo" id="email-erro">{{ $message }}</p> @enderror
            </div>
            <div class="campo">
                <label for="password">Senha</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required
                       @error('password') aria-invalid="true" aria-describedby="password-erro" @enderror>
                @error('password') <p class="erro-campo" id="password-erro">{{ $message }}</p> @enderror
            </div>
            <label class="linha-checkbox" for="lembrar">
                <input id="lembrar" name="lembrar" type="checkbox" value="1"> Manter conectado neste aparelho
            </label>
            <button type="submit" class="botao" style="width:100%">Entrar</button>
        </form>
        <p class="pequeno" style="margin:1rem 0 0">Ainda não tem conta? <a href="{{ route('cadastrar') }}">Criar uma conta</a></p>
        @if (app()->environment('local'))
            <p class="demo">Conta de demonstração (dados fictícios):<br><strong>demo@balcao.test</strong> · senha <strong>demonstracao</strong></p>
        @endif
    </div>
</div>
@endsection
