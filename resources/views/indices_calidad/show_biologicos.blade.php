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
            {{ __('Registros de Parámetros Biológicos') }}
        </h2>
    </x-slot>

    <section class="bg-gray-50 dark:bg-gray-900 p-3 sm:p-5">
        <div class="px-4 pt-5 pb-5 mx-auto sm:max-w-xl md:max-w-full lg:max-w-screen-xl md:px-24 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden border-t-4 border-purple-500">

                <div
                    class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">

                    <div class="w-full md:w-1/2">
                        <form class="flex items-center" onsubmit="return false;">
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
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full pl-10 p-2"
                                    placeholder="{{ $placeholderText }}">
                            </div>
                        </form>
                    </div>

                    <div
                        class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">

                        <a class="flex items-center justify-center text-white bg-indigo-700 hover:bg-indigo-800 focus:ring-4 focus:ring-indigo-300 font-medium rounded-lg text-sm px-4 py-2"
                            href="{{ route('indicesCalidad.create', ['tipo' => 'biologicos']) }}">
                            <svg class="h-3.5 w-3.5 mr-2" fill="currentColor" viewbox="0 0 20 20">
                                <path clip-rule="evenodd" fill-rule="evenodd"
                                    d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                            </svg>
                            Nuevo Biológico
                        </a>

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
                                        <a href="{{ route('indicesCalidad.show', ['tipo' => 'biologicos', 'tiempo' => 'general']) }}"
                                            class="block py-2 px-4 hover:bg-gray-100 {{ $tiempo == 'general' ? 'bg-gray-100 font-bold' : '' }}">
                                            Por Fecha
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('indicesCalidad.show', ['tipo' => 'biologicos', 'tiempo' => 'zonas_conteo']) }}"
                                            class="block py-2 px-4 hover:bg-gray-100 {{ $tiempo == 'zonas_conteo' ? 'bg-gray-100 font-bold' : '' }}">
                                            Por Punto
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto p-5" id="table-container">
                    @include('indices_calidad.partials.table-biologicos', ['registros' => $registros])
                </div>
            </div>
        </div>
    </section>
    @push('js')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('simple-search');
                const tipoInput = 'biologicos'; 
                const tiempoInput = '{{ $tiempo ?? 'general' }}';
                if (searchInput) {
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
                            })
                            .catch(error => console.error('Error:', error));
                    });
                }
                @if (session('swal'))
                    Swal.fire({
                        icon: '{{ session('swal.icon') }}',
                        title: '{{ session('swal.title') }}',
                        text: '{{ session('swal.text') }}',
                        confirmButtonColor: '#7C3AED', 
                    });
                @endif
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                        const progress = toast.querySelector('.swal2-timer-progress-bar');
                        if (progress) progress.style.backgroundColor = '#7C3AED';
                    }
                });
                @if (session('success'))
                    Toast.fire({
                        icon: 'success',
                        title: '{{ session('success') }}'
                    });
                @endif
                document.addEventListener('submit', function(e) {
                    if (e.target && e.target.classList.contains('form-eliminar')) {
                        e.preventDefault();
                        const form = e.target;

                        Swal.fire({
                            title: '¿Estás seguro?',
                            text: "¡No podrás revertir esto! El registro biológico será eliminado permanentemente.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#7C3AED',
                            cancelButtonColor: '#6B7280',
                            confirmButtonText: 'Sí, eliminarlo',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    }
                });

            });
        </script>
    @endpush
</x-app-layout>
