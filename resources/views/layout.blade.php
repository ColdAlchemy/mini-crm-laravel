<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Mini CRM')</title>
    <style>
        :root { --bg:#f6f7f9; --card:#fff; --txt:#1c2330; --mut:#667085; --br:#dfe3ea; --acc:#2457d6; --ok:#157f3b; --err:#b42318; }
        @media (prefers-color-scheme: dark) { :root { --bg:#12151b; --card:#1b2029; --txt:#e8ecf3; --mut:#9aa4b5; --br:#2c3442; --acc:#6b93ff; --ok:#4cc27a; --err:#f97066; } }
        * { box-sizing: border-box; }
        body { margin:0; font:15px/1.5 system-ui, sans-serif; background:var(--bg); color:var(--txt); }
        header { background:var(--card); border-bottom:1px solid var(--br); padding:12px 16px; display:flex; gap:20px; align-items:center; flex-wrap:wrap; }
        header strong { font-size:17px; } header a { color:var(--txt); text-decoration:none; } header a:hover { color:var(--acc); }
        main { max-width:1100px; margin:0 auto; padding:20px 16px; }
        .card { background:var(--card); border:1px solid var(--br); border-radius:10px; padding:16px; margin-bottom:16px; }
        table { width:100%; border-collapse:collapse; } th, td { text-align:left; padding:8px; border-bottom:1px solid var(--br); }
        input, select, textarea, button { font:inherit; padding:8px 10px; border:1px solid var(--br); border-radius:8px; background:var(--bg); color:var(--txt); }
        button, .btn { background:var(--acc); color:#fff; border:0; cursor:pointer; text-decoration:none; padding:8px 14px; border-radius:8px; display:inline-block; }
        button.sec { background:transparent; color:var(--txt); border:1px solid var(--br); } button.perigo { background:var(--err); }
        label { display:block; margin:10px 0 4px; color:var(--mut); } .linha { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
        .ok { color:var(--ok); } .erro { color:var(--err); } .mut { color:var(--mut); }
        .kanban { display:grid; grid-template-columns:repeat(auto-fit, minmax(190px,1fr)); gap:12px; align-items:start; }
        .kanban .col { background:var(--card); border:1px solid var(--br); border-radius:10px; padding:10px; }
        .kanban .item { border:1px solid var(--br); border-radius:8px; padding:8px; margin-top:8px; background:var(--bg); }
    </style>
</head>
<body>
<header>
    <strong>Mini CRM</strong>
    <a href="{{ route('clientes.index') }}">Clientes</a>
    <a href="{{ route('negocios.index') }}">Negócios</a>
</header>
<main>
    @if (session('ok')) <p class="ok">{{ session('ok') }}</p> @endif
    @if ($errors->any())
        <div class="card erro">@foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach</div>
    @endif
    @yield('conteudo')
</main>
</body>
</html>
