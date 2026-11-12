<?php

namespace App\Http\Requests;

use App\Models\Pago;
use App\Models\Plan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * [VALIDACION] Validación en el servidor para el registro de un pago de membresía.
 * [REGLA-NEGOCIO] Verifica que el monto del pago coincida exactamente con el precio del plan.
 */
class StorePagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'socio_id'   => ['required', 'integer', 'exists:socios,id'],
            'plan_id'    => ['required', 'integer', 'exists:planes,id'],
            'fecha_pago' => ['required', 'date', 'before_or_equal:today'],
            'monto'      => ['required', 'numeric', 'min:0.01'],
            'metodo'     => ['required', Rule::in(Pago::METODOS)],
        ];
    }

    /**
     * [REGLA-NEGOCIO] El monto del pago debe coincidir exactamente con el precio del plan.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->has('plan_id') || $validator->errors()->has('monto')) {
                    return;
                }

                $plan = Plan::find($this->input('plan_id'));
                if (! $plan) {
                    return;
                }

                $montoIngresado = round((float) $this->input('monto'), 2);
                $precioPlan = round((float) $plan->precio, 2);

                if ($montoIngresado !== $precioPlan) {
                    $validator->errors()->add(
                        'monto',
                        sprintf(
                            'El monto del pago ($%s) debe coincidir exactamente con el precio del plan %s ($%s).',
                            number_format($montoIngresado, 2),
                            $plan->nombre,
                            number_format($precioPlan, 2)
                        )
                    );
                }
            },
        ];
    }

    // [VALIDACION] Mensajes de error personalizados en español.
    public function messages(): array
    {
        return [
            'socio_id.required'          => 'Seleccione el socio que realiza el pago.',
            'socio_id.exists'            => 'El socio seleccionado no existe en el sistema.',
            'plan_id.required'           => 'Seleccione el plan de membresía a pagar.',
            'plan_id.exists'             => 'El plan de membresía seleccionado no es válido.',
            'fecha_pago.required'        => 'La fecha de pago es obligatoria.',
            'fecha_pago.date'            => 'Ingrese una fecha de pago válida.',
            'fecha_pago.before_or_equal' => 'La fecha de pago no puede ser posterior a hoy.',
            'monto.required'             => 'El monto del pago es obligatorio.',
            'monto.numeric'              => 'El monto debe ser un valor numérico válido.',
            'monto.min'                  => 'El monto debe ser mayor que cero.',
            'metodo.required'            => 'Seleccione el método de pago.',
            'metodo.in'                  => 'El método de pago debe ser efectivo, tarjeta o transferencia.',
        ];
    }
}
