<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $tabla['titulo'] }}</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1f2937; }
        .encabezado { border-bottom: 3px solid #0f766e; padding-bottom: 8px; margin-bottom: 14px; }
        .marca { color: #0f766e; font-size: 11px; font-weight: bold; letter-spacing: 1px; }
        h1 { margin: 4px 0 2px; font-size: 17px; }
        .resumen { color: #4b5563; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #0f766e; color: #ffffff; text-align: left; padding: 6px 7px; font-size: 9px; }
        td { padding: 5px 7px; border-bottom: 1px solid #e5e7eb; }
        tr.par td { background: #f3f4f6; }
        tr.pie td { border-top: 2px solid #0f766e; border-bottom: none; font-weight: bold; background: #ecfdf5; }
        .derecha { text-align: right; }
        .vacio { text-align: center; color: #6b7280; padding: 18px; }
        .pie-pagina { margin-top: 14px; color: #6b7280; font-size: 8px; }
    </style>
</head>
<body>
    <div class="encabezado">
        <div class="marca">GYMCONTROL · Gimnasio y membresías</div>
        <h1>{{ $tabla['titulo'] }}</h1>
        <div class="resumen">{{ $tabla['resumen'] }}</div>
    </div>

    <table>
        <thead>
            <tr>
                @foreach ($tabla['columnas'] as $columna)
                    <th>{{ $columna }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($tabla['filas'] as $fila)
                <tr @class(['par' => $loop->even])>
                    @foreach ($fila as $celda)
                        <td @class(['derecha' => is_int($celda) || is_numeric($celda)])>{{ $celda }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td class="vacio" colspan="{{ count($tabla['columnas']) }}">No hay registros para este reporte.</td></tr>
            @endforelse

            @if ($tabla['pie'] !== null)
                <tr class="pie">
                    @foreach ($tabla['pie'] as $celda)
                        <td @class(['derecha' => is_int($celda) || is_numeric($celda)])>{{ $celda }}</td>
                    @endforeach
                </tr>
            @endif
        </tbody>
    </table>

    <div class="pie-pagina">Generado el {{ $generado->format('d/m/Y H:i') }} · GymControl</div>
</body>
</html>
