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

            <div class="mb-4">
                <x-label class="mb-1">
                    Nombre del subproducto
                </x-label>
                <x-input class="w-full" name="nombre" placeholder="Ingrese el nombre del subproducto"
                    value="{{ old('nombre') }}" />
            </div>

            <div class="flex justify-end">
                <x-button>
                    Crear Subproducto
                </x-button>
            </div>

        </form>

    </div>
</x-admin-layout>