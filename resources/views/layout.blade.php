<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0a3a3d">
    <meta name="color-scheme" content="light">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Painel') — Balcão</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='9' fill='%230a3a3d'/%3E%3Ccircle cx='16' cy='16' r='7' fill='%23f26b5b'/%3E%3C/svg%3E">
    @foreach (['node_modules/@fontsource-variable/nunito/files/nunito-latin-wght-normal.woff2'] as $fonte)
        <link rel="preload" as="font" type="font/woff2" crossorigin href="{{ Vite::asset($fonte) }}">
    @endforeach
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<a class="pular" href="#conteudo">Pular para o conteúdo</a>

@auth
@php
    $itens = [
        ['painel', 'Painel', 'painel', 'painel'],
        ['clientes.index', 'Clientes', 'clientes', 'clientes*'],
        ['negocios.index', 'Funil', 'funil', 'negocios*'],
        ['tarefas.index', 'Retornos', 'tarefas', 'tarefas*'],
    ];
@endphp
<div class="app">
    <aside class="lateral">
        <a class="marca" href="{{ route('painel') }}" translate="no">
            <span class="marca-ponto" aria-hidden="true"></span>
            <span>Balcão<small>mini CRM do comércio</small></span>
        </a>
        <nav class="menu" aria-label="Principal">
            @foreach ($itens as [$rota, $rotulo, $icone, $padrao])
                <a href="{{ route($rota) }}" @if (request()->routeIs($padrao)) aria-current="page" @endif>
                    <x-icone :nome="$icone" />
                    {{ $rotulo }}
                    @if ($icone === 'tarefas' && $retornosPendentes > 0)
                        <span class="selo-qtd" aria-label="{{ $retornosPendentes }} para hoje ou atrasados">{{ $retornosPendentes }}</span>
                    @endif
                </a>
            @endforeach
            <a href="{{ route('etiquetas.index') }}" @if (request()->routeIs('etiquetas*')) aria-current="page" @endif>
                <x-icone nome="etiqueta" /> Etiquetas
            </a>
        </nav>
        <div class="lateral-rodape">
            <strong>{{ auth()->user()->name }}</strong>
            <form method="POST" action="{{ route('sair') }}">
                @csrf
                <button type="submit" class="botao-link">Sair</button>
            </form>
        </div>
    </aside>

    <div>
        <div class="barra-topo">
            <a class="marca" href="{{ route('painel') }}" translate="no"><span class="marca-ponto" aria-hidden="true"></span>Balcão</a>
            <form method="POST" action="{{ route('sair') }}">
                @csrf
                <button type="submit" class="botao-link">Sair</button>
            </form>
        </div>

        <main id="conteudo" class="conteudo" tabindex="-1">
            @if (session('ok'))
                <div class="aviso" role="status">{{ session('ok') }}</div>
            @endif
            @if ($errors->any() && ! View::hasSection('erros-no-campo'))
                <div class="aviso erro" role="alert">
                    @foreach ($errors->all() as $e) <p>{{ $e }}</p> @endforeach
                </div>
            @endif
            @yield('conteudo')
        </main>
    </div>

    <nav class="lateral-mobile" aria-label="Principal (celular)">
        @foreach ($itens as [$rota, $rotulo, $icone, $padrao])
            <a href="{{ route($rota) }}" @if (request()->routeIs($padrao)) aria-current="page" @endif>
                <x-icone :nome="$icone" />
                {{ $rotulo }}
                @if ($icone === 'tarefas' && $retornosPendentes > 0)
                    <span class="selo-qtd" aria-label="{{ $retornosPendentes }} para hoje ou atrasados">{{ $retornosPendentes }}</span>
                @endif
            </a>
        @endforeach
    </nav>
</div>
@else
    @yield('entrada')
@endauth

<div id="anuncio" class="anuncio" role="status" aria-live="polite"></div>

<dialog id="confirmar" aria-labelledby="confirmar-titulo">
    <form method="dialog">
        <h2 id="confirmar-titulo">Tem certeza?</h2>
        <p id="confirmar-texto"></p>
        <div class="acoes">
            <button class="botao-contorno" value="cancelar">Cancelar</button>
            <button class="botao-perigo" value="ok">Sim, continuar</button>
        </div>
    </form>
</dialog>
</body>
</html>
