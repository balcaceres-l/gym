<?php

namespace App\Services;

use App\Models\Pago;
use App\Models\Socio;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Consultas agregadas de los reportes del gimnasio.
 *
 * Las usan el Panel (en pantalla) y la exportación a CSV y PDF, de modo que
 * ambos muestran siempre las mismas cifras.
 */
class ReporteService
{
    /** Reportes exportables: clave que viaja en la URL => título. */
    public const REPORTES = [
        'ingresos'   => 'Ingresos por mes',
        'por-vencer' => 'Membresías que vencen en los próximos 7 días',
        'socios'     => 'Estado de las membresías de los socios',
    ];

    /** Formatos de exportación permitidos. */
    public const FORMATOS = ['csv', 'pdf'];

    /** Ventana de días del reporte de membresías por vencer. */
    public const DIAS_POR_VENCER = 7;

    /**
     * [CRUD-READ] Ingresos agrupados por año y mes (SUM y COUNT calculados por la base de datos).
     * Sin $limite devuelve todos los meses; con $limite, los más recientes.
     */
    public function ingresosPorMes(?int $limite = null): Collection
    {
        // El año y el mes se extraen con la función propia de cada motor. Son expresiones
        // fijas del código: ningún dato del usuario se concatena en el SQL.
        if (DB::connection()->getDriverName() === 'sqlite') {
            $expresionAnio = "CAST(strftime('%Y', fecha_pago) AS INTEGER)";
            $expresionMes = "CAST(strftime('%m', fecha_pago) AS INTEGER)";
        } else {
            $expresionAnio = 'YEAR(fecha_pago)';
            $expresionMes = 'MONTH(fecha_pago)';
        }

        return Pago::query()
            ->selectRaw("{$expresionAnio} as anio, {$expresionMes} as mes, COUNT(*) as cantidad_pagos, SUM(monto) as total_mes")
            ->groupByRaw("{$expresionAnio}, {$expresionMes}")
            ->orderByDesc('anio')
            ->orderByDesc('mes')
            ->when($limite, fn ($consulta) => $consulta->limit($limite))
            ->get()
            ->map(function ($fila) {
                $fecha = Carbon::createFromDate((int) $fila->anio, (int) $fila->mes, 1);
                $fila->etiqueta_mes = ucfirst($fecha->translatedFormat('F Y'));

                return $fila;
            });
    }

    /**
     * [REGLA-NEGOCIO] Membresías activas que vencen dentro de los próximos días indicados.
     */
    public function sociosPorVencer(int $dias = self::DIAS_POR_VENCER): Collection
    {
        return Socio::with('plan')
            ->porVencer($dias)
            ->orderBy('fecha_vencimiento')
            ->orderBy('apellidos')
            ->get();
    }

    /**
     * Tabla lista para exportar: título, resumen, columnas, filas y una fila de totales opcional.
     * Cada celda ya viene como texto o número; el CSV y el PDF solo se encargan de dibujarla.
     *
     * @return array{titulo: string, resumen: string, columnas: array<int, string>, filas: array<int, array<int, string|int>>, pie: array<int, string|int>|null}
     */
    public function tabla(string $reporte): array
    {
        return match ($reporte) {
            'ingresos'   => $this->tablaIngresos(),
            'por-vencer' => $this->tablaPorVencer(),
            'socios'     => $this->tablaSocios(),
        };
    }

    private function tablaIngresos(): array
    {
        $meses = $this->ingresosPorMes();

        return [
            'titulo'   => self::REPORTES['ingresos'],
            'resumen'  => $meses->count() . ' meses con pagos registrados',
            'columnas' => ['Mes', 'Cantidad de pagos', 'Total recaudado (USD)'],
            'filas'    => $meses->map(fn ($mes) => [
                $mes->etiqueta_mes,
                (int) $mes->cantidad_pagos,
                number_format((float) $mes->total_mes, 2, '.', ''),
            ])->all(),
            'pie'      => ['Total', (int) $meses->sum('cantidad_pagos'), number_format((float) $meses->sum('total_mes'), 2, '.', '')],
        ];
    }

    private function tablaPorVencer(): array
    {
        $socios = $this->sociosPorVencer();

        return [
            'titulo'   => self::REPORTES['por-vencer'],
            'resumen'  => 'Del ' . today()->format('d/m/Y') . ' al ' . today()->addDays(self::DIAS_POR_VENCER)->format('d/m/Y')
                . ': ' . $socios->count() . ' membresías por vencer',
            'columnas' => ['Socio', 'DUI', 'Teléfono', 'Plan', 'Fecha de vencimiento', 'Días restantes'],
            'filas'    => $socios->map(fn ($socio) => [
                "{$socio->apellidos}, {$socio->nombres}",
                $socio->dui,
                $socio->telefono,
                $socio->plan->nombre,
                $socio->fecha_vencimiento->format('d/m/Y'),
                (int) $socio->dias_restantes,
            ])->all(),
            'pie'      => null,
        ];
    }

    private function tablaSocios(): array
    {
        $socios = Socio::with('plan')->orderBy('apellidos')->orderBy('nombres')->get();
        $activos = $socios->filter(fn ($socio) => $socio->estaActiva())->count();

        return [
            'titulo'   => self::REPORTES['socios'],
            'resumen'  => "Activas: {$activos} · Vencidas: " . ($socios->count() - $activos) . " · Total: {$socios->count()}",
            'columnas' => ['Socio', 'DUI', 'Correo', 'Plan', 'Fecha de inscripción', 'Fecha de vencimiento', 'Estado'],
            'filas'    => $socios->map(fn ($socio) => [
                "{$socio->apellidos}, {$socio->nombres}",
                $socio->dui,
                $socio->correo,
                $socio->plan->nombre,
                $socio->fecha_inscripcion->format('d/m/Y'),
                $socio->fecha_vencimiento->format('d/m/Y'),
                $socio->estado_membresia,
            ])->all(),
            'pie'      => null,
        ];
    }
}
