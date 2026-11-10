<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    public function definition(): array
    {
        $dias = fake()->randomElement([30, 90, 180, 365]);

        return [
            'nombre'        => 'Plan ' . fake()->unique()->word(),
            'descripcion'   => fake()->sentence(),
            'precio'        => round($dias * 0.75, 2),
            'duracion_dias' => $dias,
        ];
    }
}
