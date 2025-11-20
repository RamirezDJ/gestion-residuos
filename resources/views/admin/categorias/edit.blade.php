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
        'name' => 'Editar Categoría',
    ],
]">
    <div class="bg-white shadow rounded-lg p-6">

        {{-- FORMULARIO DE ACTUALIZACIÓN --}}
        <form action="{{ route('admin.categorias.update', $categoria) }}" method="POST">

            @csrf
            @method('PUT') {{-- NECESARIO PARA EL MÉTODO UPDATE --}}

            <x-validation-errors class="mb-4" />

            {{-- 1. CAMPO: Nombre de la Categoría --}}
            <div class="mb-4">
                <x-label class="mb-1">
                    Nombre de la Categoría
                </x-label>
                <x-input class="w-full" name="nombre" placeholder="Ejemplo: Valorizables" {{-- Pre-carga el valor actual de la categoría o el valor antiguo si falla la validación --}}
                    value="{{ old('nombre', $categoria->nombre) }}" />
            </div>

            {{-- 2. CAMPO: Descripción --}}
            {{-- Asumimos que la columna 'descripcion' existe en la BD --}}
            <div class="mb-4">
                <x-label class="mb-1">
                    Descripción de la Categoría (Opcional)
                </x-label>
                <x-textarea class="w-full" name="descripcion" placeholder="Breve descripción.">
                    {{-- Pre-carga la descripción actual --}}
                    {{ old('descripcion', $categoria->descripcion) }}
                </x-textarea>
            </div>

            {{-- Botones para guardar y eliminar --}}
            <div class="flex justify-end">
                <x-button>
                    Actualizar Categoría
                </x-button>

                <x-danger-button class="ml-2" onclick="deleteCategoria()">
                    Eliminar
                </x-danger-button>
            </div>
        </form>

        {{-- FORMULARIO OCULTO PARA ELIMINACIÓN --}}
        <form action="{{ route('admin.categorias.destroy', $categoria) }}" method="POST" id="formDelete">
            @csrf
            @method('DELETE')
        </form>


        @push('js')
            <script>
                // Función para manejar la eliminación con SweetAlert2
                function deleteCategoria() {
                    let form = document.getElementById('formDelete');

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¿Estás seguro?',
                            text: "Estás a punto de eliminar la categoría '{{ $categoria->nombre }}'. ¡Esta acción es irreversible!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33', // Rojo para confirmar la eliminación
                            cancelButtonColor: '#3085d6', // Azul para cancelar
                            confirmButtonText: 'Sí, ¡Eliminar!',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    } else {
                        // Fallback si SweetAlert2 no está cargado
                        if (confirm('¿Estás seguro de que quieres eliminar esta categoría?')) {
                            form.submit();
                        }
                    }
                }
            </script>
        @endpush

    </div>
</x-admin-layout>
