<x-app-layout>
    @php
        $placeholderText = 'Buscar...';
        if ($tiempo == 'zonas_conteo') {
            $placeholderText = 'Buscar por punto de muestreo...';
        } elseif ($tiempo == 'general') {
            $placeholderText = 'Buscar por fecha (ej: 2024-05-20)...';
        }
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registros de Parámetros Físicos') }}
        </h2>
    </x-slot>

    <section class="bg-gray-50 dark:bg-gray-900 p-3 sm:p-5">
        <div class="px-4 pt-5 pb-5 mx-auto sm:max-w-xl md:max-w-full lg:max-w-screen-xl md:px-24 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden border-t-4 border-blue-500">

                <div
                    class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">

                    {{-- BUSCADOR --}}
                    <div class="w-full md:w-1/2">
                        <form class="flex items-center">
                            <label for="simple-search" class="sr-only">Buscar</label>
                            <div class="relative w-full">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg aria-hidden="true" class="w-5 h-5 text-gray-500" fill="currentColor"
                                        viewbox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <input type="text" id="simple-search"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2"
                                    placeholder="{{ $placeholderText }}">
                            </div>
                        </form>
                    </div>

                    <div x-data="{ tiempo: '{{ $tiempo }}' }"
                        class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">

                        {{-- BOTÓN NUEVO REGISTRO (AZUL) --}}
                        <a class="flex items-center justify-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2"
                            href="{{ route('indicesCalidad.create', ['tipo' => 'fisicos']) }}">
                            <svg class="h-3.5 w-3.5 mr-2" fill="currentColor" viewbox="0 0 20 20">
                                <path clip-rule="evenodd" fill-rule="evenodd"
                                    d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                            </svg>
                            Nuevo Físico
                        </a>

                        {{-- FILTRO --}}
                        <div class="flex items-center space-x-3 w-full md:w-auto">
                            <button id="actionsDropdownButton" data-dropdown-toggle="actionsDropdown"
                                class="w-full md:w-auto flex items-center justify-center py-2 px-4 text-sm font-medium text-gray-900 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 focus:ring-4 focus:ring-gray-200"
                                type="button">
                                <svg class="-ml-1 mr-1.5 w-5 h-5" fill="currentColor" viewbox="0 0 20 20">
                                    <path clip-rule="evenodd" fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                </svg>
                                <span>Filtro</span>
                            </button>
                            <div id="actionsDropdown"
                                class="hidden z-10 w-44 bg-white rounded divide-y divide-gray-100 shadow">
                                <ul class="py-1 text-sm text-gray-700" aria-labelledby="actionsDropdownButton">
                                    <li>
                                        <a href="{{ route('indicesCalidad.show', ['tipo' => 'fisicos', 'tiempo' => 'general']) }}"
                                            class="block py-2 px-4 hover:bg-gray-100 {{ $tiempo == 'general' ? 'bg-gray-100 font-bold' : '' }}">
                                            Por Fecha
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('indicesCalidad.show', ['tipo' => 'fisicos', 'tiempo' => 'zonas_conteo']) }}"
                                            class="block py-2 px-4 hover:bg-gray-100 {{ $tiempo == 'zonas_conteo' ? 'bg-gray-100 font-bold' : '' }}">
                                            Por Punto
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CONTENEDOR DE LA TABLA --}}
                <div class="overflow-x-auto p-5" id="table-container">
                    @include('indices_calidad.partials.table-fisicos', ['registros' => $registros])
                </div>
            </div>
        </div>
    </section>

    {{-- SCRIPTS PARA BUSQUEDA --}}
    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('simple-search');
                
                const tipoInput = 'fisicos';
                const tiempoInput = '{{ $tiempo }}';

                searchInput.addEventListener('input', function() {
                    const query = searchInput.value;
                    
                    fetch(`{{ route('indicesCalidad.search') }}?query=${query}&tiempo=${tiempoInput}&tipo=${tipoInput}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.text())
                        .then(html => {
                            document.getElementById('table-container').innerHTML = html;
                        });
                });
            });
        </script>
    @endpush
</x-app-layout>
