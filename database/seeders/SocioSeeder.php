<?php

namespace Database\Seeders;

use App\Models\Pago;
use App\Models\Plan;
use App\Models\Socio;
use Illuminate\Database\Seeder;

/**
 * Crea socios con la factory y un historial de pagos coherente:
 * cada pago cubre la duración del plan y la fecha de vencimiento
 * del socio es el final del último período pagado.
 */
class SocioSeeder extends Seeder
{
    private const TOTAL_SOCIOS = 20;

    public function run(): void
    {
        $planes = Plan::all();
        $hoy = today();

        for ($i = 0; $i < self::TOTAL_SOCIOS; $i++) {
            $plan = $planes->random();
            $dias = $plan->duracion_dias;

            // Mezcla de casos para que el listado y la ficha muestren todos los estados.
            $tipo = match (true) {
                $i < 3  => 'por_vencer',  // vence en los próximos 7 días
                $i < 8  => 'vencido',
                default => 'activo',
            };

            $inscripcion = match ($tipo) {
                'por_vencer' => $hoy->copy()->subDays($dias - fake()->numberBetween(1, 6)),
                'vencido'    => $hoy->copy()->subDays(fake()->numberBetween($dias + 10, $dias * 2 + 120)),
                'activo'     => $hoy->copy()->subDays(fake()->numberBetween(0, 540)),
            };

            $socio = Socio::factory()->create([
                'plan_id'           => $plan->id,
                'fecha_inscripcion' => $inscripcion->toDateString(),
                'fecha_vencimiento' => $inscripcion->toDateString(),
            ]);

            // Genera los pagos período por período.
            $inicio = $inscripcion->copy();
            do {
                $fin = $inicio->copy()->addDays($dias);

                Pago::create([
                    'socio_id'       => $socio->id,
                    'plan_id'        => $plan->id,
                    'fecha_pago'     => $inicio->toDateString(),
                    'monto'          => $plan->precio,
                    'metodo'         => fake()->randomElement(Pago::METODOS),
                    'periodo_inicio' => $inicio->toDateString(),
                    'periodo_fin'    => $fin->toDateString(),
                ]);

                $inicio = $fin;

                $continuar = match ($tipo) {
                    // sigue pagando hasta cubrir la fecha de hoy
                    'activo', 'por_vencer' => $fin->lt($hoy),
                    // un socio vencido dejó de pagar en algún momento antes de hoy
                    'vencido' => $inicio->copy()->addDays($dias)->lt($hoy) && fake()->boolean(40),
                };
            } while ($continuar);

            $socio->update(['fecha_vencimiento' => $inicio->toDateString()]);
        }
    }
}
