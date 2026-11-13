<?php

namespace App\Http\Requests;

use App\Models\Pago;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * [VALIDACION] Validación en el servidor para el registro de un nuevo socio.
 * [REGLA-NEGOCIO] Aplica formato de DUI 00000000-0 único, correo único,
 * edad mínima de 15 años e imagen de hasta 2 MB.
 */
class StoreSocioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // [REGLA-NEGOCIO] El socio debe tener al menos 15 años cumplidos.
        $fechaMaximaNacimiento = today()->subYears(15)->toDateString();

        return [
            'plan_id'             => ['required', 'integer', 'exists:planes,id'],
            // [REGLA-NEGOCIO] DUI con formato 00000000-0 y único.
            'dui'                 => ['required', 'string', 'regex:/^\d{8}-\d$/', 'unique:socios,dui'],
            'nombres'             => ['required', 'string', 'min:2', 'max:60'],
            'apellidos'           => ['required', 'string', 'min:2', 'max:60'],
            // [REGLA-NEGOCIO] El socio debe tener al menos 15 años.
            'fecha_nacimiento'    => ['required', 'date', 'before_or_equal:' . $fechaMaximaNacimiento, 'after:1920-01-01'],
            'telefono'            => ['required', 'string', 'regex:/^\d{4}-\d{4}$/'],
            // [REGLA-NEGOCIO] Correo electrónico válido y único.
            'correo'              => ['required', 'string', 'email', 'max:100', 'unique:socios,correo'],
            'fecha_inscripcion'   => ['required', 'date', 'before_or_equal:today'],
            'metodo_pago_inicial' => ['nullable', Rule::in(Pago::METODOS)],
            // [VALIDACION] Imagen opcional, formato permitido y tamaño máximo de 2 MB (2048 KB).
            'imagen'              => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
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
            'dui.unique'                        => 'Ya existe un socio registrado con este número de DUI.',
            'nombres.required'                  => 'Ingrese los nombres del socio.',
            'nombres.min'                       => 'Los nombres deben tener al menos 2 caracteres.',
            'nombres.max'                       => 'Los nombres no pueden exceder 60 caracteres.',
            'apellidos.required'                => 'Ingrese los apellidos del socio.',
            'apellidos.min'                     => 'Los apellidos deben tener al menos 2 caracteres.',
            'apellidos.max'                     => 'Los apellidos no pueden exceder 60 caracteres.',
            'fecha_nacimiento.required'         => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.date'             => 'Ingrese una fecha de nacimiento válida.',
            'fecha_nacimiento.before_or_equal'  => 'El socio debe tener al menos 15 años de edad para inscribirse.',
            'fecha_nacimiento.after'            => 'Ingrese una fecha de nacimiento válida.',
            'telefono.required'                 => 'El número de teléfono es obligatorio.',
            'telefono.regex'                    => 'El teléfono debe tener el formato 0000-0000.',
            'correo.required'                   => 'El correo electrónico es obligatorio.',
            'correo.email'                      => 'Ingrese una dirección de correo electrónico válida.',
            'correo.max'                        => 'El correo electrónico no puede superar los 100 caracteres.',
            'correo.unique'                     => 'Ya existe un socio registrado con este correo electrónico.',
            'fecha_inscripcion.required'        => 'La fecha de inscripción es obligatoria.',
            'fecha_inscripcion.date'            => 'Ingrese una fecha de inscripción válida.',
            'fecha_inscripcion.before_or_equal' => 'La fecha de inscripción no puede ser posterior a hoy.',
            'metodo_pago_inicial.in'            => 'Seleccione un método de pago válido (efectivo, tarjeta o transferencia).',
            'imagen.image'                      => 'El archivo subido debe ser una imagen válida.',
            'imagen.mimes'                      => 'La fotografía debe ser de tipo JPG, JPEG, PNG o WEBP.',
            'imagen.max'                        => 'La fotografía no debe pesar más de 2 MB (2048 KB).',
            // PHP rechaza el archivo antes de aplicar la regla "max" cuando supera upload_max_filesize (2 MB).
            'imagen.uploaded'                   => 'La fotografía no pudo subirse: supera el tamaño máximo permitido de 2 MB (2048 KB).',
        ];
    }
}
