@extends('layout')
@section('titulo', $cliente->exists ? 'Editar cliente' : 'Novo cliente')
@section('conteudo')
<div class="card">
    <h2>{{ $cliente->exists ? 'Editar cliente' : 'Novo cliente' }}</h2>
    <form method="POST" action="{{ $cliente->exists ? route('clientes.update', $cliente) : route('clientes.store') }}">
        @csrf
        @if ($cliente->exists) @method('PUT') @endif
        <label for="nome">Nome</label>
        <input id="nome" name="nome" value="{{ old('nome', $cliente->nome) }}" required>
        <label for="email">E-mail</label>
        <input id="email" name="email" type="email" value="{{ old('email', $cliente->email) }}">
        <label for="telefone">Telefone</label>
        <input id="telefone" name="telefone" value="{{ old('telefone', $cliente->telefone) }}">
        <label for="empresa">Empresa</label>
        <input id="empresa" name="empresa" value="{{ old('empresa', $cliente->empresa) }}">
        <label for="status">Status</label>
        <select id="status" name="status">
            @foreach (\App\Models\Cliente::STATUS as $k => $v)
                <option value="{{ $k }}" @selected(old('status', $cliente->status ?? 'lead') === $k)>{{ $v }}</option>
            @endforeach
        </select>
        <p class="linha"><button type="submit">Salvar</button> <a class="btn" style="background:transparent;color:inherit;border:1px solid var(--br)" href="{{ route('clientes.index') }}">Cancelar</a></p>
    </form>
</div>
@endsection
