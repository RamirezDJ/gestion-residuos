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
        'name' => 'Editar Subproducto',
    ],
]">


    <div class="bg-white shadow rounded-lg p-6">

        <form action="{{ route('admin.subproductos.update', $subproducto) }}" method="POST">

            @csrf
            @method('PUT')

            <x-validation-errors class="mb-4" />

            <div class="mb-4">
                <x-label class="mb-1">
                    Nombre del subproducto
                </x-label>
                <x-input class="w-full" name="nombre" placeholder="Ingrese el nombre del subproducto"
                    value="{{ old('nombre', $subproducto->nombre) }}" />
            </div>

            <div class="flex justify-end">
                <x-button>
                    Actualizar Subproducto
                </x-button>

                <x-danger-button class="ml-2" onclick="deleteSubproducto()">
                    Eliminar
                </x-danger-button>
            </div>
        </form>

        <form action="{{ route('admin.subproductos.destroy', $subproducto) }}" method="POST" id="formDelete">
            @csrf
            @method('DELETE')
        </form>

        @push('js')
            <script>
                function deleteSubproducto() {
                    let form = document.getElementById('formDelete');
                    form.submit();
                }
            </script>
        @endpush

    </div>
</x-admin-layout>


