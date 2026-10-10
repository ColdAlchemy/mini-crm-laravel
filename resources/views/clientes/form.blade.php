@extends('layout')
@section('titulo', $cliente->exists ? 'Editar cliente' : 'Novo cliente')
@section('erros-no-campo', '1')
@section('conteudo')
<div class="topo">
    <div>
        <h1>{{ $cliente->exists ? 'Editar cliente' : 'Novo cliente' }}</h1>
        @if ($cliente->exists) <p>{{ $cliente->nome }}</p> @endif
    </div>
</div>

<form method="POST" action="{{ $cliente->exists ? route('clientes.update', $cliente) : route('clientes.store') }}" class="cartao" style="max-width:40rem" novalidate>
    @csrf
    @if ($cliente->exists) @method('PUT') @endif

    <div class="campo">
        <label for="nome">Nome</label>
        <input id="nome" name="nome" type="text" value="{{ old('nome', $cliente->nome) }}" maxlength="120" autocomplete="off" required
               @error('nome') aria-invalid="true" aria-describedby="nome-erro" @enderror>
        @error('nome') <p class="erro-campo" id="nome-erro">{{ $message }}</p> @enderror
    </div>
    <div class="form-linha" style="margin-bottom:1rem">
        <div class="campo">
            <label for="telefone">Telefone / WhatsApp</label>
            <input id="telefone" name="telefone" type="tel" inputmode="tel" value="{{ old('telefone', $cliente->telefone) }}" maxlength="30" autocomplete="off"
                   aria-describedby="telefone-dica">
            <p class="dica" id="telefone-dica">Com DDD, por exemplo (99) 98888-7777. É usado no botão do WhatsApp.</p>
        </div>
        <div class="campo">
            <label for="email">E-mail</label>
            <input id="email" name="email" type="email" value="{{ old('email', $cliente->email) }}" autocomplete="off" spellcheck="false"
                   @error('email') aria-invalid="true" aria-describedby="email-erro" @enderror>
            @error('email') <p class="erro-campo" id="email-erro">{{ $message }}</p> @enderror
        </div>
    </div>
    <div class="form-linha" style="margin-bottom:1.25rem">
        <div class="campo">
            <label for="empresa">Empresa ou loja</label>
            <input id="empresa" name="empresa" type="text" value="{{ old('empresa', $cliente->empresa) }}" maxlength="120" autocomplete="off">
        </div>
        <div class="campo">
            <label for="status">Situação</label>
            <select id="status" name="status" required>
                @foreach (\App\Models\Cliente::STATUS as $v => $r)
                    <option value="{{ $v }}" @selected(old('status', $cliente->status) === $v)>{{ $r }}</option>
                @endforeach
            </select>
            <p class="dica">Lead é quem ainda está em conversa; ativo já compra de você.</p>
        </div>
    </div>

    <div class="acoes">
        <button type="submit" class="botao">{{ $cliente->exists ? 'Salvar alterações' : 'Cadastrar cliente' }}</button>
        <a href="{{ $cliente->exists ? route('clientes.show', $cliente) : route('clientes.index') }}">Cancelar</a>
    </div>
</form>
@endsection
