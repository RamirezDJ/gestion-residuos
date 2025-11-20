<x-admin-layout :breadcrumb="[
    [
        'name' => 'Home',
        'url' => route('admin.dashboard'),
    ],
    [
        'name' => 'Subproductos',
        'url' => route('admin.subproductos.index'),
    ],
    [
        'name' => 'Nuevo Subproducto',
    ],
]">
    <div class="bg-white shadow rounded-lg p-6">

        <form action="{{ route('admin.subproductos.store') }}" method="POST">

            @csrf

            <x-validation-errors class="mb-4" />

            {{-- CAMPO 1: Nombre del subproducto --}}
            <div class="mb-4">
                <x-label class="mb-1">
                    Nombre del subproducto
                </x-label>
                <x-input class="w-full" name="nombre" placeholder="Ingrese el nombre del subproducto"
                    value="{{ old('nombre') }}" />
            </div>

            {{-- CAMPO 2: Selección de Categoría (NUEVO) --}}
            <div class="mb-4">
                <x-label class="mb-1">
                    Categoría
                </x-label>
                <select name="categoria_id"
                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">Seleccione una Categoría</option>
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id }}"
                            {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                            {{ $categoria->nombre }}
                        </option>
                    @endforeach
                </select>
                {{-- Muestra el error de validación si existe --}}
                @error('categoria_id')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>


            <div class="flex justify-end">
                <x-button>
                    Crear Subproducto
                </x-button>
            </div>

        </form>

    </div>
</x-admin-layout>
