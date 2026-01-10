<x-app-layout>
    @push('css')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Registro de Parámetros Químicos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">

                    <div class="mb-6 flex justify-between items-center">
                        <div>
                            <h1 class="text-2xl font-medium text-gray-900">Edición de Datos Químicos</h1>
                            <p class="text-gray-500 mt-1">
                                Editando registro del: <span
                                    class="font-bold">{{ \Carbon\Carbon::parse($registro->fecha_muestreo)->format('d/m/Y') }}</span>
                            </p>
                        </div>
                        <a href="{{ route('indicesCalidad.show', ['tipo' => 'quimicos']) }}"
                            class="text-gray-500 hover:text-gray-700 flex items-center">
                            <i class="fa-solid fa-arrow-left mr-2"></i> Volver
                        </a>
                    </div>

                    <form action="{{ route('indicesCalidad.update', $registro->id) }}" method="POST"
                        id="edit-quimicos-form">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="tipo_registro" value="quimicos">

                        <div class="bg-gray-50 p-4 rounded-lg mb-6 border border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Datos Generales</h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Fecha</label>
                                    <input type="date" name="fecha_muestreo" required
                                        class="border-gray-300 ffocus:border-green-500 focus:ring-green-500 rounded-md shadow-sm w-full mt-1"
                                        value="{{ old('fecha_muestreo', $registro->fecha_muestreo) }}">
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Hora</label>
                                    <input type="time" name="hora_muestreo" required
                                        class="border-gray-300 ffocus:border-green-500 focus:ring-green-500 rounded-md shadow-sm w-full mt-1"
                                        value="{{ old('hora_muestreo', $registro->hora_muestreo) }}">
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Punto de Muestreo</label>
                                    <select name="punto_muestreo" required
                                        class="border-gray-300 ffocus:border-green-500 focus:ring-green-500 rounded-md shadow-sm w-full mt-1">
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
                                        class="border-gray-300 ffocus:border-green-500 focus:ring-green-500 rounded-md shadow-sm w-full mt-1"
                                        value="{{ old('numero_muestra', $registro->numero_muestra) }}">
                                </div>
                            </div>
                        </div>
                        <div class="bg-green-50 p-6 rounded-lg border order-green-200 mb-6">
                            <div class="flex items-center mb-6">
                                <h3 class="text-xl font-bold text-green-800">Parámetros Químicos</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">pH</label>
                                    <input type="number" step="0.01" name="ph"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm ffocus:border-green-500"
                                        value="{{ old('ph', optional($registro->quimicos)->ph) }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Oxígeno Disuelto
                                        (ppm)</label>
                                    <input type="number" step="0.01" name="oxigeno_disuelto_ppm"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm ffocus:border-green-500"
                                        value="{{ old('oxigeno_disuelto_ppm', optional($registro->quimicos)->oxigeno_disuelto_ppm) }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">DBO (mg/L)</label>
                                    <input type="number" step="0.01" name="dbo"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm ffocus:border-green-500"
                                        value="{{ old('dbo', optional($registro->quimicos)->dbo) }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">DQO (mg/L)</label>
                                    <input type="number" step="0.01" name="dqo"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm ffocus:border-green-500"
                                        value="{{ old('dqo', optional($registro->quimicos)->dqo) }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nitratos (mg/L)</label>
                                    <input type="number" step="0.01" name="nitratos"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm ffocus:border-green-500"
                                        value="{{ old('nitratos', optional($registro->quimicos)->nitratos) }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nitritos (mg/L)</label>
                                    <input type="number" step="0.01" name="nitritos"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm ffocus:border-green-500"
                                        value="{{ old('nitritos', optional($registro->quimicos)->nitritos) }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Fosfatos (mg/L)</label>
                                    <input type="number" step="0.01" name="fosfatos"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm ffocus:border-green-500"
                                        value="{{ old('fosfatos', optional($registro->quimicos)->fosfatos) }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Cloro Libre (mg/L)</label>
                                    <input type="number" step="0.01" name="cloro_libre"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm ffocus:border-green-500"
                                        value="{{ old('cloro_libre', optional($registro->quimicos)->cloro_libre) }}">
                                </div>

                            </div>

                            <div class="mt-6">
                                <label class="block font-medium text-sm text-gray-700">Observaciones</label>
                                <textarea name="observaciones" rows="2"
                                    class="border-gray-300 ffocus:border-green-500 focus:ring-green-500 rounded-md shadow-sm w-full mt-1">{{ old('observaciones', $registro->observaciones) }}</textarea>
                            </div>

                            <div class="flex items-center justify-end mt-6 gap-4">
                                <button type="submit"
                                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg shadow transition">
                                    Actualizar Químicos
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
                const editForm = document.getElementById('edit-quimicos-form');
                editForm.addEventListener('submit', function(event) {
                    event.preventDefault();
                    Swal.fire({
                        title: '¿Guardar Cambios?',
                        text: "Se actualizará la información química.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#16A34A',
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
