<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class TarefaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'titulo' => 'Ligar para '.fake()->firstName(),
            'vence_em' => now()->addDays(2)->toDateString(),
        ];
    }
}
