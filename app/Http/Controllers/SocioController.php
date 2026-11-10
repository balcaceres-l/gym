<?php

namespace App\Http\Controllers;

use App\Models\Socio;
use Illuminate\View\View;

/**
 * Controlador de recurso de la entidad principal (Socio).
 *
 * Fase 1: index y show.
 * Fase 2: create, store, edit, update y destroy.
 */
class SocioController extends Controller
{
    /**
     * [CRUD-READ] Listado paginado de socios.
     * with('plan') hace eager loading para evitar una consulta por cada fila (problema N+1).
     */
    public function index(): View
    {
        $socios = Socio::with('plan')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->paginate(10);

        return view('socios.index', compact('socios'));
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
            'pagos' => fn ($q) => $q->with('plan')->latest('fecha_pago'),
        ]);

        return view('socios.show', compact('socio'));
    }
}
