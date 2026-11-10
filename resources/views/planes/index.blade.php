@extends('layouts.app')

@section('titulo', 'Planes')

@section('contenido')
    <div class="cabecera-pagina">
        <div>
            <h1>Planes de membresía</h1>
            <p class="subtitulo">Catálogo de planes disponibles</p>
        </div>
    </div>

    @if ($planes->isEmpty())
        <div class="vacio"><p>No hay planes registrados.</p></div>
    @else
        <div class="planes">
            @foreach ($planes as $plan)
                <article class="tarjeta plan">
                    <h2 class="plan__nombre">{{ $plan->nombre }}</h2>
                    <p class="plan__precio">${{ number_format($plan->precio, 2) }}</p>
                    <p class="texto-suave">{{ $plan->duracion_dias }} días de acceso</p>
                    @if ($plan->descripcion)
                        <p>{{ $plan->descripcion }}</p>
                    @endif
                    <p class="plan__socios">{{ $plan->socios_count }} {{ $plan->socios_count === 1 ? 'socio' : 'socios' }}</p>
                </article>
            @endforeach
        </div>
    @endif
@endsection
