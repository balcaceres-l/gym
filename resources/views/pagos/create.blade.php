@extends('layouts.app')

@section('titulo', 'Registrar pago de membresía')

@section('contenido')
    @if ($socioSeleccionado)
        <a href="{{ route('socios.show', $socioSeleccionado) }}" class="volver">‹ Volver a la ficha de {{ $socioSeleccionado->nombre_completo }}</a>
    @else
        <a href="{{ route('pagos.index') }}" class="volver">‹ Volver al listado de pagos</a>
    @endif

    <div class="cabecera-pagina">
        <div>
            <h1>Registrar pago de membresía</h1>
            <p class="subtitulo">
                Al registrar el pago, la fecha de vencimiento del socio se extenderá automáticamente según la duración del plan.
            </p>
        </div>
    </div>

    @php
        $planPorDefecto = old('plan_id', $socioSeleccionado?->plan_id ?? $planes->first()?->id);
        $objetoPlanDefecto = $planes->firstWhere('id', (int) $planPorDefecto);
        $montoPorDefecto = old('monto', $objetoPlanDefecto ? number_format((float) $objetoPlanDefecto->precio, 2, '.', '') : '');
    @endphp

    <div class="tarjeta">
        {{-- [CRUD-CREATE] [VALIDACION] [SEGURIDAD] Formulario de pago con protección CSRF y conservación old() --}}
        <form action="{{ route('pagos.store') }}" method="POST" novalidate>
            @csrf

            <div class="formulario__rejilla">
                <div class="campo campo--completo">
                    <label for="socio_id" class="campo__etiqueta">Socio <span class="campo__obligatorio">*</span></label>
                    <select
                        id="socio_id"
                        name="socio_id"
                        @class(['campo__control', 'campo__control--error' => $errors->has('socio_id')])
                        required
                    >
                        <option value="">— Seleccione un socio —</option>
                        @foreach ($socios as $socio)
                            <option
                                value="{{ $socio->id }}"
                                data-plan-id="{{ $socio->plan_id }}"
                                @selected((int) old('socio_id', $socioSeleccionado?->id) === $socio->id)
                            >
                                {{ $socio->apellidos }}, {{ $socio->nombres }} · DUI {{ $socio->dui }}
                                (Plan actual: {{ $socio->plan->nombre }} · {{ $socio->estado_membresia }} — vence {{ $socio->fecha_vencimiento->format('d/m/Y') }})
                            </option>
                        @endforeach
                    </select>
                    @error('socio_id')
                        <p class="campo__error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="campo">
                    <label for="plan_id" class="campo__etiqueta">Plan a pagar <span class="campo__obligatorio">*</span></label>
                    <select
                        id="plan_id"
                        name="plan_id"
                        @class(['campo__control', 'campo__control--error' => $errors->has('plan_id')])
                        required
                    >
                        <option value="">— Seleccione un plan —</option>
                        @foreach ($planes as $plan)
                            <option
                                value="{{ $plan->id }}"
                                data-precio="{{ number_format((float) $plan->precio, 2, '.', '') }}"
                                @selected((int) $planPorDefecto === $plan->id)
                            >
                                {{ $plan->nombre }} — ${{ number_format($plan->precio, 2) }} ({{ $plan->duracion_dias }} días)
                            </option>
                        @endforeach
                    </select>
                    <p class="campo__ayuda">El período cubierto sumará los días del plan elegido a la membresía del socio.</p>
                    @error('plan_id')
                        <p class="campo__error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="campo">
                    <label for="monto" class="campo__etiqueta">Monto a pagar ($ USD) <span class="campo__obligatorio">*</span></label>
                    <input
                        type="number"
                        id="monto"
                        name="monto"
                        step="0.01"
                        min="0.01"
                        value="{{ $montoPorDefecto }}"
                        @class(['campo__control', 'mono', 'campo__control--error' => $errors->has('monto')])
                        required
                    >
                    <p class="campo__ayuda">Regla de negocio: el monto debe coincidir exactamente con el precio del plan seleccionado.</p>
                    @error('monto')
                        <p class="campo__error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="campo">
                    <label for="metodo" class="campo__etiqueta">Método de pago <span class="campo__obligatorio">*</span></label>
                    <select
                        id="metodo"
                        name="metodo"
                        @class(['campo__control', 'campo__control--error' => $errors->has('metodo')])
                        required
                    >
                        <option value="">— Seleccione un método —</option>
                        @foreach ($metodos as $m)
                            <option value="{{ $m }}" @selected(old('metodo', 'efectivo') === $m)>
                                {{ ucfirst($m) }}
                            </option>
                        @endforeach
                    </select>
                    @error('metodo')
                        <p class="campo__error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="campo">
                    <label for="fecha_pago" class="campo__etiqueta">Fecha de pago <span class="campo__obligatorio">*</span></label>
                    <input
                        type="date"
                        id="fecha_pago"
                        name="fecha_pago"
                        value="{{ old('fecha_pago', today()->format('Y-m-d')) }}"
                        max="{{ today()->format('Y-m-d') }}"
                        @class(['campo__control', 'campo__control--error' => $errors->has('fecha_pago')])
                        required
                    >
                    @error('fecha_pago')
                        <p class="campo__error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="formulario__acciones">
                <a href="{{ $socioSeleccionado ? route('socios.show', $socioSeleccionado) : route('pagos.index') }}" class="boton boton--secundario">Cancelar</a>
                <button type="submit" class="boton">Registrar pago y extender membresía</button>
            </div>
        </form>
    </div>

    <script>
        // Sincroniza automáticamente el plan y el precio sugerido al cambiar el socio o el plan
        document.addEventListener('DOMContentLoaded', function () {
            const selectSocio = document.getElementById('socio_id');
            const selectPlan = document.getElementById('plan_id');
            const inputMonto = document.getElementById('monto');

            function actualizarPrecioDesdePlan() {
                const opcion = selectPlan.options[selectPlan.selectedIndex];
                if (opcion && opcion.dataset.precio) {
                    inputMonto.value = opcion.dataset.precio;
                }
            }

            if (selectPlan && inputMonto) {
                selectPlan.addEventListener('change', actualizarPrecioDesdePlan);
            }

            if (selectSocio && selectPlan) {
                selectSocio.addEventListener('change', function () {
                    const opcionSocio = selectSocio.options[selectSocio.selectedIndex];
                    if (opcionSocio && opcionSocio.dataset.planId) {
                        selectPlan.value = opcionSocio.dataset.planId;
                        actualizarPrecioDesdePlan();
                    }
                });
            }
        });
    </script>
@endsection
