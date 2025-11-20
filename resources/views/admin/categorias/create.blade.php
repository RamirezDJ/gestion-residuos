<x-admin-layout :breadcrumb="[
    [
        'name' => 'Home',
        'url' => route('admin.dashboard'),
    ],
    [
        'name' => 'Categorías',
        'url' => route('admin.categorias.index'),
    ],
    [
        'name' => 'Nueva Categoría',
    ],
]">
    <div class="bg-white shadow rounded-lg p-6">

        <form action="{{ route('admin.categorias.store') }}" method="POST">
            
            @csrf

            <x-validation-errors class="mb-4" />

            {{-- 1. CAMPO: Nombre de la Categoría --}}
            <div class="mb-4">
                <x-label class="mb-1">
                    Nombre de la Categoría
                </x-label>
                {{-- Usamos 'nombre' como campo, que es lo que definimos en el Modelo y la BD --}}
                <x-input 
                    class="w-full" 
                    name="nombre" 
                    placeholder="Ejemplo: Valorizables"
                    value="{{ old('nombre') }}" 
                />
            </div>

            {{-- 2. CAMPO: Descripción (Opcional, pero bueno para mantener el formato) --}}
            <div class="mb-4">
                <x-label class="mb-1">
                    Descripción de la Categoría (Opcional)
                </x-label>
                {{-- Asumimos que la tabla categorías tendrá un campo 'descripcion' --}}
                <x-textarea 
                    class="w-full" 
                    name="descripcion" 
                    placeholder="Breve descripción."
                >
                    {{ old('descripcion') }}
                </x-textarea>
            </div>
            
            {{-- Botón para guardar --}}
            <div class="flex justify-end">
                <x-button>
                    Crear Categoría
                </x-button>
            </div>
        </form>

    </div>

</x-admin-layout>