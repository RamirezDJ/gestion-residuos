<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Gestión de Residuos - Reporte</title>

    <style>
        /* --- ESTILOS DEL TEMPLATE (Sin modificar) --- */
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

        .zero-value {
            color: #999;
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
                            // CORRECCIÓN: Usamos la colección de datos crudos para la suma
                            $totalGenerado = $datosRegistrados->sum('valor_kg');
                        @endphp
                        {{ number_format($totalGenerado, 2) }} kg
                    </td>
                </tr>
                <tr>
                    <td class="data-label">Subproducto con mayor generación:</td>
                    <td class="highlight">
                        @php
                            // CORRECCIÓN: Agrupamos los datos crudos por subproducto para encontrar el mayor.
                            $subproductoMayor = $datosRegistrados
                                ->groupBy('subproducto_id')
                                ->map(function ($rows) {
                                    return [
                                        'nombre' => $rows->first()->subproducto->nombre ?? 'N/A',
                                        'total' => $rows->sum('valor_kg'), // Suma del valor_kg
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

        {{-- SECCIÓN 2: DESGLOSE (Lógica Modificada para incluir vacíos y ceros) --}}
        <div class="section">
            <h2 class="section-title">Desglose de Datos por Zona y Subproducto</h2>

            @php
                // --- PREPARACIÓN DE DATOS ÚNICA PARA EL RANGO COMPLETO ---
                // Generar la lista completa de fechas en el rango
                $fechaIterador = \Carbon\Carbon::parse($inicio);
                $fechaFin = \Carbon\Carbon::parse($final);
                $rangoFechas = [];
                while ($fechaIterador->lte($fechaFin)) {
                    $rangoFechas[] = $fechaIterador->format('Y-m-d');
                    $fechaIterador->addDay();
                }
                // Si el rango es vacío (ej. $inicio > $final), esta lista estará vacía.
            @endphp


            @foreach ($zonas as $zona)
                <div class="zona-block-title">
                    Zona: {{ $zona->nombre }}
                </div>

                @foreach ($subproductos as $subproducto)
                    @php
                        // 1. Filtrar y mapear los datos existentes por fecha para acceso rápido
                        $datosMapeados = $datosRegistrados
                            ->where('zona_id', $zona->id)
                            ->where('subproducto_id', $subproducto->id)
                            ->keyBy(function ($item) {
                                return \Carbon\Carbon::parse($item->fecha)->format('Y-m-d');
                            });

                        // 2. Construir los arrays de fechas y cantidades completos para todo el rango
                        $fechasCompletas = [];
                        $cantidadesCompletas = [];

                        foreach ($rangoFechas as $fechaStr) {
                            // Si no existe un registro para esa fecha, $kilos será 0
                            $data = $datosMapeados->get($fechaStr);
                            $kilos = $data->valor_kg ?? 0;

                            $fechasCompletas[] = $fechaStr;
                            $cantidadesCompletas[] = $kilos;
                        }

                        // 3. Dividir los datos completos en chunks de 7 (para las filas de la tabla)
                        $fechaChunks = array_chunk($fechasCompletas, 7);
                        $cantidadChunks = array_chunk($cantidadesCompletas, 7);

                    @endphp

                    <table class="data-table" style="page-break-inside: auto;">
                        <thead>
                            <tr>
                                <th colspan="8" class="main-header">{{ $subproducto->nombre }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Ya no se necesita el @if ($tieneRegistros), siempre iteramos sobre los chunks --}}
                            @foreach ($fechaChunks as $index => $fechaChunk)
                                <tr>
                                    {{-- Fila de Fechas --}}
                                    <td class="data-label" style="width: 10%;">Fecha:</td>
                                    @foreach ($fechaChunk as $fecha)
                                        <td style="text-align: center;">
                                            {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
                                        </td>
                                    @endforeach
                                    {{-- Rellenar el resto de la fila con espacios si no es múltiplo de 7 --}}
                                    @for ($i = count($fechaChunk); $i < 7; $i++)
                                        <td style="text-align: center;">&nbsp;</td>
                                    @endfor
                                </tr>
                                <tr>
                                    {{-- Fila de Cantidades --}}
                                    <td class="data-label" style="width: 10%;">Cantidad (kg):</td>
                                    @foreach ($cantidadChunks[$index] as $cantidad)
                                        @php
                                            $isZero = $cantidad == 0;
                                        @endphp
                                        <td class="{{ $isZero ? 'zero-value' : '' }}" style="text-align: center;">
                                            {{ number_format($cantidad, 2) }}
                                        </td>
                                    @endforeach
                                    {{-- Rellenar con 0.00 y gris si no es múltiplo de 7 (para mantener el formato de 8 columnas) --}}
                                    @for ($i = count($cantidadChunks[$index]); $i < 7; $i++)
                                        <td class="zero-value" style="text-align: center;">{{ number_format(0, 2) }}
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach

                            {{-- Si el rango de fechas estaba completamente vacío (ej. $inicio > $final), mostramos un mensaje por seguridad --}}
                            @if (empty($fechaChunks))
                                <tr>
                                    <td colspan="8" class="zero-value"
                                        style="text-align: center; font-style: italic;">
                                        Rango de fechas inválido o sin datos en el periodo.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                    <div style="margin-bottom: 15pt;"></div>
                @endforeach

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
