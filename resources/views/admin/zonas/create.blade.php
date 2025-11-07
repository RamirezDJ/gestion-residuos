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
        'name' => 'Nueva Zona',
    ],
]">
    <div class="bg-white shadow rounded-lg p-6">

        <form action="{{ route('admin.zonas.store') }}" method="POST">

            @csrf

            <x-validation-errors class="mb-4" />

            <div class="mb-4">
                <x-label class="mb-1">
                    Nombre de la zona
                </x-label>
                <x-input class="w-full" name="nombre" placeholder="Ingrese el nombre de la zona"
                    value="{{ old('nombre') }}" />
            </div>

            <div class="mb-4">
                <x-label class="mb-1">
                    Descripción de la zona
                </x-label>
                <x-textarea class="w-full" name="descripcion" placeholder="Sin descripción...">
                    {{ old('descripcion', isset($zona) ? $zona->descripcion : '') }}
                </x-textarea>
            </div>

            <div class="mb-4">
                <x-label class="mb-1">
                    Instituto perteneciente
                </x-label>
                <p class="text-gray-700 font-medium">
                    {{ auth()->user()->instituto?->nombre ?? 'Ninguno' }}
                </p>
                <input type="hidden" name="instituto_id" value="{{ auth()->user()->instituto_id }}">
            </div>

            <div class="mb-4">
                <x-label class="mb-2">
                    Áreas que pertenecen a esta Zona
                </x-label>

                <div class="border rounded-lg p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach ($areas as $area)
                        <div class="flex items-center">

                            <input type="checkbox" name="areas[]" id="area_{{ $area->id }}"
                                value="{{ $area->id }}"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                {{-- Revisa si el ID estaba en los datos 'old' (en caso de error de validación) --}} @if (in_array($area->id, old('areas', []))) checked @endif>
                            <label for="area_{{ $area->id }}" class="ml-3 block text-sm text-gray-700">
                                {{ $area->nombre }}
                            </label>

                        </div>
                    @endforeach
                </div>
            </div>
            <div class="flex justify-end">
                <x-button>
                    Crear Zona
                </x-button>
            </div>
        </form>

    </div>

</x-admin-layout>
