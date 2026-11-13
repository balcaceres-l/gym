<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Plan;
use App\Models\Socio;
use App\Services\ReporteService;
use Illuminate\View\View;

/**
 * Página de inicio / Reporte general con consultas agregadas Eloquent.
 * Fase 2: socios activos y vencidos, ingresos por mes y membresías que vencen en los próximos 7 días.
 * Las consultas de los reportes viven en ReporteService para compartirlas con la exportación a CSV y PDF.
 */
class PanelController extends Controller
{
    /**
     * [CRUD-READ] [REGLA-NEGOCIO] Panel de indicadores y reportes agregados del gimnasio.
     * [INYECCION-DEPENDENCIAS] Laravel inyecta el servicio de reportes.
     */
    public function index(ReporteService $reportes): View
    {
        $indicadores = [
            'socios'           => Socio::count(),
            'activos'          => Socio::activos()->count(),
            'vencidos'         => Socio::vencidos()->count(),
            'por_vencer'       => Socio::porVencer(ReporteService::DIAS_POR_VENCER)->count(),
            'pagos'            => Pago::count(),
            'ingresos_totales' => (float) Pago::sum('monto'),
        ];

        // Conteo de socios por plan, calculado por la base de datos (withCount).
        $planes = Plan::withCount('socios')->orderBy('duracion_dias')->get();

        // [REGLA-NEGOCIO] Membresías activas que vencen en los próximos 7 días.
        $sociosPorVencer = $reportes->sociosPorVencer();

        // [CRUD-READ] Consulta agregada de ingresos por mes (SUM y COUNT agrupados por año y mes): últimos 12 meses.
        $ingresosPorMes = $reportes->ingresosPorMes(12);

        return view('panel.index', compact('indicadores', 'planes', 'sociosPorVencer', 'ingresosPorMes'));
    }
}
