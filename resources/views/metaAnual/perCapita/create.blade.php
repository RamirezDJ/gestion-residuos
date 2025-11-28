<x-app-layout>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">

                    <div class="mb-6 border-b border-gray-200 pb-4">
                        <h3 class="text-lg font-bold text-gray-700">Ingresar Datos del Día</h3>
                        <p class="text-sm text-gray-500">
                            La generación per cápita se calculará automáticamente basada en el total de personas
                            (Visitantes + Trabajadores) y los residuos generados.
                        </p>
                    </div>

                    {{-- FORMULARIO --}}
                    <form action="{{ route('metaAnual.percapita.store') }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <!-- Campo: Fecha (DISEÑO SOLICITADO) -->
                            <div class="col-span-1 md:col-span-2">
                                <x-label for="fecha" class="mb-2">
                                    Fecha del Registro
                                </x-label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                            xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                        </svg>
                                    </div>
                                    <input id="fecha" datepicker datepicker-autohide datepicker-format="yyyy-mm-dd"
                                        type="text" name="fecha"
                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                        placeholder="Seleccionar fecha" value="{{ old('fecha') }}" required>
                                </div>
                                @error('fecha')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Campo: No. Visitantes -->
                            <div>
                                <label for="visitantes" class="block text-sm font-medium text-gray-700 mb-1">No.
                                    Visitantes</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-users text-gray-400"></i>
                                    </div>
                                    {{-- Agregamos oninput para llamar a la función de cálculo --}}
                                    <input type="number" name="visitantes" id="visitantes" required min="0"
                                        placeholder="Ej: 150"
                                        class="pl-10 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        value="{{ old('visitantes') }}" oninput="calcularVistaPrevia()">
                                </div>
                                @error('visitantes')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Campo: No. Trabajadores -->
                            <div>
                                <label for="trabajadores" class="block text-sm font-medium text-gray-700 mb-1">No.
                                    Trabajadores</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-user-tie text-gray-400"></i>
                                    </div>
                                    {{-- Agregamos oninput para llamar a la función de cálculo --}}
                                    <input type="number" name="trabajadores" id="trabajadores" required min="0"
                                        placeholder="Ej: 45"
                                        class="pl-10 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        value="{{ old('trabajadores') }}" oninput="calcularVistaPrevia()">
                                </div>
                                @error('trabajadores')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Campo: Generación de Residuos -->
                            <div class="col-span-1 md:col-span-2">
                                <label for="kilos" class="block text-sm font-medium text-gray-700 mb-1">Generación
                                    Total de Residuos (Kg)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-weight-hanging text-gray-400"></i>
                                    </div>
                                    {{-- Agregamos oninput para llamar a la función de cálculo --}}
                                    <input type="number" name="kilos" id="kilos" required min="0"
                                        step="0.01" placeholder="Ej: 25.50"
                                        class="pl-10 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-lg font-semibold text-gray-700"
                                        value="{{ old('kilos') }}" oninput="calcularVistaPrevia()">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">kg</span>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Ingrese el peso total recolectado en el día.</p>
                                @error('kilos')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- VISTA PREVIA DEL CÁLCULO (SIMPLE) -->
                            <div class="col-span-1 md:col-span-2 mt-2 p-4 bg-gray-50 rounded border border-gray-200">
                                <p class="text-sm font-bold text-gray-700">Vista Previa:</p>
                                <div class="flex justify-between items-center mt-2">
                                    <span class="text-sm text-gray-600">Total Personas: <span id="preview-personas"
                                            class="font-bold text-black">0</span></span>
                                    <span class="text-lg text-black-700 font-bold">Per Cápita: <span
                                            id="preview-resultado">0.000</span> kg</span>
                                </div>
                            </div>

                        </div>

                        <div class="flex items-center justify-end mt-8 pt-4 border-t border-gray-100">
                            <button type="submit"
                                class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition shadow-lg">
                                <i class="fas fa-save mr-2"></i> Guardar Registro
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPT SIMPLE PARA EL CÁLCULO --}}
    <script>
        function calcularVistaPrevia() {
            // Obtener valores
            let visitantes = parseFloat(document.getElementById('visitantes').value) || 0;
            let trabajadores = parseFloat(document.getElementById('trabajadores').value) || 0;
            let kilos = parseFloat(document.getElementById('kilos').value) || 0;

            // Calcular Total Personas
            let totalPersonas = visitantes + trabajadores;

            // Calcular Per Cápita (Evitar división por cero)
            let perCapita = 0;
            if (totalPersonas > 0) {
                perCapita = kilos / totalPersonas;
            }

            // Actualizar vista previa
            document.getElementById('preview-personas').innerText = totalPersonas;
            document.getElementById('preview-resultado').innerText = perCapita.toFixed(4); // 4 decimales
        }
    </script>

</x-app-layout>
