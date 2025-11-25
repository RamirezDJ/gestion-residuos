<table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
    <thead class="text-xs text-center text-gray-800 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
        <tr>
            <th scope="col" class="px-4 py-3">Fecha Inicio</th>
            <th scope="col" class="px-4 py-3">Fecha Final</th>
            <th scope="col" class="px-4 py-3">Total Generado</th>
            <th scope="col" class="px-4 py-3">Acciones</th>
        </tr>
    </thead>
    <tbody class="text-center">
        @forelse ($registroPeriodo as $registro)
            <tr class="border-b dark:border-gray-900">
                {{-- FECHAS --}}
                <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    {{ \Carbon\Carbon::parse($registro->fecha_inicio)->format('d/m/Y') }}
                </td>
                <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    {{ \Carbon\Carbon::parse($registro->fecha_final)->format('d/m/Y') }}
                </td>

                {{-- TOTAL --}}
                <td class="px-4 py-3">
                    {{ number_format($registro->total_kg, 2) }} kg
                </td>

                {{-- ACCIONES --}}
                <td class="px-4 py-3 flex items-center justify-center gap-2">
                    <a href="{{ route('gensubproductos.editAll', ['instituto_id' => $registro->instituto_id, 'inicio' => $registro->fecha_inicio, 'final' => $registro->fecha_final]) }}"
                        class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <a href="{{ route('gensubproductos.showAll', ['instituto_id' => $registro->instituto_id, 'inicio' => $registro->fecha_inicio, 'final' => $registro->fecha_final]) }}"
                        class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                    @can('Eliminar Registros')
                        <form class="delete-week-form"
                            action="{{ route('gensubproductos.destroyWeek', ['fecha' => $registro->fecha_inicio]) }}"
                            method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar Periodo">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="py-4 text-center text-gray-500">
                    No se encontraron registros.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
