<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Services\ImportadorCsv;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportacaoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->entrar();
    }

    private function importar(string $conteudo): array
    {
        $arquivo = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($arquivo, $conteudo);

        try {
            return app(ImportadorCsv::class)->importar($arquivo);
        } finally {
            unlink($arquivo);
        }
    }

    public function test_importa_com_ponto_e_virgula_e_acentos(): void
    {
        $r = $this->importar("nome;email;telefone;empresa;status\nJoão Pereira;joao@exemplo.com;(99) 97777-6666;Padaria São José;ativo\nMaria;maria@exemplo.com;;;lead\n");

        $this->assertSame(2, $r['criados']);
        $this->assertSame([], $r['ignoradas']);
        $this->assertDatabaseHas('clientes', ['nome' => 'João Pereira', 'empresa' => 'Padaria São José', 'status' => 'ativo']);
        $this->assertDatabaseHas('clientes', ['email' => 'maria@exemplo.com', 'status' => 'lead']);
    }

    public function test_aceita_virgula_bom_e_titulos_alternativos(): void
    {
        $r = $this->importar("\xEF\xBB\xBFName,E-mail,Celular,Company,Situação\nAna,ana@exemplo.com,99988887777,Loja Sol,Inativo\n");

        $this->assertSame(1, $r['criados']);
        $this->assertDatabaseHas('clientes', ['nome' => 'Ana', 'telefone' => '99988887777', 'empresa' => 'Loja Sol', 'status' => 'inativo']);
    }

    public function test_le_arquivo_do_excel_em_windows_1252(): void
    {
        $r = $this->importar(mb_convert_encoding("nome;email\nJosé Antônio;jose@exemplo.com\n", 'Windows-1252', 'UTF-8'));

        $this->assertSame(1, $r['criados']);
        $this->assertDatabaseHas('clientes', ['nome' => 'José Antônio']);
    }

    public function test_atualiza_quem_ja_existe_pelo_email_sem_duplicar(): void
    {
        Cliente::factory()->create(['nome' => 'Nome Antigo', 'email' => 'x@exemplo.com', 'empresa' => 'Mantida']);

        $r = $this->importar("nome;email;telefone\nNome Novo;X@Exemplo.com;(99) 91111-2222\n");

        $this->assertSame(0, $r['criados']);
        $this->assertSame(1, $r['atualizados']);
        $this->assertDatabaseCount('clientes', 1);
        $this->assertDatabaseHas('clientes', ['nome' => 'Nome Novo', 'telefone' => '(99) 91111-2222', 'empresa' => 'Mantida']);
    }

    public function test_relata_linhas_ignoradas_e_importa_o_resto(): void
    {
        $r = $this->importar("nome;email\n;sem@nome.com\nBom;bom@exemplo.com\nRuim;email-quebrado\n\n");

        $this->assertSame(1, $r['criados']);
        $this->assertSame([['linha' => 2, 'motivo' => 'Sem nome.'], ['linha' => 4, 'motivo' => 'E-mail inválido (email-quebrado).']], $r['ignoradas']);
    }

    public function test_sem_email_usa_nome_e_telefone_para_nao_duplicar(): void
    {
        $this->importar("nome;telefone\nPedro;(99) 95555-4444\n");
        $r = $this->importar("nome;telefone\nPedro;(99) 95555-4444\nPedro;(99) 96666-7777\n");

        $this->assertSame(1, $r['criados']);
        $this->assertCount(1, $r['ignoradas']);
        $this->assertDatabaseCount('clientes', 2);
    }

    public function test_exige_coluna_nome(): void
    {
        $this->expectExceptionMessage('coluna "nome"');

        $this->importar("email;telefone\na@b.com;123\n");
    }

    public function test_tela_de_importacao_envia_arquivo_e_mostra_resultado(): void
    {
        $arquivo = UploadedFile::fake()->createWithContent('clientes.csv', "nome;email\nLaura;laura@exemplo.com\n;\nSem Email Ok;\n");

        $this->post('/clientes/importar', ['planilha' => $arquivo])->assertRedirect('/clientes/importar');

        $this->assertDatabaseHas('clientes', ['nome' => 'Laura']);
        $this->get('/clientes/importar')->assertOk()->assertSee('Importação concluída');
    }

    public function test_rejeita_arquivo_que_nao_e_csv_ou_sem_coluna_nome(): void
    {
        $this->post('/clientes/importar', [])->assertSessionHasErrors('planilha');
        $this->post('/clientes/importar', ['planilha' => UploadedFile::fake()->create('foto.png', 10)])->assertSessionHasErrors('planilha');
        $this->post('/clientes/importar', ['planilha' => UploadedFile::fake()->createWithContent('x.csv', "email\na@b.com\n")])
            ->assertSessionHasErrors('planilha');
        $this->assertDatabaseCount('clientes', 0);
    }

    public function test_baixa_planilha_modelo_que_o_proprio_importador_le(): void
    {
        $csv = $this->get('/clientes/modelo.csv')->assertOk()->getContent();

        $r = $this->importar($csv);

        $this->assertSame(2, $r['criados']);
    }
}
