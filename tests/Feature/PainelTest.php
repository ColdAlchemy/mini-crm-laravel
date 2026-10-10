<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Interacao;
use App\Models\Negocio;
use App\Models\Tarefa;
use App\Services\Painel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PainelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->entrar();
    }

    private ?Cliente $comprador = null;

    private function negocio(string $etapa, float $valor, ?int $fechouHaDias = null): Negocio
    {
        // um cliente inativo só para os negócios, para não interferir na contagem de ativos e leads
        $this->comprador ??= Cliente::factory()->create(['status' => 'inativo']);
        $n = Negocio::factory()->create(['cliente_id' => $this->comprador->id, 'valor' => $valor, 'etapa' => $etapa]);
        if ($fechouHaDias !== null) {
            $n->forceFill(['fechado_em' => now()->subDays($fechouHaDias)])->save();
        }

        return $n;
    }

    public function test_painel_vazio_nao_quebra(): void
    {
        $this->get('/painel')->assertOk()->assertSee('O funil está vazio')->assertSee('Nenhuma venda fechada');
    }

    public function test_indicadores(): void
    {
        $this->negocio('novo', 1000);
        $this->negocio('proposta', 2500.5);
        $this->negocio('ganho', 4000, 1);
        $this->negocio('ganho', 600, 1);
        $this->negocio('perdido', 900, 3);
        Cliente::factory()->count(2)->create(['status' => 'ativo']);
        Cliente::factory()->create(['status' => 'lead']);

        $ind = app(Painel::class)->indicadores();

        $this->assertSame(2, $ind['clientes_ativos']);
        $this->assertSame(1, $ind['leads']);
        $this->assertSame(2, $ind['abertos_qtd']);
        $this->assertSame(3500.5, $ind['abertos_valor']);
        $this->assertSame(2, $ind['ganhos']);
        $this->assertSame(67, $ind['fechamento']); // 2 ganhos de 3 fechados
    }

    public function test_ganho_do_mes_ignora_meses_anteriores(): void
    {
        $this->negocio('ganho', 1000, 0);
        $this->negocio('ganho', 5000, 45);

        $ind = app(Painel::class)->indicadores();

        $this->assertSame(1000.0, $ind['ganho_mes_valor']);
        $this->assertSame(1, $ind['ganho_mes_qtd']);
    }

    public function test_funil_e_ganhos_por_mes(): void
    {
        $this->negocio('novo', 100);
        $this->negocio('negociacao', 400);
        $this->negocio('ganho', 800, 0);
        $this->negocio('ganho', 200, 40);

        $funil = app(Painel::class)->funil();
        $this->assertSame(['novo', 'proposta', 'negociacao'], $funil->pluck('etapa')->all());
        $this->assertSame([25, 0, 100], $funil->pluck('pct')->all());

        $meses = app(Painel::class)->ganhosPorMes(6);
        $this->assertCount(6, $meses);
        $this->assertSame(800.0, $meses->last()['valor']);
        $this->assertSame(200.0, (float) $meses->take(5)->sum('valor')); // o ganho antigo cai em meses anteriores
    }

    public function test_retornos_atrasados_de_hoje_e_proximos(): void
    {
        Tarefa::factory()->create(['titulo' => 'Atrasada', 'vence_em' => now()->subDays(2)->toDateString()]);
        Tarefa::factory()->create(['titulo' => 'De hoje', 'vence_em' => now()->toDateString()]);
        Tarefa::factory()->create(['titulo' => 'Em breve', 'vence_em' => now()->addDays(3)->toDateString()]);
        Tarefa::factory()->create(['titulo' => 'Muito longe', 'vence_em' => now()->addDays(30)->toDateString()]);
        Tarefa::factory()->create(['titulo' => 'Já feita', 'vence_em' => now()->toDateString(), 'concluida_em' => now()]);

        $r = app(Painel::class)->retornos();
        $this->assertSame(['Atrasada'], $r['atrasadas']->pluck('titulo')->all());
        $this->assertSame(['De hoje'], $r['hoje']->pluck('titulo')->all());
        $this->assertSame(['Em breve'], $r['proximas']->pluck('titulo')->all());

        $this->get('/painel')->assertOk()->assertSee('Atrasada')->assertSee('Em breve')->assertDontSee('Muito longe');
    }

    public function test_sem_contato_lista_ativos_esquecidos(): void
    {
        $esquecido = Cliente::factory()->create(['nome' => 'Esquecido', 'status' => 'ativo']);
        $esquecido->forceFill(['created_at' => now()->subDays(100)])->save();
        Interacao::create(['cliente_id' => $esquecido->id, 'tipo' => 'nota', 'descricao' => 'x'])->forceFill(['created_at' => now()->subDays(50)])->save();

        $lembrado = Cliente::factory()->create(['nome' => 'Lembrado', 'status' => 'ativo']);
        $lembrado->forceFill(['created_at' => now()->subDays(100)])->save();
        Interacao::create(['cliente_id' => $lembrado->id, 'tipo' => 'nota', 'descricao' => 'y']);

        $novo = Cliente::factory()->create(['nome' => 'Cadastrado hoje', 'status' => 'ativo']);
        $lead = Cliente::factory()->create(['nome' => 'Lead antigo', 'status' => 'lead']);
        $lead->forceFill(['created_at' => now()->subDays(100)])->save();

        $nomes = app(Painel::class)->semContato()->pluck('nome')->all();

        $this->assertSame(['Esquecido'], $nomes);
    }

    public function test_selo_do_menu_conta_retornos_de_hoje_e_atrasados(): void
    {
        Tarefa::factory()->create(['vence_em' => now()->subDay()->toDateString()]);
        Tarefa::factory()->create(['vence_em' => now()->toDateString()]);
        Tarefa::factory()->create(['vence_em' => now()->addDays(5)->toDateString()]);

        $this->get('/clientes')->assertSee('2 para hoje ou atrasados');
    }
}
