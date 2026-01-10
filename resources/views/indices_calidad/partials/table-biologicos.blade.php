<div class="overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-500"> 
        <thead class="text-xs text-center text-gray-800 uppercase bg-purple-50">
            <tr>
                @if (isset($tiempo) && $tiempo == 'zonas_conteo')
                    <th class="px-4 py-3">Punto de Muestreo</th>
                    <th class="px-4 py-3">Prom. Coliformes Totales</th>
                    <th class="px-4 py-3">Prom. Coliformes Fecales</th>
                    <th class="px-4 py-3">Cant. Muestras</th>
                @else
                    <th class="px-4 py-3">Fecha y Hora</th>
                    <th class="px-4 py-3">Punto de Muestreo</th>
                    <th class="px-4 py-3">Colif. Totales</th>
                    <th class="px-4 py-3">Colif. Fecales</th>
                @endif
                <th class="px-4 py-3">Acciones</th>
            </tr>
        </thead>
        <tbody class="text-center">
            @forelse ($registros as $registro)
                <tr class="border-b hover:bg-gray-50 transition-colors">
                    @if (isset($tiempo) && $tiempo == 'zonas_conteo')
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $registro->punto_muestreo }}</td>
                        <td class="px-4 py-3 font-bold text-purple-600">
                            {{ number_format($registro->promedio_coliformes, 2) }} <span
                                class="text-xs italic">UFC</span>
                        </td>
                        <td class="px-4 py-3 font-bold text-purple-600">
                            {{ number_format($registro->promedio_fecales, 2) }} <span class="text-xs italic">UFC</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                {{ $registro->total_registros }} registros
                            </span>
                        </td>
                    @else
                        <td class="px-4 py-3">
                            <div class="flex flex-col items-center">
                                <span>{{ \Carbon\Carbon::parse($registro->fecha_muestreo)->format('d/m/Y') }}</span>
                                <span class="text-xs text-gray-500">{{ $registro->hora_muestreo }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-900 font-medium">
                            {{ $registro->punto_muestreo }}
                            <div class="text-[10px] text-gray-400 uppercase">Muestra #{{ $registro->numero_muestra }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            {{ optional($registro->biologicos)->coliformes_totales ?? '--' }}
                        </td>
                        <td class="px-4 py-3">
                            {{ optional($registro->biologicos)->coliformes_fecales ?? '--' }}
                        </td>
                    @endif

                    <td class="px-4 py-3">
                        @if (isset($tiempo) && $tiempo != 'general')
                            <span class="text-xs text-gray-400 italic">Resumen</span>
                        @else
                            <div class="flex items-center justify-center gap-3">
                                <a href="{{ route('indicesCalidad.showDetail', $registro->id) }}"
                                    class="text-gray-500 hover:text-gray-800 transition-colors" title="Ver detalle">
                                    <i class="fa-solid fa-eye"></i>
                                </a>

                                <a href="{{ route('indicesCalidad.edit', $registro->id) }}"
                                    class="text-blue-600 hover:text-blue-900 transition-colors" title="Editar">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>

                                @can('Eliminar Registros')
                                    <form action="{{ route('indicesCalidad.destroy', $registro->id) }}" method="POST"
                                        class="inline-block form-eliminar">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 transition-colors">
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
                    <td colspan="5" class="p-8 text-center text-gray-500 italic">
                        No se encontraron registros biológicos para los filtros seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
