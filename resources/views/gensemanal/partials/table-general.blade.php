<table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
    <thead class="text-xs text-center text-gray-800 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
        <tr>
            <th scope="col" class="px-4 py-3">Fecha Inicio (Semana)</th>
            <th scope="col" class="px-4 py-3">Fecha Final (Semana)</th>
            <th scope="col" class="px-4 py-3">Total Kilos Generados</th>
            <th scope="col" class="px-4 py-3">Acciones</th>
        </tr>
    </thead>
    <tbody class="text-center">
        @foreach ($registros as $registro)
            <tr class="border-b dark:border-gray-900">

                {{-- Celdas de datos (sin cambios) --}}
                <td class="px-4 py-3">
                    {{ $registro->fecha_inicio }}
                </td>
                <td class="px-4 py-3">
                    {{ $registro->fecha_final }}
                </td>
                <td class="px-4 py-3">
                    {{ number_format($registro->total_kilos_semana, 2) }}
                </td>

                {{-- Celda de Acciones --}}
                <td class="px-4 py-3 flex items-center justify-center gap-2">

                    {{-- Enlaces (sin cambios) --}}
                    <a href="{{ route('gensemanal.showAll', ['fecha' => $registro->fecha_inicio]) }}">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                    <a href="{{ route('gensemanal.editWeek', ['fecha' => $registro->fecha_inicio]) }}">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>

                    @can('Eliminar Registros')
                        {{-- FORMULARIO MODIFICADO --}}
                        <form {{-- 1. AÑADIMOS UNA CLASE --}} class="delete-week-form"
                            action="{{ route('gensemanal.destroyWeek', ['fecha' => $registro->fecha_inicio]) }}"
                            method="POST" {{-- 2. QUITAMOS EL onsubmit="..." --}} style="display: inline;">

                            @csrf
                            @method('DELETE')

                            <button type="submit" style="background:none; border:none; color:red; cursor:pointer;"
                                title="Eliminar Semana">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    @endcan

                </td>
            </tr>
        @endforeach
    </tbody>
</table>
