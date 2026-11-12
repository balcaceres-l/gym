<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePagoRequest;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\Socio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Controlador de la entidad relacionada: Pagos de membresía.
 */
class PagoController extends Controller
{
    /**
     * [CRUD-READ] Listado paginado de pagos con filtros por socio, plan y método de pago.
     */
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('buscar', ''));
        $metodo = $request->query('metodo');
        $planId = $request->query('plan_id');

        $consultaBase = Pago::with(['socio', 'plan'])
            ->when($buscar !== '', function ($q) use ($buscar) {
                $q->whereHas('socio', fn ($sub) => $sub->buscar($buscar));
            })
            ->when($metodo, fn ($q) => $q->where('metodo', $metodo))
            ->when($planId, fn ($q) => $q->where('plan_id', $planId));

        $totalFiltrado = (clone $consultaBase)->sum('monto');

        $pagos = $consultaBase
            ->orderByDesc('fecha_pago')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $planes = Plan::orderBy('duracion_dias')->get();
        $metodos = Pago::METODOS;

        return view('pagos.index', compact('pagos', 'planes', 'metodos', 'buscar', 'metodo', 'planId', 'totalFiltrado'));
    }

    /**
     * [CRUD-CREATE] Formulario para registrar un pago de membresía.
     */
    public function create(Request $request): View
    {
        $socios = Socio::with('plan')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        $planes = Plan::orderBy('duracion_dias')->get();
        $metodos = Pago::METODOS;

        $socioSeleccionado = null;
        if ($request->filled('socio_id')) {
            $socioSeleccionado = Socio::with('plan')->find($request->integer('socio_id'));
        }

        return view('pagos.create', compact('socios', 'planes', 'metodos', 'socioSeleccionado'));
    }

    /**
     * [CRUD-CREATE] Registra un nuevo pago de membresía y extiende la fecha de vencimiento del socio.
     * [VALIDACION] Validado mediante StorePagoRequest (verifica que monto == precio del plan).
     * [REGLA-NEGOCIO] La fecha de vencimiento del socio se extiende en el servidor según la duración del plan.
     */
    public function store(StorePagoRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        $socio = Socio::findOrFail($datos['socio_id']);
        $plan = Plan::findOrFail($datos['plan_id']);
        $fechaPago = Carbon::parse($datos['fecha_pago'])->startOfDay();

        // [REGLA-NEGOCIO] Si la membresía del socio está activa al momento del pago,
        // el nuevo período inicia al finalizar su vencimiento actual para no perder días.
        // Si ya estaba vencida, el nuevo período inicia en la fecha de pago.
        if ($socio->fecha_vencimiento && $socio->fecha_vencimiento->startOfDay()->gt($fechaPago)) {
            $periodoInicio = $socio->fecha_vencimiento->copy()->startOfDay();
        } else {
            $periodoInicio = $fechaPago->copy();
        }

        // [REGLA-NEGOCIO] La fecha de vencimiento se extiende según la duración en días del plan.
        $periodoFin = $periodoInicio->copy()->addDays($plan->duracion_dias);

        DB::transaction(function () use ($socio, $plan, $datos, $fechaPago, $periodoInicio, $periodoFin) {
            Pago::create([
                'socio_id'       => $socio->id,
                'plan_id'        => $plan->id,
                'fecha_pago'     => $fechaPago->toDateString(),
                'monto'          => $plan->precio,
                'metodo'         => $datos['metodo'],
                'periodo_inicio' => $periodoInicio->toDateString(),
                'periodo_fin'    => $periodoFin->toDateString(),
            ]);

            $socio->update([
                'plan_id'           => $plan->id,
                'fecha_vencimiento' => $periodoFin->toDateString(),
            ]);
        });

        return redirect()
            ->route('socios.show', $socio)
            ->with(
                'exito',
                "Pago de $" . number_format((float) $plan->precio, 2) . " registrado correctamente. La membresía de {$socio->nombre_completo} se extendió hasta el {$periodoFin->format('d/m/Y')}."
            );
    }
}
