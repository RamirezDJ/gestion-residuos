<x-app-layout>
    <div
        class="px-4 pt-8 pb-5 mx-auto sm:max-w-xl md:max-w-full lg:max-w-screen-xl md:px-24 lg:px-8 flex items-center justify-center">
        <div
            class="bg-white px-4 py-5 mx-auto rounded-lg sm:max-w-xl md:max-w-full lg:max-w-screen-xl md:px-24 lg:px-8 shadow">

            {{-- ENCABEZADO --}}
            <div class="border-b-2 mb-4">
                <h2 class="font-semibold text-xl text-gray-600">
                    Editando Generación Semanal en {{ $instituto->nombre }}
                </h2>
                <p class="text-lg text-gray-500 mb-4">
                    Ajuste los valores generados durante la semana del <strong>{{ $fechaInicioFormateada }}</strong> al
                    <strong>{{ $fechaFinFormateada }}</strong>.
                </p>
            </div>

            <div>
                <form action="{{ route('gensemanal.updateAll') }}" method="POST" id="edit-week-form">

                    @csrf
                    @method('PUT')
                    <x-validation-errors class="mb-4" />

                    {{-- DATOS GENERALES --}}
                    <div class="text-gray-600 mb-2">
                        <p class="text-lg font-bold">Datos generales</p>
                        <p class="mb-3">Editando la semana de recolección.</p>
                    </div>

                    <div class="flex flex-wrap border-b-2 py-4 mb-4">
                        {{-- Fecha Inicial --}}
                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <x-label for="fecha_inicial_display" class="mb-1">Fecha Inicial</x-label>
                            <div class="relative">
                                <x-input id="fecha_inicial_display" type="text" value="{{ $fechaInicioFormateada }}"
                                    disabled class="w-full bg-gray-100" />
                                <input type="hidden" name="fecha_inicial" value="{{ $fechaInicioFormateada }}">
                            </div>
                        </div>
                        {{-- Fecha Final --}}
                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <x-label for="fecha_final_display" class="mb-1">Fecha Final</x-label>
                            <div class="relative">
                                <x-input id="fecha_final_display" type="text" value="{{ $fechaFinFormateada }}"
                                    disabled class="w-full bg-gray-100" />
                                <input type="hidden" name="fecha_final" value="{{ $fechaFinFormateada }}">
                            </div>
                        </div>
                        {{-- Turno --}}
                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <x-label class="mb-1">Turno</x-label>
                            <div class="relative">
                                <x-select name="turno" class="w-full">
                                    <option value="Matutino" @selected($turnoSemana == 'Matutino')>Matutino</option>
                                    <option value="Vespertino" @selected($turnoSemana == 'Vespertino')>Vespertino</option>
                                </x-select>
                            </div>
                        </div>
                        {{-- Instituto --}}
                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <x-label class="mb-1">Instituto Asignado</x-label>
                            <div class="relative">
                                <x-input name="instituto_display" value="{{ $instituto->nombre ?? '' }}" readonly
                                    disabled class="w-full bg-gray-100" />
                            </div>
                        </div>
                    </div>

                    {{-- INICIO BITÁCORA --}}
                    <div class="flex justify-between items-center border-b-2 pb-4">
                        <div>
                            <p class="text-lg font-bold">Bitácora de generación semanal</p>
                            <p class="mb-3">Todos los valores de esta sección son numéricos y se interpretan como kg*.
                            </p>
                        </div>
                    </div>

                    {{-- CONTENEDOR DE DÍAS --}}
                    <div id="dias-container" class="mt-4 min-h-[300px]">
                    </div>

                    {{-- PLANTILLA INTELIGENTE (Igual que Create) --}}
                    <template id="dia-template">
                        <div class="dia-registro border-b-2 pb-4 mb-4">
                            <h3 class="text-xl font-semibold text-gray-700 mb-3 dia-titulo capitalize"></h3>

                            @foreach ($zonas as $zona)
                                <div class="text-gray-600 mb-2">
                                    <p class="text-lg font-bold mb-4">{{ $zona->nombre }}</p>
                                </div>
                                <div
                                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-4 border-b-2 pb-4">
                                    @foreach ($zona->areas as $area)
                                        <div class="mb-4 col-span-1">
                                            <h4 class="text-md font-semibold text-gray-800 mb-2">
                                                {{ $area->nombre }}
                                            </h4>

                                            {{-- LÓGICA: Filtrar categorías usando los subproductos del área --}}
                                            @php
                                                $categoriasDelArea = $area->subproductos
                                                    ->pluck('categoria')
                                                    ->unique('id')
                                                    ->sortBy('nombre');
                                            @endphp

                                            @if ($categoriasDelArea->isNotEmpty())
                                                <div class="pl-3 space-y-2">
                                                    @foreach ($categoriasDelArea as $categoria)
                                                        @if ($categoria)
                                                            <div class="mb-2">
                                                                <x-label class="mb-1 text-sm font-normal text-gray-600">
                                                                    {{ $categoria->nombre }}
                                                                </x-label>
                                                                {{-- Agregamos 'cat_' al name para compatibilidad --}}
                                                                <x-input type="number" step="0.01" placeholder="0kg"
                                                                    class="w-full"
                                                                    name="TEMPLATE_NAME[{{ $zona->id }}][{{ $area->id }}][cat_{{ $categoria->id }}]"
                                                                    value="" />
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </template>

                    {{-- CONTROLES DE NAVEGACIÓN --}}
                    <div id="controles-navegacion" class="flex justify-between items-center gap-3 pt-4 border-t mt-4"
                        style="display: none;">
                        <button type="button" id="boton-anterior"
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                            <i class="fa-solid fa-arrow-left mr-2"></i> Anterior
                        </button>
                        <button type="button" id="boton-siguiente"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 text-white">
                            Siguiente <i class="fa-solid fa-arrow-right ml-2"></i>
                        </button>
                    </div>

                    {{-- CHECKBOX Y SUBMIT --}}
                    <div id="seccion-submit-final" class="flex justify-between items-baseline pt-6 mt-4 border-t-2"
                        style="display: none;">
                        <div class="flex items-center mb-4">
                            <input id="default-checkbox" type="checkbox" required
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded">
                            <label for="default-checkbox" class="ms-2 text-sm font-medium text-gray-900">Confirmo que la
                                información es correcta</label>
                        </div>
                        <div class="flex gap-3">
                            <a href="{{ route('gensemanal.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                                Cancelar
                            </a>
                            <x-button>Guardar Cambios</x-button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- JAVASCRIPT --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('dias-container');
            const template = document.getElementById('dia-template');
            const controlesNavegacion = document.getElementById('controles-navegacion');
            const botonAnterior = document.getElementById('boton-anterior');
            const botonSiguiente = document.getElementById('boton-siguiente');
            const seccionSubmitFinal = document.getElementById('seccion-submit-final');

            // Recuperamos los datos y protegemos si viene vacío
            const lookupData = @json($lookupDataSemanal ?? []);

            const fechaInicialStr = '{{ $fechaInicioSemana }}';
            const fechaFinalStr = '{{ $fechaFinSemana }}';

            let pasoActual = 0;
            let diasGenerados = [];

            function generarDiasDeEdicion() {
                container.innerHTML = '';
                diasGenerados = [];
                pasoActual = 0;

                const fechaInicial = new Date(fechaInicialStr + 'T00:00:00');
                const fechaFinal = new Date(fechaFinalStr + 'T00:00:00');
                let fechaActual = new Date(fechaInicial);

                while (fechaActual <= fechaFinal) {
                    const diaClonado = template.content.cloneNode(true);

                    const year = fechaActual.getFullYear();
                    const month = String(fechaActual.getMonth() + 1).padStart(2, '0');
                    const day = String(fechaActual.getDate()).padStart(2, '0');
                    const fechaISO = `${year}-${month}-${day}`;

                    const fechaVisible = fechaActual.toLocaleDateString('es-ES', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });
                    const diaSemana = fechaActual.toLocaleDateString('es-ES', {
                        weekday: 'long'
                    });

                    diaClonado.querySelector('.dia-titulo').textContent =
                        `Registro del ${diaSemana}, ${fechaVisible}`;

                    const inputs = diaClonado.querySelectorAll('input[type="number"]');
                    inputs.forEach(input => {
                        // Reemplaza el placeholder
                        input.name = input.name.replace('TEMPLATE_NAME', `valor_kg[${fechaISO}]`);

                        // Estructura: valor_kg[FECHA][ZONA][AREA][cat_ID]
                        const match = input.name.match(/\[([^\]]+)\]\[([^\]]+)\]\[([^\]]+)\]\[([^\]]+)\]/);

                        if (match) {
                            const [_, f, z, areaId, rawCatId] = match;

                            // El input se llama 'cat_5', pero la DB tiene '5'.
                            // Quitamos el 'cat_' para buscar en el array JSON
                            const cleanCatId = rawCatId.replace('cat_', '');

                            if (lookupData[f] && lookupData[f][areaId] && lookupData[f][areaId][
                                cleanCatId]) {
                                input.value = lookupData[f][areaId][cleanCatId];
                            }
                        }
                    });

                    container.appendChild(diaClonado);
                    diasGenerados.push(container.lastElementChild);
                    fechaActual.setDate(fechaActual.getDate() + 1);
                }

                if (diasGenerados.length > 0) {
                    mostrarPaso(pasoActual);
                } else {
                    controlesNavegacion.style.display = 'none';
                }
            }

            function mostrarPaso(indice) {
                diasGenerados.forEach((dia, i) => {
                    dia.style.display = (i === indice) ? 'block' : 'none';
                });
                botonAnterior.style.display = (indice === 0) ? 'none' : 'inline-block';
                botonSiguiente.style.display = (indice === diasGenerados.length - 1) ? 'none' : 'inline-block';
                seccionSubmitFinal.style.display = (indice === diasGenerados.length - 1) ? 'flex' : 'none';
                controlesNavegacion.style.display = 'flex';
            }

            botonSiguiente.addEventListener('click', function() {
                if (pasoActual < diasGenerados.length - 1) {
                    pasoActual++;
                    mostrarPaso(pasoActual);
                    window.scrollTo(0, 200);
                }
            });

            botonAnterior.addEventListener('click', function() {
                if (pasoActual > 0) {
                    pasoActual--;
                    mostrarPaso(pasoActual);
                    window.scrollTo(0, 200);
                }
            });

            generarDiasDeEdicion();

            const editForm = document.getElementById('edit-week-form');
            editForm.addEventListener('submit', function(event) {
                event.preventDefault();
                Swal.fire({
                    title: '¿Confirmar Cambios?',
                    text: "Se actualizarán los datos de toda la semana.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, guardar'
                }).then((result) => {
                    if (result.isConfirmed) editForm.submit();
                });
            });
        });
    </script>
</x-app-layout>
