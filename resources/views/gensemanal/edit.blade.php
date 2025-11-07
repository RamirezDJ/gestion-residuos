<x-app-layout>
    <div
        class="px-4 pt-8 pb-5 mx-auto sm:max-w-xl md:max-w-full lg:max-w-screen-xl md:px-24 lg:px-8flex items-center justify-center">
        <form action="{{ route('gensemanal.updateAll') }}" method="POST">
            @csrf
            @method('PUT')

            <input type="hidden" name="fecha" value="{{ $fechaUrl }}">
            <input type="hidden" name="turno" value="{{ $turno }}">

            <div
                class="bg-white px-4 py-5 mx-auto rounded-lg sm:max-w-xl md:max-w-full lg:max-w-screen-xl md:px-24 lg:px-8 shadow">

                <div class="border-b-2 mb-4">
                    <h2 class="font-semibold text-xl text-gray-600">
                        Editando Generación en {{ $instituto->nombre }}
                    </h2>
                    <p class="text-lg text-gray-500 mb-4">
                        Editando registros del día: <strong>{{ $fechaFormateada }}</strong> - Turno:
                        <strong>{{ $turno }}</strong>
                    </p>
                </div>

                <div>
                    <p class="text-lg font-bold">Bitácora de generación</p>
                    <p class="mb-3">Valores registrados en kg*.</p>

                    @foreach ($zonas as $zona)
                        <div class="text-gray-600 mb-2">
                            <p class="text-lg font-bold mb-4">
                                {{ $zona->nombre }} </p>
                        </div>

                        <div class="grid grid-cols-4 mb-4 border-b-2 pb-4">
                            @foreach ($zona->areas as $area)
                                <div class="mb-4 col-span-1">
                                    <h4 class="text-md font-semibold text-gray-800 mb-2">
                                        {{ $area->nombre }} </h4>

                                    <div class="pl-3 space-y-2">
                                        @foreach ($area->subproductos as $subproducto)
                                            <div>
                                                <x-label class="mb-1 text-sm font-normal">
                                                    {{ $subproducto->nombre }}
                                                </x-label>

                                                @php
                                                    // Buscamos el valor guardado
                                                    $valorGuardado = $lookupData[$area->id][$subproducto->id] ?? '';
                                                @endphp

                                                <x-input type="number" step="0.01" placeholder="0kg" class="w-full"
                                                    name="valor_kg[{{ $fechaUrl }}][{{ $zona->id }}][{{ $area->id }}][{{ $subproducto->id }}]"
                                                    value="{{ $valorGuardado }}" />
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach

                    <div class="flex justify-end pt-4">
                        <a href="{{ route('gensemanal.index') }}"
                            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-3">
                            Cancelar
                        </a>
                        <x-button type="submit"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Guardar Cambios
                        </x-button>
                    </div>

                </div>
            </div>
        </form>
    </div>
</x-app-layout>
