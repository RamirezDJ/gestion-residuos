<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            @if (isset($tiempo) && $tiempo == 'cronologico')
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Mes / Año</th>
            @elseif(isset($tiempo) && $tiempo == 'zonas_conteo')
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Punto de Muestreo</th>
            @else
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Fecha y Hora</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Punto de Muestreo</th>
            @endif
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ isset($tiempo) && $tiempo != 'general' ? 'Promedio pH' : 'pH' }}
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ isset($tiempo) && $tiempo != 'general' ? 'Promedio Oxígeno (mg/L)' : 'Oxígeno (mg/L)' }}
            </th>

            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                Acciones</th>
        </tr>
    </thead>

    <tbody class="bg-white divide-y divide-gray-200">
        @forelse ($registros as $registro)
            <tr class="hover:bg-gray-50 transition-colors duration-200">

                @if (isset($tiempo) && $tiempo == 'cronologico')
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ \Carbon\Carbon::parse($registro->fecha_grupo)->format('F Y') }}
                    </td>
                @elseif(isset($tiempo) && $tiempo == 'zonas_conteo')
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $registro->punto_muestreo }}
                    </td>
                @else
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($registro->fecha_muestreo)->format('d/m/Y') }}</div>
                        <div class="text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($registro->hora_muestreo)->format('H:i') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $registro->punto_muestreo }}
                        <div class="text-xs text-gray-400">Muestra #{{ $registro->numero_muestra }}</div>
                    </td>
                @endif

                <td class="px-6 py-4 whitespace-nowrap">
                    @php
                        $valorPh =
                            isset($tiempo) && $tiempo != 'general'
                                ? $registro->promedio_ph
                                : optional($registro->quimicos)->ph;
                    @endphp
                    @if ($valorPh)
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $valorPh < 6.5 || $valorPh > 8.5 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                            {{ number_format($valorPh, 2) }}
                        </span>
                    @else
                        <span class="text-gray-400">-</span>
                    @endif
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ isset($tiempo) && $tiempo != 'general'
                        ? number_format($registro->promedio_oxigeno, 2)
                        : optional($registro->quimicos)->oxigeno_disuelto_ppm ?? '-' }}
                </td>

                <td class="px-4 py-3">
                    {{-- Mantenemos la lógica de filtros, pero unificamos el estilo visual --}}
                    @if (isset($tiempo) && $tiempo != 'general')
                        <span class="text-xs text-gray-400 italic">Ver detalle en lista general</span>
                    @else
                        <div class="flex items-center justify-center gap-3">
                            {{-- Ver Detalle --}}
                            <a href="{{ route('indicesCalidad.showDetail', $registro->id) }}"
                                class="text-gray-500 hover:text-gray-800 transition-colors duration-200"
                                title="Ver detalle">
                                <i class="fa-solid fa-eye"></i>
                            </a>

                            {{-- Editar --}}
                            <a href="{{ route('indicesCalidad.edit', $registro->id) }}"
                                class="text-blue-600 hover:text-blue-900 transition-colors duration-200"
                                title="Editar registro">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>

                            {{-- Eliminar --}}
                            @can('Eliminar Registros')
                                <form action="{{ route('indicesCalidad.destroy', $registro->id) }}" method="POST"
                                    class="inline-block form-eliminar">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-red-600 hover:text-red-800 transition-colors duration-200"
                                        title="Eliminar">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="p-4 text-center text-gray-500">No hay registros encontrados.</td>
            </tr>
        @endforelse
    </tbody>
</table>
