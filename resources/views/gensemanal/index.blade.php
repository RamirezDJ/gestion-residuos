<x-app-layout>

    @php
        $placeholderText = 'Buscar...'; 
        if ($tiempo == 'zonas_areas') {
            $placeholderText = 'Buscar por fecha, turno, zona o área...';
        } elseif ($tiempo == 'zonas_conteo') {
            $placeholderText = 'Buscar por nombre de zona...';
        } elseif ($tiempo == 'general') {
            $placeholderText = 'Buscar por fecha (ej: 2025-08-26)...';
        }
    @endphp

    <section class="bg-gray-50 dark:bg-gray-900 p-3 sm:p-5">
        <div class="px-4 pt-5 pb-5 mx-auto sm:max-w-xl md:max-w-full lg:max-w-screen-xl md:px-24 lg:px-8">
            <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">

                <div
                    class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">

                    <div class="w-full md:w-1/2">
                        <form class="flex items-center">
                            <label for="simple-search" class="sr-only">Search</label>
                            <div class="relative w-full">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400"
                                        fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <input type="text" id="simple-search"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                    placeholder="{{ $placeholderText }}" required="">
                            </div>
                        </form>
                    </div>

                    <div x-data="{ tiempo: '{{ $tiempo }}' }"
                        class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">

                        <a class="flex items-center justify-center bg-blue-700 text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none"
                            href="{{ route('gensemanal.create') }}">
                            <svg class="h-3.5 w-3.5 mr-2" fill="currentColor" viewbox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path clip-rule="evenodd" fill-rule="evenodd"
                                    d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                            </svg>
                            Nuevo Registro
                        </a>

                        <div class="flex items-center space-x-3 w-full md:w-auto">
                            <button id="actionsDropdownButton" data-dropdown-toggle="actionsDropdown"
                                class="w-full md:w-auto flex items-center justify-center py-2 px-4 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700"
                                type="button">
                                <svg class="-ml-1 mr-1.5 w-5 h-5" fill="currentColor" viewbox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path clip-rule="evenodd" fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                </svg>
                                <span>Elegir filtro</span>
                            </button>
                            <div id="actionsDropdown"
                                class="hidden z-10 w-44 bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600">
                                <ul class="py-1 text-sm text-gray-700 dark:text-gray-200"
                                    aria-labelledby="actionsDropdownButton">
                                    <li>
                                        <a href="#"
                                            @click.prevent="tiempo = 'general'; $nextTick(() => $refs.form.submit())"
                                            :class="{ 'bg-gray-200 dark:bg-gray-600': tiempo === 'general' }"
                                            class="block py-2 px-4 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">General
                                            (por Semana)</a> 
                                    </li>
                                    <li>
                                        <a href="#"
                                            @click.prevent="tiempo = 'zonas_conteo'; $nextTick(() => $refs.form.submit())"
                                            :class="{ 'bg-gray-200 dark:bg-gray-600': tiempo === 'zonas_conteo' }"
                                            class="block py-2 px-4 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Por
                                            Zona</a>
                                    </li>
                                    <li>
                                        <a href="#"
                                            @click.prevent="tiempo = 'zonas_areas'; $nextTick(() => $refs.form.submit())"
                                            :class="{ 'bg-gray-200 dark:bg-gray-600': tiempo === 'zonas_areas' }"
                                            class="block py-2 px-4 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Detallado
                                            (por Área)</a>
                                    </li>
                                </ul>
                            </div>

                            <form x-ref="form" action="{{ route('gensemanal.index') }}" method="GET" class="hidden">
                                <input type="hidden" name="tiempo" x-model="tiempo">
                            </form>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto p-5" id="table-container">
                    @include($viewName, ['registros' => $registros, 'tiempo' => $tiempo])
                </div>
            </div>
        </div>
    </section>

    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('simple-search');
                const tiempoInput = '{{ $tiempo }}';

                searchInput.addEventListener('input', function() {
                    const query = searchInput.value;

                    fetch(`{{ route('gensemanal.search') }}?query=${query}&tiempo=${tiempoInput}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.text())
                        .then(html => {
                            document.getElementById('table-container').innerHTML = html;
                        })
                        .catch(error => {
                            console.error('Error en la búsqueda:', error);
                        });
                });
            });
            document.addEventListener('submit', function(event) {
                if (event.target.classList.contains('delete-week-form')) {

                    event.preventDefault();
                    const form = event.target;
                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: "Vas a eliminar TODOS los registros de esta semana. ¡Esta acción no se puede deshacer!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, eliminar semana',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>
