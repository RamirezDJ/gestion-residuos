<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nuevo Registro de Parámetros Físicos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">

                    <div class="mb-6">
                        <h1 class="text-2xl font-medium text-gray-900">Captura de Datos Físicos</h1>
                        <p class="text-gray-500 mt-1">
                            Completa la información de la bitácora. Estás registrando:
                            <span class="font-bold text-blue-600">PARÁMETROS FÍSICOS</span>
                        </p>
                    </div>

                    <form action="{{ route('indicesCalidad.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tipo_registro" value="fisicos">

                        {{-- 1. DATOS DE IDENTIFICACIÓN --}}
                        <div class="bg-gray-50 p-4 rounded-lg mb-6 border border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Datos Generales</h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Fecha</label>
                                    <input type="date" name="fecha_muestreo" required
                                        class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full mt-1"
                                        value="{{ date('Y-m-d') }}">
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Hora</label>
                                    <input type="time" name="hora_muestreo" required
                                        class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full mt-1"
                                        value="{{ date('H:i') }}">
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Punto de Muestreo</label>
                                    <select name="punto_muestreo" required
                                        class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full mt-1">
                                        <option value="" disabled selected>Selecciona...</option>
                                        <option value="Punta 1">Punta 1</option>
                                        <option value="Cisterna">Cisterna</option>
                                        <option value="Punto 2">Punto 2</option>
                                        <option value="Punto 3">Punto 3</option>
                                        <option value="Punto 4">Punto 4</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">No. Muestra</label>
                                    <input type="number" name="numero_muestra" min="1" value="1" required
                                        class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full mt-1">
                                </div>
                            </div>
                        </div>

                        {{-- 2. BLOQUE AZUL (FÍSICOS) --}}
                        <div class="bg-blue-50 p-6 rounded-lg border border-blue-200 mb-6">
                            <div class="flex items-center mb-6">
                                <div class="p-2 bg-blue-100 rounded-full mr-3 text-blue-600">
                                    {{-- Icono Gota --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-blue-800">Parámetros Físicos</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Conductividad Eléctrica
                                        (ms/cm)</label>
                                    <input type="number" step="0.01" name="conductividad_electrica"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Turbidez (UNT)</label>
                                    <input type="number" step="0.01" name="turbidez"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Temperatura (°C)</label>
                                    <input type="number" step="0.1" name="temperatura"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Oxígeno Disuelto
                                        (DOPPM)</label>
                                    <input type="number" step="0.01" name="oxigeno_disuelto_ppm"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Oxígeno Disuelto (%)</label>
                                    <input type="number" step="0.01" name="oxigeno_disuelto_porcentaje"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Sólidos Disueltos (mg/L -
                                        TDS)</label>
                                    <input type="number" step="0.01" name="solidos_disueltos"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        {{-- 3. OBSERVACIONES --}}
                        <div class="mt-6">
                            <label class="block font-medium text-sm text-gray-700">Observaciones</label>
                            <textarea name="observaciones" rows="2"
                                class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full mt-1"></textarea>
                        </div>

                        <div class="flex items-center justify-end mt-6 gap-4">
                            <a href="{{ route('indicesCalidad.index') }}"
                                class="text-gray-600 hover:text-gray-900 underline text-sm">Cancelar</a>
                            <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow transition">Guardar
                                Físicos</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
