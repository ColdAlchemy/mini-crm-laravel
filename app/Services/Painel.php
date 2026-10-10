<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Tarefa;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/** Números do painel inicial. Tudo calculado no banco; nada de dados inventados. */
class Painel
{
    /** @return array<string, mixed> */
    public function indicadores(): array
    {
        $abertos = Negocio::whereIn('etapa', Negocio::ABERTAS);
        $ganhosMes = Negocio::where('etapa', 'ganho')->where('fechado_em', '>=', now()->startOfMonth());
        $ganhos = Negocio::where('etapa', 'ganho')->count();
        $perdidos = Negocio::where('etapa', 'perdido')->count();

        return [
            'clientes_ativos' => Cliente::where('status', 'ativo')->count(),
            'leads' => Cliente::where('status', 'lead')->count(),
            'abertos_qtd' => (clone $abertos)->count(),
            'abertos_valor' => (float) (clone $abertos)->sum('valor'),
            'ganho_mes_qtd' => (clone $ganhosMes)->count(),
            'ganho_mes_valor' => (float) (clone $ganhosMes)->sum('valor'),
            'ganhos' => $ganhos,
            'perdidos' => $perdidos,
            'fechamento' => ($ganhos + $perdidos) > 0 ? (int) round($ganhos / ($ganhos + $perdidos) * 100) : null,
        ];
    }

    /**
     * Negócios em aberto por etapa, com a largura relativa da barra (0–100).
     *
     * @return Collection<int, array{etapa: string, rotulo: string, qtd: int, valor: float, pct: int}>
     */
    public function funil(): Collection
    {
        $porEtapa = Negocio::whereIn('etapa', Negocio::ABERTAS)
            ->selectRaw('etapa, count(*) as qtd, sum(valor) as valor')
            ->groupBy('etapa')->get()->keyBy('etapa');

        $linhas = collect(Negocio::ABERTAS)->map(fn (string $e) => [
            'etapa' => $e,
            'rotulo' => Negocio::ETAPAS[$e],
            'qtd' => (int) ($porEtapa[$e]->qtd ?? 0),
            'valor' => (float) ($porEtapa[$e]->valor ?? 0),
        ]);
        $maior = max(1.0, (float) $linhas->max('valor'));

        return $linhas->map(fn ($l) => $l + ['pct' => (int) round($l['valor'] / $maior * 100)]);
    }

    /**
     * Valor ganho por mês, dos últimos $meses meses (o atual é o último).
     *
     * @return Collection<int, array{mes: Carbon, qtd: int, valor: float, pct: int}>
     */
    public function ganhosPorMes(int $meses = 6): Collection
    {
        $inicio = now()->startOfMonth()->subMonths($meses - 1);
        $ganhos = Negocio::where('etapa', 'ganho')->where('fechado_em', '>=', $inicio)->get(['valor', 'fechado_em']);

        $linhas = collect(range(0, $meses - 1))->map(function (int $i) use ($inicio, $ganhos) {
            $mes = $inicio->copy()->addMonths($i);
            $doMes = $ganhos->filter(fn ($n) => $n->fechado_em->isSameMonth($mes));

            return ['mes' => $mes, 'qtd' => $doMes->count(), 'valor' => (float) $doMes->sum('valor')];
        });
        $maior = max(1.0, (float) $linhas->max('valor'));

        return $linhas->map(fn ($l) => $l + ['pct' => (int) round($l['valor'] / $maior * 100)]);
    }

    /** @return array{atrasadas: Collection, hoje: Collection, proximas: Collection} */
    public function retornos(): array
    {
        $pendentes = Tarefa::with('cliente:id,nome,telefone')->whereNull('concluida_em');
        $hoje = now()->toDateString();

        return [
            'atrasadas' => (clone $pendentes)->whereDate('vence_em', '<', $hoje)->orderBy('vence_em')->limit(8)->get(),
            'hoje' => (clone $pendentes)->whereDate('vence_em', $hoje)->orderBy('id')->get(),
            'proximas' => (clone $pendentes)->whereDate('vence_em', '>=', now()->addDay()->toDateString())->whereDate('vence_em', '<=', now()->addDays(7)->toDateString())->orderBy('vence_em')->limit(8)->get(),
        ];
    }

    /** Clientes ativos sem nenhuma interação há $dias dias (ou nunca contatados e cadastrados há mais que isso). */
    public function semContato(int $dias = 30, int $limite = 6): Collection
    {
        $corte = now()->subDays($dias);

        return Cliente::where('status', 'ativo')
            ->withMax('interacoes as ultimo_contato', 'created_at')
            ->get()
            ->filter(fn (Cliente $c) => ($c->ultimo_contato ? Carbon::parse($c->ultimo_contato) : $c->created_at)->lt($corte))
            ->sortBy(fn (Cliente $c) => $c->ultimo_contato ?? $c->created_at->toDateTimeString())
            ->take($limite)
            ->values();
    }
}
