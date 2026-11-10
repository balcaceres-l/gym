<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * [MIGRACION] Entidad relacionada: pagos de membresía realizados por los socios.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) { // [MIGRACION]
            $table->id();

            // [RELACION] Un socio tiene muchos pagos (1:N).
            // cascadeOnDelete: si se elimina el socio, su historial de pagos se elimina con él.
            $table->foreignId('socio_id')->constrained('socios')->cascadeOnDelete();

            // [RELACION] Plan que se pagó. Se guarda aparte del plan actual del socio
            // porque el socio puede cambiar de plan y el historial debe conservar el original.
            $table->foreignId('plan_id')->constrained('planes')->restrictOnDelete();

            $table->date('fecha_pago');
            $table->decimal('monto', 8, 2);                                   // debe coincidir con el precio del plan
            $table->enum('metodo', ['efectivo', 'tarjeta', 'transferencia']);
            $table->date('periodo_inicio');                                   // período cubierto por el pago
            $table->date('periodo_fin');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
