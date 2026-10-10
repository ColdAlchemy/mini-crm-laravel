@extends('layout')
@section('titulo', 'Etiquetas')
@section('erros-no-campo', '1')
@section('conteudo')
<div class="topo">
    <div>
        <h1>Etiquetas</h1>
        <p>Marque clientes por grupo (ex.: “Atacado”, “VIP”) e filtre a lista por elas.</p>
    </div>
</div>

<div class="grade-2">
    <section class="cartao" aria-labelledby="h-lista">
        <h2 id="h-lista">Suas etiquetas</h2>
        @if ($etiquetas->isEmpty())
            <div class="vazio"><strong>Nenhuma etiqueta ainda</strong>Crie a primeira ao lado.</div>
        @else
            <ul class="lista-retornos">
                @foreach ($etiquetas as $e)
                    <li>
                        <div class="corpo">
                            <a href="{{ route('clientes.index', ['etiqueta' => $e->id]) }}"><span class="etiqueta etiqueta--{{ $e->cor }}">{{ $e->nome }}</span></a>
                            <span class="sub">{{ trans_choice(':count cliente|:count clientes', $e->clientes_count) }}</span>
                        </div>
                        <form method="POST" action="{{ route('etiquetas.destroy', $e) }}" data-confirmar="Remover a etiqueta “{{ $e->nome }}”? Os clientes continuam cadastrados, só perdem a etiqueta.">
                            @csrf @method('DELETE')
                            <button type="submit" class="botao-link pequeno">Remover…</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="cartao" aria-labelledby="h-nova">
        <h2 id="h-nova">Nova etiqueta</h2>
        <form method="POST" action="{{ route('etiquetas.store') }}" novalidate>
            @csrf
            <div class="campo">
                <label for="nome">Nome</label>
                <input id="nome" name="nome" type="text" maxlength="40" value="{{ old('nome') }}" autocomplete="off" required
                       @error('nome') aria-invalid="true" aria-describedby="nome-erro" @enderror>
                @error('nome') <p class="erro-campo" id="nome-erro">{{ $message }}</p> @enderror
            </div>
            <fieldset style="border:0;padding:0;margin:0 0 1rem">
                <legend style="font-weight:800;font-size:.9rem;margin-bottom:.4rem">Cor</legend>
                @foreach (\App\Models\Etiqueta::CORES as $k => $v)
                    <label class="linha-checkbox" for="cor-{{ $k }}">
                        <input id="cor-{{ $k }}" type="radio" name="cor" value="{{ $k }}" @checked(old('cor', 'petroleo') === $k)>
                        <span class="etiqueta etiqueta--{{ $k }}">{{ $v }}</span>
                    </label>
                @endforeach
                @error('cor') <p class="erro-campo">{{ $message }}</p> @enderror
            </fieldset>
            <button type="submit" class="botao">Criar etiqueta</button>
        </form>
    </section>
</div>
@endsection
