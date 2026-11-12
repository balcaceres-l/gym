@extends('layouts.app')

@section('titulo', 'Panel y Reportes')

@section('contenido')
    <div class="cabecera-pagina">
        <div>
            <h1>Panel y Reportes</h1>
        </div>
        <div class="acciones-linea">
            <a href="{{ route('pagos.create') }}" class="boton boton--secundario">+ Registrar pago</a>
            <a href="{{ route('socios.create') }}" class="boton">+ Nuevo socio</a>
        </div>
    </div>

    {{-- Indicadores principales con consultas agregadas Eloquent (count, sum) --}}
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
        <div class="tarjeta indicador indicador--alerta">
            <span class="indicador__etiqueta">Ingresos totales ({{ $indicadores['pagos'] }} pagos)</span>
            <span class="indicador__valor">${{ number_format($indicadores['ingresos_totales'], 2) }}</span>
        </div>
    </div>

    {{-- Exportación del estado de las membresías (activas y vencidas) a CSV o PDF --}}
    <div class="cabecera-seccion">
        <span class="texto-suave">Exportar el listado de socios con el estado de su membresía</span>
        @include('partials.exportar', ['reporte' => 'socios'])
    </div>

    {{-- Reporte obligatorio Caso 6: Membresías que vencen en los próximos 7 días --}}
    <section class="tarjeta">
        <div class="cabecera-seccion">
            <div>
                <h2>Membresías que vencen en los próximos 7 días</h2>
                <span class="texto-suave">
                    Socios activos cuya fecha de vencimiento está entre {{ today()->format('d/m/Y') }} y {{ today()->addDays(7)->format('d/m/Y') }}
                </span>
            </div>
            <div class="acciones-linea">
                <span class="insignia insignia--alerta">{{ $sociosPorVencer->count() }} por vencer</span>
                @include('partials.exportar', ['reporte' => 'por-vencer'])
            </div>
        </div>

        @if ($sociosPorVencer->isEmpty())
            <p class="vacio">No hay membresías próximas a vencer en los siguientes 7 días.</p>
        @else
            <div class="tabla-contenedor">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th>Socio</th>
                            <th>DUI</th>
                            <th>Plan actual</th>
                            <th>Fecha de vencimiento</th>
                            <th>Tiempo restante</th>
                            <th class="derecha">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sociosPorVencer as $socio)
                            <tr>
                                <td data-label="Socio">
                                    <strong>{{ $socio->apellidos }}, {{ $socio->nombres }}</strong>
                                    <span class="texto-suave">{{ $socio->telefono }} · {{ $socio->correo }}</span>
                                </td>
                                <td data-label="DUI" class="mono">{{ $socio->dui }}</td>
                                <td data-label="Plan actual">{{ $socio->plan->nombre }}</td>
                                <td data-label="Vencimiento">{{ $socio->fecha_vencimiento->format('d/m/Y') }}</td>
                                <td data-label="Tiempo restante">
                                    <span class="insignia insignia--alerta">
                                        @if ($socio->dias_restantes === 0)
                                            Vence hoy
                                        @elseif ($socio->dias_restantes === 1)
                                            Vence mañana
                                        @else
                                            En {{ $socio->dias_restantes }} días
                                        @endif
                                    </span>
                                </td>
                                <td class="derecha acciones">
                                    <div class="acciones-linea">
                                        <a href="{{ route('socios.show', $socio) }}" class="boton boton--secundario boton--chico">Ficha</a>
                                        <a href="{{ route('pagos.create', ['socio_id' => $socio->id]) }}" class="boton boton--chico">Renovar</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <div class="rejilla-2">
        {{-- Reporte obligatorio Caso 6: Ingresos por mes --}}
        <section class="tarjeta">
            <div class="cabecera-seccion">
                <h2>Ingresos por mes</h2>
                <div class="acciones-linea">
                    <a href="{{ route('pagos.index') }}" class="texto-suave">Ver todos los pagos ›</a>
                    @include('partials.exportar', ['reporte' => 'ingresos'])
                </div>
            </div>

            @if ($ingresosPorMes->isEmpty())
                <p class="vacio">Aún no se registran pagos.</p>
            @else
                <div class="tabla-contenedor">
                    <table class="tabla">
                        <thead>
                            <tr>
                                <th>Mes</th>
                                <th>Pagos</th>
                                <th class="derecha">Total recaudado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ingresosPorMes as $mes)
                                <tr>
                                    <td data-label="Mes"><strong>{{ $mes->etiqueta_mes }}</strong></td>
                                    <td data-label="Pagos">{{ $mes->cantidad_pagos }} {{ $mes->cantidad_pagos == 1 ? 'pago' : 'pagos' }}</td>
                                    <td data-label="Total recaudado" class="derecha"><strong>${{ number_format((float) $mes->total_mes, 2) }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="tarjeta">
            <h2>Distribución de socios por plan</h2>
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

            <h2 style="margin-top: 24px;">Accesos rápidos</h2>
            <div class="modulos">
                <a href="{{ route('socios.index') }}" class="modulo">
                    <strong>Socios</strong>
                    <span class="texto-suave">Gestión CRUD completa de socios, búsqueda, filtros y fotografías.</span>
                </a>
                <a href="{{ route('pagos.index') }}" class="modulo">
                    <strong>Pagos de membresía</strong>
                    <span class="texto-suave">Historial y registro de pagos con extensión automática de vigencia.</span>
                </a>
                <a href="{{ route('planes.index') }}" class="modulo">
                    <strong>Planes</strong>
                    <span class="texto-suave">Catálogo de planes con precios y duración en días.</span>
                </a>
            </div>
        </section>
    </div>
@endsection
