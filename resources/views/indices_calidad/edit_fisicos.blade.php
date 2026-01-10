<x-app-layout>
    @push('css')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Registro de Parámetros Físicos') }}
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
                                Editando registro físico del: <span
                                    class="font-bold">{{ $registro->fecha_muestreo }}</span>
                            </p>
                        </div>
                        <a href="{{ route('indicesCalidad.show', ['tipo' => 'fisicos']) }}"
                            class="text-gray-500 hover:text-gray-700">
                            <i class="fa-solid fa-arrow-left mr-2"></i> Volver
                        </a>
                    </div>

                    <form action="{{ route('indicesCalidad.update', $registro->id) }}" method="POST"
                        id="edit-fisicos-form">
                        @csrf
                        @method('PUT')

                        <input type="hidden" name="tipo_registro" value="fisicos">

                        <div class="bg-gray-50 p-4 rounded-lg mb-6 border border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Datos Generales</h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Fecha</label>
                                    <input type="date" name="fecha_muestreo" required
                                        class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full mt-1"
                                        value="{{ old('fecha_muestreo', $registro->fecha_muestreo) }}">
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Hora</label>
                                    <input type="time" name="hora_muestreo" required
                                        class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full mt-1"
                                        value="{{ old('hora_muestreo', $registro->hora_muestreo) }}">
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Punto de Muestreo</label>
                                    <select name="punto_muestreo" required
                                        class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full mt-1">
                                        @foreach (['Punta 1', 'Punto 2', 'Punto 3', 'Punto 4'] as $punto)
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
                                        class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full mt-1"
                                        value="{{ old('numero_muestra', $registro->numero_muestra) }}">
                                </div>
                            </div>
                        </div>

                        <div class="bg-blue-50 p-6 rounded-lg border border-blue-200 mb-6">
                            <div class="flex items-center mb-6">
                                <div class="p-2 bg-blue-100 rounded-full mr-3 text-blue-600">
                                    <i class="fa-solid fa-flask text-xl"></i>
                                </div>
                                <h3 class="text-xl font-bold text-blue-800">Parámetros Físicos</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                {{-- Fila 1 --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Temperatura (°C)</label>
                                    <input type="number" step="0.1" name="temperatura"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500"
                                        value="{{ old('temperatura', optional($registro->fisicos)->temperatura) }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Color</label>
                                    <input type="text" name="color"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500"
                                        value="{{ old('color', optional($registro->fisicos)->color) }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Olor</label>
                                    <input type="text" name="olor"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500"
                                        value="{{ old('olor', optional($registro->fisicos)->olor) }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Sabor</label>
                                    <input type="text" name="sabor"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500"
                                        value="{{ old('sabor', optional($registro->fisicos)->sabor) }}">
                                </div>

                                {{-- Fila 2 --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Turbidez (UNT)</label>
                                    <input type="number" step="0.01" name="turbidez"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500"
                                        value="{{ old('turbidez', optional($registro->fisicos)->turbidez) }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Conductividad (ms/cm)</label>
                                    <input type="number" step="0.01" name="conductividad_electrica"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500"
                                        value="{{ old('conductividad_electrica', optional($registro->fisicos)->conductividad_electrica) }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Sólidos Disueltos
                                        (mg/L)</label>
                                    <input type="number" step="0.01" name="solidos_disueltos"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500"
                                        value="{{ old('solidos_disueltos', optional($registro->fisicos)->solidos_disueltos) }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Sólidos en Suspensión
                                        (mg/L)</label>
                                    <input type="number" step="0.01" name="solidos_suspension"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500"
                                        value="{{ old('solidos_suspension', optional($registro->fisicos)->solidos_suspension) }}">
                                </div>
                            </div>

                            <div class="mt-6">
                                <label class="block font-medium text-sm text-gray-700">Observaciones</label>
                                <textarea name="observaciones" rows="2"
                                    class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full mt-1">{{ old('observaciones', $registro->observaciones) }}</textarea>
                            </div>

                            <div class="flex items-center justify-end mt-6 gap-4">
                                <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow transition">
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
                const editForm = document.getElementById('edit-fisicos-form');

                editForm.addEventListener('submit', function(event) {
                    event.preventDefault();

                    Swal.fire({
                        title: '¿Guardar Cambios?',
                        text: "Se actualizará la información de este registro.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#2563EB',
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
