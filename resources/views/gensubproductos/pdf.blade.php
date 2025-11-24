<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Gestión de Residuos - Reporte</title>

    <style>
        /* --- ESTILOS DEL TEMPLATE --- */
        @page {
            margin: 1cm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            box-sizing: border-box;
            max-width: 100%;
        }

        /* Cabecera idéntica al template */
        .header {
            background-color: #611232;
            color: white;
            padding: 15pt;
            margin: -1cm -1cm 10pt -1cm;
            text-align: center;
        }

        .title {
            font-size: 16pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            color: white;
        }

        .section {
            margin-bottom: 20pt;
        }

        .section-title {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 10pt;
            color: black;
            border-bottom: 1pt solid #611232;
            padding-bottom: 5pt;
        }

        /* Tablas unificadas con el estilo del template */
        .summary-table,
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15pt;
            font-size: 9pt;
            border: 0.5pt solid #ddd;
        }

        .summary-table td,
        .data-table td,
        .data-table th {
            border: 0.5pt solid #ddd;
            padding: 6pt;
            text-align: left;
        }

        /* Filas alternadas (gris claro) */
        .summary-table tr:nth-child(even),
        .data-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .data-label {
            font-weight: bold;
            width: 40%;
            background-color: #f8f9fa;
            /* Un gris muy suave para las etiquetas */
        }

        .highlight {
            font-weight: bold;
            color: #cc0303;
        }

        /* Cabeceras de tabla estilo Institucional */
        .data-table th.main-header {
            background-color: #611232;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
        }

        /* Estilo visual para separar las ZONAS (Similar a .zona-header del template) */
        .zona-block-title {
            background-color: #e9ecef;
            color: #1a202c;
            font-size: 11pt;
            font-weight: bold;
            padding: 8pt;
            border-left: 5pt solid #611232;
            margin-bottom: 10pt;
            margin-top: 15pt;
        }

        /* Pie de página fijo */
        .footer {
            position: fixed;
            bottom: -0.5cm;
            left: 0cm;
            right: 0cm;
            height: 1cm;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #666;
            border-top: 0.5pt solid #611232;
            padding-top: 5pt;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    {{-- PIE DE PÁGINA (Estilo Template) --}}
    <div class="footer">
        <p>Fecha de descarga: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>{{ $instituto->nombre }} - Reporte de Gestión de Residuos</p>
    </div>

    <div class="container">
        {{-- CABECERA (Estilo Template) --}}
        <div class="header">
            <h1 class="title">Gestión de residuos sólidos institucionales</h1>
        </div>

        {{-- SECCIÓN 1: RESUMEN --}}
        <div class="section">
            <h2 class="section-title">Resumen de Datos Generados</h2>
            <table class="summary-table">
                <tr>
                    <td class="data-label">Instituto:</td>
                    <td>{{ $instituto->nombre }}</td>
                </tr>
                <tr>
                    <td class="data-label">Rango de fechas:</td>
                    <td>{{ $inicio->format('d/m/Y') }} al {{ $final->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="data-label">Total generado de subproductos:</td>
                    <td class="highlight">
                        @php
                            $totalGenerado = $datosAgrupados->flatten(2)->sum('valor_kg');
                        @endphp
                        {{ number_format($totalGenerado, 2) }} kg
                    </td>
                </tr>
                <tr>
                    <td class="data-label">Subproducto con mayor generación:</td>
                    <td class="highlight">
                        @php
                            $subproductoMayor = $datosAgrupados
                                ->flatten(2)
                                ->groupBy('subproducto_nombre')
                                ->map(function ($rows, $nombre) {
                                    return [
                                        'nombre' => $nombre,
                                        'total' => $rows->sum('total_kg'),
                                    ];
                                })
                                ->sortByDesc('total')
                                ->first();
                        @endphp
                        {{ $subproductoMayor ? $subproductoMayor['nombre'] : 'No disponible' }}
                    </td>
                </tr>
            </table>
        </div>

        {{-- Salto de página antes del desglose --}}
        <div class="page-break"></div>

        {{-- SECCIÓN 2: DESGLOSE --}}
        <div class="section">
            <h2 class="section-title">Desglose de Datos por Zona y Subproducto</h2>

            @foreach ($datosAgrupados as $zonaNombre => $subproductos)
                {{-- Título de ZONA --}}
                <div class="zona-block-title">
                    Zona: {{ $zonaNombre }}
                </div>

                {{-- Bucle de Subproductos de esa zona --}}
                @foreach ($subproductos as $subproductoNombre => $datos)
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th colspan="7" class="main-header">{{ $subproductoNombre }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $fechaChunks = array_chunk($datos->pluck('fecha')->toArray(), 6);
                                $cantidadChunks = array_chunk($datos->pluck('valor_kg')->toArray(), 6);
                            @endphp

                            @foreach ($fechaChunks as $index => $fechaChunk)
                                <tr>
                                    <td class="data-label" style="width: 15%;">Fecha:</td>
                                    @foreach ($fechaChunk as $fecha)
                                        <td style="text-align: center;">
                                            {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
                                        </td>
                                    @endforeach
                                    {{-- Rellenar vacíos --}}
                                    @foreach (array_pad($fechaChunk, 6, '') as $fecha)
                                        @if ($fecha === '')
                                            <td>&nbsp;</td>
                                        @endif
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="data-label">Cantidad:</td>
                                    @foreach ($cantidadChunks[$index] as $cantidad)
                                        <td style="text-align: center;">{{ $cantidad }}</td>
                                    @endforeach
                                    {{-- Rellenar vacíos --}}
                                    @foreach (array_pad($cantidadChunks[$index], 6, '') as $cantidad)
                                        @if ($cantidad === '')
                                            <td>&nbsp;</td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Espacio entre tablas (opcional) --}}
                    <div style="margin-bottom: 15pt;"></div>
                @endforeach

                {{-- LÓGICA DE SALTO DE PÁGINA POR ZONA --}}
                {{-- "Si NO es la última zona, haz un salto de página" --}}
                @if (!$loop->last)
                    <div class="page-break"></div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Script de numeración de páginas (opcional, si el footer fijo no es suficiente) --}}
    <script type="text/php">
        if (isset($pdf)) {
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $size = 9;
            $font = $fontMetrics->getFont("Helvetica");
            $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 20;
            $pdf->page_text($x, $y, $text, $font, $size, [0.4, 0.4, 0.4]);
        }
    </script>
</body>

</html>
