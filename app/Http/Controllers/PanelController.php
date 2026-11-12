<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Plan;
use App\Models\Socio;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Página de inicio / Reporte general con consultas agregadas Eloquent.
 * Fase 2: socios activos y vencidos, ingresos por mes y membresías que vencen en los próximos 7 días.
 */
class PanelController extends Controller
{
    /**
     * [CRUD-READ] [REGLA-NEGOCIO] Panel de indicadores y reportes agregados del gimnasio.
     */
    public function index(): View
    {
        $indicadores = [
            'socios'           => Socio::count(),
            'activos'          => Socio::activos()->count(),
            'vencidos'         => Socio::vencidos()->count(),
            'por_vencer'       => Socio::porVencer(7)->count(),
            'pagos'            => Pago::count(),
            'ingresos_totales' => (float) Pago::sum('monto'),
        ];

        // Conteo de socios por plan, calculado por la base de datos (withCount).
        $planes = Plan::withCount('socios')->orderBy('duracion_dias')->get();

        // [REGLA-NEGOCIO] Membresías activas que vencen en los próximos 7 días.
        $sociosPorVencer = Socio::with('plan')
            ->porVencer(7)
            ->orderBy('fecha_vencimiento')
            ->orderBy('apellidos')
            ->get();

        // [CRUD-READ] Consulta agregada de ingresos por mes (SUM y COUNT agrupados por año y mes).
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $expresionAnio = "CAST(strftime('%Y', fecha_pago) AS INTEGER)";
            $expresionMes = "CAST(strftime('%m', fecha_pago) AS INTEGER)";
        } else {
            $expresionAnio = 'YEAR(fecha_pago)';
            $expresionMes = 'MONTH(fecha_pago)';
        }

        $ingresosPorMes = Pago::query()
            ->selectRaw("{$expresionAnio} as anio, {$expresionMes} as mes, COUNT(*) as cantidad_pagos, SUM(monto) as total_mes")
            ->groupByRaw("{$expresionAnio}, {$expresionMes}")
            ->orderByDesc('anio')
            ->orderByDesc('mes')
            ->limit(12)
            ->get()
            ->map(function ($fila) {
                $fecha = Carbon::createFromDate((int) $fila->anio, (int) $fila->mes, 1);
                $fila->etiqueta_mes = ucfirst($fecha->translatedFormat('F Y'));

                return $fila;
            });

        return view('panel.index', compact('indicadores', 'planes', 'sociosPorVencer', 'ingresosPorMes'));
    }
}
