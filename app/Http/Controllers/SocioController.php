<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Socio;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controlador de recurso de la entidad principal (Socio).
 */
class SocioController extends Controller
{
    /**
     * [CRUD-READ] Listado paginado de socios con búsqueda y filtros.
     * with('plan') hace eager loading para evitar una consulta por cada fila (problema N+1).
     */
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('buscar', ''));
        $planId = $request->query('plan_id');
        $estado = $request->query('estado');

        $socios = Socio::with('plan')
            ->buscar($buscar)
            ->when($planId, fn ($q) => $q->where('plan_id', $planId))
            ->when($estado === 'activa', fn ($q) => $q->activos())
            ->when($estado === 'vencida', fn ($q) => $q->vencidos())
            ->when($estado === 'por_vencer', fn ($q) => $q->porVencer(7))
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->paginate(10)
            ->withQueryString();

        $planes = Plan::orderBy('duracion_dias')->get();

        return view('socios.index', compact('socios', 'planes', 'buscar', 'planId', 'estado'));
    }

    /**
     * [CRUD-READ] Ficha de detalle de un socio con su plan y su historial de pagos.
     * [INYECCION-DEPENDENCIAS] Route model binding: Laravel busca el Socio por el {socio}
     * de la URL y responde 404 automáticamente si no existe.
     */
    public function show(Socio $socio): View
    {
        $socio->load([
            'plan',
            'pagos' => fn ($q) => $q->with('plan')->latest('fecha_pago')->latest('id'),
        ]);

        return view('socios.show', compact('socio'));
    }
}
