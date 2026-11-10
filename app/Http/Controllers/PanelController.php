<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Plan;
use App\Models\Socio;
use Illuminate\View\View;

/**
 * Página de inicio con indicadores resumidos.
 * Fase 1: totales básicos. Fase 2: ingresos por mes y vencimientos próximos.
 */
class PanelController extends Controller
{
    // [CRUD-READ]
    public function index(): View
    {
        $indicadores = [
            'socios'   => Socio::count(),
            'activos'  => Socio::activos()->count(),
            'vencidos' => Socio::vencidos()->count(),
            'pagos'    => Pago::count(),
        ];

        // Conteo de socios por plan, calculado por la base de datos (withCount).
        $planes = Plan::withCount('socios')->orderBy('duracion_dias')->get();

        return view('panel.index', compact('indicadores', 'planes'));
    }
}
