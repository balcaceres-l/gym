<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * [MIGRACION] Entidad principal: socios del gimnasio.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('socios', function (Blueprint $table) { // [MIGRACION]
            $table->id();

            // [RELACION] Cada socio pertenece a un plan (N:1).
            // restrictOnDelete: no se puede borrar un plan que todavía tiene socios.
            $table->foreignId('plan_id')->constrained('planes')->restrictOnDelete();

            $table->string('dui', 10)->unique();          // formato 00000000-0
            $table->string('nombres', 60);
            $table->string('apellidos', 60);
            $table->date('fecha_nacimiento');
            $table->string('telefono', 9);                // formato 0000-0000
            $table->string('correo', 100)->unique();
            $table->date('fecha_inscripcion');
            $table->date('fecha_vencimiento');            // se extiende con cada pago (Fase 2)
            $table->string('imagen')->nullable();         // ruta del archivo en storage, no la imagen
            $table->timestamps();

            $table->index('fecha_vencimiento');           // acelera filtros de activos / vencidos
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('socios');
    }
};
