@extends('layout')
@section('titulo', 'Retornos')
@section('erros-no-campo', '1')
@section('conteudo')
<div class="topo">
    <div>
        <h1>Retornos</h1>
        <p>O que você prometeu a cada cliente, por dia.</p>
    </div>
</div>

<div class="grade-3">
    <div class="pilha">
        <section class="cartao" aria-labelledby="h-atrasados">
            <h2 id="h-atrasados">Atrasados <span class="chip chip--atrasado">{{ $atrasadas->count() }}</span></h2>
            @if ($atrasadas->isEmpty())
                <p class="mut pequeno">Nenhum retorno atrasado.</p>
            @else
                <ul>@foreach ($atrasadas as $t) @include('partials.retorno', ['t' => $t]) @endforeach</ul>
            @endif
        </section>

        <section class="cartao" aria-labelledby="h-hoje">
            <h2 id="h-hoje">Para hoje <span class="chip chip--hoje">{{ $hoje->count() }}</span></h2>
            @if ($hoje->isEmpty())
                <p class="mut pequeno">Nada marcado para hoje.</p>
            @else
                <ul>@foreach ($hoje as $t) @include('partials.retorno', ['t' => $t]) @endforeach</ul>
            @endif
        </section>

        <section class="cartao" aria-labelledby="h-proximos">
            <h2 id="h-proximos">Próximos <span class="chip">{{ $proximas->count() }}</span></h2>
            @if ($proximas->isEmpty())
                <p class="mut pequeno">Nenhum retorno futuro agendado.</p>
            @else
                <ul>@foreach ($proximas as $t) @include('partials.retorno', ['t' => $t]) @endforeach</ul>
            @endif
        </section>

        @if ($concluidas->isNotEmpty())
        <section class="cartao" aria-labelledby="h-feitos">
            <h2 id="h-feitos">Concluídos recentemente</h2>
            <ul>@foreach ($concluidas as $t) @include('partials.retorno', ['t' => $t]) @endforeach</ul>
        </section>
        @endif
    </div>

    <section class="cartao" aria-labelledby="h-novo">
        <h2 id="h-novo">Agendar retorno</h2>
        @if ($clientes->isEmpty())
            <p class="mut">Cadastre um cliente primeiro. <a href="{{ route('clientes.create') }}">Novo cliente</a></p>
        @else
            <form method="POST" action="{{ route('tarefas.store') }}" novalidate>
                @csrf
                <div class="campo">
                    <label for="cliente_id">Cliente</label>
                    <select id="cliente_id" name="cliente_id" required @error('cliente_id') aria-invalid="true" aria-describedby="cliente-erro" @enderror>
                        <option value="">Escolha…</option>
                        @foreach ($clientes as $c) <option value="{{ $c->id }}" @selected((int) old('cliente_id') === $c->id)>{{ $c->nome }}</option> @endforeach
                    </select>
                    @error('cliente_id') <p class="erro-campo" id="cliente-erro">{{ $message }}</p> @enderror
                </div>
                <div class="campo">
                    <label for="titulo">O que fazer?</label>
                    <input id="titulo" name="titulo" type="text" maxlength="150" value="{{ old('titulo') }}" autocomplete="off" required
                           @error('titulo') aria-invalid="true" aria-describedby="titulo-erro" @enderror>
                    @error('titulo') <p class="erro-campo" id="titulo-erro">{{ $message }}</p> @enderror
                </div>
                <div class="campo">
                    <label for="vence_em">Para o dia</label>
                    <input id="vence_em" name="vence_em" type="date" value="{{ old('vence_em', now()->addDays(3)->toDateString()) }}" required
                           @error('vence_em') aria-invalid="true" aria-describedby="vence-erro" @enderror>
                    @error('vence_em') <p class="erro-campo" id="vence-erro">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="botao">Agendar</button>
            </form>
        @endif
    </section>
</div>
@endsection
