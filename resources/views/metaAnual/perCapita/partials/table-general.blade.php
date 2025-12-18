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
                <th scope="col" class="py-3 px-6">Acciones</th> {{-- Nueva Columna --}}
            </tr>
        </thead>
        <tbody>
            @forelse ($registrosPerCapita as $registro)
                <tr class="bg-white border-b hover:bg-gray-50 text-center">
                    {{-- Fecha --}}
                    <td class="py-4 px-6 text-gray-900 font-medium">
                        {{ \Carbon\Carbon::parse($registro->fecha)->format('d/m/Y') }}
                    </td>

                    {{-- Datos --}}
                    <td class="py-4 px-6">{{ number_format($registro->visitantes) }}</td>
                    <td class="py-4 px-6">{{ number_format($registro->trabajadores) }}</td>
                    <td class="py-4 px-6">{{ number_format($registro->visitantes + $registro->trabajadores) }}</td>
                    <td class="py-4 px-6">{{ number_format($registro->kilos_residuos, 2) }} kg</td>

                    {{-- Per Cápita --}}
                    <td class="py-4 px-6">
                        <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                            {{ number_format($registro->per_capita, 4) }}
                        </span>
                    </td>

                    {{-- ACCIONES --}}
                    <td class="py-4 px-6">
                        <div class="flex items-center justify-center gap-3">
                            {{-- Botón Editar --}}
                            <a href="{{ route('metaAnual.percapita.edit', $registro->id) }}"
                                class="text-gray-600 hover:text-blue-900 transition" title="Editar Registro">
                                <i class="fas fa-pen-to-square"></i>
                            </a>

                            {{-- Botón Ver (Opcional, si tienes vista de detalle)
                            <a href="{{ route('metaAnual.percapita.show', $registro->id) }}"
                                class="text-gray-600 hover:text-gray-900 transition" title="Ver Detalles">
                                <i class="fas fa-eye"></i>
                            </a> --}}

                            {{-- Botón Eliminar --}}
                            @can('Eliminar Registros')
                                {{-- O el permiso que uses --}}
                                <form action="{{ route('metaAnual.percapita.destroy', $registro->id) }}" method="POST"
                                    onsubmit="return confirm('¿Estás seguro de que deseas eliminar este registro?');"
                                    style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 transition"
                                        title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-8 text-center text-gray-500">
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

{{-- Paginación fuera de la tabla --}}
<div class="mt-4">
    {{ $registrosPerCapita->links() }}
</div>
