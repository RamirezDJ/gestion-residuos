<x-app-layout>
    @push('css')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Registro de Parámetros Biológicos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">

                    <div class="mb-6 flex justify-between items-center">
                        <div>
                            <h1 class="text-2xl font-medium text-gray-900">Edición de Datos</h1>
                            <p class="text-gray-500 mt-1">
                                Editando registro biológico del: <span
                                    class="font-bold">{{ $registro->fecha_muestreo }}</span>
                            </p>
                        </div>
                        <a href="{{ route('indicesCalidad.show', ['tipo' => 'biologicos']) }}"
                            class="text-gray-500 hover:text-gray-700">
                            <i class="fa-solid fa-arrow-left mr-2"></i> Volver
                        </a>
                    </div>

                    <form action="{{ route('indicesCalidad.update', $registro->id) }}" method="POST"
                        id="edit-biologicos-form">
                        @csrf
                        @method('PUT')

                        <input type="hidden" name="tipo_registro" value="biologicos">

                        {{-- Sección: Datos Generales --}}
                        <div class="bg-gray-50 p-4 rounded-lg mb-6 border border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Datos Generales</h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Fecha</label>
                                    <input type="date" name="fecha_muestreo" required
                                        class="border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm w-full mt-1"
                                        value="{{ old('fecha_muestreo', $registro->fecha_muestreo) }}">
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Hora</label>
                                    <input type="time" name="hora_muestreo" required
                                        class="border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm w-full mt-1"
                                        value="{{ old('hora_muestreo', $registro->hora_muestreo) }}">
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Punto de Muestreo</label>
                                    <select name="punto_muestreo" required
                                        class="border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm w-full mt-1">
                                        @foreach (['Punto 1', 'Punto 2', 'Punto 3', 'Punto 4'] as $punto)
                                            <option value="{{ $punto }}"
                                                {{ old('punto_muestreo', $registro->punto_muestreo) == $punto ? 'selected' : '' }}>
                                                {{ $punto }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">No. Muestra</label>
                                    <input type="number" name="numero_muestra" min="1" required
                                        class="border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm w-full mt-1"
                                        value="{{ old('numero_muestra', $registro->numero_muestra) }}">
                                </div>
                            </div>
                        </div>

                        {{-- Sección: Parámetros Biológicos --}}
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
                                <h3 class="text-xl font-bold text-purple-800">Parámetros Biológicos</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Coliformes Totales
                                        (NMP/100ml)</label>
                                    <input type="number" step="0.01" name="coliformes_totales"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500"
                                        value="{{ old('coliformes_totales', optional($registro->biologicos)->coliformes_totales) }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Coliformes Fecales
                                        (NMP/100ml)</label>
                                    <input type="number" step="0.01" name="coliformes_fecales"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500"
                                        value="{{ old('coliformes_fecales', optional($registro->biologicos)->coliformes_fecales) }}">
                                </div>
                            </div>

                            <div class="mt-6">
                                <label class="block font-medium text-sm text-gray-700">Observaciones</label>
                                <textarea name="observaciones" rows="2"
                                    class="border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm w-full mt-1">{{ old('observaciones', $registro->observaciones) }}</textarea>
                            </div>

                            <div class="flex items-center justify-end mt-6 gap-4">
                                <button type="submit"
                                    class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-6 rounded-lg shadow transition">
                                    Actualizar Registro
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
                const editForm = document.getElementById('edit-biologicos-form');

                editForm.addEventListener('submit', function(event) {
                    event.preventDefault();

                    Swal.fire({
                        title: '¿Guardar Cambios?',
                        text: "Se actualizará la información del registro biológico.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#9333ea', // Color morado para combinar
                        cancelButtonColor: '#6B7280',
                        confirmButtonText: 'Sí, actualizar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            editForm.submit();
                        }
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
