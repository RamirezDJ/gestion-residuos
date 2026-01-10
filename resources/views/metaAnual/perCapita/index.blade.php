<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                {{ __('Generación Per Cápita') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">

                    {{-- 1. Encabezado y Botón --}}
                    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
                        <div>
                            <h3 class="text-xl font-bold text-gray-700">Registros Diarios</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Consulta y gestión de los registros Per Cápita diario.
                            </p>
                        </div>

                        <a href="{{ route('metaAnual.percapita.create') }}"
                            class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition">
                            <i class="fas fa-plus mr-2"></i> Nuevo Registro
                        </a>
                    </div>

                    {{-- 2. Tabla de Registros (Tu parcial) --}}
                    <div class="mt-4">
                        @include('metaAnual.perCapita.partials.table-general')
                    </div>

                    {{-- 3. SECCIÓN INFERIOR: TARJETAS Y GRÁFICA --}}
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 mt-8 border-t pt-6 border-gray-100">

                        <!-- Tarjeta Promedio (Izquierda) -->
                        <div
                            class="bg-gray-50 rounded-lg border border-gray-200 p-4 h-full flex flex-col justify-center">
                            <div class="flex items-center justify-between">
                                <h3 class="text-md font-medium text-gray-600">Promedio General</h3>
                                <div class="p-2 bg-blue-100 rounded-full">
                                    <i class="fas fa-chart-line text-blue-600"></i>
                                </div>
                            </div>
                            <div class="mt-4">
                                <p class="text-3xl font-bold text-gray-800">
                                    {{ number_format($promedioPercapitaDiario ?? 0, 3) }}
                                </p>
                                <p class="text-sm font-normal text-gray-500">kg/persona/día</p>
                            </div>
                        </div>

                        <!-- Tarjeta Gráfica (Derecha - ESTA ES LA QUE FALTABA) -->
                        <div class="lg:col-span-2 bg-white rounded-lg border border-gray-200 p-4 shadow-sm">
                            <h3 class="text-sm font-bold text-gray-700 mb-4">Tendencia Histórica</h3>
                            {{-- Contenedor donde ApexCharts dibujará la gráfica --}}
                            <div id="trendChart" class="w-full h-64"></div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- 4. SCRIPT PARA DIBUJAR LA GRÁFICA --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Obtenemos los datos que mandó el controlador
            const datos = @json($datosGrafica ?? []);

            const options = {
                series: [{
                    name: "Per Cápita",
                    data: datos // Formato esperado: [{x: 'fecha', y: valor}, ...]
                }],
                chart: {
                    type: 'area',
                    height: 250,
                    fontFamily: 'Inter, sans-serif',
                    zoom: {
                        enabled: false
                    },
                    toolbar: {
                        show: false
                    }
                },
                colors: ['#2563eb'],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.05,
                        stops: [0, 100]
                    }
                },
                xaxis: {
                    type: 'datetime',
                    labels: {
                        format: 'dd MMM'
                    },
                    tooltip: {
                        enabled: false
                    }
                },
                yaxis: {
                    labels: {
                        formatter: (val) => val.toFixed(3)
                    }
                },
                tooltip: {
                    x: {
                        format: 'dd MMM yyyy'
                    },
                    y: {
                        formatter: function(value) {
                            return value + " kg/p/día";
                        }
                    }
                }
            };

            if (datos.length > 0) {
                const chart = new ApexCharts(document.querySelector("#trendChart"), options);
                chart.render();
            } else {
                document.querySelector("#trendChart").innerHTML =
                    '<div class="flex items-center justify-center h-full text-gray-400 text-sm">No hay suficientes datos para la gráfica.</div>';
            }
        });
    </script>

</x-app-layout>
