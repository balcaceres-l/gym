<?php

use App\Http\Controllers\PanelController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SocioController;
use Illuminate\Support\Facades\Route;

// Inicio / panel con indicadores
Route::get('/', [PanelController::class, 'index'])->name('panel');

// Recurso de la entidad principal.
// Fase 1 expone solo la consulta (index, show); en la Fase 2 se quita ->only()
// para habilitar create, store, edit, update y destroy.
Route::resource('socios', SocioController::class)->only(['index', 'show']);

// Catálogo de planes (consulta)
Route::get('planes', [PlanController::class, 'index'])->name('planes.index');
