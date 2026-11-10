<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * [MIGRACION] Catálogo de planes de membresía (mensual, trimestral, anual...).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes', function (Blueprint $table) { // [MIGRACION]
            $table->id();
            $table->string('nombre', 50)->unique();              // no puede haber dos planes con el mismo nombre
            $table->string('descripcion', 255)->nullable();      // opcional
            $table->decimal('precio', 8, 2);                     // dinero: decimal, nunca float
            $table->unsignedSmallInteger('duracion_dias');       // 30, 90, 365...
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planes');
    }
};
