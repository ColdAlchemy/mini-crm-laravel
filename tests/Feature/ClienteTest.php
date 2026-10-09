<?php

namespace Tests\Feature;

use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteTest extends TestCase
{
    use RefreshDatabase;

    public function test_lista_e_busca_clientes(): void
    {
        Cliente::factory()->create(['nome' => 'Maria Silva']);
        Cliente::factory()->create(['nome' => 'João Souza']);

        $this->get('/clientes?q=Maria')->assertOk()->assertSee('Maria Silva')->assertDontSee('João Souza');
    }

    public function test_cria_cliente(): void
    {
        $this->post('/clientes', ['nome' => 'Ana', 'email' => 'ana@exemplo.com', 'status' => 'lead'])
            ->assertRedirect();

        $this->assertDatabaseHas('clientes', ['email' => 'ana@exemplo.com']);
    }

    public function test_nome_e_obrigatorio_e_email_e_unico(): void
    {
        Cliente::factory()->create(['email' => 'dup@exemplo.com']);

        $this->post('/clientes', ['status' => 'lead'])->assertSessionHasErrors('nome');
        $this->post('/clientes', ['nome' => 'X', 'email' => 'dup@exemplo.com', 'status' => 'lead'])->assertSessionHasErrors('email');
    }

    public function test_atualiza_e_exclui_cliente(): void
    {
        $c = Cliente::factory()->create();

        $this->put("/clientes/{$c->id}", ['nome' => 'Novo Nome', 'status' => 'ativo'])->assertRedirect();
        $this->assertDatabaseHas('clientes', ['id' => $c->id, 'nome' => 'Novo Nome', 'status' => 'ativo']);

        $this->delete("/clientes/{$c->id}")->assertRedirect('/clientes');
        $this->assertDatabaseMissing('clientes', ['id' => $c->id]);
    }

    public function test_registra_interacao(): void
    {
        $c = Cliente::factory()->create();

        $this->post("/clientes/{$c->id}/interacoes", ['tipo' => 'ligacao', 'descricao' => 'Primeiro contato'])->assertRedirect();

        $this->assertDatabaseHas('interacoes', ['cliente_id' => $c->id, 'tipo' => 'ligacao']);
        $this->get("/clientes/{$c->id}")->assertSee('Primeiro contato');
    }
}
