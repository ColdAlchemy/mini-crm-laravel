@php
    $dias = $t->diasRestantes();
    $quando = match (true) {
        $t->estaConcluida() => 'feito em '.$t->concluida_em->format('d/m'),
        $dias === 0 => 'hoje',
        $dias === 1 => 'amanhã',
        $dias > 1 => 'em '.$dias.' dias',
        $dias === -1 => 'venceu ontem',
        default => 'venceu há '.abs($dias).' dias',
    };
    $zap = $t->cliente?->linkWhatsapp();
@endphp
<li class="retorno {{ $t->estaConcluida() ? 'feito' : '' }}">
    <form method="POST" action="{{ route($t->estaConcluida() ? 'tarefas.reabrir' : 'tarefas.concluir', $t) }}">
        @csrf @method('PATCH')
        <button type="submit" class="marcar {{ $t->estaConcluida() ? 'feito' : '' }}" aria-label="{{ $t->estaConcluida() ? 'Reabrir' : 'Concluir' }}: {{ $t->titulo }}">
            <x-icone nome="ok" />
        </button>
    </form>
    <div>
        <div class="titulo">{{ $t->titulo }}</div>
        <div class="sub">
            <a href="{{ route('clientes.show', $t->cliente_id) }}">{{ $t->cliente->nome }}</a> ·
            <time datetime="{{ $t->vence_em->toDateString() }}">{{ $t->vence_em->format('d/m/Y') }}</time>
            @unless ($t->estaConcluida())
                · <span class="chip {{ $dias < 0 ? 'chip--atrasado' : ($dias === 0 ? 'chip--hoje' : '') }}">{{ $quando }}</span>
            @endunless
        </div>
    </div>
    <div class="acoes acoes-retorno">
        @if ($zap && ! $t->estaConcluida())
            <a class="botao-whats botao-p" href="{{ $zap }}" target="_blank" rel="noopener noreferrer"><x-icone nome="whatsapp" /> Chamar<span class="so-leitor"> {{ $t->cliente->nome }} no WhatsApp (abre em nova aba)</span></a>
        @endif
        <form method="POST" action="{{ route('tarefas.destroy', $t) }}" data-confirmar="Remover o retorno “{{ $t->titulo }}”?">
            @csrf @method('DELETE')
            <button type="submit" class="botao-link pequeno">Remover…</button>
        </form>
    </div>
</li>
