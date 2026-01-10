<div class="overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
        <thead class="text-xs text-center text-gray-800 uppercase bg-blue-50">
            <tr>
                @if (isset($tiempo) && $tiempo == 'zonas_conteo')
                    <th class="px-4 py-3">Punto de Muestreo</th>
                    <th class="px-4 py-3">Promedio Turbidez</th>
                    <th class="px-4 py-3">Cant. Muestras</th>
                    <th class="px-4 py-3">Última Fecha</th>
                @else
                    <th class="px-4 py-3">Fecha y Hora</th>
                    <th class="px-4 py-3">Punto de Muestreo</th>
                    <th class="px-4 py-3">Turbidez</th>
                @endif
                <th class="px-4 py-3">Acciones</th>
            </tr>
        </thead>
        <tbody class="text-center">
            @forelse ($registros as $registro)
                <tr class="border-b hover:bg-gray-50">

                    @if (isset($tiempo) && $tiempo == 'zonas_conteo')
                        <td class="px-4 py-3 font-medium text-gray-900">
                            {{ $registro->punto_muestreo }}
                        </td>
                        <td class="px-4 py-3 font-bold text-blue-600">
                            {{ number_format($registro->promedio_turbidez, 2) }} UNT
                        </td>
                        <td class="px-4 py-3">
                            {{ $registro->total_registros }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            {{ \Carbon\Carbon::parse($registro->ultima_fecha)->format('d/m/Y') }}
                        </td>
                    @else
                        <td class="px-4 py-3 font-medium">
                            <div class="flex flex-col items-center">
                                <span>{{ \Carbon\Carbon::parse($registro->fecha_muestreo)->format('d/m/Y') }}</span>
                                <span class="text-xs text-gray-500">{{ $registro->hora_muestreo }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            {{ $registro->punto_muestreo }}
                            <div class="text-xs text-gray-400">Muestra #{{ $registro->numero_muestra }}</div>
                        </td>
                        <td class="px-4 py-3 font-bold text-gray-700">
                            {{ optional($registro->fisicos)->turbidez ?? '--' }} UNT
                        </td>
                    @endif
                    <td class="px-4 py-3">
                        @if (isset($tiempo) && $tiempo == 'zonas_conteo')
                            <span class="text-xs text-gray-400 italic">Ver detalle en lista general</span>
                        @else
                            <div class="flex items-center justify-center gap-3">
                                <a href="{{ route('indicesCalidad.showDetail', $registro->id) }}"
                                    class="text-gray-500 hover:text-gray-800 transition-colors duration-200"
                                    title="Ver detalle">
                                    <i class="fa-solid fa-eye"></i>
                                </a>

                                <a href="{{ route('indicesCalidad.edit', $registro->id) }}"
                                    class="text-blue-600 hover:text-blue-900 transition-colors duration-200"
                                    title="Editar registro">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>

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
</div>

@if (method_exists($registros, 'links'))
    <div class="p-4">
        {{ $registros->appends(['tiempo' => $tiempo ?? 'general', 'tipo' => 'fisicos'])->links() }}
    </div>
@endif
