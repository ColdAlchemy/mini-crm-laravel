<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutenticacaoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_cadastro_cria_conta_e_entra_no_painel(): void
    {
        $this->post('/cadastrar', [
            'name' => 'Carla Teste', 'email' => 'carla@exemplo.test',
            'password' => 'senha-segura-1', 'password_confirmation' => 'senha-segura-1',
        ])->assertRedirect('/painel');

        $this->assertAuthenticatedAs(User::where('email', 'carla@exemplo.test')->firstOrFail());
    }

    public function test_cadastro_valida_os_campos_em_portugues(): void
    {
        User::factory()->create(['email' => 'repetido@exemplo.test']);

        $this->post('/cadastrar', ['name' => '', 'email' => 'repetido@exemplo.test', 'password' => 'curta', 'password_confirmation' => 'outra'])
            ->assertSessionHasErrors(['name', 'email', 'password']);
        $this->get('/cadastrar')->assertOk()->assertSee('Criar conta');
    }

    public function test_entrar_e_sair(): void
    {
        $user = User::factory()->create(['password' => 'minha-senha-123']);

        $this->post('/entrar', ['email' => $user->email, 'password' => 'minha-senha-123'])->assertRedirect('/painel');
        $this->assertAuthenticatedAs($user);

        $this->post('/sair')->assertRedirect('/entrar');
        $this->assertGuest();
    }

    public function test_senha_errada_nao_autentica(): void
    {
        $user = User::factory()->create(['password' => 'minha-senha-123']);

        $this->post('/entrar', ['email' => $user->email, 'password' => 'errada'])
            ->assertSessionHasErrors(['email' => 'E-mail ou senha incorretos. Confira e tente de novo.']);
        $this->assertGuest();
    }

    public function test_logado_vai_direto_ao_painel(): void
    {
        $this->entrar();

        $this->get('/entrar')->assertRedirect('/painel');
        $this->get('/')->assertRedirect('/painel');
    }
}
