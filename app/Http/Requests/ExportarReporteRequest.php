<?php

namespace App\Http\Requests;

use App\Services\ReporteService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * [VALIDACION] [SEGURIDAD] Valida en el servidor qué reporte y en qué formato se exporta.
 * Solo se aceptan los valores de la lista blanca definida en ReporteService; cualquier otro
 * valor se rechaza antes de ejecutar consultas o generar archivos.
 */
class ExportarReporteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reporte' => ['required', 'string', Rule::in(array_keys(ReporteService::REPORTES))],
            'formato' => ['required', 'string', Rule::in(ReporteService::FORMATOS)],
        ];
    }

    // [VALIDACION] Mensajes de error en español.
    public function messages(): array
    {
        return [
            'reporte.required' => 'Indique qué reporte desea exportar.',
            'reporte.string'   => 'El reporte indicado no es válido.',
            'reporte.in'       => 'El reporte solicitado no existe. Opciones: ' . implode(', ', array_keys(ReporteService::REPORTES)) . '.',
            'formato.required' => 'Indique el formato de exportación (CSV o PDF).',
            'formato.string'   => 'El formato indicado no es válido.',
            'formato.in'       => 'El formato solicitado no es válido. Use CSV o PDF.',
        ];
    }

    // Al ser un enlace (GET) y no un formulario, el error se muestra como mensaje flash en el Panel.
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            redirect()->route('panel')->with('error', $validator->errors()->first())
        );
    }
}
