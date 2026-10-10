@extends('layout')
@section('titulo', $cliente->nome)
@section('erros-no-campo', '1')
@section('conteudo')
@php
    use App\Support\Formato;
    $zap = $cliente->linkWhatsapp();
    $pendentes = $cliente->tarefas->whereNull('concluida_em');
@endphp

<div class="topo">
    <div class="perfil">
        <span class="avatar avatar--g avatar--{{ (crc32($cliente->nome) % 6) + 1 }}" aria-hidden="true">{{ mb_strtoupper(mb_substr($cliente->nome, 0, 1)) }}</span>
        <div>
            <h1>{{ $cliente->nome }}</h1>
            <div class="meta">
                <span class="chip chip--{{ $cliente->status }}">{{ \App\Models\Cliente::STATUS[$cliente->status] }}</span>
                @if ($cliente->empresa) <span>{{ $cliente->empresa }}</span> @endif
                @if ($cliente->email) <a href="mailto:{{ $cliente->email }}">{{ $cliente->email }}</a> @endif
                @if ($cliente->telefone) <span>{{ $cliente->telefone }}</span> @endif
            </div>
        </div>
    </div>
    <div class="acoes">
        @if ($zap)
            <a class="botao-whats" href="{{ $zap }}" target="_blank" rel="noopener noreferrer"><x-icone nome="whatsapp" /> Chamar no WhatsApp<span class="so-leitor"> (abre em nova aba)</span></a>
        @endif
        <a class="botao-contorno" href="{{ route('clientes.edit', $cliente) }}">Editar</a>
        <form method="POST" action="{{ route('clientes.destroy', $cliente) }}" data-confirmar="O cliente “{{ $cliente->nome }}”, com todos os contatos, negócios e retornos dele, será removido. Isso não pode ser desfeito.">
            @csrf @method('DELETE')
            <button type="submit" class="botao-perigo">Excluir…</button>
        </form>
    </div>
</div>

<div class="grade-3">
    <div class="pilha">
        <section class="cartao" aria-labelledby="h-registrar">
            <h2 id="h-registrar">Registrar contato</h2>
            <form method="POST" action="{{ route('interacoes.store', $cliente) }}" novalidate>
                @csrf
                <div class="form-linha">
                    <div class="campo" style="flex:0 0 11rem">
                        <label for="tipo">Como foi?</label>
                        <select id="tipo" name="tipo">
                            @foreach (\App\Models\Interacao::TIPOS as $k => $v) <option value="{{ $k }}" @selected(old('tipo') === $k)>{{ $v }}</option> @endforeach
                        </select>
                    </div>
                    <div class="campo" style="flex:3">
                        <label for="descricao">O que foi combinado?</label>
                        <input id="descricao" name="descricao" type="text" value="{{ old('descricao') }}" maxlength="2000" autocomplete="off" required
                               @error('descricao') aria-invalid="true" aria-describedby="descricao-erro" @enderror>
                        @error('descricao') <p class="erro-campo" id="descricao-erro">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="botao">Registrar</button>
                </div>
            </form>
        </section>

        <section class="cartao" aria-labelledby="h-tempo">
            <h2 id="h-tempo">Linha do tempo</h2>
            @if ($linhaDoTempo->isEmpty())
                <div class="vazio"><strong>Nada registrado ainda</strong>Anote o primeiro contato acima.</div>
            @else
                <ol class="tempo">
                    @foreach ($linhaDoTempo as $e)
                        <li class="tipo-{{ $e['tipo'] }}">
                            <div class="quando"><time datetime="{{ $e['quando']->toIso8601String() }}">{{ $e['quando']->format('d/m/Y H:i') }}</time></div>
                            <div class="titulo">{{ $e['titulo'] }}</div>
                            @if ($e['detalhe']) <div class="detalhe">{{ $e['detalhe'] }}</div> @endif
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>
    </div>

    <div class="pilha">
        <section class="cartao" aria-labelledby="h-retornos">
            <h2 id="h-retornos">Retornos</h2>
            @if ($pendentes->isNotEmpty())
                <ul>
                    @foreach ($pendentes as $t) @php $t->setRelation('cliente', $cliente); @endphp @include('partials.retorno', ['t' => $t]) @endforeach
                </ul>
            @else
                <p class="mut pequeno">Nenhum retorno agendado.</p>
            @endif
            <form method="POST" action="{{ route('tarefas.store') }}" novalidate style="margin-top:.75rem">
                @csrf
                <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">
                <div class="campo">
                    <label for="titulo-retorno">Agendar retorno</label>
                    <input id="titulo-retorno" name="titulo" type="text" maxlength="150" autocomplete="off" required aria-describedby="retorno-dica">
                    <p class="dica" id="retorno-dica">Ex.: ligar para confirmar o orçamento.</p>
                </div>
                <div class="campo">
                    <label for="vence_em">Para o dia</label>
                    <input id="vence_em" name="vence_em" type="date" value="{{ now()->addDays(3)->toDateString() }}" required>
                </div>
                <button type="submit" class="botao-contorno">Agendar</button>
            </form>
        </section>

        <section class="cartao" aria-labelledby="h-negocios">
            <div class="cartao-cab">
                <h2 id="h-negocios">Negócios</h2>
                <a href="{{ route('negocios.index', ['cliente' => $cliente->id]) }}#novo-negocio">Novo negócio</a>
            </div>
            @forelse ($cliente->negocios as $n)
                <div class="retorno" style="grid-template-columns:minmax(0,1fr) auto">
                    <div>
                        <div class="titulo">{{ $n->titulo }}</div>
                        <div class="sub">{{ Formato::moeda($n->valor) }}</div>
                    </div>
                    <span class="chip {{ $n->etapa === 'ganho' ? 'chip--ativo' : ($n->etapa === 'perdido' ? 'chip--atrasado' : 'chip--lead') }}">{{ $etapas[$n->etapa] }}</span>
                </div>
            @empty
                <p class="mut pequeno">Nenhum negócio com este cliente.</p>
            @endforelse
        </section>

        <section class="cartao" aria-labelledby="h-etiquetas">
            <h2 id="h-etiquetas">Etiquetas</h2>
            @if ($todasEtiquetas->isEmpty())
                <p class="mut pequeno">Você ainda não criou etiquetas. <a href="{{ route('etiquetas.index') }}">Criar a primeira</a>.</p>
            @else
                <form method="POST" action="{{ route('clientes.etiquetas', $cliente) }}">
                    @csrf @method('PUT')
                    <fieldset style="border:0;padding:0;margin:0 0 .75rem">
                        <legend class="so-leitor">Etiquetas deste cliente</legend>
                        @foreach ($todasEtiquetas as $e)
                            <label class="linha-checkbox" for="et-{{ $e->id }}">
                                <input id="et-{{ $e->id }}" type="checkbox" name="etiquetas[]" value="{{ $e->id }}" @checked($cliente->etiquetas->contains($e->id))>
                                <span class="etiqueta etiqueta--{{ $e->cor }}">{{ $e->nome }}</span>
                            </label>
                        @endforeach
                    </fieldset>
                    <button type="submit" class="botao-contorno botao-p">Salvar etiquetas</button>
                </form>
            @endif
        </section>
    </div>
</div>
@endsection
