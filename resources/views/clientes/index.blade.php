@extends('layout')
@section('titulo', 'Clientes')
@section('conteudo')
<div class="topo">
    <div>
        <h1>Clientes</h1>
        <p>{{ $total === 0 ? 'Nenhum cliente cadastrado ainda.' : trans_choice(':count cliente|:count clientes', $total).' no seu balcão.' }}</p>
    </div>
    <div class="acoes">
        <a class="botao-contorno" href="{{ route('clientes.importar') }}"><x-icone nome="importar" /> Importar planilha</a>
        @if ($total > 0)
            <a class="botao-contorno" href="{{ route('clientes.exportar', array_filter($filtros)) }}"><x-icone nome="exportar" /> Baixar CSV</a>
        @endif
        <a class="botao" href="{{ route('clientes.create') }}"><x-icone nome="mais" /> Novo cliente</a>
    </div>
</div>

@if ($total > 0)
<form method="GET" action="{{ route('clientes.index') }}" class="filtros" role="search" aria-label="Filtrar clientes">
    <div class="campo">
        <label for="q">Buscar</label>
        <input id="q" name="q" type="search" value="{{ $filtros['q'] }}" autocomplete="off" spellcheck="false">
        <p class="dica">Nome, e-mail, telefone ou empresa.</p>
    </div>
    <div class="campo">
        <label for="status">Situação</label>
        <select id="status" name="status">
            <option value="">Todas</option>
            @foreach (\App\Models\Cliente::STATUS as $v => $r)
                <option value="{{ $v }}" @selected($filtros['status'] === $v)>{{ $r }}</option>
            @endforeach
        </select>
    </div>
    <div class="campo">
        <label for="etiqueta">Etiqueta</label>
        <select id="etiqueta" name="etiqueta">
            <option value="">Todas</option>
            @foreach ($etiquetas as $e)
                <option value="{{ $e->id }}" @selected($filtros['etiqueta'] === $e->id)>{{ $e->nome }}</option>
            @endforeach
        </select>
    </div>
    <div class="campo">
        <label for="ordem">Ordenar por</label>
        <select id="ordem" name="ordem">
            <option value="nome" @selected($filtros['ordem'] === 'nome')>Nome</option>
            <option value="contato" @selected($filtros['ordem'] === 'contato')>Mais tempo sem contato</option>
        </select>
    </div>
    <div class="acoes">
        <button type="submit" class="botao">Filtrar</button>
        @if ($filtros['q'] || $filtros['status'] || $filtros['etiqueta'] || $filtros['ordem'] !== 'nome')
            <a href="{{ route('clientes.index') }}">Limpar</a>
        @endif
    </div>
</form>
@endif

@if ($clientes->isEmpty())
    <div class="vazio">
        @if ($total === 0)
            <strong>Seu balcão está vazio</strong>
            Cadastre o primeiro cliente ou <a href="{{ route('clientes.importar') }}">importe uma planilha</a> com todos de uma vez.
        @else
            <strong>Nenhum cliente encontrado</strong>
            Tente outra busca ou <a href="{{ route('clientes.index') }}">limpe os filtros</a>.
        @endif
    </div>
@else
    <div class="cartao cartao--tabela">
        <table class="tabela">
            <caption class="so-leitor">Lista de clientes</caption>
            <thead>
                <tr>
                    <th scope="col">Cliente</th>
                    <th scope="col">Situação</th>
                    <th scope="col">Último contato</th>
                    <th scope="col">Próximo retorno</th>
                    <th scope="col"><span class="so-leitor">Ações</span></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($clientes as $c)
                @php
                    $ultimo = $c->ultimo_contato ? \Illuminate\Support\Carbon::parse($c->ultimo_contato) : null;
                    $retorno = $c->proximo_retorno ? \Illuminate\Support\Carbon::parse($c->proximo_retorno) : null;
                    $atrasado = $retorno && $retorno->lt(now()->startOfDay());
                    $zap = $c->linkWhatsapp();
                @endphp
                <tr>
                    <td>
                        <div class="nome-cel">
                            <span class="avatar avatar--{{ (crc32($c->nome) % 6) + 1 }}" aria-hidden="true">{{ mb_strtoupper(mb_substr($c->nome, 0, 1)) }}</span>
                            <div>
                                <div class="nome"><a href="{{ route('clientes.show', $c) }}">{{ $c->nome }}</a></div>
                                <div class="sub">{{ $c->empresa ?: ($c->email ?: 'sem empresa') }}</div>
                                @if ($c->etiquetas->isNotEmpty())
                                    <div class="etiquetas" style="margin-top:.25rem">
                                        @foreach ($c->etiquetas as $e) <span class="etiqueta etiqueta--{{ $e->cor }}">{{ $e->nome }}</span> @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td data-rotulo="Situação"><span class="chip chip--{{ $c->status }}">{{ \App\Models\Cliente::STATUS[$c->status] ?? $c->status }}</span></td>
                    <td data-rotulo="Último contato" class="sub">{{ $ultimo ? $ultimo->diffForHumans() : 'nunca' }}</td>
                    <td data-rotulo="Próximo retorno" class="num">
                        @if ($retorno)
                            <time datetime="{{ $retorno->toDateString() }}" @class(['chip', 'chip--atrasado' => $atrasado])>{{ $retorno->format('d/m/Y') }}</time>
                        @else
                            <span class="mut">—</span>
                        @endif
                    </td>
                    <td>
                        @if ($zap)
                            <a class="botao-whats botao-p" href="{{ $zap }}" target="_blank" rel="noopener noreferrer"><x-icone nome="whatsapp" /> WhatsApp<span class="so-leitor"> com {{ $c->nome }} (abre em nova aba)</span></a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="paginacao">{{ $clientes->links() }}</div>
@endif
@endsection
