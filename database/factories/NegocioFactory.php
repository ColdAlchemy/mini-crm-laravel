<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class NegocioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'titulo' => fake()->sentence(3),
            'valor' => fake()->randomFloat(2, 100, 10000),
            'etapa' => 'novo',
        ];
    }
}
