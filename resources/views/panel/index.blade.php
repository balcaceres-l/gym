@extends('layouts.app')

@section('titulo', 'Panel')

@section('contenido')
    <div class="cabecera-pagina">
        <div>
            <h1>Panel</h1>
            <p class="subtitulo">Resumen general del gimnasio al {{ today()->format('d/m/Y') }}</p>
        </div>
    </div>

    <div class="indicadores">
        <div class="tarjeta indicador">
            <span class="indicador__etiqueta">Socios registrados</span>
            <span class="indicador__valor">{{ $indicadores['socios'] }}</span>
        </div>
        <div class="tarjeta indicador indicador--activa">
            <span class="indicador__etiqueta">Membresías activas</span>
            <span class="indicador__valor">{{ $indicadores['activos'] }}</span>
        </div>
        <div class="tarjeta indicador indicador--vencida">
            <span class="indicador__etiqueta">Membresías vencidas</span>
            <span class="indicador__valor">{{ $indicadores['vencidos'] }}</span>
        </div>
        <div class="tarjeta indicador">
            <span class="indicador__etiqueta">Pagos registrados</span>
            <span class="indicador__valor">{{ $indicadores['pagos'] }}</span>
        </div>
    </div>

    <div class="rejilla-2">
        <section class="tarjeta">
            <h2>Socios por plan</h2>
            <ul class="barras">
                @php $maximo = max($planes->max('socios_count'), 1); @endphp
                @foreach ($planes as $plan)
                    <li class="barras__fila">
                        <span class="barras__nombre">{{ $plan->nombre }}</span>
                        <span class="barras__pista"><span class="barras__relleno" style="width: {{ round($plan->socios_count / $maximo * 100) }}%"></span></span>
                        <span class="barras__valor">{{ $plan->socios_count }}</span>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="tarjeta">
            <h2>Módulos</h2>
            <div class="modulos">
                <a href="{{ route('socios.index') }}" class="modulo">
                    <strong>Socios</strong>
                    <span class="texto-suave">Listado y ficha de cada socio con su historial de pagos.</span>
                </a>
                <a href="{{ route('planes.index') }}" class="modulo">
                    <strong>Planes</strong>
                    <span class="texto-suave">Catálogo de planes con precio y duración.</span>
                </a>
            </div>
        </section>
    </div>
@endsection
