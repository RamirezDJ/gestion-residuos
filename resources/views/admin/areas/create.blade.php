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
    <div class="bg-white shadow rounded-lg p-6">

        <form action="{{ route('admin.areas.store') }}" method="POST">

            @csrf

            <x-validation-errors class="mb-4" />

            <div class="mb-4">
                <x-label class="mb-1">
                    Nombre del Area
                </x-label>
                <x-input class="w-full" name="nombre" placeholder="Ingrese el nombre del Area"
                    value="{{ old('nombre') }}" />
            </div>

            <div class="mb-4">
                <x-label class="mb-1">
                    Descripción del Area
                </x-label>
                <x-textarea class="w-full" name="descripcion" placeholder="Sin descripción...">
                    {{ old('descripcion', isset($area) ? $area->descripcion : '') }}
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
                    Subproductos que genera esta Área
                </x-label>
                
                <div class="border rounded-lg p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach ($subproductos_todos as $subproducto)
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   name="subproductos[]" 
                                   id="sub_{{ $subproducto->id }}" 
                                   value="{{ $subproducto->id }}"
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                   
                                   {{-- Revisa 'old' en caso de error de validación --}}
                                   @if(in_array($subproducto->id, old('subproductos', [])))
                                       checked 
                                   @endif
                            >
                            <label for="sub_{{ $subproducto->id }}" class="ml-3 block text-sm text-gray-700">
                                {{ $subproducto->nombre }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="flex justify-end">
                <x-button>
                    Crear Area
                </x-button>
            </div>
        </form>

    </div>

</x-admin-layout>