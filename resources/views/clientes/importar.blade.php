@extends('layout')
@section('titulo', 'Importar planilha')
@section('erros-no-campo', '1')
@section('conteudo')
@php $resultado = session('resultado'); @endphp
<div class="topo">
    <div>
        <h1>Importar planilha</h1>
        <p>Traga seus clientes de uma planilha do Excel ou do Google Planilhas.</p>
    </div>
</div>

@if ($resultado)
    <section class="cartao" aria-labelledby="h-resultado" style="margin-bottom:1.25rem">
        <h2 id="h-resultado">Importação concluída</h2>
        <p>
            <strong>{{ $resultado['criados'] }}</strong> {{ $resultado['criados'] === 1 ? 'cliente novo' : 'clientes novos' }} ·
            <strong>{{ $resultado['atualizados'] }}</strong> {{ $resultado['atualizados'] === 1 ? 'atualizado' : 'atualizados' }} ·
            <strong>{{ count($resultado['ignoradas']) }}</strong> {{ count($resultado['ignoradas']) === 1 ? 'linha ignorada' : 'linhas ignoradas' }}
        </p>
        @if ($resultado['ignoradas'])
            <p class="pequeno mut">Corrija estas linhas na planilha e importe de novo; as que já entraram serão apenas atualizadas.</p>
            <ul class="lista-retornos">
                @foreach (array_slice($resultado['ignoradas'], 0, 20) as $i)
                    <li><div class="corpo"><span class="titulo">Linha {{ $i['linha'] }}</span> <span class="sub">{{ $i['motivo'] }}</span></div></li>
                @endforeach
            </ul>
            @if (count($resultado['ignoradas']) > 20) <p class="pequeno mut">…e mais {{ count($resultado['ignoradas']) - 20 }}.</p> @endif
        @endif
        <a class="botao" href="{{ route('clientes.index') }}">Ver os clientes</a>
    </section>
@endif

<div class="grade-2">
    <form method="POST" action="{{ route('clientes.importar') }}" enctype="multipart/form-data" class="cartao" novalidate>
        @csrf
        <h2>Enviar arquivo</h2>
        <div class="campo">
            <label for="planilha">Arquivo CSV (até 1&nbsp;MB)</label>
            <input id="planilha" name="planilha" type="file" accept=".csv,text/csv,text/plain" required
                   aria-describedby="planilha-dica @error('planilha') planilha-erro @enderror" @error('planilha') aria-invalid="true" @enderror>
            <p class="dica" id="planilha-dica">No Excel: Arquivo → Salvar como → CSV.</p>
            @error('planilha') <p class="erro-campo" id="planilha-erro">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="botao"><x-icone nome="importar" /> Importar clientes</button>
    </form>

    <section class="cartao" aria-labelledby="h-como">
        <h2 id="h-como">Como preparar a planilha</h2>
        <ul class="pilha pequeno" style="list-style:disc;padding-left:1.2rem">
            <li>A primeira linha tem os títulos das colunas: <strong>nome</strong> (obrigatória), <strong>email</strong>, <strong>telefone</strong>, <strong>empresa</strong> e <strong>status</strong> (lead, ativo ou inativo).</li>
            <li>Quem já existe (mesmo e-mail) é atualizado, não duplicado.</li>
            <li>Linhas sem nome ou com e-mail inválido são puladas e mostradas no resultado.</li>
            <li>Aceita separador vírgula ou ponto e vírgula, e acentos do Excel.</li>
        </ul>
        <p style="margin-top:1rem"><a class="botao-contorno" href="{{ route('clientes.modelo') }}"><x-icone nome="exportar" /> Baixar planilha de exemplo</a></p>
    </section>
</div>
@endsection
