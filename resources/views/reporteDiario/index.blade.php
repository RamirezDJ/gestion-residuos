<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                {{ __('Reporte Per Cápita Diario') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    <h3 class="text-xl font-bold text-gray-700 mb-6">Ingreso de Datos para Reporte</h3>

                    <form action="{{ route('reporteDiario.generar') }}" method="POST">
                        @csrf

                        <div class="space-y-4">
                            <div>
                                <label for="fecha" class="block text-sm font-medium text-gray-700">Día del Reporte</label>
                                <input type="date" name="fecha" id="fecha" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('fecha') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="visitantes" class="block text-sm font-medium text-gray-700">No. Visitantes por día</label>
                                <input type="number" name="visitantes" id="visitantes" required min="0" step="1"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Ej: 444">
                                @error('visitantes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="trabajadores" class="block text-sm font-medium text-gray-700">No. de trabajadores por día</label>
                                <input type="number" name="trabajadores" id="trabajadores" required min="0" step="1"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Ej: 52">
                                @error('trabajadores') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="residuos" class="block text-sm font-medium text-gray-700">Generación de residuos por día (kg)</label>
                                <input type="number" name="residuos" id="residuos" required min="0" step="0.01"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Ej: 42.47">
                                @error('residuos') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Generar Reporte
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>