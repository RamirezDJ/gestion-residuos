<x-app-layout>
    {{-- 1. Encabezado (La barra gris con el título) --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Inicio') }}
        </h2>
    </x-slot>

    {{-- 2. Contenedores de diseño (Esto crea el efecto de "ventana flotante") --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

                {{-- === INICIO DEL CONTENIDO (Lo que teníamos antes) === --}}

                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <x-application-logo2 class="block h-20 w-20" />

                    <h1 class="mt-8 text-2xl font-medium text-gray-900">
                        Bienvenido {{ Auth::user()->name }}!
                    </h1>

                    <p class="mt-6 text-gray-500 leading-relaxed">
                        Este sistema permite monitorear los Índices de Calidad del Agua (ICA),
                        registrando parámetros físicos y químicos para asegurar el cumplimiento de las normativas
                        NOM-001-SEMARNAT-2021 y NOM-127-SSA1-2021.
                    </p>
                </div>

                @unless (Auth::user()->hasRole('Participante'))
                    <div class="bg-gray-200 bg-opacity-25 grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 p-6 lg:p-8">

                        {{-- TARJETA 1: PARÁMETROS FÍSICOS --}}
                        <div>
                            <div class="flex items-center">
                                <h2 class="text-xl font-semibold text-gray-900">
                                    <p>Parámetros Físicos</p>
                                </h2>
                            </div>

                            <p class="mt-4 text-gray-500 text-sm leading-relaxed">
                                Ingresa al formulario para capturar los datos de Conductividad, Turbidez, Temperatura y
                                Oxígeno Disuelto.
                            </p>

                            <p class="mt-4 text-sm">
                                <a href="{{ route('indicesCalidad.show', ['tipo' => 'fisicos']) }}"
                                    class="inline-flex items-center font-semibold text-indigo-700">
                                    Ir a registros

                                    <svg viewBox="0 0 20 20" class="ms-1 w-5 h-5 fill-indigo-500">
                                        <path fill-rule="evenodd"
                                            d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </p>
                        </div>

                        {{-- TARJETA 2: PARÁMETROS QUÍMICOS --}}
                        <div>
                            <div class="flex items-center">
                                <h2 class="text-xl font-semibold text-gray-900">
                                    <p>Parámetros Químicos</p>
                                </h2>
                            </div>

                            <p class="mt-4 text-gray-500 text-sm leading-relaxed">
                                Ingresa al formulario para capturar los niveles de pH, Dureza, Nitratos, Nitritos y Demanda
                                Química de Oxígeno (DQO).
                            </p>

                            <p class="mt-4 text-sm">
                                <a href="{{ route('indicesCalidad.show', ['tipo' => 'quimicos']) }}"
                                    class="inline-flex items-center font-semibold text-indigo-700">
                                    Ir a registros

                                    <svg viewBox="0 0 20 20" class="ms-1 w-5 h-5 fill-indigo-500">
                                        <path fill-rule="evenodd"
                                            d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </p>
                        </div>
                    </div>
                @else
                    <div class="bg-gray-200 bg-opacity-25 gap-6 lg:gap-8 p-6 lg:p-8">
                        <div class="p-4 text-sm text-blue-500 rounded-lg bg-blue-50" role="alert">
                            <span class="font-medium">Alerta!</span> Para continuar necesitas que un administrador te de
                            acceso a estos
                            apartados.
                        </div>
                    </div>
                @endunless

                {{-- === FIN DEL CONTENIDO === --}}

            </div>
        </div>
    </div>
</x-app-layout>
