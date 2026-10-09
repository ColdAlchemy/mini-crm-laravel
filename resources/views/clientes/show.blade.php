@extends('layout')
@section('titulo', $cliente->nome)
@section('conteudo')
<div class="card">
    <h2>{{ $cliente->nome }}</h2>
    <p class="mut">{{ $cliente->empresa }} · {{ $cliente->email }} · {{ $cliente->telefone }} · {{ \App\Models\Cliente::STATUS[$cliente->status] ?? $cliente->status }}</p>
    <div class="linha">
        <a class="btn" href="{{ route('clientes.edit', $cliente) }}">Editar</a>
        <form method="POST" action="{{ route('clientes.destroy', $cliente) }}">@csrf @method('DELETE')<button class="perigo" type="submit">Excluir</button></form>
    </div>
</div>

<div class="card">
    <h3>Negócios</h3>
    @forelse ($cliente->negocios as $n)
        <div>{{ $n->titulo }} — R$ {{ number_format($n->valor, 2, ',', '.') }} ({{ \App\Models\Negocio::ETAPAS[$n->etapa] ?? $n->etapa }})</div>
    @empty
        <p class="mut">Sem negócios. Crie um em <a href="{{ route('negocios.index') }}">Negócios</a>.</p>
    @endforelse
</div>

<div class="card">
    <h3>Histórico de interações</h3>
    <form method="POST" action="{{ route('interacoes.store', $cliente) }}" class="linha">
        @csrf
        <select name="tipo">@foreach (\App\Models\Interacao::TIPOS as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select>
        <input name="descricao" placeholder="O que aconteceu?" style="flex:1;min-width:200px" required>
        <button type="submit">Registrar</button>
    </form>
    @foreach ($cliente->interacoes as $i)
        <p><strong>{{ \App\Models\Interacao::TIPOS[$i->tipo] ?? $i->tipo }}</strong> <span class="mut">{{ $i->created_at->format('d/m/Y H:i') }}</span><br>{{ $i->descricao }}</p>
    @endforeach
</div>
@endsection
