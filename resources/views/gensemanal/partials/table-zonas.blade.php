<table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
    <thead class="text-xs text-center text-gray-800 uppercase bg-gray-50 ...">
        <tr>
            <th scope="col" class="px-4 py-3">Zona</th>
            <th scope="col" class="px-4 py-3">N° de Áreas con Registro</th>
            <th scope="col" class="px-4 py-3">Total Kilos Generados</th>
        </tr>
    </thead>
    <tbody class="text-center">
        @foreach ($registros as $registro)
            <tr class="border-b dark:border-gray-900">
                <td class="px-4 py-3 font-medium ...">{{ $registro->zona }}</td>
                <td class="px-4 py-3">{{ $registro->conteo_areas }}</td>
                <td class="px-4 py-3">{{ $registro->total_kilos_zona }} kg</td>
            </tr>
        @endforeach
    </tbody>
</table>