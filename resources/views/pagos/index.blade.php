@extends('layouts.app')

@section('titulo', 'Pagos de membresía')

@section('contenido')
    <div class="cabecera-pagina">
        <div>
            <h1>Pagos de membresía</h1>
            <p class="subtitulo">
                {{ $pagos->total() }} {{ $pagos->total() === 1 ? 'pago registrado' : 'pagos registrados' }} ·
                Total en listado: <strong>${{ number_format((float) $totalFiltrado, 2) }}</strong>
            </p>
        </div>
        <a href="{{ route('pagos.create') }}" class="boton">+ Registrar pago</a>
    </div>

    {{-- [CRUD-READ] Filtros por socio, plan y método de pago --}}
    <form action="{{ route('pagos.index') }}" method="GET" class="tarjeta filtros">
        <div class="filtros__fila">
            <div class="campo">
                <label for="buscar" class="campo__etiqueta">Socio</label>
                <input
                    type="search"
                    id="buscar"
                    name="buscar"
                    value="{{ $buscar }}"
                    placeholder="Nombre, apellido o DUI del socio..."
                    class="campo__control"
                >
            </div>

            <div class="campo">
                <label for="plan_id" class="campo__etiqueta">Plan pagado</label>
                <select id="plan_id" name="plan_id" class="campo__control">
                    <option value="">Todos los planes</option>
                    @foreach ($planes as $plan)
                        <option value="{{ $plan->id }}" @selected((string) $planId === (string) $plan->id)>
                            {{ $plan->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="campo">
                <label for="metodo" class="campo__etiqueta">Método de pago</label>
                <select id="metodo" name="metodo" class="campo__control">
                    <option value="">Todos los métodos</option>
                    @foreach ($metodos as $m)
                        <option value="{{ $m }}" @selected($metodo === $m)>{{ ucfirst($m) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filtros__acciones">
                <button type="submit" class="boton">Filtrar</button>
                @if ($buscar !== '' || $planId || $metodo)
                    <a href="{{ route('pagos.index') }}" class="boton boton--secundario">Limpiar</a>
                @endif
            </div>
        </div>
    </form>

    @if ($pagos->isEmpty())
        <div class="vacio">
            <p>No se encontraron pagos registrados con los filtros seleccionados.</p>
            <a href="{{ route('pagos.create') }}" class="boton boton--chico">+ Registrar nuevo pago</a>
        </div>
    @else
        <div class="tarjeta tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Socio</th>
                        <th>Plan</th>
                        <th>Método</th>
                        <th>Período cubierto</th>
                        <th class="derecha">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pagos as $pago)
                        <tr>
                            <td data-label="Fecha">{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                            <td data-label="Socio">
                                <a href="{{ route('socios.show', $pago->socio) }}">
                                    <strong>{{ $pago->socio->apellidos }}, {{ $pago->socio->nombres }}</strong>
                                </a>
                                <span class="texto-suave mono">{{ $pago->socio->dui }}</span>
                            </td>
                            <td data-label="Plan">{{ $pago->plan->nombre }}</td>
                            <td data-label="Método">{{ ucfirst($pago->metodo) }}</td>
                            <td data-label="Período">{{ $pago->periodo_inicio->format('d/m/Y') }} – {{ $pago->periodo_fin->format('d/m/Y') }}</td>
                            <td data-label="Monto" class="derecha"><strong>${{ number_format($pago->monto, 2) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $pagos->links('partials.paginacion') }}
    @endif
@endsection
