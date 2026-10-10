<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Etiqueta;
use App\Models\Interacao;
use App\Models\Negocio;
use App\Models\Tarefa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/** Dados fictícios para demonstração (nomes e números inventados). Datas relativas a hoje. */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'demo@balcao.test'],
            ['name' => 'Carla Demonstração', 'password' => 'demonstracao'],
        );

        if (Cliente::exists()) {
            return;
        }

        $etiquetas = collect([
            ['Atacado', 'azul'], ['VIP', 'coral'], ['Indicação', 'petroleo'], ['Cobrar', 'ambar'],
        ])->mapWithKeys(fn ($e) => [$e[0] => Etiqueta::create(['nome' => $e[0], 'cor' => $e[1]])]);

        // nome, empresa, telefone, status, etiquetas, dias desde o último contato (null = nunca)
        $clientes = [
            ['Marta Oliveira', 'Mercadinho Boa Compra', '(99) 98811-2201', 'ativo', ['VIP', 'Atacado'], 2],
            ['Roberto Lima', 'Padaria Pão Quente', '(99) 98822-3302', 'ativo', ['Atacado'], 12],
            ['Fernanda Alves', 'Studio Bela Forma', '(99) 98833-4403', 'ativo', ['VIP'], 5],
            ['Tiago Mendes', 'Auto Peças Mendes', '(99) 98844-5504', 'lead', [], 1],
            ['Juliana Costa', 'Boutique Juju', '(99) 98855-6605', 'lead', ['Indicação'], 3],
            ['Paulo Ribeiro', 'Ribeiro Materiais de Construção', '(99) 98866-7706', 'ativo', ['Atacado', 'Cobrar'], 47],
            ['Camila Rocha', 'Café Grão Dourado', '(99) 98877-8807', 'ativo', [], 33],
            ['Daniel Pires', 'Pires Informática', '(99) 98888-9908', 'lead', [], null],
            ['Aline Barros', 'Papelaria Letra Viva', '(99) 98899-0009', 'ativo', ['Indicação'], 8],
            ['Sérgio Nunes', 'Nunes Farmácia', '(99) 98810-1110', 'inativo', [], 140],
            ['Larissa Teixeira', 'Pet Shop Amigo Fiel', '(99) 98821-2221', 'lead', ['Indicação'], 6],
            ['Henrique Duarte', 'Duarte Móveis', '(99) 98832-3332', 'ativo', ['VIP'], 15],
        ];

        $porNome = [];
        foreach ($clientes as [$nome, $empresa, $telefone, $status, $tags, $dias]) {
            $cliente = Cliente::create([
                'nome' => $nome,
                'empresa' => $empresa,
                'telefone' => $telefone,
                'email' => strtolower(explode(' ', Str::ascii($nome))[0]).'@exemplo.com',
                'status' => $status,
            ]);
            $cliente->forceFill(['created_at' => now()->subDays(($dias ?? 5) + 60), 'updated_at' => now()])->save();
            $cliente->etiquetas()->sync(array_map(fn ($t) => $etiquetas[$t]->id, $tags));
            $porNome[$nome] = $cliente;

            if ($dias !== null) {
                $this->interacao($cliente, 'whatsapp', 'Combinamos os próximos passos e enviei o catálogo.', $dias);
                $this->interacao($cliente, 'ligacao', 'Ligação rápida para apresentar as condições de pagamento.', $dias + 9);
            }
        }

        // nome do cliente, título, valor, etapa, dias desde que abriu, dias desde que fechou (se fechado)
        $negocios = [
            ['Marta Oliveira', 'Pedido de fim de ano', 4800, 'negociacao', 6, null],
            ['Roberto Lima', 'Reforma da vitrine', 3200, 'proposta', 10, null],
            ['Tiago Mendes', 'Primeiro pedido', 1500, 'novo', 1, null],
            ['Juliana Costa', 'Coleção de verão', 2750.5, 'proposta', 4, null],
            ['Daniel Pires', 'Contrato de suporte mensal', 900, 'novo', 2, null],
            ['Larissa Teixeira', 'Kit de lançamento', 1200, 'novo', 3, null],
            ['Fernanda Alves', 'Pacote anual', 5400, 'ganho', 40, 3],
            ['Henrique Duarte', 'Linha de sofás', 7200, 'ganho', 80, 20],
            ['Aline Barros', 'Material escolar', 1850, 'ganho', 95, 40],
            ['Camila Rocha', 'Máquina de café', 6300, 'ganho', 130, 70],
            ['Paulo Ribeiro', 'Lote de ferramentas', 3900, 'ganho', 160, 100],
            ['Marta Oliveira', 'Reposição de estoque', 2600, 'ganho', 170, 130],
            ['Sérgio Nunes', 'Sistema de pedidos', 4100, 'perdido', 120, 90],
            ['Roberto Lima', 'Forno novo', 2300, 'perdido', 50, 25],
        ];
        foreach ($negocios as [$nome, $titulo, $valor, $etapa, $abriu, $fechou]) {
            $negocio = Negocio::create(['cliente_id' => $porNome[$nome]->id, 'titulo' => $titulo, 'valor' => $valor, 'etapa' => $etapa]);
            $negocio->forceFill([
                'created_at' => now()->subDays($abriu),
                'fechado_em' => $fechou === null ? null : now()->subDays($fechou),
            ])->save();
        }

        // nome do cliente, título, vence em (dias a partir de hoje; negativo = atrasado)
        $tarefas = [
            ['Paulo Ribeiro', 'Cobrar a parcela em aberto', -4],
            ['Camila Rocha', 'Perguntar se o café está saindo bem', -1],
            ['Marta Oliveira', 'Confirmar o pedido de fim de ano', 0],
            ['Tiago Mendes', 'Enviar o orçamento do primeiro pedido', 0],
            ['Juliana Costa', 'Mandar fotos da coleção', 2],
            ['Roberto Lima', 'Ligar sobre a proposta da vitrine', 3],
            ['Daniel Pires', 'Marcar visita técnica', 6],
        ];
        foreach ($tarefas as [$nome, $titulo, $dias]) {
            Tarefa::create(['cliente_id' => $porNome[$nome]->id, 'titulo' => $titulo, 'vence_em' => now()->addDays($dias)->toDateString()]);
        }
        Tarefa::create(['cliente_id' => $porNome['Fernanda Alves']->id, 'titulo' => 'Agradecer a compra', 'vence_em' => now()->subDays(2)->toDateString(), 'concluida_em' => now()->subDays(2)]);
    }

    private function interacao(Cliente $cliente, string $tipo, string $texto, int $diasAtras): void
    {
        $i = Interacao::create(['cliente_id' => $cliente->id, 'tipo' => $tipo, 'descricao' => $texto]);
        $i->forceFill(['created_at' => now()->subDays($diasAtras), 'updated_at' => now()->subDays($diasAtras)])->save();
    }
}
