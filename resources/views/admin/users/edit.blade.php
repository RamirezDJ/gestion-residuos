<x-admin-layout :breadcrumb="[
    [
        'name' => 'Home',
        'url' => route('admin.dashboard'),
    ],
    [
        'name' => 'Usuarios',
        'url' => route('admin.users.index'),
    ],
    [
        'name' => 'Editar Usuario',
    ],
]">

    <div class="bg-white rounded shadow-lg p-6">

        <form action="{{ route('admin.users.update', $user) }}" method="POST">

            @csrf

            @method('PUT')

            <x-validation-errors class="mb-4" />

            <div class="mb-4">
                <X-label>
                    Nombre
                </X-label>

                <x-input name="name" value="{{ old('name', $user->name) }}" class="w-full" />
            </div>

            <div class="mb-4">
                <X-label>
                    Email
                </X-label>

                <x-input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full" />
            </div>

            <div class="mb-4">
                <X-label>
                    Password
                </X-label>

                <x-input type="password" name="password" class="w-full" />
            </div>

            <div class="mb-4">
                <X-label>
                    Confirm Password
                </X-label>

                <x-input type="password" name="password_confirmation" class="w-full" />
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-500 mb-2">
                    Este usuario actualmente pertenece a:
                </p>

                <x-select class="w-full" name="instituto_id">
                    <!-- Para que no seleccione ninguno por defecto -->
                    <option value="" disabled {{ old('instituto_id', $user->instituto_id) ? '' : 'selected' }}
                        class="text-gray-400 opacity-60">
                        POR ASIGANAR INSTITUTO
                    </option>

                    <!-- Permita al crear un registro vacio en instituto -->
                    <option value="crear" class="font-semibold" {{ old('instituto_id') == 'crear' ? 'selected' : '' }}>
                        NUEVO INSTITUTO
                    </option>

                    @foreach ($institutos as $instituto)
                        <option value="{{ $instituto->id }}"
                            {{ old('instituto_id', $user->instituto_id) == $instituto->id ? 'selected' : '' }}>
                            {{ $instituto->nombre }}
                        </option>
                    @endforeach
                </x-select>
            </div>

            <div class="mb-4">
                <ul>
                    @foreach ($roles as $role)
                        <li>
                            <label>
                                {{-- Verifica si el id de los roles se encuentra en los roles del usuario para activar el checkbox --}}
                                <x-checkbox name="roles[]" value="{{ $role->id }}" :checked="in_array($role->id, old('roles', $user->roles->pluck('id')->toArray()))" />
                                {{ $role->name }}
                            </label>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="flex justify-end">
                <x-button>
                    Actualizar
                </x-button>
            </div>
        </form>
    </div>

</x-admin-layout>
