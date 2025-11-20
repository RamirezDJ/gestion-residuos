<x-app-layout>
    <div
        class="px-4 pt-8 pb-5 mx-auto sm:max-w-xl md:max-w-full lg:max-w-screen-xl md:px-24 lg:px-8flex items-center justify-center">
        <div
            class="bg-white px-4 py-5  mx-auto rounded-lg sm:max-w-xl md:max-w-full lg:max-w-screen-xl md:px-24 lg:px-8 shadow">
            <div class="border-b-2 mb-4">
                <h2 class="font-semibold text-xl text-gray-600">Generación Semanal de residuos solidos en el
                    {{ $instituto->nombre }}
                </h2>
                <p class="text-lg text-gray-500 mb-4">Es importante contar con la bitacora de generación semanal para
                    poder
                    capturar lo que se generó durante la semana.</p>
            </div>
            <div>
                <form action="{{ route('gensemanal.store') }}" method="POST" id="create-week-form">

                    @csrf
                    <x-validation-errors class="mb-4" />

                    {{-- Datos generales (Primer apartado) --}}
                    <div class="text-gray-600 mb-2">
                        <p class="text-lg font-bold">Datos generales</p>
                        <p class="mb-3">Seleccione la semana de recolección.</p>
                    </div>
                    <div class="flex justify-between content-center border-b-2 py-4 mb-4">
                        <div class="w-1/2 px-2 mb-4">
                            <x-label for="datepicker_inicial" class="mb-2">
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
                                <input id="datepicker_inicial" datepicker datepicker-autohide
                                    datepicker-format="dd/mm/yyyy" type="text" name='fecha_inicial'
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="Seleccionar fecha" value="{{ old('fecha_inicial') }}" required>
                            </div>
                        </div>

                        <div class="w-1/2 px-2 mb-4">
                            <x-label for="datepicker_final" class="mb-2">
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
                                <input id="datepicker_final" datepicker datepicker-autohide
                                    datepicker-format="dd/mm/yyyy" type="text" name='fecha_final'
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="Seleccionar fecha" value="{{ old('fecha_final') }}" required>
                            </div>
                        </div>
                        <div class="w-1/2 px-2 mb-4"> <x-label class="mb-2">
                                Turno de la generacion
                            </x-label>
                            <div class="relative">
                                <x-select class="w-full" name="turno">
                                    <option disabled>
                                        -- Seleccione un turno --
                                    </option>
                                    <option value="Matutino">
                                        Matutino
                                    </option>
                                    <option value="Vespertino">
                                        Vespertino
                                    </option>
                                </x-select>
                            </div>
                        </div>
                        <div class="w-full px-2 mb-4"> <x-label class="mb-2">
                                Instituto Asignado
                            </x-label>
                            <div class="relative">
                                <x-input name="instituto" value="{{ $instituto->nombre ?? old('instituto') }}" readonly
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                            </div>
                        </div>
                    </div>

                    {{-- Inicio bitacora de generacion semanal (Cada zona con sus areas) --}}

                    <div class="flex justify-between items-center border-b-2 pb-4">
                        <div>
                            <p class="text-lg font-bold">Bitacora de generacion semanal</p>
                            <p class="mb-3">Todos los valores de esta seccion son numericos y se interpreta como kg*.
                            </p>
                        </div>
                        <button type="button" id="generar-dias"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Generar Días
                        </button>
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
                            {{-- Título del día (se llena con JS) --}}
                            <h3 class="text-xl font-semibold text-gray-700 mb-3 dia-titulo"></h3>

                            @foreach ($zonas as $zona)
                                {{-- Título de la Zona --}}
                                <div class="text-gray-600 mb-2">
                                    <p class="text-lg font-bold mb-4">{{ $zona->nombre }}</p>
                                </div>

                                {{-- Grid de Áreas --}}
                                <div
                                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-4 border-b-2 pb-4">

                                    @foreach ($zona->areas as $area)
                                        <div class="mb-4 col-span-1">

                                            {{-- Nombre del Área --}}
                                            <h4 class="text-md font-semibold text-gray-800 mb-2">
                                                {{ $area->nombre }}
                                            </h4>

                                            {{-- LÓGICA INTELIGENTE: Obtenemos categorías a través de los subproductos --}}
                                            @php
                                                // Sacamos los subproductos, obtenemos su categoría, filtramos únicas y ordenamos
                                                $categoriasDelArea = $area->subproductos
                                                    ->pluck('categoria')
                                                    ->unique('id')
                                                    ->sortBy('nombre');
                                            @endphp

                                            {{-- Lista de Inputs (Solo las categorías de esta área) --}}
                                            <div class="pl-3 space-y-2">
                                                @foreach ($categoriasDelArea as $categoria)
                                                    {{-- Si la categoría es válida (no nula) --}}
                                                    @if ($categoria)
                                                        <div class="mb-2">
                                                            <x-label class="mb-1 text-sm font-normal text-gray-600">
                                                                {{ $categoria->nombre }}
                                                            </x-label>

                                                            <x-input type="number" step="0.01" placeholder="0kg"
                                                                class="w-full" {{-- Usamos cat_ID para el controlador --}}
                                                                name="TEMPLATE_NAME[{{ $zona->id }}][{{ $area->id }}][cat_{{ $categoria->id }}]"
                                                                value="" />
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </template>

                    <div id="seccion-submit-final" class="flex justify-between items-baseline pt-4"
                        style="display: none;">
                        <div class="flex items-center mb-4">
                            <input id="default-checkbox" type="checkbox" value="" required
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            <label for="default-checkbox" class="ms-2 text-gray-900 dark:text-gray-300">Confirmo
                                que la informacion es correcta</label>
                        </div>
                        <x-button>
                            Crear nuevo registro
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.8.2/pikaday.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.8.2/css/pikaday.min.css">

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- 1. REFERENCIAS A ELEMENTOS ---
            // Elementos del formulario
            const botonGenerar = document.getElementById('generar-dias');
            const container = document.getElementById('dias-container');
            const template = document.getElementById('dia-template');
            const fechaInicialInput = document.getElementById('datepicker_inicial');
            const fechaFinalInput = document.getElementById('datepicker_final');

            // Nuevos elementos para el "wizard" (asistente)
            const controlesNavegacion = document.getElementById('controles-navegacion');
            const botonAnterior = document.getElementById('boton-anterior');
            const botonSiguiente = document.getElementById('boton-siguiente');
            const seccionSubmitFinal = document.getElementById('seccion-submit-final');

            // --- 2. VARIABLES DE ESTADO ---
            let pasoActual = 0;
            let diasGenerados = []; // Un array para guardar los divs de los días

            // --- 3. BOTÓN PRINCIPAL: GENERAR DÍAS ---
            botonGenerar.addEventListener('click', function() {
                // Limpiamos todo para una nueva generación
                container.innerHTML = '';
                diasGenerados = [];
                pasoActual = 0;

                // --- Validaciones de Fechas (Sin cambios) ---
                const fechaInicialStr = parsearFecha(fechaInicialInput.value);
                const fechaFinalStr = parsearFecha(fechaFinalInput.value);

                if (!fechaInicialStr || !fechaFinalStr) {
                    alert('Por favor, seleccione una fecha inicial y final válidas.');
                    return;
                }

                const fechaInicial = new Date(fechaInicialStr + 'T00:00:00');
                const fechaFinal = new Date(fechaFinalStr + 'T00:00:00');

                if (fechaFinal < fechaInicial) {
                    alert('La fecha final no puede ser anterior a la fecha inicial.');
                    return;
                }

                const diffTime = Math.abs(fechaFinal - fechaInicial);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                if (diffDays > 6) {
                    alert('El rango seleccionado no puede ser mayor a 7 días (1 semana).');
                    return;
                }
                // --- Fin de Validaciones ---


                // --- Lógica de Generación (Bucle while) ---
                let fechaActual = new Date(fechaInicial);
                while (fechaActual <= fechaFinal) {

                    // Clonamos la plantilla
                    const diaClonado = template.content.cloneNode(true);

                    // Formateamos fechas
                    const fechaISO = fechaActual.toISOString().split('T')[0];
                    const fechaVisible = fechaActual.toLocaleDateString('es-ES', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });
                    const diaSemana = fechaActual.toLocaleDateString('es-ES', {
                        weekday: 'long'
                    });

                    // Actualizamos el título del día
                    diaClonado.querySelector('.dia-titulo').textContent =
                        `Registro del ${diaSemana}, ${fechaVisible}`;

                    // Actualizamos el 'name' de todos los inputs en el clon
                    const inputs = diaClonado.querySelectorAll('input');
                    inputs.forEach(input => {
                        input.name = input.name.replace(
                            'TEMPLATE_NAME',
                            `valor_kg[${fechaISO}]`
                        );
                    });

                    // Añadimos el día clonado al DOM
                    container.appendChild(diaClonado);

                    // Guardamos la REFERENCIA al div del día en nuestro array
                    // Usamos .lastElementChild porque el clon se añade dentro del container
                    diasGenerados.push(container.lastElementChild);

                    // Avanzamos al siguiente día
                    fechaActual.setDate(fechaActual.getDate() + 1);
                }

                // --- 4. INICIALIZAR EL WIZARD ---
                if (diasGenerados.length > 0) {
                    mostrarPaso(pasoActual); // Mostramos el primer paso (Lunes)
                    controlesNavegacion.style.display = 'flex'; // Mostramos los botones "Siguiente"
                    seccionSubmitFinal.style.display = 'none'; // Nos aseguramos que el submit esté oculto
                }
            });

            // --- 5. EVENT LISTENERS PARA NAVEGACIÓN ---
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

            // --- 6. FUNCIÓN PRINCIPAL DEL WIZARD ---
            function mostrarPaso(indice) {
                // Ocultamos todos los días
                diasGenerados.forEach((dia, i) => {
                    dia.style.display = (i === indice) ? 'block' : 'none';
                });

                // Actualizamos visibilidad de botones
                // Ocultar "Anterior" si es el primer paso
                botonAnterior.style.display = (indice === 0) ? 'none' : 'inline-block';

                // Ocultar "Siguiente" si es el último paso
                botonSiguiente.style.display = (indice === diasGenerados.length - 1) ? 'none' : 'inline-block';

                // Mostramos el botón de "Crear Registro" SÓLO en el último paso
                seccionSubmitFinal.style.display = (indice === diasGenerados.length - 1) ? 'flex' : 'none';
            }

            // --- 7. FUNCIÓN HELPER (sin cambios) ---
            function parsearFecha(fechaStr) {
                const partes = fechaStr.split('/');
                if (partes.length === 3) {
                    // [dd, mm, yyyy] -> yyyy-mm-dd
                    return `${partes[2]}-${partes[1]}-${partes[0]}`;
                }
                return null;
            }
        });

        const createForm = document.getElementById('create-week-form');

        // 2. Escuchamos el evento 'submit'
        createForm.addEventListener('submit', function(event) {

            // 3. Prevenimos el envío automático
            event.preventDefault();

            // 4. Mostramos la confirmación de SweetAlert
            Swal.fire({
                title: '¿Guardar?',
                // Usamos html para el salto de línea
                html: "Estas a puntos de crear un nuevo registro.<br><b>¡La fechas no podrá modificarse después!</b>",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d', // Un gris para cancelar
                confirmButtonText: 'Sí, crear registro',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                // 5. Si el usuario confirma...
                if (result.isConfirmed) {
                    // ...enviamos el formulario.
                    createForm.submit();
                }
            });
        });
    </script>
</x-app-layout>
