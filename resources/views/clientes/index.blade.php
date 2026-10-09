@extends('layout')
@section('titulo', 'Clientes')
@section('conteudo')
<div class="card">
    <form method="GET" class="linha">
        <input name="q" value="{{ request('q') }}" placeholder="Buscar por nome, e-mail ou empresa">
        <select name="status">
            <option value="">Todos os status</option>
            @foreach (\App\Models\Cliente::STATUS as $k => $v)
                <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
            @endforeach
        </select>
        <button type="submit">Filtrar</button>
        <a class="btn" href="{{ route('clientes.create') }}">Novo cliente</a>
    </form>
</div>
<div class="card">
    <table>
        <thead><tr><th>Nome</th><th>Empresa</th><th>E-mail</th><th>Status</th></tr></thead>
        <tbody>
        @forelse ($clientes as $c)
            <tr>
                <td><a href="{{ route('clientes.show', $c) }}">{{ $c->nome }}</a></td>
                <td>{{ $c->empresa }}</td><td>{{ $c->email }}</td>
                <td>{{ \App\Models\Cliente::STATUS[$c->status] ?? $c->status }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="mut">Nenhum cliente encontrado.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $clientes->links() }}
</div>
@endsection
