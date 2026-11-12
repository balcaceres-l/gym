@extends('layouts.app')

@section('titulo', $socio->nombre_completo)

@section('contenido')
    <a href="{{ route('socios.index') }}" class="volver">‹ Volver al listado</a>

    <div class="ficha">
        <aside class="tarjeta ficha__lateral">
            <img src="{{ $socio->imagen_url }}" alt="Foto de {{ $socio->nombre_completo }}" class="ficha__foto" width="220" height="220">
            <h1 class="ficha__nombre">{{ $socio->nombre_completo }}</h1>
            @include('partials.estado', ['socio' => $socio])
            <p class="texto-suave">
                @if ($socio->estaActiva())
                    Vence el {{ $socio->fecha_vencimiento->translatedFormat('d \d\e F \d\e Y') }}
                @else
                    Venció el {{ $socio->fecha_vencimiento->translatedFormat('d \d\e F \d\e Y') }}
                @endif
            </p>

            <div class="acciones-ficha">
                @if (Route::has('pagos.create'))
                    <a href="{{ route('pagos.create', ['socio_id' => $socio->id]) }}" class="boton boton--chico">+ Registrar pago</a>
                @endif
                <a href="{{ route('socios.edit', $socio) }}" class="boton boton--secundario boton--chico">Editar</a>

                {{-- [CRUD-DELETE] [SEGURIDAD] Eliminación con confirmación y directiva @method('DELETE') --}}
                <form
                    action="{{ route('socios.destroy', $socio) }}"
                    method="POST"
                    class="form-en-linea"
                    onsubmit="return confirm('¿Confirma que desea eliminar al socio {{ addslashes($socio->nombre_completo) }} y su historial de pagos?');"
                >
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="boton boton--peligro boton--chico">Eliminar</button>
                </form>
            </div>
        </aside>

        <section class="tarjeta ficha__datos">
            <h2>Datos del socio</h2>
            <dl class="datos">
                <div><dt>DUI</dt><dd class="mono">{{ $socio->dui }}</dd></div>
                <div><dt>Nombres</dt><dd>{{ $socio->nombres }}</dd></div>
                <div><dt>Apellidos</dt><dd>{{ $socio->apellidos }}</dd></div>
                <div><dt>Fecha de nacimiento</dt><dd>{{ $socio->fecha_nacimiento->format('d/m/Y') }} ({{ $socio->edad }} años)</dd></div>
                <div><dt>Teléfono</dt><dd>{{ $socio->telefono }}</dd></div>
                <div><dt>Correo</dt><dd>{{ $socio->correo }}</dd></div>
                <div><dt>Plan</dt><dd>{{ $socio->plan->nombre }} · ${{ number_format($socio->plan->precio, 2) }} / {{ $socio->plan->duracion_dias }} días</dd></div>
                <div><dt>Fecha de inscripción</dt><dd>{{ $socio->fecha_inscripcion->format('d/m/Y') }}</dd></div>
                <div><dt>Fecha de vencimiento</dt><dd>{{ $socio->fecha_vencimiento->format('d/m/Y') }}</dd></div>
            </dl>
        </section>
    </div>

    {{-- Registros relacionados: historial de pagos del socio --}}
    <section class="tarjeta">
        <div class="cabecera-seccion">
            <div>
                <h2>Historial de pagos</h2>
                <span class="texto-suave">
                    {{ $socio->pagos->count() }} {{ $socio->pagos->count() === 1 ? 'pago' : 'pagos' }} · Total ${{ number_format($socio->pagos->sum('monto'), 2) }}
                </span>
            </div>
            @if (Route::has('pagos.create'))
                <a href="{{ route('pagos.create', ['socio_id' => $socio->id]) }}" class="boton boton--chico">+ Registrar pago / Renovar</a>
            @endif
        </div>

        @if ($socio->pagos->isEmpty())
            <p class="vacio">Este socio todavía no tiene pagos registrados.</p>
        @else
            <div class="tabla-contenedor">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th>Fecha de pago</th>
                            <th>Plan</th>
                            <th>Método</th>
                            <th>Período cubierto</th>
                            <th class="derecha">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($socio->pagos as $pago)
                            <tr>
                                <td data-label="Fecha de pago">{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                                <td data-label="Plan">{{ $pago->plan->nombre }}</td>
                                <td data-label="Método">{{ ucfirst($pago->metodo) }}</td>
                                <td data-label="Período">{{ $pago->periodo_inicio->format('d/m/Y') }} – {{ $pago->periodo_fin->format('d/m/Y') }}</td>
                                <td data-label="Monto" class="derecha">${{ number_format($pago->monto, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
