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

                    {{-- Encabezado de la sección y Botón de Crear --}}
                    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
                        <div>
                            <h3 class="text-xl font-bold text-gray-700">Registros Diarios</h3>
                            <p class="text-sm text-gray-500 mt-1">Consulta y gestion de los registros Per Capita diario.
                            </p>
                        </div>

                        {{-- BOTÓN CREAR NUEVO REGISTRO --}}
                        <a href="{{ route('metaAnual.percapita.create') }}"
                            class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition">
                            <i class="fas fa-plus mr-2"></i> Nuevo Registro
                        </a>
                    </div>

                    {{-- Tabla de Registros --}}
                    <div class="mt-4">
                        <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-center text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="py-3 px-6">Fecha</th>
                                        <th scope="col" class="py-3 px-6">Visitantes</th>
                                        <th scope="col" class="py-3 px-6">Trabajadores</th>
                                        <th scope="col" class="py-3 px-6">Total Personas</th>
                                        <th scope="col" class="py-3 px-6">Residuos (Kg)</th>
                                        <th scope="col" class="py-3 px-6">Per Cápita</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Usamos la variable $registrosPerCapita que enviamos desde el controlador --}}
                                    @forelse ($registrosPerCapita as $registro)
                                        <tr class="bg-white border-b hover:bg-gray-50 text-center">
                                            {{-- Fecha --}}
                                            <td class="py-4 px-6 text-gray-900 font-medium">
                                                {{ \Carbon\Carbon::parse($registro->fecha)->format('d/m/Y') }}
                                            </td>

                                            {{-- Visitantes --}}
                                            <td class="py-4 px-6">
                                                {{ number_format($registro->visitantes) }}
                                            </td>

                                            {{-- Trabajadores --}}
                                            <td class="py-4 px-6">
                                                {{ number_format($registro->trabajadores) }}
                                            </td>

                                            {{-- Total Personas (Calculado en la vista) --}}
                                            <td class="py-4 px-6">
                                                {{ number_format($registro->visitantes + $registro->trabajadores) }}
                                            </td>

                                            {{-- Residuos (kilos_residuos en la BD) --}}
                                            <td class="py-4 px-6">
                                                {{ number_format($registro->kilos_residuos, 2) }} kg
                                            </td>

                                            {{-- Per Cápita (Resaltado) --}}
                                            <td class="py-4 px-6">
                                                <span
                                                    class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                                    {{ number_format($registro->per_capita, 4) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="py-8 text-center text-gray-500">
                                                <div class="flex flex-col items-center justify-center">
                                                    <i class="fas fa-folder-open text-4xl mb-3 text-gray-300"></i>
                                                    <p>No hay registros diarios capturados aún.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $registrosPerCapita->links() }}
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
</x-app-layout>
