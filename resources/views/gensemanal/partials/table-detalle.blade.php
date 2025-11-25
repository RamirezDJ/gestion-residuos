<table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
    <thead class="text-xs text-center text-gray-800 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
        <tr>
            <th scope="col" class="px-4 py-3">Fecha</th>
            <th scope="col" class="px-4 py-3">Zona</th>
            <th scope="col" class="px-4 py-3">Area asignada</th>
            <th scope="col" class="px-4 py-3">Turno</th>
            <th scope="col" class="px-4 py-3">Total generado</th>
            {{-- SE ELIMINÓ LA COLUMNA ACCIONES AQUÍ --}}
        </tr>
    </thead>
    <tbody class="text-center">
        @foreach ($registros as $registro)
            <tr class="border-b dark:border-gray-900">

                <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    {{ \Carbon\Carbon::parse($registro->fecha)->format('d/m/Y') }}
                </td>
                <td class="px-4 py-3">{{ $registro->zona }}</td>
                <td class="px-4 py-3">{{ $registro->areaAsignada }}</td>
                <td class="px-4 py-3">{{ $registro->turno }}</td>
                <td class="px-4 py-3">
                    {{ $registro->total_kilos_area }} kg
                </td>
                {{-- SE ELIMINÓ LA CELDA DE BOTONES (EDITAR/VER) AQUÍ --}}
            </tr>
        @endforeach
    </tbody>
</table>
