<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                {{ __('Resultado del Reporte Per Cápita Diario') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    <h3 class="text-xl font-bold text-gray-700 mb-6">Reporte para el día {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg shadow">
                            <p class="text-sm font-medium text-gray-500">Total de Personas (Visitantes + Trabajadores)</p>
                            <p class="text-3xl font-bold text-gray-900">{{ number_format($totalPersonas, 0) }}</p>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg shadow">
                            <p class="text-sm font-medium text-gray-500">Generación Total de Residuos (kg)</p>
                            <p class="text-3xl font-bold text-gray-900">{{ number_format($residuos, 2) }} kg</p>
                        </div>
                        
                        <div class="md:col-span-2 bg-blue-100 p-6 rounded-lg shadow-lg border-l-4 border-blue-500">
                            <p class="text-lg font-semibold text-blue-700">Generación Per Cápita Diario</p>
                            <p class="text-5xl font-extrabold text-blue-900 mt-2">{{ number_format($generacionPercapita, 3) }} kg</p>
                            <p class="text-sm text-blue-700 mt-1">Kilogramos de residuos generados por persona por día.</p>
                        </div>
                    </div>

                    <h4 class="text-lg font-bold text-gray-700 mt-8 mb-4">Detalles Ingresados</h4>
                    <ul class="space-y-2 text-gray-600">
                        <li>**No. Visitantes:** {{ number_format($visitantes) }}</li>
                        <li>**No. Trabajadores:** {{ number_format($trabajadores) }}</li>
                        <li>**Generación de Residuos (kg):** {{ number_format($residuos, 2) }}</li>
                    </ul>

                    <div class="mt-8">
                        <a href="{{ route('reporteDiario.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            <i class="fas fa-redo-alt mr-2"></i> Nuevo Reporte
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>