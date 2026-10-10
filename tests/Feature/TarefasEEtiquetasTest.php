<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Etiqueta;
use App\Models\Tarefa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TarefasEEtiquetasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->entrar();
    }

    public function test_agenda_retorno(): void
    {
        $c = Cliente::factory()->create();

        $this->post('/tarefas', ['cliente_id' => $c->id, 'titulo' => 'Ligar amanhã', 'vence_em' => now()->addDay()->toDateString()])->assertRedirect();

        $this->assertDatabaseHas('tarefas', ['cliente_id' => $c->id, 'titulo' => 'Ligar amanhã', 'concluida_em' => null]);
    }

    public function test_valida_o_retorno(): void
    {
        $this->post('/tarefas', [])->assertSessionHasErrors(['cliente_id', 'titulo', 'vence_em']);
        $this->post('/tarefas', ['cliente_id' => Cliente::factory()->create()->id, 'titulo' => 'x', 'vence_em' => 'ontem à noite'])->assertSessionHasErrors('vence_em');
    }

    public function test_conclui_e_reabre(): void
    {
        $t = Tarefa::factory()->create();

        $this->patch("/tarefas/{$t->id}/concluir")->assertRedirect();
        $this->assertTrue($t->fresh()->estaConcluida());

        $this->patch("/tarefas/{$t->id}/reabrir")->assertRedirect();
        $this->assertFalse($t->fresh()->estaConcluida());
    }

    public function test_remove_retorno(): void
    {
        $t = Tarefa::factory()->create();

        $this->delete("/tarefas/{$t->id}")->assertRedirect();

        $this->assertDatabaseCount('tarefas', 0);
    }

    public function test_pagina_separa_atrasados_hoje_proximos_e_concluidos(): void
    {
        Tarefa::factory()->create(['titulo' => 'Tarefa atrasada', 'vence_em' => now()->subDays(3)->toDateString()]);
        Tarefa::factory()->create(['titulo' => 'Tarefa de hoje', 'vence_em' => now()->toDateString()]);
        Tarefa::factory()->create(['titulo' => 'Tarefa futura', 'vence_em' => now()->addDays(20)->toDateString()]);
        Tarefa::factory()->create(['titulo' => 'Tarefa feita', 'concluida_em' => now()]);

        $this->get('/tarefas')->assertOk()
            ->assertSeeInOrder(['Atrasados', 'Tarefa atrasada', 'Para hoje', 'Tarefa de hoje', 'Próximos', 'Tarefa futura', 'Concluídos recentemente', 'Tarefa feita'])
            ->assertSee('venceu há 3 dias');
    }

    public function test_cria_lista_e_remove_etiqueta_sem_apagar_clientes(): void
    {
        $this->post('/etiquetas', ['nome' => 'Atacado', 'cor' => 'azul'])->assertRedirect('/etiquetas');
        $e = Etiqueta::firstOrFail();
        $c = Cliente::factory()->create(['nome' => 'Cliente com etiqueta']);
        $c->etiquetas()->attach($e);

        $this->get('/etiquetas')->assertSee('Atacado')->assertSee('1 cliente');

        $this->delete("/etiquetas/{$e->id}")->assertRedirect('/etiquetas');
        $this->assertDatabaseCount('etiquetas', 0);
        $this->assertDatabaseHas('clientes', ['nome' => 'Cliente com etiqueta']);
        $this->assertDatabaseCount('cliente_etiqueta', 0);
    }

    public function test_valida_etiqueta(): void
    {
        Etiqueta::factory()->create(['nome' => 'VIP']);

        $this->post('/etiquetas', ['nome' => '', 'cor' => 'azul'])->assertSessionHasErrors('nome');
        $this->post('/etiquetas', ['nome' => 'VIP', 'cor' => 'azul'])->assertSessionHasErrors(['nome' => 'Já existe uma etiqueta com esse nome.']);
        $this->post('/etiquetas', ['nome' => 'Nova', 'cor' => 'rosa-choque'])->assertSessionHasErrors('cor');
    }
}
