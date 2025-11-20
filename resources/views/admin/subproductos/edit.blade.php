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

            {{-- 1. CAMPO: Nombre del subproducto --}}
            <div class="mb-4">
                <x-label class="mb-1">
                    Nombre del subproducto
                </x-label>
                <x-input class="w-full" name="nombre" placeholder="Ingrese el nombre del subproducto"
                    value="{{ old('nombre', $subproducto->nombre) }}" />
            </div>

            {{-- 2. CAMPO: Selección de Categoría (Añadido) --}}
            <div class="mb-4">
                <x-label class="mb-1">
                    Categoría
                </x-label>
                <select name="categoria_id"
                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">-- Seleccione una Categoría --</option>
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id }}"
                            {{ old('categoria_id', $subproducto->categoria_id) == $categoria->id ? 'selected' : '' }}>
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
                // Función actualizada para usar SweetAlert2 para la confirmación
                function deleteSubproducto() {
                    let form = document.getElementById('formDelete');

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¿Estás seguro?',
                            text: "Estás a punto de eliminar el subproducto '{{ $subproducto->nombre }}'. ¡Esta acción es irreversible!",
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
                        // Fallback simple si SweetAlert2 no está cargado
                        if (confirm('¿Estás seguro de que quieres eliminar este subproducto?')) {
                            form.submit();
                        }
                    }
                }
            </script>
        @endpush

    </div>
</x-admin-layout>
