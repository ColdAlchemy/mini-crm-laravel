@extends('layout')
@section('titulo', 'Painel')
@section('conteudo')
@php
    use App\Support\Formato;
    $hora = now()->hour;
    $saudacao = $hora < 12 ? 'Bom dia' : ($hora < 18 ? 'Boa tarde' : 'Boa noite');
    $totalRetornos = $retornos['atrasadas']->count() + $retornos['hoje']->count();
@endphp

<div class="saudacao">
    <h1>{{ $saudacao }}, {{ auth()->user()->primeiroNome() }}!</h1>
    <p>
        {{ now()->locale('pt_BR')->translatedFormat('l, j \d\e F') }}.
        @if ($totalRetornos > 0)
            Você tem <strong>{{ $totalRetornos }} {{ $totalRetornos === 1 ? 'retorno' : 'retornos' }}</strong> para hoje ou atrasados{{ $retornos['atrasadas']->isNotEmpty() ? ' (' . $retornos['atrasadas']->count() . ' atrasados)' : '' }}.
        @else
            Nenhum retorno pendente por hoje. Bom trabalho!
        @endif
    </p>
</div>

<dl class="indicadores">
    <div class="indicador">
        <dt>Clientes ativos</dt>
        <dd>{{ $ind['clientes_ativos'] }}</dd>
        <small>{{ $ind['leads'] }} {{ $ind['leads'] === 1 ? 'lead' : 'leads' }} em conversa</small>
    </div>
    <div class="indicador">
        <dt>Negócios em aberto</dt>
        <dd>{{ Formato::moedaInteira($ind['abertos_valor']) }}</dd>
        <small>{{ $ind['abertos_qtd'] }} {{ $ind['abertos_qtd'] === 1 ? 'negócio' : 'negócios' }} no funil</small>
    </div>
    <div class="indicador">
        <dt>Ganho neste mês</dt>
        <dd>{{ Formato::moedaInteira($ind['ganho_mes_valor']) }}</dd>
        <small>{{ $ind['ganho_mes_qtd'] }} {{ $ind['ganho_mes_qtd'] === 1 ? 'negócio fechado' : 'negócios fechados' }}</small>
    </div>
    <div class="indicador">
        <dt>Taxa de fechamento</dt>
        <dd>{{ $ind['fechamento'] === null ? '—' : $ind['fechamento'].'%' }}</dd>
        <small>
            @if ($ind['fechamento'] === null) Ainda sem negócios ganhos ou perdidos
            @else {{ $ind['ganhos'] }} {{ $ind['ganhos'] === 1 ? 'ganho' : 'ganhos' }} · {{ $ind['perdidos'] }} {{ $ind['perdidos'] === 1 ? 'perdido' : 'perdidos' }}
            @endif
        </small>
    </div>
</dl>

<div class="grade-3">
    <section class="cartao" aria-labelledby="h-retornos">
        <div class="cartao-cab">
            <h2 id="h-retornos">Retornos</h2>
            <a href="{{ route('tarefas.index') }}">Ver todos</a>
        </div>
        @php $lista = $retornos['atrasadas']->concat($retornos['hoje'])->concat($retornos['proximas']); @endphp
        @if ($lista->isEmpty())
            <div class="vazio"><strong>Nada agendado para os próximos 7 dias</strong>Agende um retorno na página do cliente.</div>
        @else
            <ul>
                @foreach ($lista as $t)
                    @include('partials.retorno', ['t' => $t])
                @endforeach
            </ul>
        @endif
    </section>

    <section class="cartao" aria-labelledby="h-sem-contato">
        <h2 id="h-sem-contato">Faz tempo que não falamos</h2>
        <p class="pequeno mut">Clientes ativos sem contato há mais de 30 dias.</p>
        @if ($semContato->isEmpty())
            <div class="vazio"><strong>Ninguém esquecido</strong>Todos os clientes ativos foram contatados recentemente.</div>
        @else
            <ul class="lista-retornos">
                @foreach ($semContato as $c)
                    @php
                        $ultimo = $c->ultimo_contato ? \Illuminate\Support\Carbon::parse($c->ultimo_contato) : null;
                        $zap = $c->linkWhatsapp();
                    @endphp
                    <li>
                        <div class="corpo">
                            <div class="titulo"><a href="{{ route('clientes.show', $c) }}">{{ $c->nome }}</a></div>
                            <div class="sub">{{ $ultimo ? 'último contato '.$ultimo->diffForHumans() : 'nunca contatado' }}</div>
                        </div>
                        @if ($zap)
                            <a class="botao-whats botao-p" href="{{ $zap }}" target="_blank" rel="noopener noreferrer"><x-icone nome="whatsapp" /> Chamar<span class="so-leitor"> {{ $c->nome }} no WhatsApp (abre em nova aba)</span></a>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</div>

<div class="grade-2 secao">
    <section class="cartao" aria-labelledby="h-funil">
        <div class="cartao-cab">
            <h2 id="h-funil">Funil em aberto</h2>
            <a href="{{ route('negocios.index') }}">Abrir funil</a>
        </div>
        @if ($ind['abertos_qtd'] === 0)
            <div class="vazio"><strong>O funil está vazio</strong>Crie um negócio no <a href="{{ route('negocios.index') }}">Funil</a>.</div>
        @else
            <ul class="barras">
                @foreach ($funil as $l)
                    <li>
                        <span>{{ $l['rotulo'] }}</span>
                        <span class="trilho" role="img" aria-label="{{ $l['rotulo'] }}: {{ Formato::moedaInteira($l['valor']) }}"><span class="enchimento" style="width: {{ $l['pct'] }}%"></span></span>
                        <span><span class="valor">{{ Formato::moedaInteira($l['valor']) }}</span><span class="qtd">{{ $l['qtd'] }} {{ $l['qtd'] === 1 ? 'negócio' : 'negócios' }}</span></span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="cartao" aria-labelledby="h-meses">
        <h2 id="h-meses">Vendas fechadas por mês</h2>
        @if ($meses->sum('valor') == 0)
            <div class="vazio"><strong>Nenhuma venda fechada nos últimos 6 meses</strong>Quando você marcar um negócio como Ganho, ele aparece aqui.</div>
        @else
            <ol class="colunas-graf">
                @foreach ($meses as $m)
                    <li>
                        <span class="coluna-valor">{{ $m['valor'] > 0 ? Formato::moedaInteira($m['valor']) : '—' }}</span>
                        <span class="coluna-barra" style="height: {{ max(3, (int) round($m['pct'] * 0.7)) }}%" role="img" aria-label="{{ $m['mes']->locale('pt_BR')->translatedFormat('F') }}: {{ Formato::moedaInteira($m['valor']) }}, {{ $m['qtd'] }} {{ $m['qtd'] === 1 ? 'negócio' : 'negócios' }}"></span>
                        <span class="coluna-mes">{{ $m['mes']->locale('pt_BR')->translatedFormat('M') }}</span>
                    </li>
                @endforeach
            </ol>
        @endif
    </section>
</div>
@endsection
