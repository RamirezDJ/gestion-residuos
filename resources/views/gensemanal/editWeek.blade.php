<x-app-layout>
    <div
        class="px-4 pt-8 pb-5 mx-auto sm:max-w-xl md:max-w-full lg:max-w-screen-xl md:px-24 lg:px-8flex items-center justify-center">
        <div
            class="bg-white px-4 py-5  mx-auto rounded-lg sm:max-w-xl md:max-w-full lg:max-w-screen-xl md:px-24 lg:px-8 shadow">
            <div class="border-b-2 mb-4">
                <h2 class="font-semibold text-xl text-gray-600">Editando Generación Semanal en
                    {{ $instituto->nombre }}
                </h2>
                <p class="text-lg text-gray-500 mb-4">Ajuste los valores generados durante la semana del
                    <strong>{{ $fechaInicioFormateada }}</strong> al <strong>{{ $fechaFinFormateada }}</strong>.
                </p>
            </div>
            <div>
                <form action="{{ route('gensemanal.updateAll') }}" method="POST">

                    @csrf
                    @method('PUT')
                    <x-validation-errors class="mb-4" />

                    {{-- Datos generales (Corregido, sin duplicados) --}}
                    <div class="text-gray-600 mb-2">
                        <p class="text-lg font-bold">Datos generales</p>
                        <p class="mb-3">Editando la semana de recolección.</p>
                    </div>
                    <div class="flex flex-wrap border-b-2 py-4 mb-4">
                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <x-label for="fecha_inicial_display" class="mb-1">
                                Fecha Inicial
                            </x-label>
                            <div class="relative">
                                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                    </svg>
                                </div>
                                <x-input id="fecha_inicial_display" type="text" value="{{ $fechaInicioFormateada }}"
                                    disabled
                                    class="w-full ps-10 p-2.5 bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400" />
                                <input type="hidden" name="fecha_inicial" value="{{ $fechaInicioFormateada }}">
                            </div>
                        </div>

                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <x-label for="fecha_final_display" class="mb-1">
                                Fecha Final
                            </x-label>
                            <div class="relative">
                                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                    </svg>
                                </div>
                                <x-input id="fecha_final_display" type="text" value="{{ $fechaFinFormateada }}"
                                    disabled
                                    class="w-full ps-10 p-2.5 bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400" />
                                <input type="hidden" name="fecha_final" value="{{ $fechaFinFormateada }}">
                            </div>
                        </div>

                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <x-label class="mb-1">
                                Turno
                            </x-label>
                            <div class="relative">
                                <x-select name="turno" class="w-full">
                                    <option value="Matutino" @selected($turnoSemana == 'Matutino')>Matutino</option>
                                    <option value="Vespertino" @selected($turnoSemana == 'Vespertino')>Vespertino</option>
                                </x-select>
                            </div>
                        </div>

                        <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                            <x-label class="mb-1">
                                Instituto Asignado
                            </x-label>
                            <div class="relative">
                                <x-input name="instituto_display" value="{{ $instituto->nombre ?? '' }}" readonly
                                    disabled
                                    class="w-full bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400" />
                            </div>
                        </div>
                    </div>


                    {{-- Inicio bitacora --}}
                    <div class="flex justify-between items-center border-b-2 pb-4">
                        <div>
                            <p class="text-lg font-bold">Bitacora de generacion semanal</p>
                            <p class="mb-3">Todos los valores de esta seccion son numericos y se interpreta como kg*.
                            </p>
                        </div>
                    </div>

                    <div id="dias-container" class="mt-4">
                    </div>

                    <div id="dias-container" class="mt-4">
                    </div>

                    <div id="controles-navegacion" class="flex justify-end items-center gap-3 pt-4"
                        style="display: none;">

                        <button type="button" id="boton-anterior"
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            <i class="fa-solid fa-arrow-left mr-2"></i>
                            Anterior
                        </button>

                        <button type="button" id="boton-siguiente"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Siguiente
                            <i class="fa-solid fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                    
                    <template id="dia-template">
                        <div class="dia-registro border-b-2 pb-4 mb-4">

                            <h3 class="text-xl font-semibold text-gray-700 mb-3 dia-titulo"></h3>

                            @foreach ($zonas as $zona)
                                <div class="text-gray-600 mb-2">
                                    <p class="text-lg font-bold mb-4">{{ $zona->nombre }}</p>
                                </div>
                                <div class="grid grid-cols-4 mb-4 border-b-2 pb-4">
                                    @foreach ($zona->areas as $area)
                                        <div class="mb-4 col-span-1">
                                            <h4 class="text-md font-semibold text-gray-800 mb-2">
                                                {{ $area->nombre }} </h4>
                                            <div class="pl-3">
                                                @foreach ($area->subproductos as $subproducto)
                                                    <div class="mb-2">
                                                        <x-label class="mb-1 text-sm font-normal">
                                                            {{ $subproducto->nombre }}
                                                        </x-label>
                                                        <x-input type="number" step="0.01" placeholder="0kg"
                                                            name="TEMPLATE_NAME[{{ $zona->id }}][{{ $area->id }}][{{ $subproducto->id }}]"
                                                            value="" />
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </template>

                    <div id="seccion-submit-final" class="flex justify-between items-baseline pt-4">
                        <div class="flex items-center mb-4">
                            <input id="default-checkbox" type="checkbox" value="" required
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded ...">
                            <label for="default-checkbox" class="ms-2 text-gray-900 dark:text-gray-300">Confirmo
                                que la informacion es correcta</label>
                        </div>
                        <x-button>
                            Guardar Cambios
                        </x-button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- 1. REFERENCIAS A ELEMENTOS ---
            const container = document.getElementById('dias-container');
            const template = document.getElementById('dia-template');

            // Referencias del Wizard
            const controlesNavegacion = document.getElementById('controles-navegacion');
            const botonAnterior = document.getElementById('boton-anterior');
            const botonSiguiente = document.getElementById('boton-siguiente');
            const seccionSubmitFinal = document.getElementById('seccion-submit-final');

            // Datos inyectados desde el controlador
            const lookupData = @json($lookupDataSemanal);
            const fechaInicialStr = '{{ $fechaInicioSemana }}'; // 'Y-m-d'
            const fechaFinalStr = '{{ $fechaFinSemana }}'; // 'Y-m-d'

            console.log("--- SCRIPT DE EDICIÓN CARGADO ---");
            console.log("Fecha Inicial (Y-m-d):", fechaInicialStr);
            console.log("Fecha Final (Y-m-d):", fechaFinalStr);
            console.log("LOOKUP DATA (Datos de la BD):", lookupData);

            // --- 2. VARIABLES DE ESTADO ---
            let pasoActual = 0;
            let diasGenerados = []; // Array para guardar los divs de los días

            // --- 3. FUNCIÓN PARA GENERAR LOS DÍAS Y PRE-LLENARLOS ---
            function generarDiasDeEdicion() {
                container.innerHTML = '';
                diasGenerados = []; // Limpiamos el array
                pasoActual = 0;

                const fechaInicial = new Date(fechaInicialStr + 'T00:00:00');
                const fechaFinal = new Date(fechaFinalStr + 'T00:00:00');
                let fechaActual = new Date(fechaInicial);

                while (fechaActual <= fechaFinal) {
                    const diaClonado = template.content.cloneNode(true);
                    const fechaISO = fechaActual.toISOString().split('T')[0];
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

                    // Actualizamos el 'name' y pre-llenamos el 'value'
                    const inputs = diaClonado.querySelectorAll('input[type="number"]');
                    inputs.forEach(input => {
                        // Reemplazar TEMPLATE_NAME
                        input.name = input.name.replace(
                            'TEMPLATE_NAME',
                            `valor_kg[${fechaISO}]`
                        );

                        // Pre-llenar datos (la lógica que ya tenías)
                        const match = input.name.match(/\[([^\]]+)\]\[([^\]]+)\]\[([^\]]+)\]\[([^\]]+)\]/);
                        if (match) {
                            const [_, fechaInput, zonaIdInput, areaIdInput, subproductoIdInput] = match;
                            let valorGuardado = '';
                            if (lookupData[fechaInput] &&
                                lookupData[fechaInput][zonaIdInput] &&
                                lookupData[fechaInput][zonaIdInput][areaIdInput] &&
                                lookupData[fechaInput][zonaIdInput][areaIdInput][subproductoIdInput]) {
                                valorGuardado = lookupData[fechaInput][zonaIdInput][areaIdInput][
                                    subproductoIdInput
                                ];
                            }
                            input.value = valorGuardado;
                        }
                    });

                    container.appendChild(diaClonado);
                    // Guardamos la REFERENCIA al div
                    diasGenerados.push(container.lastElementChild);

                    fechaActual.setDate(fechaActual.getDate() + 1);
                }

                // --- 4. INICIALIZAR EL WIZARD ---
                if (diasGenerados.length > 0) {
                    mostrarPaso(pasoActual); // Mostramos el primer paso
                    // No mostramos los controles de navegación aún, solo el 'Siguiente'
                } else {
                    controlesNavegacion.style.display = 'none';
                }
            }

            // --- 5. FUNCIÓN PRINCIPAL DEL WIZARD (IDÉNTICA A CREATE) ---
            function mostrarPaso(indice) {
                diasGenerados.forEach((dia, i) => {
                    dia.style.display = (i === indice) ? 'block' : 'none';
                });

                // Visibilidad de botones
                botonAnterior.style.display = (indice === 0) ? 'none' : 'inline-block';
                botonSiguiente.style.display = (indice === diasGenerados.length - 1) ? 'none' :
                    'inline-block';

                // Mostrar "Guardar Cambios" SÓLO en el último paso
                seccionSubmitFinal.style.display = (indice === diasGenerados.length - 1) ? 'flex' : 'none';

                // Mostrar los controles (Siguiente/Anterior) en todos menos el último paso
                controlesNavegacion.style.display = 'flex';
            }

            // --- 6. EVENT LISTENERS PARA NAVEGACIÓN ---
            botonSiguiente.addEventListener('click', function() {
                if (pasoActual < diasGenerados.length - 1) {
                    pasoActual++;
                    mostrarPaso(pasoActual);
                }
            });

            botonAnterior.addEventListener('click', function() {
                if (pasoActual > 0) {
                    pasoActual--;
                    mostrarPaso(pasoActual);
                }
            });

            // --- 7. EJECUTAR LA GENERACIÓN AL CARGAR ---
            generarDiasDeEdicion();

            // --- 8. MENSAJES DE SWEETALERT (Como los teníamos) ---
            Swal.fire({
                title: 'Estás Editando la Semana Completa',
                icon: 'info',
                html: "Vas a editar la semana del <b>{{ $fechaInicioFormateada }}</b> al <b>{{ $fechaFinFormateada }}</b>." +
                    "<br><br>Puedes editar <b>los dias faltantes de tu semana</b> en esta misma pantalla." +
                    "<br><br><small>Recuerda: Las fechas no son editables.</small>",
                confirmButtonText: '¡Entendido!'
            });

            const editForm = document.getElementById('edit-week-form');
            editForm.addEventListener('submit', function(event) {
                event.preventDefault();
                Swal.fire({
                    title: '¿Confirmar Cambios?',
                    text: "Estás a punto de sobrescribir los datos de esta semana. Se actualizarán los Kilos y el Turno.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, guardar cambios',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        editForm.submit();
                    }
                });
            });

        }); // Cierre del 'DOMContentLoaded'
    </script>
</x-app-layout>
