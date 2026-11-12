<?php

use App\Http\Controllers\PagoController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SocioController;
use Illuminate\Support\Facades\Route;

// Inicio / panel con indicadores y reportes (Fase 2)
Route::get('/', [PanelController::class, 'index'])->name('panel');

// Recurso completo de la entidad principal (Socio):
// index, create, store, show, edit, update y destroy.
Route::resource('socios', SocioController::class);

// Entidad relacionada (Pago): listado general y registro de pagos con extensión de membresía.
Route::resource('pagos', PagoController::class)->only(['index', 'create', 'store']);

// Catálogo de planes (consulta)
Route::get('planes', [PlanController::class, 'index'])->name('planes.index');
