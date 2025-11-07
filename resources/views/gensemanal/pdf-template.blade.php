<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Gestión de Residuos - Reporte Semanal</title>

    <style>
        /* --- ESTILOS DE TU DISEÑO ORIGINAL (Ligeramente ajustados) --- */
        @page {
            margin: 1cm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            /* Un poco más pequeño para que quepa más */
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
            /* Ajustado */
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

        /* Tabla de Resumen (Datos Generales) */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15pt;
            font-size: 10pt;
        }

        .summary-table td {
            padding: 6pt;
            border: 0.5pt solid #ddd;
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

        /* Tabla de Desglose (Datos por día/zona) */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15pt;
            font-size: 9pt;
        }

        .data-table th,
        .data-table td {
            padding: 6pt;
            text-align: left;
            border: 0.5pt solid #ddd;
            word-wrap: break-word;
        }

        .data-table th {
            background-color: #611232;
            color: white;
            font-weight: bold;
        }

        .data-table .zona-header {
            background-color: #f3f3f3;
            font-size: 11pt;
            font-weight: bold;
            color: #333;
        }

        .data-table .area-header {
            font-weight: bold;
            padding-left: 15px;
            background-color: #fafafa;
        }

        .data-table .subproducto-row td {
            padding-left: 30px;
        }

        /* Pie de página */
        .footer {
            position: fixed;
            /* Cambiado para dompdf */
            bottom: -0.5cm;
            /* Ajusta según sea necesario */
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
        <div class="section">
            <h2 class="section-title">Desglose de Datos por Día y Subproducto</h2>

            @php
                $fechaActual = \Carbon\Carbon::createFromFormat('d/m/Y', $fechaInicioFormateada, 'Europe/London');
                $fechaFin = \Carbon\Carbon::createFromFormat('d/m/Y', $fechaFinFormateada, 'Europe/London');
            @endphp

            @while ($fechaActual <= $fechaFin)
                @php
                    $fechaIter = $fechaActual->format('Y-m-d');
                    $datosDelDia = $lookupDataSemanal[$fechaIter] ?? []; // Busca los datos para ESTE día
                @endphp

                @if (!empty($datosDelDia))
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th colspan="3">
                                    {{ $fechaActual->isoFormat('dddd, D [de] MMMM') }}
                                    ({{ $fechaActual->format('d/m/Y') }})
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($zonas as $zona)
                                @php
                                    // Revisamos si esta zona tiene datos ESE DÍA
                                    $zonaTieneDatos = false;
                                    foreach ($zona->areas as $area) {
                                        if (isset($datosDelDia[$area->id])) {
                                            $zonaTieneDatos = true;
                                            break;
                                        }
                                    }
                                @endphp

                                @if ($zonaTieneDatos)
                                    <tr>
                                        <td colspan="3" class="zona-header">
                                            Zona: {{ $zona->nombre }}
                                        </td>
                                    </tr>

                                    @foreach ($zona->areas as $area)
                                        @php
                                            $datosDelArea = $datosDelDia[$area->id] ?? [];
                                        @endphp

                                        @if (!empty($datosDelArea))
                                            <tr>
                                                <td colspan="3" class="area-header">
                                                    Área: {{ $area->nombre }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-left: 30px; font-weight: bold;">Subproducto</td>
                                                <td style="font-weight: bold;">Kilos (kg)</td>
                                                <td style="font-weight: bold;">Turno(s)</td>
                                            </tr>

                                            @foreach ($area->subproductos as $subproducto)
                                                @php
                                                    $kilos = $datosDelArea[$subproducto->id] ?? 0;
                                                @endphp

                                                @if ($kilos > 0)
                                                    <tr class="subproducto-row">
                                                        <td>{{ $subproducto->nombre }}</td>
                                                        <td>{{ number_format($kilos, 2) }} kg</td>
                                                        <td>{{ $datosSemana->where('fecha', $fechaIter)->pluck('turno')->unique()->implode(', ') }}
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @endif
                                    @endforeach
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @endif @php $fechaActual->addDay(); @endphp
            @endwhile
        </div>
    </div>
</body>

</html>
