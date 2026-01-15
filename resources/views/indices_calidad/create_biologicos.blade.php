<x-app-layout>
    @push('css')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nuevo Registro de Parámetros Biológicos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">

                    <div class="mb-6">
                        <h1 class="text-2xl font-medium text-gray-900">Captura de Datos Biológicos</h1>
                        <p class="text-gray-500 mt-1">
                            Completa la información de la bitácora. Estás registrando:
                            <span class="font-bold text-purple-600">PARÁMETROS BIOLÓGICOS</span>
                        </p>
                    </div>

                    <form action="{{ route('indicesCalidad.store') }}" method="POST" id="create-biologicos-form">
                        @csrf
                        <input type="hidden" name="tipo_registro" value="biologicos">
                        <div class="bg-gray-50 p-4 rounded-lg mb-6 border border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Datos Generales</h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Fecha</label>
                                    <input type="date" name="fecha_muestreo" required
                                        class="border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm w-full mt-1"
                                        value="{{ date('Y-m-d') }}">
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Hora</label>
                                    <input type="time" name="hora_muestreo" required
                                        class="border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm w-full mt-1"
                                        value="{{ date('H:i') }}">
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Punto de Muestreo</label>
                                    <select name="punto_muestreo" required
                                        class="border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm w-full mt-1">
                                        <option value="" disabled selected>Selecciona...</option>
                                        <option value="Punta 1">Punta 1</option>
                                        <option value="Punto 2">Punto 2</option>
                                        <option value="Punto 3">Punto 3</option>
                                        <option value="Punto 4">Punto 4</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">No. Muestra</label>
                                    <input type="number" name="numero_muestra" min="1" value="1" required
                                        class="border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm w-full mt-1">
                                </div>
                            </div>
                        </div>

                        <div class="bg-purple-50 p-6 rounded-lg border border-purple-200 mb-6">
                            <div class="flex items-center mb-6">
                                <div class="p-2 bg-purple-100 rounded-full mr-3 text-purple-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13a9 9 0 1118 0 9 9 0 01-18 0z" />
                                        <circle cx="12" cy="13" r="3" stroke-width="2" />
                                        <path stroke-linecap="round" d="M12 7v1m3 2h1m-7 0H8m3 8v1" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-purple-800">Análisis Bacteriológico</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Coliformes Totales
                                        (UFC/100ml)</label>
                                    <input type="number" step="0.01" name="coliformes_totales" required
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                                        placeholder="Ingrese el valor">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Coliformes Fecales
                                        (UFC/100ml)</label>
                                    <input type="number" step="0.01" name="coliformes_fecales" required
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                                        placeholder="Ingrese el valor">
                                </div>
                            </div>

                            <div class="mt-6">
                                <label class="block font-medium text-sm text-gray-700">Observaciones</label>
                                <textarea name="observaciones" rows="2"
                                    class="border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm w-full mt-1"
                                    placeholder="Notas adicionales..."></textarea>
                            </div>

                            <div class="flex items-center justify-end mt-6 gap-4">
                                <button type="submit"
                                    class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-6 rounded-lg shadow transition">
                                    Guardar Biológicos
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const createForm = document.getElementById('create-biologicos-form');

                createForm.addEventListener('submit', function(event) {
                    event.preventDefault();

                    Swal.fire({
                        title: '¿Guardar Registro?',
                        html: "Estás a punto de crear un nuevo registro biológico.<br><b>Verifica que los datos sean correctos.</b>",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#7C3AED',
                        cancelButtonColor: '#6B7280',
                        confirmButtonText: 'Sí, guardar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            createForm.submit();
                        }
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
