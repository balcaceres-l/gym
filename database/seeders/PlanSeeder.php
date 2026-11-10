<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * Catálogo fijo de planes de membresía.
 */
class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $planes = [
            ['nombre' => 'Mensual',    'precio' => 25.00,  'duracion_dias' => 30,  'descripcion' => 'Acceso libre a sala de pesas y cardio durante 30 días.'],
            ['nombre' => 'Trimestral', 'precio' => 65.00,  'duracion_dias' => 90,  'descripcion' => 'Tres meses de acceso con una evaluación física incluida.'],
            ['nombre' => 'Semestral',  'precio' => 120.00, 'duracion_dias' => 180, 'descripcion' => 'Seis meses de acceso y clases grupales ilimitadas.'],
            ['nombre' => 'Anual',      'precio' => 220.00, 'duracion_dias' => 365, 'descripcion' => 'Un año completo con el mejor precio por día y casillero incluido.'],
        ];

        foreach ($planes as $plan) {
            Plan::create($plan);
        }
    }
}
