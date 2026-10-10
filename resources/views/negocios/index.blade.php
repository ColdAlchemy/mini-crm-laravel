@extends('layout')
@section('titulo', 'Funil')
@section('erros-no-campo', '1')
@section('conteudo')
@php
    use App\Models\Negocio;
    use App\Support\Formato;
@endphp
<div class="topo">
    <div>
        <h1>Funil de vendas</h1>
        <p>Arraste os cartões entre as colunas, ou use “Mover para” em cada cartão.</p>
    </div>
</div>

<details class="cartao" id="novo-negocio" data-foco @if ($errors->any() || $clienteEscolhido) open @endif style="margin-bottom:1.25rem">
    <summary style="cursor:pointer;font-weight:800"><x-icone nome="mais" style="width:1.1rem;height:1.1rem;vertical-align:-0.2em" /> Novo negócio</summary>
    @if ($clientes->isEmpty())
        <p class="mut" style="margin-top:.75rem">Cadastre um cliente antes de abrir um negócio. <a href="{{ route('clientes.create') }}">Novo cliente</a></p>
    @else
        <form method="POST" action="{{ route('negocios.store') }}" class="form-linha" style="margin-top:1rem" novalidate>
            @csrf
            <div class="campo">
                <label for="cliente_id">Cliente</label>
                <select id="cliente_id" name="cliente_id" required @error('cliente_id') aria-invalid="true" aria-describedby="cliente-erro" @enderror>
                    <option value="">Escolha…</option>
                    @foreach ($clientes as $c) <option value="{{ $c->id }}" @selected((int) old('cliente_id', $clienteEscolhido) === $c->id)>{{ $c->nome }}</option> @endforeach
                </select>
                @error('cliente_id') <p class="erro-campo" id="cliente-erro">{{ $message }}</p> @enderror
            </div>
            <div class="campo" style="flex:2">
                <label for="titulo">O que está sendo vendido?</label>
                <input id="titulo" name="titulo" type="text" maxlength="150" value="{{ old('titulo') }}" autocomplete="off" required
                       @error('titulo') aria-invalid="true" aria-describedby="titulo-erro" @enderror>
                @error('titulo') <p class="erro-campo" id="titulo-erro">{{ $message }}</p> @enderror
            </div>
            <div class="campo" style="flex:0 0 10rem">
                <label for="valor">Valor (R$)</label>
                <input id="valor" name="valor" type="text" inputmode="decimal" value="{{ old('valor') }}" autocomplete="off" required
                       @error('valor') aria-invalid="true" aria-describedby="valor-erro" @enderror>
                @error('valor') <p class="erro-campo" id="valor-erro">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="botao">Criar negócio</button>
        </form>
    @endif
</details>

<div class="quadro" data-quadro>
    @foreach (Negocio::ETAPAS as $etapa => $rotulo)
        @php $lista = $negocios->get($etapa, collect()); @endphp
        <section class="coluna" data-etapa="{{ $etapa }}" aria-labelledby="col-{{ $etapa }}">
            <div class="coluna-cab">
                <h2 id="col-{{ $etapa }}">{{ $rotulo }} <span class="chip" data-qtd>{{ $lista->count() }}</span></h2>
                <span class="total" data-total>{{ Formato::moeda($lista->sum('valor')) }}</span>
            </div>
            <div class="cartoes">
                @foreach ($lista as $n)
                    <article class="negocio" draggable="true" data-url="{{ route('negocios.etapa', $n) }}" data-titulo="{{ $n->titulo }}" data-valor="{{ $n->valor }}">
                        <div class="titulo">{{ $n->titulo }}</div>
                        <div class="cliente"><a href="{{ route('clientes.show', $n->cliente_id) }}">{{ $n->cliente->nome }}</a></div>
                        <div class="valor">{{ Formato::moeda($n->valor) }}</div>
                        <form method="POST" action="{{ route('negocios.etapa', $n) }}" class="mover">
                            @csrf @method('PATCH')
                            <label class="so-leitor" for="mover-{{ $n->id }}">Mover “{{ $n->titulo }}” para</label>
                            <select id="mover-{{ $n->id }}" name="etapa">
                                @foreach (Negocio::ETAPAS as $k => $v) <option value="{{ $k }}" @selected($k === $etapa)>{{ $v }}</option> @endforeach
                            </select>
                            <button type="submit" class="botao-contorno botao-p">Mover</button>
                        </form>
                        <div class="extra">
                            @if ($n->fechado_em) <span class="pequeno mut">fechado em {{ $n->fechado_em->format('d/m/Y') }}</span> @else <span></span> @endif
                            <form method="POST" action="{{ route('negocios.destroy', $n) }}" data-confirmar="Remover o negócio “{{ $n->titulo }}”?">
                                @csrf @method('DELETE')
                                <button type="submit" class="botao-link pequeno">Remover…</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="coluna-vazia">Solte um cartão aqui</div>
        </section>
    @endforeach
</div>
@endsection
