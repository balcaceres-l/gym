<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\View\View;

/**
 * Consulta del catálogo de planes de membresía.
 */
class PlanController extends Controller
{
    // [CRUD-READ] Lista de planes con la cantidad de socios inscritos en cada uno.
    public function index(): View
    {
        $planes = Plan::withCount('socios')->orderBy('duracion_dias')->get();

        return view('planes.index', compact('planes'));
    }
}
