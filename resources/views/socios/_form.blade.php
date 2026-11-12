{{-- [VALIDACION] Parcial compartido por create y edit con old() y @error en español --}}
@php
    $esEdicion = $socio->exists;
@endphp

<div class="formulario__rejilla">
    <div class="campo">
        <label for="dui" class="campo__etiqueta">DUI <span class="campo__obligatorio">*</span></label>
        <input
            type="text"
            id="dui"
            name="dui"
            value="{{ old('dui', $socio->dui) }}"
            placeholder="00000000-0"
            maxlength="10"
            @class(['campo__control', 'mono', 'campo__control--error' => $errors->has('dui')])
            required
        >
        <p class="campo__ayuda">Formato obligatorio: 8 dígitos, guion y 1 dígito (ej. 04589123-7).</p>
        @error('dui')
            <p class="campo__error">{{ $message }}</p>
        @enderror
    </div>

    <div class="campo">
        <label for="fecha_nacimiento" class="campo__etiqueta">Fecha de nacimiento <span class="campo__obligatorio">*</span></label>
        <input
            type="date"
            id="fecha_nacimiento"
            name="fecha_nacimiento"
            value="{{ old('fecha_nacimiento', $socio->fecha_nacimiento?->format('Y-m-d')) }}"
            max="{{ today()->subYears(15)->format('Y-m-d') }}"
            @class(['campo__control', 'campo__control--error' => $errors->has('fecha_nacimiento')])
            required
        >
        <p class="campo__ayuda">Regla de negocio: el socio debe tener al menos 15 años cumplidos.</p>
        @error('fecha_nacimiento')
            <p class="campo__error">{{ $message }}</p>
        @enderror
    </div>

    <div class="campo">
        <label for="nombres" class="campo__etiqueta">Nombres <span class="campo__obligatorio">*</span></label>
        <input
            type="text"
            id="nombres"
            name="nombres"
            value="{{ old('nombres', $socio->nombres) }}"
            placeholder="Ej. Carlos Ernesto"
            maxlength="60"
            @class(['campo__control', 'campo__control--error' => $errors->has('nombres')])
            required
        >
        @error('nombres')
            <p class="campo__error">{{ $message }}</p>
        @enderror
    </div>

    <div class="campo">
        <label for="apellidos" class="campo__etiqueta">Apellidos <span class="campo__obligatorio">*</span></label>
        <input
            type="text"
            id="apellidos"
            name="apellidos"
            value="{{ old('apellidos', $socio->apellidos) }}"
            placeholder="Ej. Mendoza Rivera"
            maxlength="60"
            @class(['campo__control', 'campo__control--error' => $errors->has('apellidos')])
            required
        >
        @error('apellidos')
            <p class="campo__error">{{ $message }}</p>
        @enderror
    </div>

    <div class="campo">
        <label for="telefono" class="campo__etiqueta">Teléfono <span class="campo__obligatorio">*</span></label>
        <input
            type="text"
            id="telefono"
            name="telefono"
            value="{{ old('telefono', $socio->telefono) }}"
            placeholder="7000-0000"
            maxlength="9"
            @class(['campo__control', 'mono', 'campo__control--error' => $errors->has('telefono')])
            required
        >
        <p class="campo__ayuda">Formato: 0000-0000</p>
        @error('telefono')
            <p class="campo__error">{{ $message }}</p>
        @enderror
    </div>

    <div class="campo">
        <label for="correo" class="campo__etiqueta">Correo electrónico <span class="campo__obligatorio">*</span></label>
        <input
            type="email"
            id="correo"
            name="correo"
            value="{{ old('correo', $socio->correo) }}"
            placeholder="socio@ejemplo.com"
            maxlength="100"
            @class(['campo__control', 'campo__control--error' => $errors->has('correo')])
            required
        >
        @error('correo')
            <p class="campo__error">{{ $message }}</p>
        @enderror
    </div>

    <div class="campo">
        <label for="plan_id" class="campo__etiqueta">Plan de membresía <span class="campo__obligatorio">*</span></label>
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
                    @selected((int) old('plan_id', $socio->plan_id) === $plan->id)
                >
                    {{ $plan->nombre }} — ${{ number_format($plan->precio, 2) }} ({{ $plan->duracion_dias }} días)
                </option>
            @endforeach
        </select>
        @error('plan_id')
            <p class="campo__error">{{ $message }}</p>
        @enderror
    </div>

    <div class="campo">
        <label for="fecha_inscripcion" class="campo__etiqueta">Fecha de inscripción <span class="campo__obligatorio">*</span></label>
        <input
            type="date"
            id="fecha_inscripcion"
            name="fecha_inscripcion"
            value="{{ old('fecha_inscripcion', $socio->fecha_inscripcion?->format('Y-m-d') ?? today()->format('Y-m-d')) }}"
            max="{{ today()->format('Y-m-d') }}"
            @class(['campo__control', 'campo__control--error' => $errors->has('fecha_inscripcion')])
            required
        >
        <p class="campo__ayuda">
            @if ($esEdicion)
                La fecha de vencimiento actual ({{ $socio->fecha_vencimiento->format('d/m/Y') }}) se extiende al registrar nuevos pagos.
            @else
                La fecha de vencimiento inicial se calculará automáticamente según la duración del plan.
            @endif
        </p>
        @error('fecha_inscripcion')
            <p class="campo__error">{{ $message }}</p>
        @enderror
    </div>

    @if (! $esEdicion && isset($metodos))
        <div class="campo">
            <label for="metodo_pago_inicial" class="campo__etiqueta">Método del pago inicial</label>
            <select
                id="metodo_pago_inicial"
                name="metodo_pago_inicial"
                @class(['campo__control', 'campo__control--error' => $errors->has('metodo_pago_inicial')])
            >
                @foreach ($metodos as $metodo)
                    <option value="{{ $metodo }}" @selected(old('metodo_pago_inicial', 'efectivo') === $metodo)>
                        {{ ucfirst($metodo) }}
                    </option>
                @endforeach
            </select>
            <p class="campo__ayuda">Al inscribir al socio se registra automáticamente su primer período pagado.</p>
            @error('metodo_pago_inicial')
                <p class="campo__error">{{ $message }}</p>
            @enderror
        </div>
    @endif

    <div class="campo campo--completo">
        <label for="imagen" class="campo__etiqueta">Fotografía del socio (opcional)</label>

        @if ($esEdicion && $socio->imagen)
            <div class="foto-actual">
                <img src="{{ $socio->imagen_url }}" alt="Foto actual de {{ $socio->nombre_completo }}" class="foto-actual__img">
                <div>
                    <strong>Fotografía actual registrada</strong>
                    <span class="texto-suave">Si selecciona una nueva imagen, la fotografía anterior será eliminada del servidor.</span>
                </div>
            </div>
        @endif

        <input
            type="file"
            id="imagen"
            name="imagen"
            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
            @class(['campo__control', 'campo__control--error' => $errors->has('imagen')])
        >
        <p class="campo__ayuda">Formatos permitidos: JPG, JPEG, PNG o WEBP. Tamaño máximo: 2 MB. Si no sube una imagen se mostrará el avatar por defecto.</p>
        @error('imagen')
            <p class="campo__error">{{ $message }}</p>
        @enderror
    </div>
</div>
