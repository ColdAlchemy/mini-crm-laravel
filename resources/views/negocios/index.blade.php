@extends('layout')
@section('titulo', 'Negócios')
@section('conteudo')
<div class="card">
    <form method="POST" action="{{ route('negocios.store') }}" class="linha">
        @csrf
        <select name="cliente_id" required>
            <option value="">Cliente…</option>
            @foreach ($clientes as $c)<option value="{{ $c->id }}">{{ $c->nome }}</option>@endforeach
        </select>
        <input name="titulo" placeholder="Título do negócio" required>
        <input name="valor" type="number" step="0.01" min="0" placeholder="Valor (R$)" required>
        <button type="submit">Adicionar</button>
    </form>
</div>

<div class="kanban">
@foreach (\App\Models\Negocio::ETAPAS as $chave => $nome)
    @php($lista = $negocios[$chave] ?? collect())
    <div class="col">
        <strong>{{ $nome }}</strong> <span class="mut">({{ $lista->count() }} · R$ {{ number_format($lista->sum('valor'), 2, ',', '.') }})</span>
        @foreach ($lista as $n)
            <div class="item">
                <div>{{ $n->titulo }}</div>
                <div class="mut">{{ $n->cliente->nome }} · R$ {{ number_format($n->valor, 2, ',', '.') }}</div>
                <form method="POST" action="{{ route('negocios.etapa', $n) }}" class="linha" style="margin-top:6px">
                    @csrf @method('PATCH')
                    <select name="etapa" onchange="this.form.submit()">
                        @foreach (\App\Models\Negocio::ETAPAS as $k => $v)<option value="{{ $k }}" @selected($k === $n->etapa)>{{ $v }}</option>@endforeach
                    </select>
                </form>
            </div>
        @endforeach
    </div>
@endforeach
</div>
@endsection
