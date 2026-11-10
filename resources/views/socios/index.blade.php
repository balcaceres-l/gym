@extends('layouts.app')

@section('titulo', 'Socios')

@section('contenido')
    <div class="cabecera-pagina">
        <div>
            <h1>Socios</h1>
            <p class="subtitulo">{{ $socios->total() }} socios registrados</p>
        </div>
    </div>

    @if ($socios->isEmpty())
        <div class="vacio">
            <p>Todavía no hay socios registrados.</p>
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
                                <a href="{{ route('socios.show', $socio) }}" class="boton boton--secundario boton--chico">Ver</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $socios->links('partials.paginacion') }}
    @endif
@endsection
