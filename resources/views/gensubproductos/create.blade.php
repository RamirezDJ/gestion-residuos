<x-app-layout>
    {{-- Contenedor Principal (Gris de fondo) --}}
    <div
        class="px-4 pt-8 pb-5 mx-auto sm:max-w-xl md:max-w-full lg:max-w-screen-xl md:px-24 lg:px-8 flex items-center justify-center">

        {{-- Tarjeta Blanca Unificada (Contiene Título y Formulario) --}}
        <div
            class="bg-white px-4 py-5 mx-auto rounded-lg w-full shadow-lg border border-gray-100 sm:max-w-xl md:max-w-full lg:max-w-screen-xl md:px-24 lg:px-8">

            {{-- 1. ENCABEZADO (Dentro de la tarjeta) --}}
            <div class="border-b-2 mb-6 pb-4">
                <h2 class="font-semibold text-xl text-gray-600">
                    Generación Semanal de Subproductos en el
                    {{ auth()->user()->instituto->nombre_corto ?? 'Instituto' }}
                </h2>
                <p class="text-lg text-gray-500 mt-1">
                    Es importante contar con la bitácora de subproductos para poder capturar lo que se generó durante la
                    semana.
                </p>
            </div>

            <form action="{{ route('gensubproductos.store') }}" method="POST" id="form-wizard">
                @csrf
                <div id="seccion-fechas">
                    <div class="text-gray-600 mb-2">
                        <p class="text-lg font-bold">Datos generales</p>
                        <p class="mb-3">Seleccione la semana de recolección.</p>
                    </div>

                    {{-- Contenedor del Datepicker --}}
                    <div id="date-range-picker" date-rangepicker
                        class="flex flex-col md:flex-row justify-between content-center border-b-2 py-4 mb-4 gap-4">

                        {{-- Fecha Inicial --}}
                        <div class="w-full md:w-1/3">
                            <label for="fecha_inicio" class="block text-gray-700 font-bold mb-2 text-sm">Fecha
                                Inicial</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                    </svg>
                                </div>
                                {{-- CORRECCIÓN: Se agregó datepicker-format="dd/mm/yyyy" --}}
                                <input id="fecha_inicio" name="fecha_inicial" type="text"
                                    datepicker-format="dd/mm/yyyy"
                                    class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 shadow-sm"
                                    placeholder="Seleccionar fecha" required>
                            </div>
                        </div>

                        {{-- Fecha Final --}}
                        <div class="w-full md:w-1/3">
                            <label for="fecha_fin" class="block text-gray-700 font-bold mb-2 text-sm">Fecha
                                Final</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                    </svg>
                                </div>
                                {{-- CORRECCIÓN: Se agregó datepicker-format="dd/mm/yyyy" --}}
                                <input id="fecha_fin" name="fecha_final" type="text" datepicker-format="dd/mm/yyyy"
                                    class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 shadow-sm"
                                    placeholder="Seleccionar fecha" required>
                            </div>
                        </div>

                        {{-- Botón Generar --}}
                        <div class="w-full md:w-auto flex items-end">
                            <button type="button" id="btn-generar"
                                class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow transition text-sm h-[42px]">
                                Generar Días
                            </button>
                        </div>
                    </div>
                </div>

                <x-validation-errors class="mb-4" />

                {{-- 3. CONTENEDOR DEL WIZARD (Aquí JS pintará las tablas) --}}
                <div id="wizard-container" class="mt-4">
                    {{-- El contenido se genera dinámicamente --}}
                </div>

            </form>
        </div>
    </div>

    {{-- ESTILOS CSS --}}
    <style>
        .step-content {
            display: none;
        }

        .step-content.active {
            display: block;
            animation: fadeIn 0.4s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    @push('js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/datepicker.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            const zonas = @json($zonas);
            const subproductos = @json($subproductos);
            let diasSeleccionados = [];
            const diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

            document.getElementById('btn-generar').addEventListener('click', function() {
                const fInicioRaw = document.getElementById('fecha_inicio').value;
                const fFinRaw = document.getElementById('fecha_fin').value;
                const btn = this; // Referencia al botón

                if (!fInicioRaw || !fFinRaw) {
                    Swal.fire('Atención', 'Seleccione ambas fechas para continuar.', 'warning');
                    return;
                }

                // 1. LEER FECHAS SEGURAS
                function crearFechaSegura(fechaStr) {
                    const partes = fechaStr.split('/');
                    return new Date(partes[2], partes[0] - 1, partes[1]);
                }

                const inicio = crearFechaSegura(fInicioRaw);
                const fin = crearFechaSegura(fFinRaw);

                // 2. VALIDACIONES
                if (isNaN(inicio.getTime()) || isNaN(fin.getTime())) {
                    Swal.fire('Error', 'Formato de fecha no válido.', 'error');
                    return;
                }
                if (inicio > fin) {
                    Swal.fire('Error', 'La fecha inicial no puede ser mayor a la final.', 'error');
                    return;
                }

                // 3. CHECK AJAX
                const inicioISO = inicio.toISOString().split('T')[0];
                const finISO = fin.toISOString().split('T')[0];

                const textoOriginal = btn.innerText;
                btn.innerText = 'Verificando...';
                btn.disabled = true; // Bloqueo temporal solo mientras carga

                fetch(`{{ route('gensubproductos.checkWeek') }}?inicio=${inicioISO}&final=${finISO}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Semana ya registrada',
                                text: 'Ya existen registros en este rango. Verifique sus fechas.',
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Entendido'
                            }).then((result) => {
                                // AQUÍ ESTÁ EL CAMBIO:
                                // Si el usuario da clic en el botón, recargamos la página
                                if (result.isConfirmed) {
                                    window.location.reload();
                                }
                            });

                            // Aunque se vaya a recargar, por seguridad reseteamos el botón visualmente un instante
                            btn.innerText = textoOriginal;
                            btn.disabled = false;
                            return;
                        }

                        // SI TODO ESTÁ BIEN: GENERAMOS LA TABLA
                        generarTablaWizard(inicio, fin);

                        // RESTO DEL CÓDIGO NORMAL...
                        btn.innerText = 'Actualizar Días';
                        btn.disabled = false;

                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'bottom-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true, // Opcional: se ve bonito
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer)
                                toast.addEventListener('mouseleave', Swal.resumeTimer)
                            }
                        });
                        Toast.fire({
                            icon: 'success',
                            title: 'Tabla generada correctamente'
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'No se pudo verificar la semana.', 'error');
                        btn.innerText = textoOriginal;
                        btn.disabled = false;
                    });
            });

            // (El resto de tus funciones generarTablaWizard, renderizarWizard, etc. se quedan igual)

            // 5. FUNCIÓN SEPARADA PARA GENERAR EL WIZARD (Para mantener el código limpio)
            function generarTablaWizard(inicio, fin) {
                diasSeleccionados = [];
                let d = new Date(inicio);
                let contadorSeguridad = 0;

                while (d <= fin) {
                    if (contadorSeguridad > 20) {
                        Swal.fire('Error', 'El rango de fechas es demasiado grande (máx 20 días).', 'error');
                        return;
                    }

                    const year = d.getFullYear();
                    const month = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');

                    diasSeleccionados.push({
                        fecha: `${year}-${month}-${day}`,
                        diaNombre: diasSemana[d.getDay()],
                        diaCorto: `${day}/${month}`
                    });

                    d.setDate(d.getDate() + 1);
                    contadorSeguridad++;
                }

                renderizarWizard();
            }

            // --- RENDERIZADOR (Se mantiene igual) ---
            function renderizarWizard() {
                const container = document.getElementById('wizard-container');
                container.innerHTML = '';

                if (zonas.length === 0) {
                    container.innerHTML = '<p class="text-red-500 font-bold">No hay zonas registradas.</p>';
                    return;
                }

                const numCols = 2 + diasSeleccionados.length;

                zonas.forEach((zona, index) => {
                    const esUltimo = index === zonas.length - 1;

                    const stepDiv = document.createElement('div');
                    stepDiv.classList.add('step-content');
                    stepDiv.dataset.step = index;
                    if (index === 0) stepDiv.classList.add('active');

                    let html = `
                    <div class="mb-6 mt-2">
                        <div class="flex justify-between items-end mb-4 pb-2 border-b border-gray-200">
                            <div>
                                <span class="text-xs font-bold text-gray-600 uppercase tracking-widest">Zona Activa</span>
                                <h3 class="text-2xl font-bold text-gray-700">${zona.nombre}</h3>
                            </div>
                            <span class="text-gray-400 text-sm italic">Paso ${index + 1} de ${zonas.length}</span>
                        </div>

                        <div class="overflow-x-auto pb-4">
                            <div class="w-full min-w-[800px] grid gap-1 gap-y-1 text-sm shadow-sm p-1 border border-gray-100 rounded" style="grid-template-columns: repeat(${numCols}, minmax(0, 1fr));">
                                
                                <div class="col-span-2 bg-yellow-400 text-center text-lg font-medium p-2 rounded-sm flex items-center justify-center border border-yellow-500/20">
                                    <span>Subproductos</span>
                                </div>
                                
                                ${diasSeleccionados.map(dia => `
                                                                                    <div class="col-span-1 bg-blue-600 text-center text-white p-2 rounded-sm border border-blue-700/20">
                                                                                        <div class="text-base font-bold">${dia.diaNombre}</div>
                                                                                        <div class="text-xs opacity-80">${dia.diaCorto}</div>
                                                                                    </div>
                                                                                `).join('')}

                                ${subproductos.map(sub => `
                                                                                    <div class="col-span-2 mb-1 mt-1">
                                                                                        <div class="w-full bg-gray-50 border border-gray-200 rounded px-3 py-2 text-gray-700 font-medium">
                                                                                            ${sub.nombre}
                                                                                        </div>
                                                                                    </div>

                                                                                    ${diasSeleccionados.map((dia, i) => `
                                        <div class="col-span-1 mb-1 mt-1">
                                            <input type="number" step="0.01" min="0" 
                                                name="valores[${zona.id}][${sub.id}][${dia.fecha}]"
                                                class="w-full valor-dia-${index} border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500 p-2 text-center shadow-sm"
                                                placeholder="0"
                                                data-dia-index="${i}" 
                                                oninput="recalcularTotales(${index}, ${i})">
                                        </div>
                                    `).join('')}
                                                                                `).join('')}

                                <div class="col-span-2 bg-green-400 text-center text-lg font-medium p-2 rounded-sm mt-2 border border-green-500/20">
                                    <span>Total</span>
                                </div>

                                ${diasSeleccionados.map((dia, i) => `
                                                                                    <div class="col-span-1 bg-gray-100 text-center text-lg font-bold p-2 rounded-sm mt-2 border border-gray-300 text-gray-700">
                                                                                        <span id="total-zona-${index}-dia-${i}">0.00</span> <span class="text-xs font-normal text-gray-500">kg</span>
                                                                                    </div>
                                                                                `).join('')}

                            </div>
                        </div>

                        <div class="flex justify-between mt-8 pt-4 border-t border-gray-100">
                            <button type="button" 
                                class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded shadow ${index === 0 ? 'invisible' : ''}"
                                onclick="cambiarPaso(${index - 1})">
                                Anterior
                            </button>

                            ${esUltimo 
                                ? `<button type="button" onclick="confirmarGuardado()" class="bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 px-8 rounded shadow-lg uppercase tracking-wide text-sm transform hover:scale-105 transition">
                                                                                     Guardar Registro
                                                                                   </button>`
                                : `<button type="button" onclick="cambiarPaso(${index + 1})" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-8 rounded shadow uppercase text-sm">
                                                                                     Siguiente
                                                                                   </button>`
                            }
                        </div>
                    </div>
                `;

                    stepDiv.innerHTML = html;
                    container.appendChild(stepDiv);
                });
            }

            window.cambiarPaso = function(stepIndex) {
                document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
                const target = document.querySelector(`.step-content[data-step="${stepIndex}"]`);
                if (target) {
                    target.classList.add('active');
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                }
            };

            window.recalcularTotales = function(zonaIndex, diaIndex) {
                let total = 0;
                const inputs = document.querySelectorAll(`.valor-dia-${zonaIndex}[data-dia-index="${diaIndex}"]`);
                inputs.forEach(input => {
                    total += parseFloat(input.value) || 0;
                });
                document.getElementById(`total-zona-${zonaIndex}-dia-${diaIndex}`).innerText = total.toFixed(2);
            };

            window.confirmarGuardado = function() {
                Swal.fire({
                    title: '¿Guardar?',
                    html: "Verifica que todos los datos sean correctos.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#1e293b',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, guardar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('form-wizard').submit();
                    }
                });
            };
        </script>
    @endpush
</x-app-layout>
