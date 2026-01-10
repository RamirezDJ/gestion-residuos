<x-app-layout>
    <section class="bg-gray-50 dark:bg-gray-900 p-3 sm:p-5">
        <div class="px-4 pt-5 pb-5 mx-auto sm:max-w-xl md:max-w-full lg:max-w-screen-xl md:px-24 lg:px-8">

            <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 p-4">

                    <div class="bg-white overflow-hidden shadow rounded-lg border col-span-1 h-full">
                        <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Datos del Muestreo</h3>
                            <p class="mt-1 max-w-2xl text-xs text-gray-500">Identificación y trazabilidad.</p>
                        </div>
                        <div class="px-4 py-5 sm:p-0">
                            <dl class="divide-y divide-gray-200">
                                <div class="py-3 sm:py-4 px-4">
                                    <dt class="text-xs font-medium text-gray-500 uppercase">Punto</dt>
                                    <dd class="mt-1 text-sm font-bold text-gray-900">{{ $registro->punto_muestreo }}
                                    </dd>
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
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Resultados Paramétricos</h3>
                                <p class="mt-1 max-w-2xl text-sm text-gray-500">Valores obtenidos en el análisis
                                    químico.</p>
                            </div>
                            <span
                                class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded border border-green-400">
                                Químicos
                            </span>
                        </div>

                        <div class="p-6">
                            @if ($registro->quimicos)
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                                    <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                                        <p class="text-sm font-medium text-gray-500">pH</p>
                                        <p class="text-2xl font-bold text-green-800">
                                            {{ $registro->quimicos->ph ?? '--' }}
                                            <span class="text-xs text-gray-400 font-normal">Unid.</span>
                                        </p>
                                    </div>

                                    <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                                        <p class="text-sm font-medium text-gray-500">Oxígeno Disuelto</p>
                                        <p class="text-2xl font-bold text-green-800">
                                            {{ $registro->quimicos->oxigeno_disuelto_ppm ?? '--' }}
                                            <span class="text-sm text-gray-400 font-normal">mg/L</span>
                                        </p>
                                    </div>

                                    <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                                        <p class="text-sm font-medium text-gray-500">DBO</p>
                                        <p class="text-2xl font-bold text-green-800">
                                            {{ $registro->quimicos->dbo ?? '--' }}
                                            <span class="text-sm text-gray-400 font-normal">mg/L</span>
                                        </p>
                                    </div>

                                    <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                                        <p class="text-sm font-medium text-gray-500">DQO</p>
                                        <p class="text-2xl font-bold text-green-800">
                                            {{ $registro->quimicos->dqo ?? '--' }}
                                            <span class="text-sm text-gray-400 font-normal">mg/L</span>
                                        </p>
                                    </div>

                                    <div class="bg-white p-4 rounded-lg border border-gray-100 shadow-sm">
                                        <p class="text-sm font-medium text-gray-500">Nitratos</p>
                                        <p class="text-2xl font-bold text-gray-800">
                                            {{ $registro->quimicos->nitratos ?? '--' }}
                                            <span class="text-sm text-gray-400 font-normal">mg/L</span>
                                        </p>
                                    </div>

                                    <div class="bg-white p-4 rounded-lg border border-gray-100 shadow-sm">
                                        <p class="text-sm font-medium text-gray-500">Nitritos</p>
                                        <p class="text-2xl font-bold text-gray-800">
                                            {{ $registro->quimicos->nitritos ?? '--' }}
                                            <span class="text-sm text-gray-400 font-normal">mg/L</span>
                                        </p>
                                    </div>

                                    <div class="bg-white p-4 rounded-lg border border-gray-100 shadow-sm">
                                        <p class="text-sm font-medium text-gray-500">Fosfatos</p>
                                        <p class="text-2xl font-bold text-gray-800">
                                            {{ $registro->quimicos->fosfatos ?? '--' }}
                                            <span class="text-sm text-gray-400 font-normal">mg/L</span>
                                        </p>
                                    </div>

                                    <div class="bg-white p-4 rounded-lg border border-gray-100 shadow-sm">
                                        <p class="text-sm font-medium text-gray-500">Cloro Libre</p>
                                        <p class="text-2xl font-bold text-gray-800">
                                            {{ $registro->quimicos->cloro_libre ?? '--' }}
                                            <span class="text-sm text-gray-400 font-normal">mg/L</span>
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-8 border-t pt-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Observaciones Generales</h4>
                                    <div
                                        class="p-4 bg-green-50 rounded-md border border-green-100 text-sm text-green-800">
                                        {{ $registro->observaciones ?? 'Sin observaciones registradas.' }}
                                    </div>
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center py-12">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-4"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                    </svg>
                                    <p class="text-gray-500 font-medium">No se han encontrado parámetros químicos
                                        vinculados a este muestreo.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</x-app-layout>
