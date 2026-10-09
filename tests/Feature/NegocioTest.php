<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Negocio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NegocioTest extends TestCase
{
    use RefreshDatabase;

    public function test_cria_negocio_na_etapa_novo(): void
    {
        $c = Cliente::factory()->create();

        $this->post('/negocios', ['cliente_id' => $c->id, 'titulo' => 'Site', 'valor' => 1500])->assertRedirect();

        $this->assertDatabaseHas('negocios', ['titulo' => 'Site', 'etapa' => 'novo']);
    }

    public function test_move_negocio_de_etapa(): void
    {
        $n = Negocio::factory()->create();

        $this->patch("/negocios/{$n->id}/etapa", ['etapa' => 'ganho'])->assertRedirect();

        $this->assertSame('ganho', $n->fresh()->etapa);
    }

    public function test_rejeita_etapa_invalida(): void
    {
        $n = Negocio::factory()->create();

        $this->patch("/negocios/{$n->id}/etapa", ['etapa' => 'inexistente'])->assertSessionHasErrors('etapa');
    }

    public function test_quadro_mostra_negocios(): void
    {
        Negocio::factory()->create(['titulo' => 'Consultoria Alfa']);

        $this->get('/negocios')->assertOk()->assertSee('Consultoria Alfa');
    }

    public function test_excluir_cliente_remove_negocios(): void
    {
        $n = Negocio::factory()->create();

        $this->delete("/clientes/{$n->cliente_id}");

        $this->assertDatabaseMissing('negocios', ['id' => $n->id]);
    }
}
