<x-admin-layout :breadcrumb="[
    [
        'name' => 'Home',
        'url' => route('admin.dashboard'),
    ],
    [
        'name' => 'Areas',
        'url' => route('admin.areas.index'),
    ],
    [
        'name' => $area->nombre,
    ],
]">
    <div class="bg-white shadow rounded-lg p-6 space-y-6">

        {{-- Formulario principal de ACTUALIZACIÓN --}}
        <form action="{{ route('admin.areas.update', $area) }}" method="POST">
            @csrf
            @method('PUT')

            <x-validation-errors class="mb-4" />

            {{-- Sección de Datos Generales --}}
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Datos Generales</h2>

            <div class="mb-4">
                <x-label class="mb-1">
                    Nombre del Area
                </x-label>
                <x-input class="w-full" name="nombre" placeholder="Ingrese el nombre del Área"
                    value="{{ old('nombre', $area->nombre) }}" />
            </div>

            <div class="mb-4">
                <x-label class="mb-1">
                    Descripción del Area
                </x-label>
                <x-textarea class="w-full" name="descripcion" placeholder="Sin descripción...">
                    {{ old('descripcion', $area->descripcion) }}
                </x-textarea>
            </div>

            <div>
                <x-label class="mb-1">
                    Instituto perteneciente
                </x-label>
                <div class="p-2 bg-gray-100 rounded text-gray-700 font-medium border border-gray-200">
                    {{ auth()->user()->instituto?->nombre ?? 'Ninguno' }}
                </div>
                <input type="hidden" name="instituto_id" value="{{ auth()->user()->instituto_id }}">
            </div>

            {{-- NUEVA Sección: Selección por Categorías --}}
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2 mt-8">
                Categorías de Residuos
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($categorias as $categoria)
                    @php
                        // 1. Comprobar si se marcó en un submit anterior fallido
                        $oldChecked = in_array($categoria->id, old('categorias', []));

                        // 2. Comprobar si en la BD ya existen subproductos de esta categoría asignados al área
                        //    (Usamos intersect para ver si hay coincidencia entre los del area y los de la categoria)
                        $hasRelatedSubproducts = $area->subproductos
                            ->where('categoria_id', $categoria->id)
                            ->isNotEmpty();

                        // 3. Determinar estado final
                        $isChecked = $oldChecked || (!$oldChecked && $hasRelatedSubproducts);

                        // Extra: Generar lista de subproductos para mostrar al usuario qué incluye
                        $listaNombres = $categoria->subproductos->pluck('nombre')->join(', ');
                    @endphp

                    <label
                        class="relative flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors {{ $isChecked ? 'ring-2 ring-indigo-500 bg-indigo-50 border-transparent' : 'border-gray-200' }}">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="categorias[]" value="{{ $categoria->id }}"
                                class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                @checked($isChecked)>
                        </div>
                        <div class="ml-3 text-sm">
                            <span class="font-medium text-gray-900">{{ $categoria->nombre }}</span>
                            @if ($listaNombres)
                                <p class="text-gray-500 text-xs mt-1">Incluye: {{ Str::limit($listaNombres, 50) }}</p>
                            @else
                                <p class="text-gray-400 text-xs mt-1 italic">Sin subproductos definidos</p>
                            @endif
                        </div>
                    </label>
                @endforeach
            </div>

            {{-- Botones de Acción --}}
            <div class="flex justify-end mt-8">
                <x-button>
                    Actualizar Area
                </x-button>

                <x-danger-button class="ml-2" type="button" onclick="deleteArea()">
                    Eliminar
                </x-danger-button>
            </div>
        </form>

        {{-- Formulario oculto para eliminar --}}
        <form action="{{ route('admin.areas.destroy', $area) }}" method="POST" id="formDelete">
            @csrf
            @method('DELETE')
        </form>

    </div>

    @push('js')
        <script>
            function deleteArea() {
                // Puedes agregar SweetAlert aquí si lo deseas
                if (confirm('¿Estás seguro de eliminar esta área?')) {
                    document.getElementById('formDelete').submit();
                }
            }
        </script>
    @endpush

</x-admin-layout>
