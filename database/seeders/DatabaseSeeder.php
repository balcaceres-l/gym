<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * php artisan migrate:fresh --seed
     * Primero el catálogo (planes) y luego los socios con sus pagos.
     */
    public function run(): void
    {
        $this->call([
            PlanSeeder::class,
            SocioSeeder::class,
        ]);
    }
}
