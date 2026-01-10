<x-app-layout>
    <section class="bg-gray-50 dark:bg-gray-900 p-3 sm:p-5">
        <div class="px-4 pt-5 pb-5 mx-auto sm:max-w-xl md:max-w-full lg:max-w-screen-xl md:px-24 lg:px-8">
            <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 p-4">
                    <div class="bg-white overflow-hidden shadow rounded-lg border col-span-1 h-full">
                        <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Datos del Muestreo
                            </h3>
                            <p class="mt-1 max-w-2xl text-xs text-gray-500">
                                Identificación y trazabilidad.
                            </p>
                        </div>
                        <div class="px-4 py-5 sm:p-0">
                            <dl class="divide-y divide-gray-200">
                                <div class="py-3 sm:py-4 px-4">
                                    <dt class="text-xs font-medium text-gray-500 uppercase">Punto</dt>
                                    <dd class="mt-1 text-sm font-bold text-gray-900">{{ $registro->punto_muestreo }}</dd>
                                </div>

                                <div class="py-3 sm:py-4 px-4">
                                    <dt class="text-xs font-medium text-gray-500 uppercase">Fecha / Hora</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($registro->fecha_muestreo)->format('d/m/Y') }}
                                        <span class="text-gray-500 text-xs block">{{ $registro->hora_muestreo }}</span>
                                    </dd>
                                </div>

                                <div class="py-3 sm:py-4 px-4">
                                    <dt class="text-xs font-medium text-gray-500 uppercase">N° Muestra</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $registro->numero_muestra }}</dd>
                                </div>
                            </dl>
                        </div>
                        <div class="border-t bg-gray-50 px-4 py-4">
                            <dt class="text-xs font-medium text-gray-500 uppercase">Responsable</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-medium">
                                {{ $registro->responsable->name ?? 'No registrado' }}
                            </dd>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow rounded-lg border col-span-1 lg:col-span-3 h-full">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200 flex justify-between items-center">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Resultados Bacteriológicos
                                </h3>
                                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                    Valores obtenidos en el análisis microbiológico.
                                </p>
                            </div>
                            <span class="bg-purple-100 text-purple-800 text-xs font-semibold px-2.5 py-0.5 rounded border border-purple-400">
                                Biológicos
                            </span>
                        </div>

                        <div class="p-6">
                            @if ($registro->biologicos)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div class="bg-purple-50 p-6 rounded-lg border border-purple-100 flex flex-col items-center text-center">
                                        <p class="text-sm font-medium text-purple-700 uppercase mb-1">Coliformes Totales</p>
                                        <p class="text-3xl font-black text-gray-800">
                                            {{ number_format($registro->biologicos->coliformes_totales, 2) }}
                                            <span class="text-sm text-gray-400 font-normal block mt-1 uppercase">UFC / 100ml</span>
                                        </p>
                                    </div>
                                    <div class="bg-purple-50 p-6 rounded-lg border border-purple-100 flex flex-col items-center text-center">
                                        <p class="text-sm font-medium text-purple-700 uppercase mb-1">Coliformes Fecales</p>
                                        <p class="text-3xl font-black text-gray-800">
                                            {{ number_format($registro->biologicos->coliformes_fecales, 2) }}
                                            <span class="text-sm text-gray-400 font-normal block mt-1 uppercase">UFC / 100ml</span>
                                        </p>
                                    </div>

                                </div>
                                <div class="mt-8 border-t pt-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Observaciones del Análisis</h4>
                                    <div class="p-4 bg-gray-50 rounded-md border border-gray-100 text-sm text-gray-600 italic">
                                        {{ $registro->observaciones ?? 'Sin observaciones adicionales registradas.' }}
                                    </div>
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center py-12">
                                    <i class="fa-solid fa-triangle-exclamation text-4xl text-gray-300 mb-4"></i>
                                    <p class="text-gray-500 font-medium">No se han encontrado parámetros biológicos vinculados a este muestreo.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</x-app-layout>