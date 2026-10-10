<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Negocio;
use App\Support\Formato;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NegocioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->entrar();
    }

    public function test_cria_negocio_na_etapa_novo(): void
    {
        $c = Cliente::factory()->create();

        $this->post('/negocios', ['cliente_id' => $c->id, 'titulo' => 'Site', 'valor' => 1500])->assertRedirect();

        $this->assertDatabaseHas('negocios', ['titulo' => 'Site', 'etapa' => 'novo', 'fechado_em' => null]);
    }

    public function test_aceita_valor_digitado_no_formato_brasileiro(): void
    {
        $c = Cliente::factory()->create();

        $this->post('/negocios', ['cliente_id' => $c->id, 'titulo' => 'A', 'valor' => '1.500,50'])->assertSessionHasNoErrors();
        $this->post('/negocios', ['cliente_id' => $c->id, 'titulo' => 'B', 'valor' => 'R$ 2.000'])->assertSessionHasNoErrors();
        $this->post('/negocios', ['cliente_id' => $c->id, 'titulo' => 'C', 'valor' => 'muito'])->assertSessionHasErrors('valor');

        $this->assertEquals(1500.50, Negocio::where('titulo', 'A')->value('valor'));
        $this->assertEquals(2000, Negocio::where('titulo', 'B')->value('valor'));
    }

    public function test_formato_decimal(): void
    {
        $this->assertSame(1500.5, Formato::decimal('1.500,50'));
        $this->assertSame(1500.0, Formato::decimal('1.500'));
        $this->assertSame(1500.5, Formato::decimal('1500.50'));
        $this->assertSame(12.0, Formato::decimal('R$ 12'));
        $this->assertSame(0.99, Formato::decimal('0,99'));
        $this->assertNull(Formato::decimal(''));
        $this->assertNull(Formato::decimal('abc'));
        $this->assertSame('R$ 1.234,50', Formato::moeda(1234.5));
        $this->assertSame('R$ 12.400', Formato::moedaInteira(12400));
    }

    public function test_move_negocio_de_etapa_e_registra_fechamento(): void
    {
        $n = Negocio::factory()->create();

        $this->patch("/negocios/{$n->id}/etapa", ['etapa' => 'ganho'])->assertRedirect();
        $this->assertSame('ganho', $n->fresh()->etapa);
        $this->assertNotNull($n->fresh()->fechado_em);

        $this->patch("/negocios/{$n->id}/etapa", ['etapa' => 'negociacao']);
        $this->assertNull($n->fresh()->fechado_em); // reabriu
    }

    public function test_mover_entre_ganho_e_perdido_mantem_a_data_original_de_fechamento(): void
    {
        $n = Negocio::factory()->create();
        $n->moverPara('ganho');
        $n->forceFill(['fechado_em' => now()->subDays(10)])->save();
        $data = $n->fresh()->fechado_em->toDateString();

        $n->fresh()->moverPara('perdido');

        $this->assertSame($data, $n->fresh()->fechado_em->toDateString());
    }

    public function test_arrastar_e_soltar_usa_json(): void
    {
        $n = Negocio::factory()->create();

        $this->patchJson("/negocios/{$n->id}/etapa", ['etapa' => 'proposta'])
            ->assertOk()
            ->assertJson(['id' => $n->id, 'etapa' => 'proposta', 'rotulo' => 'Proposta', 'fechado_em' => null]);
        $this->patchJson("/negocios/{$n->id}/etapa", ['etapa' => 'inexistente'])->assertStatus(422);
    }

    public function test_rejeita_etapa_invalida(): void
    {
        $n = Negocio::factory()->create();

        $this->patch("/negocios/{$n->id}/etapa", ['etapa' => 'inexistente'])->assertSessionHasErrors('etapa');
    }

    public function test_quadro_mostra_negocios_com_total_por_coluna(): void
    {
        $c = Cliente::factory()->create(['nome' => 'Dona Rita']);
        Negocio::factory()->create(['cliente_id' => $c->id, 'titulo' => 'Pedido de teste', 'valor' => 1000, 'etapa' => 'novo']);
        Negocio::factory()->create(['cliente_id' => $c->id, 'titulo' => 'Outro', 'valor' => 250.5, 'etapa' => 'novo']);

        $this->get('/negocios')->assertOk()->assertSee('Pedido de teste')->assertSee('Dona Rita')->assertSee('R$ 1.250,50');
    }

    public function test_exclui_negocio(): void
    {
        $n = Negocio::factory()->create();

        $this->delete("/negocios/{$n->id}")->assertRedirect();

        $this->assertDatabaseCount('negocios', 0);
    }

    public function test_valida_a_criacao(): void
    {
        $this->post('/negocios', [])->assertSessionHasErrors(['cliente_id', 'titulo', 'valor']);
        $this->post('/negocios', ['cliente_id' => 999, 'titulo' => 'x', 'valor' => 1])->assertSessionHasErrors('cliente_id');
    }
}
