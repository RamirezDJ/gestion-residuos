<table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
    <thead class="text-xs text-center text-gray-800 uppercase bg-blue-50">
        <tr>
            <th class="px-4 py-3">Fecha</th>
            <th class="px-4 py-3">Muestras</th>
            <th class="px-4 py-3">Promedio Turbidez</th>
            <th class="px-4 py-3">Acciones</th>
        </tr>
    </thead>
    <tbody class="text-center">
        @forelse ($registros as $registro)
            <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-3 font-medium">{{ $registro->fecha_muestreo }}</td>
                <td class="px-4 py-3">{{ $registro->total_muestras }}</td>
                <td class="px-4 py-3">{{ number_format($registro->promedio_dato, 2) }} UNT</td>
                <td class="px-4 py-3">
                    <a href="#" class="text-blue-600 hover:underline"><i class="fa-solid fa-eye"></i> Ver Detalles</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="p-4 text-center">No hay registros físicos.</td></tr>
        @endforelse
    </tbody>
</table>