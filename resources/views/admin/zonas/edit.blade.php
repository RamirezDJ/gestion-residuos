<x-admin-layout :breadcrumb="[
    [
        'name' => 'Home',
        'url' => route('admin.dashboard'),
    ],
    [
        'name' => 'Zonas',
        'url' => route('admin.zonas.index'),
    ],
    [
        'name' => $zona->nombre,
    ],
]">
    <div class="bg-white shadow rounded-lg p-6 space-y-6">

        {{-- Formulario de Edición --}}
        <form action="{{ route('admin.zonas.update', $zona) }}" method="POST">
            @csrf
            @method('PUT')

            <x-validation-errors class="mb-4" />

            {{-- Sección: Datos de la Zona --}}
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Datos de la Zona</h2>

            <div class="grid grid-cols-1 gap-6 mb-6">
                <div>
                    <x-label class="mb-1">
                        Nombre de la zona
                    </x-label>
                    <x-input class="w-full" name="nombre" placeholder="Ingrese el nombre de la zona"
                        value="{{ old('nombre', $zona->nombre) }}" />
                </div>

                <div>
                    <x-label class="mb-1">
                        Descripción de la zona
                    </x-label>
                    <x-textarea class="w-full" name="descripcion" placeholder="Sin descripción...">
                        {{ old('descripcion', $zona->descripcion) }}
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
            </div>

            {{-- Sección: Áreas Asignadas --}}
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2 mt-8">
                Áreas Pertenecientes
            </h2>

            {{-- GRID DE TARJETAS (Diseño limpio sin fondo de color al seleccionar) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($areas as $area)
                    @php
                        // Lógica para saber si está chequeado
                        $isChecked = in_array($area->id, old('areas', $zona->areas->pluck('id')->toArray()));
                    @endphp

                    {{-- 
                        CAMBIO: Eliminada la lógica condicional de colores (bg-indigo-50, ring-2).
                        Ahora siempre mantiene el borde gris y fondo blanco/hover gris suave.
                    --}}
                    <label
                        class="relative flex items-start p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">

                        {{-- Checkbox --}}
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="areas[]" value="{{ $area->id }}"
                                class="h-5 w-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                @checked($isChecked)>
                        </div>

                        {{-- Texto de la Tarjeta --}}
                        <div class="ml-3 w-full">
                            <span class="block text-base font-semibold text-gray-900">
                                {{ $area->nombre }}
                            </span>

                            {{-- Descripción del Área --}}
                            @if ($area->descripcion)
                                <p class="text-gray-500 text-xs mt-1 line-clamp-2">
                                    {{ $area->descripcion }}
                                </p>
                            @else
                                <p class="text-gray-400 text-xs mt-1 italic">Sin descripción disponible</p>
                            @endif
                        </div>
                    </label>
                @endforeach
            </div>

            {{-- Botones de Acción --}}
            <div class="flex justify-end mt-8 pt-4 border-t">
                <x-button>
                    Actualizar Zona
                </x-button>

                <x-danger-button class="ml-2" type="button" onclick="deleteZona()">
                    Eliminar
                </x-danger-button>
            </div>
        </form>

        {{-- Formulario oculto para eliminar --}}
        <form action="{{ route('admin.zonas.destroy', $zona) }}" method="POST" id="formDelete">
            @csrf
            @method('DELETE')
        </form>

    </div>

    @push('js')
        <script>
            function deleteZona() {
                if (confirm('¿Estás seguro de eliminar esta Zona?')) {
                    document.getElementById('formDelete').submit();
                }
            }
        </script>
    @endpush

</x-admin-layout>
