<x-app-layout>
    <section class="bg-gray-50 dark:bg-gray-900 p-3 sm:p-5">
        <div class="px-4 pt-5 pb-5 mx-auto sm:max-w-xl md:max-w-full lg:max-w-screen-xl md:px-24 lg:px-8">
            <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                <div class="grid grid-cols-4 gap-4 p-4">

                    <div class="bg-white overflow-hidden shadow rounded-lg border col-span-1">
                        <div class="px-4 py-5 sm:px-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Resumen Semanal
                            </h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Resumen de los datos generados del instituto.
                            </p>
                        </div>
                        <div class="border-t border-gray-200 px-3 py-5 sm:p-0">
                            <dl class="sm:divide-y sm:divide-gray-200">
                                <div class="py-3 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-4">
                                    <dt class="text-sm font-medium text-gray-500">
                                        Fecha
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-1">
                                        {{ $fechaInicioFormateada }} - {{ $fechaFinFormateada }}
                                    </dd>
                                </div>
                                <div class="py-3 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-4">
                                    <dt class="text-sm font-medium text-gray-500">
                                        Total generado (semana)
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-1">
                                        {{ number_format($totalGeneradoSemana, 2) }} kg
                                    </dd>
                                </div>
                                <div class="py-3 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-4">
                                    <dt class="text-sm font-medium text-gray-500">
                                        Zona con mayor generación (semana)
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-1">
                                        {{ $zonaMayorNombreSemana }}
                                        ({{ number_format($zonaMayorTotalSemana, 2) }} kg)
                                    </dd>
                                </div>
                                <div class="py-3 sm:py-5 sm:gap-4 sm:px-4">
                                    <dt class="text-sm font-medium text-gray-500 mb-2">
                                        Instituto
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0">
                                        {{ $instituto->nombre }}
                                    </dd>
                                </div>
                                <div class="p-4">
                                    <dt class=" text-sm font-medium text-gray-500 mb-4">
                                        Descargar archivo
                                    </dt>
                                    <div class="flex justify-center gap-4">

                                        <a href="{{ route('gensemanal.pdf', ['fecha' => $fechaInicioSemana]) }}"
                                            target="_blank"
                                            class="bg-red-500 hover:bg-red-600 text-white p-3 rounded shadow">
                                            <i class="fa-solid fa-file-pdf"></i> En PDF
                                        </a>

                                        <a href="{{ route('gensemanal.excel', ['fecha' => $fechaInicioSemana]) }}"
                                            class="bg-green-500 hover:bg-green-600 text-white p-3 rounded shadow">
                                            <i class="fa-solid fa-file-excel"></i> En Excel
                                        </a>
                                    </div>
                                </div>
                            </dl>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow rounded-lg border col-span-3">
                        <div class="px-4 py-5 sm:px-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Desglose Diario de la Semana
                            </h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Kilos generados por día, zona, área y subproducto.
                            </p>
                        </div>
                        <div class="border-t border-gray-200 px-4 py-5 sm:p-0 max-h-[500px] overflow-y-auto">
                            <div class="p-4 space-y-6">

                                @php
                                    // LE DECIMOS A CARBON QUE EL FORMATO ES 'd/m/Y'
                                    $fechaActual = \Carbon\Carbon::createFromFormat(
                                        'd/m/Y',
                                        $fechaInicioFormateada,
                                        'Europe/London',
                                    );
                                    $fechaFin = \Carbon\Carbon::createFromFormat(
                                        'd/m/Y',
                                        $fechaFinFormateada,
                                        'Europe/London',
                                    );
                                @endphp

                                @while ($fechaActual <= $fechaFin)
                                    @php
                                        $fechaIter = $fechaActual->format('Y-m-d');
                                        $datosDelDia = $lookupDataSemanal[$fechaIter] ?? []; // Busca los datos para ESTE día
                                    @endphp

                                    {{-- Quitamos el 'if empty' para mostrar todos los días aunque estén vacíos --}}
                                    <div
                                        class="border rounded-md p-4 shadow-sm {{ empty($datosDelDia) ? 'bg-gray-50' : '' }}">
                                        <h4
                                            class="text-md font-semibold text-gray-800 mb-3 border-b pb-2 flex justify-between">
                                            <span>
                                                {{ $fechaActual->isoFormat('dddd, D [de] MMMM') }}
                                                ({{ $fechaActual->format('d/m/Y') }})
                                            </span>
                                            @if (empty($datosDelDia))
                                                <span class="text-xs font-normal text-gray-400 italic">Sin registros
                                                    capturados</span>
                                            @endif
                                        </h4>

                                        @foreach ($zonas as $zona)
                                            <div class="mb-4">
                                                <p class="text-sm font-bold text-gray-700 mb-2">{{ $zona->nombre }}</p>

                                                <div class="grid grid-cols-3 gap-x-4 gap-y-2 pl-4">
                                                    @foreach ($zona->areas as $area)
                                                        <div>
                                                            <p class="text-xs font-semibold text-gray-600 mb-1">
                                                                {{ $area->nombre }}
                                                            </p>

                                                            <div class="pl-2 border-l">
                                                                {{-- ▼▼ AQUÍ ESTÁ EL CAMBIO: FILTRADO POR ÁREA ▼▼ --}}
                                                                @php
                                                                    // Obtenemos solo las categorías configuradas para esta área
                                                                    $categoriasDelArea = $area->subproductos
                                                                        ->pluck('categoria')
                                                                        ->unique('id')
                                                                        ->sortBy('nombre');
                                                                @endphp

                                                                @foreach ($categoriasDelArea as $categoria)
                                                                    @if ($categoria)
                                                                        @php
                                                                            $kilos =
                                                                                $datosDelDia[$area->id][
                                                                                    $categoria->id
                                                                                ] ?? 0;
                                                                        @endphp

                                                                        {{-- Mostramos siempre, incluso si es 0, porque pertenece al área --}}
                                                                        <div
                                                                            class="text-xs flex justify-between border-b border-gray-100 py-0.5">
                                                                            <span
                                                                                class="text-gray-500">{{ $categoria->nombre }}:</span>
                                                                            <span
                                                                                class="font-medium {{ $kilos > 0 ? 'text-gray-800' : 'text-gray-400' }}">
                                                                                {{ number_format($kilos, 2) }} kg
                                                                            </span>
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                                {{-- ▲▲ FIN DEL CAMBIO ▲▲ --}}
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    @php $fechaActual->addDay(); @endphp
                                @endwhile
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
