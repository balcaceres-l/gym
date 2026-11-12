@extends('layouts.app')

@section('titulo', 'Socios')

@section('contenido')
    <div class="cabecera-pagina">
        <div>
            <h1>Socios</h1>
            <p class="subtitulo">{{ $socios->total() }} {{ $socios->total() === 1 ? 'socio encontrado' : 'socios encontrados' }}</p>
        </div>
        <div class="acciones-linea">
            @if (Route::has('pagos.create'))
                <a href="{{ route('pagos.create') }}" class="boton boton--secundario">+ Registrar pago</a>
            @endif
            <a href="{{ route('socios.create') }}" class="boton">+ Nuevo socio</a>
        </div>
    </div>

    {{-- [CRUD-READ] Barra de búsqueda y filtrado por plan y estado de membresía --}}
    <form action="{{ route('socios.index') }}" method="GET" class="tarjeta filtros">
        <div class="filtros__fila">
            <div class="campo">
                <label for="buscar" class="campo__etiqueta">Buscar socio</label>
                <input
                    type="search"
                    id="buscar"
                    name="buscar"
                    value="{{ $buscar ?? request('buscar') }}"
                    placeholder="Nombre, apellido, DUI o correo..."
                    class="campo__control"
                >
            </div>

            <div class="campo">
                <label for="plan_id" class="campo__etiqueta">Plan</label>
                <select id="plan_id" name="plan_id" class="campo__control">
                    <option value="">Todos los planes</option>
                    @isset($planes)
                        @foreach ($planes as $plan)
                            <option value="{{ $plan->id }}" @selected((string) ($planId ?? request('plan_id')) === (string) $plan->id)>
                                {{ $plan->nombre }}
                            </option>
                        @endforeach
                    @endisset
                </select>
            </div>

            <div class="campo">
                <label for="estado" class="campo__etiqueta">Estado de membresía</label>
                <select id="estado" name="estado" class="campo__control">
                    <option value="">Todos los estados</option>
                    <option value="activa" @selected(($estado ?? request('estado')) === 'activa')>Activas</option>
                    <option value="por_vencer" @selected(($estado ?? request('estado')) === 'por_vencer')>Por vencer (7 días)</option>
                    <option value="vencida" @selected(($estado ?? request('estado')) === 'vencida')>Vencidas</option>
                </select>
            </div>

            <div class="filtros__acciones">
                <button type="submit" class="boton">Filtrar</button>
                @if (request()->filled('buscar') || request()->filled('plan_id') || request()->filled('estado'))
                    <a href="{{ route('socios.index') }}" class="boton boton--secundario">Limpiar</a>
                @endif
            </div>
        </div>
    </form>

    @if ($socios->isEmpty())
        <div class="vacio">
            <p>No se encontraron socios con los criterios seleccionados.</p>
            <a href="{{ route('socios.create') }}" class="boton boton--chico">+ Registrar nuevo socio</a>
        </div>
    @else
        <div class="tarjeta tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Socio</th>
                        <th>DUI</th>
                        <th>Plan</th>
                        <th>Vence</th>
                        <th>Estado</th>
                        <th class="derecha">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($socios as $socio)
                        <tr>
                            <td data-label="Foto">
                                <img src="{{ $socio->imagen_url }}" alt="Foto de {{ $socio->nombre_completo }}" class="miniatura" width="48" height="48" loading="lazy">
                            </td>
                            <td data-label="Socio">
                                <strong>{{ $socio->apellidos }}, {{ $socio->nombres }}</strong>
                                <span class="texto-suave">{{ $socio->correo }}</span>
                            </td>
                            <td data-label="DUI" class="mono">{{ $socio->dui }}</td>
                            <td data-label="Plan">{{ $socio->plan->nombre }}</td>
                            <td data-label="Vence">{{ $socio->fecha_vencimiento->format('d/m/Y') }}</td>
                            <td data-label="Estado">@include('partials.estado', ['socio' => $socio])</td>
                            <td class="derecha acciones">
                                <div class="acciones-linea">
                                    <a href="{{ route('socios.show', $socio) }}" class="boton boton--secundario boton--chico">Ver</a>
                                    <a href="{{ route('socios.edit', $socio) }}" class="boton boton--secundario boton--chico">Editar</a>

                                    {{-- [CRUD-DELETE] [SEGURIDAD] Eliminación con confirmación y directiva @method('DELETE') --}}
                                    <form
                                        action="{{ route('socios.destroy', $socio) }}"
                                        method="POST"
                                        class="form-en-linea"
                                        onsubmit="return confirm('¿Está seguro de que desea eliminar al socio {{ addslashes($socio->nombre_completo) }} y todo su historial de pagos?');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="boton boton--peligro boton--chico">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $socios->links('partials.paginacion') }}
    @endif
@endsection
