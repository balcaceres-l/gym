<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * Datos falsos para la entidad principal (Socio).
 *
 * @extends Factory<\App\Models\Socio>
 */
class SocioFactory extends Factory
{
    public function definition(): array
    {
        $plan = Plan::inRandomOrder()->first() ?? Plan::factory()->create();
        $inscripcion = Carbon::instance(fake()->dateTimeBetween('-18 months', 'now'))->startOfDay();

        return [
            'plan_id'           => $plan->id,
            'dui'               => fake()->unique()->numerify('0#######-#'),                   // 00000000-0
            'nombres'           => fake()->firstName() . ' ' . fake()->firstName(),
            'apellidos'         => fake()->lastName() . ' ' . fake()->lastName(),
            'fecha_nacimiento'  => fake()->dateTimeBetween('-60 years', '-16 years')->format('Y-m-d'), // mayor de 15
            'telefono'          => fake()->regexify('[267][0-9]{3}-[0-9]{4}'),                // 0000-0000
            'correo'            => fake()->unique()->safeEmail(),
            'fecha_inscripcion' => $inscripcion->toDateString(),
            'fecha_vencimiento' => $inscripcion->copy()->addDays($plan->duracion_dias)->toDateString(),
            'imagen'            => null,                                                       // usa la imagen por defecto
        ];
    }
}
