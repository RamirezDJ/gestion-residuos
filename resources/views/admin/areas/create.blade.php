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
        'name' => 'Nueva Area',
    ],
]">
    <div class="bg-white shadow rounded-lg p-6 space-y-6">

        {{-- Formulario de Creación --}}
        <form action="{{ route('admin.areas.store') }}" method="POST">
            @csrf

            <x-validation-errors class="mb-4" />

            {{-- Sección: Datos Generales --}}
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Datos Generales</h2>

            <div class="mb-4">
                <x-label class="mb-1">
                    Nombre del Area
                </x-label>
                <x-input class="w-full" name="nombre" placeholder="Ingrese el nombre del Área"
                    value="{{ old('nombre') }}" />
            </div>

            <div class="mb-4">
                <x-label class="mb-1">
                    Descripción del Area
                </x-label>
                <x-textarea class="w-full" name="descripcion" placeholder="Sin descripción...">
                    {{ old('descripcion') }}
                </x-textarea>
            </div>

            <div class="mb-4">
                <x-label class="mb-1">
                    Instituto perteneciente
                </x-label>
                <div class="p-2 bg-gray-100 rounded text-gray-700 font-medium border border-gray-200">
                    {{ auth()->user()->instituto?->nombre ?? 'Ninguno' }}
                </div>
                <input type="hidden" name="instituto_id" value="{{ auth()->user()->instituto_id }}">
            </div>

            {{-- Sección: Selección por Categorías (Estilo Tarjetas Limpias) --}}
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2 mt-8">
                Categorías de Residuos
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($categorias as $categoria)
                    @php
                        // 1. Comprobar si se marcó en un submit anterior fallido (validación)
                        //    Al ser 'create', no hay datos de BD previos, solo old()
                        $isChecked = in_array($categoria->id, old('categorias', []));

                        // Extra: Generar lista de subproductos para mostrar qué incluye
                        $listaNombres = $categoria->subproductos->pluck('nombre')->join(', ');
                    @endphp

                    <label
                        class="relative flex items-start p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                        {{-- Checkbox --}}
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="categorias[]" value="{{ $categoria->id }}"
                                class="h-5 w-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                @checked($isChecked)>
                        </div>

                        {{-- Texto de la Tarjeta --}}
                        <div class="ml-3 w-full">
                            <span class="block text-base font-semibold text-gray-900">
                                {{ $categoria->nombre }}
                            </span>

                            {{-- Lista de Subproductos --}}
                            @if ($listaNombres)
                                <p class="text-gray-500 text-xs mt-1 line-clamp-2">
                                    Incluye: {{ $listaNombres }}
                                </p>
                            @else
                                <p class="text-gray-400 text-xs mt-1 italic">Sin subproductos definidos</p>
                            @endif
                        </div>
                    </label>
                @endforeach
            </div>

            {{-- Botón de Guardar --}}
            <div class="flex justify-end mt-8 pt-4 border-t">
                <x-button>
                    Crear Area
                </x-button>
            </div>
        </form>

    </div>

</x-admin-layout>
