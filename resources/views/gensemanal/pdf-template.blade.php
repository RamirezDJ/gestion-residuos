<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Gestión de Residuos - Reporte Semanal</title>

    <style>
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
            margin-bottom: 15pt;
        }

        .section-title {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 10pt;
            color: black;
            border-bottom: 1pt solid #611232;
            padding-bottom: 5pt;
        }

        /* Tablas */
        .summary-table,
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15pt;
            font-size: 9pt;
        }

        .summary-table td,
        .data-table td,
        .data-table th {
            border: 0.5pt solid #ddd;
            padding: 6pt;
            text-align: left;
        }

        .summary-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .data-label {
            font-weight: bold;
            width: 40%;
        }

        .highlight {
            font-weight: bold;
            color: #cc0303;
        }

        .data-table th {
            background-color: #611232;
            color: white;
            font-weight: bold;
        }

        .zona-header {
            background-color: #e9ecef;
            font-weight: bold;
            color: #1a202c;
            font-size: 10pt;
        }

        .area-header {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #4a5568;
            padding-left: 15pt;
        }

        .subproducto-row td:first-child {
            padding-left: 30pt;
        }

        .zero-value {
            color: #999;
        }

        /* Pie de página */
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
    <div class="footer">
        <p>Fecha de descarga: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>{{ $instituto->nombre }} - Reporte de Gestión de Residuos</p>
    </div>

    <div class="container">
        <div class="header">
            <h1 class="title">Gestión de residuos sólidos institucionales</h1>
        </div>

        {{-- RESUMEN --}}
        <div class="section">
            <h2 class="section-title">Resumen de Datos Semanales</h2>
            <table class="summary-table">
                <tr>
                    <td class="data-label">Instituto:</td>
                    <td>{{ $instituto->nombre }}</td>
                </tr>
                <tr>
                    <td class="data-label">Semana del:</td>
                    <td>{{ $fechaInicioFormateada }} al {{ $fechaFinFormateada }}</td>
                </tr>
                <tr>
                    <td class="data-label">Total generado en la semana:</td>
                    <td class="highlight">
                        {{ number_format($totalGeneradoSemana, 2) }} kg
                    </td>
                </tr>
                <tr>
                    <td class="data-label">Zona con mayor generación:</td>
                    <td class="highlight">
                        {{ $zonaMayorNombreSemana }}
                        ({{ number_format($zonaMayorTotalSemana, 2) }} kg)
                    </td>
                </tr>
            </table>
        </div>

        <div class="page-break"></div>

        {{-- DESGLOSE DIARIO --}}
        <div class="section">
            <h2 class="section-title">Desglose de Datos por Día y Subproducto</h2>

            @php
                $fechaActual = \Carbon\Carbon::parse($fechaInicioSemana)->startOfDay();
                // Usamos solo el formato de fecha para la comparación del salto de página
                $fechaFinString = \Carbon\Carbon::parse($fechaFinSemana)->format('Y-m-d');
            @endphp

            @while ($fechaActual->lte(\Carbon\Carbon::parse($fechaFinSemana)))
                @php
                    $fechaIter = $fechaActual->format('Y-m-d');
                    // Datos estructurados: [zona_id][area_id][categoria_id] => ['kilos', 'turno']
                    $datosDelDia = $lookupDataSemanal[$fechaIter] ?? [];
                @endphp

                <table class="data-table">
                    <thead>
                        <tr>
                            <th colspan="3">
                                {{ $fechaActual->locale('es')->isoFormat('dddd, D [de] MMMM') }}
                                ({{ $fechaActual->format('d/m/Y') }})
                            </th>
                        </tr>
                    </thead>
                    <tbody>

                        {{-- BUCLE DE ZONAS --}}
                        @foreach ($zonas as $zona)
                            @php
                                $zonaId = $zona->id;
                                $zonaData = $datosDelDia[$zonaId] ?? [];
                                $totalZonaDia = $totalPorZonaDia[$fechaIter][$zonaId] ?? 0;
                            @endphp

                            {{-- Título de Zona --}}
                            <tr>
                                <td colspan="3" class="zona-header">
                                    Zona: {{ $zona->nombre }}
                                </td>
                            </tr>

                            {{-- BUCLE DE ÁREAS --}}
                            @foreach ($zona->areas as $area)
                                @php
                                    $areaId = $area->id;
                                    $areaData = $zonaData[$areaId] ?? [];
                                @endphp

                                {{-- Título de Área: Se revierte al nombre dinámico --}}
                                <tr>
                                    <td colspan="3" class="area-header">
                                        Área: {{ $area->nombre }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 30px; font-weight: bold;">Categoría</td>
                                    <td style="font-weight: bold; text-align: center;">Kilos (kg)</td>
                                    <td style="font-weight: bold; text-align: center;">Turno(s)</td>
                                </tr>

                                @php
                                    $categoriasDelArea = $area->subproductos
                                        ->pluck('categoria')
                                        ->unique('id')
                                        ->sortBy('nombre');
                                @endphp

                                {{-- BUCLE DE CATEGORÍAS (Se imprime siempre, incluyendo 0 kg) --}}
                                @foreach ($categoriasDelArea as $categoria)
                                    @php
                                        $registro = $areaData[$categoria->id] ?? null;
                                        $kilos = $registro['kilos'] ?? 0;

                                        // Lógica para el turno (usa $turnoSemana si $kilos es 0)
                                        $turnoAMostrar = $kilos > 0 ? $registro['turno'] ?? '-' : $turnoSemana ?? '-';
                                    @endphp

                                    <tr class="subproducto-row">
                                        <td>{{ $categoria->nombre }}</td>

                                        <td class="{{ $kilos == 0 ? 'zero-value' : '' }}" style="text-align: center;">
                                            {{ number_format($kilos, 2) }} kg
                                        </td>

                                        {{-- Aplicar la clase 'zero-value' al turno si los kilos son 0 --}}
                                        <td class="{{ $kilos == 0 ? 'zero-value' : '' }}" style="text-align: center;">
                                            {{ $turnoAMostrar }}
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach {{-- Fin foreach Area --}}

                            {{-- FILA DE TOTAL POR ZONA --}}
                            <tr>
                                <td colspan="3" class="zona-header" style="text-align: right; padding-right: 10pt;">
                                    TOTAL {{ $zona->nombre }}: {{ number_format($totalZonaDia, 2) }} kg
                                </td>
                            </tr>
                        @endforeach {{-- Fin foreach Zona --}}

                    </tbody>
                </table>

                {{-- CORRECCIÓN DEL SALTO DE PÁGINA: Solo se añade si no estamos en el último día --}}
                @if ($fechaActual->format('Y-m-d') !== $fechaFinString)
                    <div class="page-break"></div>
                @endif

                @php $fechaActual->addDay(); @endphp

            @endwhile
        </div>
    </div>
</body>

</html>
