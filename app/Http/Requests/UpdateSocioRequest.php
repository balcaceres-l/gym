<?php

namespace App\Http\Requests;

use App\Models\Socio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * [VALIDACION] Validación en el servidor para la edición de un socio existente.
 * [REGLA-NEGOCIO] Mantiene la unicidad de DUI y correo ignorando el registro actual,
 * y valida la edad mínima de 15 años.
 */
class UpdateSocioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $socio = $this->route('socio');
        $socioId = $socio instanceof Socio ? $socio->id : $socio;

        // [REGLA-NEGOCIO] El socio debe tener al menos 15 años cumplidos.
        $fechaMaximaNacimiento = today()->subYears(15)->toDateString();

        return [
            'plan_id'           => ['required', 'integer', 'exists:planes,id'],
            // [REGLA-NEGOCIO] DUI con formato 00000000-0 y único (ignorando al socio actual).
            'dui'               => ['required', 'string', 'regex:/^\d{8}-\d$/', Rule::unique('socios', 'dui')->ignore($socioId)],
            'nombres'           => ['required', 'string', 'min:2', 'max:60'],
            'apellidos'         => ['required', 'string', 'min:2', 'max:60'],
            // [REGLA-NEGOCIO] El socio debe tener al menos 15 años.
            'fecha_nacimiento'  => ['required', 'date', 'before_or_equal:' . $fechaMaximaNacimiento, 'after:1920-01-01'],
            'telefono'          => ['required', 'string', 'regex:/^\d{4}-\d{4}$/'],
            // [REGLA-NEGOCIO] Correo válido y único (ignorando al socio actual).
            'correo'            => ['required', 'string', 'email', 'max:100', Rule::unique('socios', 'correo')->ignore($socioId)],
            'fecha_inscripcion' => ['required', 'date', 'before_or_equal:today'],
            // [VALIDACION] Fotografía opcional de hasta 2 MB (2048 KB).
            'imagen'            => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    // [VALIDACION] Mensajes de error personalizados en español.
    public function messages(): array
    {
        return [
            'plan_id.required'                  => 'Seleccione un plan de membresía.',
            'plan_id.exists'                    => 'El plan de membresía seleccionado no es válido.',
            'dui.required'                      => 'El número de DUI es obligatorio.',
            'dui.regex'                         => 'El DUI debe tener el formato 00000000-0 (ocho dígitos, guion y un dígito).',
            'dui.unique'                        => 'Otro socio ya tiene registrado este número de DUI.',
            'nombres.required'                  => 'Ingrese los nombres del socio.',
            'nombres.min'                       => 'Los nombres deben tener al menos 2 caracteres.',
            'nombres.max'                       => 'Los nombres no pueden exceder 60 caracteres.',
            'apellidos.required'                => 'Ingrese los apellidos del socio.',
            'apellidos.min'                     => 'Los apellidos deben tener al menos 2 caracteres.',
            'apellidos.max'                     => 'Los apellidos no pueden exceder 60 caracteres.',
            'fecha_nacimiento.required'         => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.date'             => 'Ingrese una fecha de nacimiento válida.',
            'fecha_nacimiento.before_or_equal'  => 'El socio debe tener al menos 15 años de edad.',
            'fecha_nacimiento.after'            => 'Ingrese una fecha de nacimiento válida.',
            'telefono.required'                 => 'El número de teléfono es obligatorio.',
            'telefono.regex'                    => 'El teléfono debe tener el formato 0000-0000.',
            'correo.required'                   => 'El correo electrónico es obligatorio.',
            'correo.email'                      => 'Ingrese una dirección de correo electrónico válida.',
            'correo.max'                        => 'El correo electrónico no puede superar los 100 caracteres.',
            'correo.unique'                     => 'Otro socio ya tiene registrado este correo electrónico.',
            'fecha_inscripcion.required'        => 'La fecha de inscripción es obligatoria.',
            'fecha_inscripcion.date'            => 'Ingrese una fecha de inscripción válida.',
            'fecha_inscripcion.before_or_equal' => 'La fecha de inscripción no puede ser posterior a hoy.',
            'imagen.image'                      => 'El archivo subido debe ser una imagen válida.',
            'imagen.mimes'                      => 'La fotografía debe ser de tipo JPG, JPEG, PNG o WEBP.',
            'imagen.max'                        => 'La fotografía no debe pesar más de 2 MB (2048 KB).',
        ];
    }
}
