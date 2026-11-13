<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportarReporteRequest;
use App\Services\ReporteService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Exportación de los reportes del panel a CSV y PDF (puntos extra de la Fase 2).
 */
class ReporteController extends Controller
{
    /**
     * [CRUD-READ] Descarga el reporte pedido en el formato pedido.
     * [INYECCION-DEPENDENCIAS] Laravel inyecta el Form Request (ya validado) y el servicio de consultas.
     * [VALIDACION] Solo llega aquí un reporte y un formato de la lista blanca (ExportarReporteRequest).
     * Es una operación de solo lectura: no modifica datos, por eso se expone con GET.
     */
    public function exportar(ExportarReporteRequest $request, ReporteService $reportes): StreamedResponse|Response
    {
        $datos = $request->validated();
        $tabla = $reportes->tabla($datos['reporte']);
        $nombreArchivo = 'reporte-' . $datos['reporte'] . '-' . today()->format('Y-m-d');

        return $datos['formato'] === 'csv'
            ? $this->descargarCsv($tabla, $nombreArchivo . '.csv')
            : $this->descargarPdf($tabla, $nombreArchivo . '.pdf');
    }

    private function descargarCsv(array $tabla, string $nombreArchivo): StreamedResponse
    {
        return response()->streamDownload(function () use ($tabla) {
            $salida = fopen('php://output', 'w');

            // BOM UTF-8: Excel reconoce las tildes y la ñ al abrir el archivo.
            fwrite($salida, "\xEF\xBB\xBF");

            // El cuarto y quinto parámetro (comillas y escape) se indican para evitar el aviso de PHP 8.4.
            fputcsv($salida, $tabla['columnas'], ',', '"', '');
            foreach ($tabla['filas'] as $fila) {
                fputcsv($salida, array_map([$this, 'celdaSegura'], $fila), ',', '"', '');
            }
            if ($tabla['pie'] !== null) {
                fputcsv($salida, array_map([$this, 'celdaSegura'], $tabla['pie']), ',', '"', '');
            }

            fclose($salida);
        }, $nombreArchivo, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function descargarPdf(array $tabla, string $nombreArchivo): Response
    {
        $opciones = new Options();
        $opciones->set('defaultFont', 'DejaVu Sans');   // incluye tildes y ñ
        $opciones->set('isRemoteEnabled', false);        // el PDF no descarga recursos externos
        $opciones->set('isPhpEnabled', false);

        // La vista imprime todo con {{ }} (escape automático), por lo que los datos no pueden inyectar HTML.
        $html = view('reportes.pdf', ['tabla' => $tabla, 'generado' => now()])->render();

        $pdf = new Dompdf($opciones);
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('A4', count($tabla['columnas']) > 5 ? 'landscape' : 'portrait');
        $pdf->render();

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
        ]);
    }

    /**
     * [SEGURIDAD] Evita la inyección de fórmulas en Excel: un texto que empiece con
     * = + - @ se interpretaría como fórmula, así que se antepone una comilla simple.
     * Los números se dejan tal cual.
     */
    private function celdaSegura(string|int|float $valor): string|int|float
    {
        if (is_string($valor) && preg_match('/^[=+\-@\t\r]/', $valor) === 1) {
            return "'" . $valor;
        }

        return $valor;
    }
}
