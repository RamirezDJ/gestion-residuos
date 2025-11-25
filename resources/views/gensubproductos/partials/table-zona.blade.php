<table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
    <thead class="text-xs text-center text-gray-800 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
        <tr>
            <th scope="col" class="px-4 py-3">Fecha Inicio</th>
            <th scope="col" class="px-4 py-3">Fecha Final</th>
            <th scope="col" class="px-4 py-3">Zona</th>
            <th scope="col" class="px-4 py-3">Total Generado</th>
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

                {{-- ZONA --}}
                <td class="px-4 py-3">
                    {{ $registro->zona->nombre ?? 'Zona ID: ' . $registro->zona_id }}
                </td>

                {{-- TOTAL --}}
                <td class="px-4 py-3">
                    {{ number_format($registro->total_kg, 2) }} kg
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