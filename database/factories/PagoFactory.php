<?php

namespace Database\Factories;

use App\Models\Pago;
use App\Models\Socio;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Pago>
 */
class PagoFactory extends Factory
{
    public function definition(): array
    {
        $socio = Socio::with('plan')->inRandomOrder()->first() ?? Socio::factory()->create();
        $inicio = Carbon::instance(fake()->dateTimeBetween('-6 months', 'now'))->startOfDay();

        return [
            'socio_id'       => $socio->id,
            'plan_id'        => $socio->plan_id,
            'fecha_pago'     => $inicio->toDateString(),
            'monto'          => $socio->plan->precio,          // el monto coincide con el precio del plan
            'metodo'         => fake()->randomElement(Pago::METODOS),
            'periodo_inicio' => $inicio->toDateString(),
            'periodo_fin'    => $inicio->copy()->addDays($socio->plan->duracion_dias)->toDateString(),
        ];
    }
}
