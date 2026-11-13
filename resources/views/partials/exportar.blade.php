{{-- Botones de exportación de un reporte del panel. Uso: @include('partials.exportar', ['reporte' => 'ingresos']) --}}
<span class="acciones-linea" role="group" aria-label="Exportar reporte">
    <a href="{{ route('reportes.exportar', ['reporte' => $reporte, 'formato' => 'csv']) }}" class="boton boton--secundario boton--chico">CSV</a>
    <a href="{{ route('reportes.exportar', ['reporte' => $reporte, 'formato' => 'pdf']) }}" class="boton boton--secundario boton--chico">PDF</a>
</span>
