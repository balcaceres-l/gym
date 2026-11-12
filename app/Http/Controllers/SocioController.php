<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSocioRequest;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\Socio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
     * [CRUD-CREATE] Muestra el formulario para registrar un nuevo socio.
     */
    public function create(): View
    {
        $socio = new Socio([
            'fecha_inscripcion' => today(),
        ]);
        $planes = Plan::orderBy('duracion_dias')->get();
        $metodos = Pago::METODOS;

        return view('socios.create', compact('socio', 'planes', 'metodos'));
    }

    /**
     * [CRUD-CREATE] Almacena un nuevo socio, su fotografía y su pago inicial de membresía.
     * [VALIDACION] Validado mediante StoreSocioRequest.
     * [REGLA-NEGOCIO] Calcula la fecha de vencimiento en el servidor según la duración del plan.
     * [ALMACENAMIENTO] Guarda la imagen subida en el disco público de Laravel Storage.
     */
    public function store(StoreSocioRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $plan = Plan::findOrFail($datos['plan_id']);

        $fechaInscripcion = Carbon::parse($datos['fecha_inscripcion'])->startOfDay();
        // [REGLA-NEGOCIO] La fecha de vencimiento inicial se calcula en el servidor según la duración del plan.
        $fechaVencimiento = $fechaInscripcion->copy()->addDays($plan->duracion_dias);

        // [ALMACENAMIENTO] Si se cargó una fotografía, se guarda en storage/app/public/socios.
        $rutaImagen = null;
        if ($request->hasFile('imagen')) {
            $rutaImagen = $request->file('imagen')->store('socios', 'public');
        }

        $metodoInicial = $datos['metodo_pago_inicial'] ?? 'efectivo';

        $socio = DB::transaction(function () use ($datos, $plan, $fechaInscripcion, $fechaVencimiento, $rutaImagen, $metodoInicial) {
            $nuevoSocio = Socio::create([
                'plan_id'           => $plan->id,
                'dui'               => $datos['dui'],
                'nombres'           => $datos['nombres'],
                'apellidos'         => $datos['apellidos'],
                'fecha_nacimiento'  => $datos['fecha_nacimiento'],
                'telefono'          => $datos['telefono'],
                'correo'            => $datos['correo'],
                'fecha_inscripcion' => $fechaInscripcion->toDateString(),
                'fecha_vencimiento' => $fechaVencimiento->toDateString(),
                'imagen'            => $rutaImagen,
            ]);

            // [REGLA-NEGOCIO] Se registra el pago inicial con el precio exacto del plan contratado.
            Pago::create([
                'socio_id'       => $nuevoSocio->id,
                'plan_id'        => $plan->id,
                'fecha_pago'     => $fechaInscripcion->toDateString(),
                'monto'          => $plan->precio,
                'metodo'         => $metodoInicial,
                'periodo_inicio' => $fechaInscripcion->toDateString(),
                'periodo_fin'    => $fechaVencimiento->toDateString(),
            ]);

            return $nuevoSocio;
        });

        return redirect()
            ->route('socios.show', $socio)
            ->with('exito', "El socio {$socio->nombre_completo} fue registrado exitosamente con el plan {$plan->nombre}.");
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
