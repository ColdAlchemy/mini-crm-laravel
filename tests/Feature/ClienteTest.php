<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Etiqueta;
use App\Models\Interacao;
use App\Models\Negocio;
use App\Models\Tarefa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->entrar();
    }

    public function test_lista_e_busca_clientes(): void
    {
        Cliente::factory()->create(['nome' => 'Maria Silva']);
        Cliente::factory()->create(['nome' => 'João Souza']);

        $this->get('/clientes?q=Maria')->assertOk()->assertSee('Maria Silva')->assertDontSee('João Souza');
    }

    public function test_busca_por_telefone_e_empresa_e_trata_curingas(): void
    {
        Cliente::factory()->create(['nome' => 'Ana', 'telefone' => '(99) 91111-2222', 'empresa' => 'Loja Sol']);
        Cliente::factory()->create(['nome' => 'Bia', 'telefone' => '(99) 93333-4444', 'empresa' => 'Loja Lua']);

        $this->get('/clientes?q=91111')->assertSee('Ana')->assertDontSee('Bia');
        $this->get('/clientes?q=Lua')->assertSee('Bia')->assertDontSee('Ana');
        $this->get('/clientes?q=%25')->assertOk()->assertDontSee('Ana'); // "%" é texto, não curinga
    }

    public function test_filtra_por_status_e_etiqueta(): void
    {
        $vip = Etiqueta::factory()->create(['nome' => 'VIP']);
        $a = Cliente::factory()->create(['nome' => 'Cliente Ativo', 'status' => 'ativo']);
        Cliente::factory()->create(['nome' => 'Cliente Lead', 'status' => 'lead']);
        $a->etiquetas()->attach($vip);

        $this->get('/clientes?status=ativo')->assertSee('Cliente Ativo')->assertDontSee('Cliente Lead');
        $this->get("/clientes?etiqueta={$vip->id}")->assertSee('Cliente Ativo')->assertDontSee('Cliente Lead');
        $this->get('/clientes?status=invalido')->assertSessionHasErrors('status');
    }

    public function test_ordena_por_quem_esta_ha_mais_tempo_sem_contato(): void
    {
        $recente = Cliente::factory()->create(['nome' => 'Zeca Recente']);
        $antigo = Cliente::factory()->create(['nome' => 'Abel Antigo']);
        $nunca = Cliente::factory()->create(['nome' => 'Mara Nunca']);
        Interacao::create(['cliente_id' => $recente->id, 'tipo' => 'nota', 'descricao' => 'x'])->forceFill(['created_at' => now()->subDay()])->save();
        Interacao::create(['cliente_id' => $antigo->id, 'tipo' => 'nota', 'descricao' => 'y'])->forceFill(['created_at' => now()->subDays(60)])->save();

        $this->get('/clientes?ordem=contato')->assertSeeInOrder(['Mara Nunca', 'Abel Antigo', 'Zeca Recente']);
    }

    public function test_cria_cliente(): void
    {
        $this->post('/clientes', ['nome' => 'Ana', 'email' => 'ana@exemplo.com', 'status' => 'lead'])->assertRedirect();

        $this->assertDatabaseHas('clientes', ['email' => 'ana@exemplo.com']);
    }

    public function test_nome_e_obrigatorio_e_email_e_unico(): void
    {
        Cliente::factory()->create(['email' => 'repetido@exemplo.com']);

        $this->post('/clientes', ['nome' => '', 'status' => 'lead'])->assertSessionHasErrors('nome');
        $this->post('/clientes', ['nome' => 'X', 'email' => 'repetido@exemplo.com', 'status' => 'lead'])
            ->assertSessionHasErrors(['email' => 'Já existe um cliente com esse e-mail.']);
        $this->post('/clientes', ['nome' => 'X', 'status' => 'qualquer'])->assertSessionHasErrors('status');
    }

    public function test_atualiza_e_exclui_cliente_com_tudo_em_cascata(): void
    {
        $c = Cliente::factory()->create(['nome' => 'Antes']);
        Negocio::factory()->create(['cliente_id' => $c->id]);
        Tarefa::factory()->create(['cliente_id' => $c->id]);
        Interacao::create(['cliente_id' => $c->id, 'tipo' => 'nota', 'descricao' => 'oi']);

        $this->put("/clientes/{$c->id}", ['nome' => 'Depois', 'email' => $c->email, 'status' => 'ativo'])->assertRedirect();
        $this->assertSame('Depois', $c->fresh()->nome);

        $this->delete("/clientes/{$c->id}")->assertRedirect('/clientes');
        $this->assertDatabaseCount('clientes', 0);
        $this->assertDatabaseCount('negocios', 0);
        $this->assertDatabaseCount('tarefas', 0);
        $this->assertDatabaseCount('interacoes', 0);
    }

    public function test_registra_interacao(): void
    {
        $c = Cliente::factory()->create();

        $this->post("/clientes/{$c->id}/interacoes", ['tipo' => 'whatsapp', 'descricao' => 'Combinei o preço'])->assertRedirect();

        $this->assertDatabaseHas('interacoes', ['cliente_id' => $c->id, 'tipo' => 'whatsapp']);
        $this->post("/clientes/{$c->id}/interacoes", ['tipo' => 'nota', 'descricao' => ''])->assertSessionHasErrors('descricao');
        $this->post("/clientes/{$c->id}/interacoes", ['tipo' => 'fax', 'descricao' => 'x'])->assertSessionHasErrors('tipo');
    }

    public function test_perfil_mostra_linha_do_tempo_com_contatos_negocios_e_retornos(): void
    {
        $c = Cliente::factory()->create(['nome' => 'Dona Lia']);
        Interacao::create(['cliente_id' => $c->id, 'tipo' => 'ligacao', 'descricao' => 'Pediu desconto']);
        $n = Negocio::factory()->create(['cliente_id' => $c->id, 'titulo' => 'Pedido grande', 'valor' => 1234.5]);
        $n->moverPara('ganho');
        Tarefa::factory()->create(['cliente_id' => $c->id, 'titulo' => 'Agradecer', 'concluida_em' => now()]);

        $this->get("/clientes/{$c->id}")->assertOk()
            ->assertSee('Pediu desconto')
            ->assertSee('Negócio aberto: Pedido grande')
            ->assertSee('Negócio ganho: Pedido grande')
            ->assertSee('R$ 1.234,50')
            ->assertSee('Retorno concluído: Agradecer');
    }

    public function test_define_etiquetas_do_cliente(): void
    {
        $c = Cliente::factory()->create();
        [$a, $b] = [Etiqueta::factory()->create(), Etiqueta::factory()->create()];

        $this->put("/clientes/{$c->id}/etiquetas", ['etiquetas' => [$a->id, $b->id]])->assertRedirect();
        $this->assertCount(2, $c->fresh()->etiquetas);

        $this->put("/clientes/{$c->id}/etiquetas", ['etiquetas' => [$a->id]]);
        $this->assertSame([$a->id], $c->fresh()->etiquetas->pluck('id')->all());
        $this->put("/clientes/{$c->id}/etiquetas", ['etiquetas' => [9999]])->assertSessionHasErrors('etiquetas.0');
    }

    public function test_link_do_whatsapp_normaliza_o_telefone(): void
    {
        $c = new Cliente(['nome' => 'Maria da Silva', 'telefone' => '(99) 98888-7777']);
        $this->assertSame('https://wa.me/5599988887777?text='.rawurlencode('Olá, Maria! Tudo bem?'), $c->linkWhatsapp());

        // com 55 na frente, não duplica
        $this->assertStringStartsWith('https://wa.me/5599988887777?', (new Cliente(['nome' => 'A', 'telefone' => '+55 99 98888-7777']))->linkWhatsapp());
        // fixo (10 dígitos)
        $this->assertStringStartsWith('https://wa.me/559933334444?', (new Cliente(['nome' => 'A', 'telefone' => '(99) 3333-4444']))->linkWhatsapp());
        // inválidos e vazios
        $this->assertNull((new Cliente(['nome' => 'A', 'telefone' => '12345']))->linkWhatsapp());
        $this->assertNull((new Cliente(['nome' => 'A', 'telefone' => null]))->linkWhatsapp());
        $this->assertNull((new Cliente(['nome' => 'A', 'telefone' => 'sem telefone']))->linkWhatsapp());
    }

    public function test_botao_do_whatsapp_aparece_so_com_telefone_valido(): void
    {
        Cliente::factory()->create(['nome' => 'Com Zap', 'telefone' => '(99) 98888-7777']);
        Cliente::factory()->create(['nome' => 'Sem Zap', 'telefone' => null]);

        $html = $this->get('/clientes')->getContent();

        $this->assertSame(1, substr_count($html, 'https://wa.me/'));
    }

    public function test_exporta_csv_com_filtros_e_protege_contra_formulas(): void
    {
        Cliente::factory()->create(['nome' => '=SOMA(1;1)', 'email' => 'a@exemplo.com', 'status' => 'ativo']);
        Cliente::factory()->create(['nome' => 'Fora do filtro', 'email' => 'b@exemplo.com', 'status' => 'lead']);

        $r = $this->get('/clientes.csv?status=ativo')->assertOk();
        $csv = $r->streamedContent();

        $this->assertStringContainsString("'=SOMA(1;1)", $csv);
        $this->assertStringContainsString('a@exemplo.com', $csv);
        $this->assertStringNotContainsString('Fora do filtro', $csv);
        $this->assertStringStartsWith("\xEF\xBB\xBF".'nome;email', $csv);
    }

    public function test_telas_exigem_login(): void
    {
        auth()->logout();

        foreach (['/painel', '/clientes', '/clientes/importar', '/negocios', '/tarefas', '/etiquetas', '/clientes.csv'] as $rota) {
            $this->get($rota)->assertRedirect('/entrar');
        }
    }
}
