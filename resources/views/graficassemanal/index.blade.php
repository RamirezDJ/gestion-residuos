<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Graficas de residuos') }}
            </h2>
            <div class="flex gap-4 items-center">
                <label class="font-bold">Elija un rango de fecha: </label>
                <button id="dateRangeButton" data-dropdown-toggle="dateRangeDropdown"
                    data-dropdown-ignore-click-outside-class="datepicker" type="button"
                    class="inline-flex items-center text-blue-700 dark:text-blue-600 font-medium hover:underline">
                    Inicio - Final
                    <svg class="w-3 h-3 ms-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 10 6">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 4 4 4-4" />
                    </svg>
                </button>
                <div id="dateRangeDropdown"
                    class="z-10 hidden bg-customColor divide-y divide-gray-100 rounded-lg shadow w-80 lg:w-96 dark:bg-gray-700 dark:divide-gray-600">
                    <div class="p-3" aria-labelledby="dateRangeButton">
                        <div date-rangepicker datepicker-autohide class="flex items-center">
                            <div class="relative">
                                <input id="startDate" name="start" type="text"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="Fecha inicial">
                            </div>
                            <span class="mx-2 text-gray-100 dark:text-gray-400">a</span>
                            <div class="relative">
                                <input id="endDate" name="end" type="text"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="Fecha final">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Botón para enviar los datos -->
                <button id="submitDates" class="ml-4 bg-blue-500 text-white px-4 py-1 rounded-lg"
                    onclick="fetchAllData()">
                    Actualizar graficas
                </button>
            </div>
        </div>
    </x-slot>

    <div class="max-w-8xl mx-auto sm:px-6 lg:px-8 mt-5 mb-10">
        <div class="p-6 lg:p-5 bg-gray-200 border-b border-gray-200 rounded-lg grid grid-cols-6 gap-4">
            <div class="max-w-8xl w-full bg-white rounded-lg shadow dark:bg-gray-800 p-4 col-span-4">
                {{-- Titulo de las graficas --}}
                <div class="flex justify-between items-start">
                    <div class="flex items-center">
                        <div class="flex justify-center items-center mt-3 ml-3 -mb-2">
                            <h5 class="text-xl font-bold leading-none text-gray-900 dark:text-white pe-1">
                                Lo mas generado
                            </h5>
                        </div>
                    </div>
                </div>
                {{-- 3 columnas para representar el top --}}
                <div class="grid grid-cols-3 gap-2 mt-3">
                    @foreach ($top3Generado as $index => $generado)
                        <div class="col-span-1 @if ($index != 2 && $index != 0) border-l-2 border-r-2 -mr-4 @endif">
                            <div class="flex items-center justify-center">
                                <!-- Donut Chart -->
                                <div class="w-40" id="radial-chart-{{ $index + 1 }}"></div>
                                <div>
                                    <h2 class="text-gray-500">
                                        @if ($index == 0)
                                            Primero
                                        @elseif($index == 1)
                                            Segundo
                                        @else
                                            Tercero
                                        @endif
                                    </h2>
                                    <p class="text-3xl font-bold" id="porcentaje-{{ $index + 1 }}">0%</p>
                                    <p class="lg">
                                        {{ $generado['nombre'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Grafica tipo pastel --}}
            <div class="max-w-8xl w-full bg-white rounded-lg shadow dark:bg-gray-800 p-4 md:p-6 col-span-2 row-span-2">
                <div class="flex justify-between items-start w-full">
                    <div class="flex-col items-center">
                        <div class="flex items-center mb-1">
                            <h5 class="text-xl font-bold leading-none text-gray-900 dark:text-white me-1">Porcentaje de
                                Generacion por Zona</h5>
                        </div>
                    </div>
                </div>

                <!-- Pie Chart -->
                <div class="py-7" id="pie-chart"></div>

                <div
                    class="grid grid-cols-1 items-center border-gray-200 border-t dark:border-gray-700 justify-between">
                    <div class="flex justify-between items-center pt-5">
                        <!-- Button -->
                        <button id="dropdownDefaultButton1" data-dropdown-toggle="lastDaysdropdown1"
                            data-dropdown-placement="bottom"
                            class="text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-900 text-center inline-flex items-center dark:hover:text-white"
                            type="button" onclick="toggleDropdown()">
                            <span id="selectedOptionText1">Opción Rápida</span>
                            <svg class="w-2.5 m-2.5 ms-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 10 6">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 4 4 4-4" />
                            </svg>
                        </button>

                        <!-- Dropdown menu -->
                        <div id="lastDaysdropdown1"
                            class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700">
                            <ul class="py-2 text-sm text-gray-700 dark:text-gray-200"
                                aria-labelledby="dropdownDefaultButton1">
                                <li>
                                    <a onclick="selectOptionZonas('Todo', 'Todo')"
                                        class="dropdown-option block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white cursor-pointer">Todo</a>
                                </li>
                                <li>
                                    <a onclick="selectOptionZonas('Últimos 7 Días', '7_dias')"
                                        class="dropdown-option block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white cursor-pointer">Últimos
                                        7 Días</a>
                                </li>
                                <li>
                                    <a onclick="selectOptionZonas('Últimos 30 días', '30_dias')"
                                        class="dropdown-option block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white cursor-pointer">Últimos
                                        30 días</a>
                                </li>
                                <li>
                                    <a onclick="selectOptionZonas('Últimos 90 días', '90_dias')"
                                        class="dropdown-option block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white cursor-pointer">Últimos
                                        90 días</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Grafica tipo barras --}}
            <div class="max-w-8xl w-full bg-white rounded-lg shadow dark:bg-gray-800 p-4 md:p-6 col-span-4">
                <div class="flex justify-between pb-4 mb-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center">
                        <div
                            class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center me-3">
                            <svg class="w-6 h-6 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 19">
                                <path
                                    d="M14.5 0A3.987 3.987 0 0 0 11 2.1a4.977 4.977 0 0 1 3.9 5.858A3.989 3.989 0 0 0 14.5 0ZM9 13h2a4 4 0 0 1 4 4v2H5v-2a4 4 0 0 1 4-4Z" />
                                <path
                                    d="M5 19h10v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2ZM5 7a5.008 5.008 0 0 1 4-4.9 3.988 3.988 0 1 0-3.9 5.859A4.974 4.974 0 0 1 5 7Zm5 3a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm5-1h-.424a5.016 5.016 0 0 1-1.942 2.232A6.007 6.007 0 0 1 17 17h2a1 1 0 0 0 1-1v-2a5.006 5.006 0 0 0-5-5ZM5.424 9H5a5.006 5.006 0 0 0-5 5v2a1 1 0 0 0 1 1h2a6.007 6.007 0 0 1 4.366-5.768A5.016 5.016 0 0 1 5.424 9Z" />
                            </svg>
                        </div>
                        <div>
                            <h5 class="leading-none text-2xl font-bold text-gray-900 dark:text-white pb-1">Grafica de
                                barras, generacion de residuos por Zonas</h5>
                            <p class="text-sm font-normal text-gray-500 dark:text-gray-400">Para mostrar otros
                                parametros, elija una opción en la parte inferior
                            </p>
                        </div>
                    </div>
                </div>

                <div id="column-chart"></div>

                <div
                    class="grid grid-cols-1 items-center border-gray-200 border-t dark:border-gray-700 justify-between">
                    <div class="flex justify-between items-center pt-5">
                        <!-- Button -->
                        <button id="dropdownDefaultButton2" data-dropdown-toggle="lastDaysdropdown2"
                            data-query="consulta2" data-dropdown-placement="bottom"
                            class="text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-900 text-center inline-flex items-center dark:hover:text-white"
                            type="button" onclick="toggleDropdown2()">
                            <span id="selectedOptionText2">Opción Rápida</span>
                            <svg class="w-2.5 m-2.5 ms-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 10 6">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 4 4 4-4" />
                            </svg>
                        </button>

                        <!-- Dropdown menu -->
                        <div id="lastDaysdropdown2"
                            class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700">
                            <ul class="py-2 text-sm text-gray-700 dark:text-gray-200"
                                aria-labelledby="dropdownDefaultButton2">
                                <li>
                                    <a onclick="selectOptionZonas2('Todo', 'Todo')"
                                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white cursor-pointer dropdown-option1">Todo</a>
                                </li>
                                <li>
                                    <a onclick="selectOptionZonas2('Últimos 7 Días', '7_dias')"
                                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white cursor-pointer dropdown-option1">Últimos
                                        7 Días</a>
                                </li>
                                <li>
                                    <a onclick="selectOptionZonas2('Últimos 30 días', '30_dias')"
                                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white cursor-pointer dropdown-option1">Últimos
                                        30 días</a>
                                </li>
                                <li>
                                    <a onclick="selectOptionZonas2('Últimos 90 días', '90_dias')"
                                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white cursor-pointer dropdown-option1">Últimos
                                        90 días</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Grafica tipo lineas de tendencia --}}
            <div class="max-w-8xl w-full bg-white rounded-lg shadow dark:bg-gray-800 p-4 md:p-6 col-span-6">
                <div class="flex justify-between pb-4 mb-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center">
                        <div
                            class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center me-3">
                            <svg class="w-6 h-6 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 19">
                                <path
                                    d="M14.5 0A3.987 3.987 0 0 0 11 2.1a4.977 4.977 0 0 1 3.9 5.858A3.989 3.989 0 0 0 14.5 0ZM9 13h2a4 4 0 0 1 4 4v2H5v-2a4 4 0 0 1 4-4Z" />
                                <path
                                    d="M5 19h10v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2ZM5 7a5.008 5.008 0 0 1 4-4.9 3.988 3.988 0 1 0-3.9 5.859A4.974 4.974 0 0 1 5 7Zm5 3a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm5-1h-.424a5.016 5.016 0 0 1-1.942 2.232A6.007 6.007 0 0 1 17 17h2a1 1 0 0 0 1-1v-2a5.006 5.006 0 0 0-5-5ZM5.424 9H5a5.006 5.006 0 0 0-5 5v2a1 1 0 0 0 1 1h2a6.007 6.007 0 0 1 4.366-5.768A5.016 5.016 0 0 1 5.424 9Z" />
                            </svg>
                        </div>
                        <div>
                            <h5 class="leading-none text-2xl font-bold text-gray-900 dark:text-white pb-1">Tendencia de
                                generacion de residuos por día</h5>
                            <p class="text-sm font-normal text-gray-500 dark:text-gray-400">Para mostrar otros
                                parametros, elija una opción en la parte inferior
                            </p>
                        </div>
                    </div>
                </div>

                <div id="data-labels-chart"></div>

                <div
                    class="grid grid-cols-1 items-center border-gray-200 border-t dark:border-gray-700 justify-between">
                    <div class="flex justify-between items-center pt-5">
                        <!-- Button -->
                        <button id="dropdownDefaultButton3" data-dropdown-toggle="lastDaysdropdown3"
                            data-query="consulta3" data-dropdown-placement="bottom"
                            class="text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-900 text-center inline-flex items-center dark:hover:text-white"
                            type="button" onclick="toggleDropdown3()">
                            <span id="selectedOptionText3">Opción Rápida</span>
                            <svg class="w-2.5 m-2.5 ms-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 10 6">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 4 4 4-4" />
                            </svg>
                        </button>

                        <!-- Dropdown menu -->
                        <div id="lastDaysdropdown3"
                            class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700">
                            <ul class="py-2 text-sm text-gray-700 dark:text-gray-200"
                                aria-labelledby="dropdownDefaultButton3">
                                <li>
                                    <a onclick="selectOptionZonas3('Todo', 'Todo')"
                                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white cursor-pointer dropdown-option2">Todo</a>
                                </li>
                                <li>
                                    <a onclick="selectOptionZonas3('Últimos 7 Días', '7_dias')"
                                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white cursor-pointer dropdown-option2">Últimos
                                        7 Días</a>
                                </li>
                                <li>
                                    <a onclick="selectOptionZonas3('Últimos 30 días', '30_dias')"
                                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white cursor-pointer dropdown-option2">Últimos
                                        30 días</a>
                                </li>
                                <li>
                                    <a onclick="selectOptionZonas3('Últimos 90 días', '90_dias')"
                                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white cursor-pointer dropdown-option2">Últimos
                                        90 días</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('js')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <script>
            // Datos iniciales
            window.GeneradoData = @json($top3Generado ?? []);

            // 1. FUNCIONES AUXILIARES
            function convertirFechaISO(fechaStr) {
                if (!fechaStr) return '';
                const partes = fechaStr.split('/');
                if (partes.length === 3) {
                    return `${partes[2]}-${partes[1]}-${partes[0]}`;
                }
                return fechaStr;
            }

            function formatearFechaVisual(date) {
                const d = String(date.getDate()).padStart(2, '0');
                const m = String(date.getMonth() + 1).padStart(2, '0');
                const y = date.getFullYear();
                return `${d}/${m}/${y}`;
            }

            function aplicarRangoRapido(rango) {
                console.log('Aplicando rango rápido:', rango); // DEBUG
                const fin = new Date();
                const inicio = new Date();

                if (rango === '7_dias') inicio.setDate(fin.getDate() - 7);
                else if (rango === '30_dias') inicio.setDate(fin.getDate() - 30);
                else if (rango === '90_dias') inicio.setDate(fin.getDate() - 90);
                else inicio.setFullYear(fin.getFullYear() - 1);

                // Verificar si existen los inputs antes de asignar valor
                const inputStart = document.getElementById('startDate');
                const inputEnd = document.getElementById('endDate');

                if (inputStart && inputEnd) {
                    inputStart.value = formatearFechaVisual(inicio);
                    inputEnd.value = formatearFechaVisual(fin);
                    fetchAllData();
                } else {
                    console.error("ERROR: No encuentro los inputs con id='startDate' o 'endDate'");
                }
            }

            // 2. FUNCIONES DE DROPDOWNS
            // Pastel
            function toggleDropdown() {
                document.getElementById("lastDaysdropdown1").classList.toggle("hidden");
            }

            function selectOptionZonas(label, value) {
                document.getElementById("selectedOptionText1").textContent = label;
                document.getElementById("lastDaysdropdown1").classList.add("hidden");
                aplicarRangoRapido(value);
            }
            // Barras
            function toggleDropdown2() {
                document.getElementById("lastDaysdropdown2").classList.toggle("hidden");
            }

            function selectOptionZonas2(label, value) {
                document.getElementById("selectedOptionText2").textContent = label;
                document.getElementById("lastDaysdropdown2").classList.add("hidden");
                aplicarRangoRapido(value);
            }
            // Tendencia
            function toggleDropdown3() {
                document.getElementById("lastDaysdropdown3").classList.toggle("hidden");
            }

            function selectOptionZonas3(label, value) {
                document.getElementById("selectedOptionText3").textContent = label;
                document.getElementById("lastDaysdropdown3").classList.add("hidden");
                aplicarRangoRapido(value);
            }

            // Cierres globales
            document.addEventListener('click', function(event) {
                const closes = [{
                        dd: 'lastDaysdropdown1',
                        btn: 'dropdownDefaultButton1'
                    },
                    {
                        dd: 'lastDaysdropdown2',
                        btn: 'dropdownDefaultButton2'
                    },
                    {
                        dd: 'lastDaysdropdown3',
                        btn: 'dropdownDefaultButton3'
                    }
                ];
                closes.forEach(item => {
                    const dd = document.getElementById(item.dd);
                    const btn = document.getElementById(item.btn);
                    if (dd && btn && !dd.contains(event.target) && !btn.contains(event.target)) {
                        dd.classList.add("hidden");
                    }
                });
            });

            // 3. FETCH DATA (CON DEBUG)
            function fetchAllData() {
                console.log("Iniciando fetchAllData..."); // DEBUG

                var inputStart = document.getElementById('startDate');
                var inputEnd = document.getElementById('endDate');

                if (!inputStart || !inputEnd) {
                    console.error("ERROR CRÍTICO: No existen los inputs de fecha en el DOM.");
                    return;
                }

                var startRaw = inputStart.value;
                var endRaw = inputEnd.value;

                console.log("Fechas crudas:", startRaw, endRaw); // DEBUG

                if (!startRaw || !endRaw) {
                    alert("Por favor, selecciona un rango de fechas válido.");
                    return;
                }

                var inicio = convertirFechaISO(startRaw);
                var final = convertirFechaISO(endRaw);

                var url = `/graficassemanal/data?tipoGrafico=all&inicio=${inicio}&final=${final}`;
                console.log("URL generada:", url); // DEBUG

                fetch(url, {
                        method: 'GET',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then((response) => {
                        console.log("Status respuesta:", response.status); // DEBUG
                        if (!response.ok) throw new Error('Error en la solicitud HTTP: ' + response.status);
                        return response.json();
                    })
                    .then((data) => {
                        console.log("Datos recibidos del servidor:", data); // DEBUG

                        // Disparar evento
                        const event = new CustomEvent('updateCharts', {
                            detail: data
                        });
                        document.dispatchEvent(event);
                        console.log("Evento 'updateCharts' despachado correctamente.");
                    })
                    .catch((error) => {
                        console.error('Error en FETCH:', error);
                        alert('Error al cargar datos. Revisa la consola (F12).');
                    });
            }
        </script>
    @endpush

    @vite('resources/js/app.js')
</x-app-layout>
