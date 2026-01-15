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
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Resultados Paramétricos
                                </h3>
                                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                    Valores obtenidos en el análisis físico.
                                </p>
                            </div>
                            <span
                                class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded border border-blue-400">
                                Físicos
                            </span>
                        </div>

                        <div class="p-6">
                            @if ($registro->fisicos)
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

                                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                                        <p class="text-sm font-medium text-gray-500">Temperatura</p>
                                        <p class="text-2xl font-bold text-blue-800">
                                            {{ $registro->fisicos->temperatura ?? '--' }}
                                            <span class="text-sm text-gray-400 font-normal">°C</span>
                                        </p>
                                    </div>

                                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                                        <p class="text-sm font-medium text-gray-500">Conductividad</p>
                                        <p class="text-2xl font-bold text-blue-800">
                                            {{ $registro->fisicos->conductividad_electrica ?? '--' }}
                                            <span class="text-sm text-gray-400 font-normal">µS/cm</span>
                                        </p>
                                    </div>

                                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                                        <p class="text-sm font-medium text-gray-500">Turbidez</p>
                                        <p class="text-2xl font-bold text-blue-800">
                                            {{ $registro->fisicos->turbidez ?? '--' }}
                                            <span class="text-sm text-gray-400 font-normal">NTU</span>
                                        </p>
                                    </div>

                                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                                        <p class="text-sm font-medium text-gray-500">Olor</p>
                                        <p class="text-lg font-semibold text-blue-800 break-words mt-1">
                                            {{ $registro->fisicos->olor ?? '--' }}
                                        </p>
                                    </div>

                                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                                        <p class="text-sm font-medium text-gray-500">Sabor</p>
                                        <p class="text-lg font-semibold text-blue-800 break-words mt-1">
                                            {{ $registro->fisicos->sabor ?? '--' }}
                                        </p>
                                    </div>

                                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Color
                                        </dt>
                                        <p class="text-lg font-semibold text-blue-800 break-words mt-1">
                                            {{ $registro->fisicos->color ?? '--' }}
                                        </p>
                                    </div>

                                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                                        <p class="text-sm font-medium text-gray-500">Sólidos Disueltos</p>
                                        <p class="text-2xl font-bold text-blue-800">
                                            {{ $registro->fisicos->solidos_disueltos ?? '--' }}
                                            <span class="text-sm text-gray-400 font-normal">mg/L</span>
                                        </p>
                                    </div>

                                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                                        <p class="text-sm font-medium text-gray-500">Sólidos en Suspensión</p>
                                        <p class="text-2xl font-bold text-blue-800">
                                            {{ $registro->fisicos->solidos_suspension ?? '--' }}
                                            <span class="text-sm text-gray-400 font-normal">mg/L</span>
                                        </p>
                                    </div>

                                </div>

                                <div class="mt-8 border-t pt-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Observaciones Generales</h4>
                                    <div class="p-4 bg-blue-50 rounded-md border border-blue-100 text-sm text-blue-800">
                                        {{ $registro->observaciones ?? 'Sin observaciones registradas.' }}
                                    </div>
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center py-12">
                                    <p class="text-gray-500 font-medium">No se han cargado datos físicos.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</x-app-layout>
