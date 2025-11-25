<x-app-layout>
    {{-- 1. CÁLCULOS PREVIOS EN PHP PARA EL RESUMEN --}}
    @php
        // Suma total
        $totalKg = $datosRegistrados->sum('valor_kg');

        // Subproducto mayor
        $mayorSubproductoId = $datosRegistrados
            ->groupBy('subproducto_id')
            ->map(fn($row) => $row->sum('valor_kg'))
            ->sortDesc()
            ->keys()
            ->first();

        $nombreMayor = $subproductos->where('id', $mayorSubproductoId)->first()->nombre ?? 'N/A';

        // Formato de fechas para mostrar
        $fechaInicioFmt = \Carbon\Carbon::parse($inicio)->format('d/m/Y');
        $fechaFinFmt = \Carbon\Carbon::parse($final)->format('d/m/Y');
    @endphp

    <div
        class="px-4 pt-8 pb-5 mx-auto sm:max-w-xl md:max-w-full lg:max-w-screen-xl md:px-24 lg:px-8 flex items-center justify-center">
        <div
            class="bg-white px-4 py-5 mx-auto rounded-lg w-full shadow-lg border border-gray-100 sm:max-w-xl md:max-w-full lg:max-w-screen-xl md:px-24 lg:px-8">

            {{-- === NUEVO ENCABEZADO ESTILO GENSEMANAL === --}}

            <div class="border-b-2 mb-4">
                <h2 class="font-semibold text-xl text-gray-600">
                    Editando Generación de Subproductos en {{ $instituto->nombre }}
                </h2>
                <p class="text-lg text-gray-500 mb-4">
                    Ajuste los valores generados durante la semana del <strong>{{ $fechaInicioFmt }}</strong> al
                    <strong>{{ $fechaFinFmt }}</strong>.
                </p>
            </div>

            {{-- FORMULARIO --}}
            <form action="{{ route('gensubproductos.updateMultiple') }}" method="POST" id="form-wizard">
                @csrf
                @method('PUT')

                <input type="hidden" name="instituto_id" value="{{ $instituto_id }}">
                <input type="hidden" name="inicio" value="{{ $inicio }}">
                <input type="hidden" name="final" value="{{ $final }}">

                <x-validation-errors class="mb-4" />

                {{-- DATOS GENERALES (VISUALES) --}}
                <div class="text-gray-600 mb-2">
                    <p class="text-lg font-bold">Datos generales</p>
                    <p class="mb-3">Resumen de la semana a editar.</p>
                </div>

                <div class="flex flex-wrap border-b-2 py-4 mb-4">
                    {{-- Fecha Inicial --}}
                    <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                        <x-label for="fecha_inicial_display" class="mb-1">Fecha Inicial</x-label>
                        <div class="relative">
                            <x-input id="fecha_inicial_display" type="text" value="{{ $fechaInicioFmt }}" disabled
                                class="w-full bg-gray-100 text-center font-medium" />
                        </div>
                    </div>

                    {{-- Fecha Final --}}
                    <div class="w-full md:w-1/2 lg:w-1/4 px-2 mb-4">
                        <x-label for="fecha_final_display" class="mb-1">Fecha Final</x-label>
                        <div class="relative">
                            <x-input id="fecha_final_display" type="text" value="{{ $fechaFinFmt }}" disabled
                                class="w-full bg-gray-100 text-center font-medium" />
                        </div>
                    </div>
                </div>

                {{-- CONTENEDOR DEL WIZARD (TABLAS) --}}
                <div id="wizard-container" class="mt-4 min-h-[300px]">
                    <div class="flex justify-center items-center py-12 text-gray-400">
                        <svg class="animate-spin h-8 w-8 mr-3" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Cargando registros...
                    </div>
                </div>

            </form>
        </div>
    </div>

    {{-- ESTILOS PARA EL WIZARD --}}
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
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            const zonas = @json($zonas);
            const subproductos = @json($subproductos);
            const datosDB = @json($datosRegistrados);

            // Fechas (Ya formateadas YYYY-MM-DD desde el controller)
            const fInicioStr = "{{ $inicio }}";
            const fFinStr = "{{ $final }}";

            let diasSeleccionados = [];
            const diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

            const lookup = {};
            datosDB.forEach(dato => {
                if (!lookup[dato.zona_id]) lookup[dato.zona_id] = {};
                if (!lookup[dato.zona_id][dato.subproducto_id]) lookup[dato.zona_id][dato.subproducto_id] = {};
                const fechaLimpia = dato.fecha.split(' ')[0];
                lookup[dato.zona_id][dato.subproducto_id][fechaLimpia] = dato.valor_kg;
            });

            document.addEventListener('DOMContentLoaded', () => {
                iniciarWizard();
            });

            function iniciarWizard() {
                const [y1, m1, d1] = fInicioStr.split('-').map(Number);
                const [y2, m2, d2] = fFinStr.split('-').map(Number);
                const inicio = new Date(y1, m1 - 1, d1);
                const fin = new Date(y2, m2 - 1, d2);

                diasSeleccionados = [];
                let d = new Date(inicio);

                while (d <= fin) {
                    const year = d.getFullYear();
                    const month = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    diasSeleccionados.push({
                        fecha: `${year}-${month}-${day}`,
                        diaNombre: diasSemana[d.getDay()],
                        diaCorto: `${day}/${month}`
                    });
                    d.setDate(d.getDate() + 1);
                }

                renderizarHTML();

                zonas.forEach((zona, zIndex) => {
                    diasSeleccionados.forEach((dia, dIndex) => {
                        window.recalcularTotales(zIndex, dIndex);
                    });
                });
            }

            function renderizarHTML() {
                const container = document.getElementById('wizard-container');
                container.innerHTML = '';

                if (zonas.length === 0) {
                    container.innerHTML =
                        '<div class="text-center text-red-500 py-10 font-bold">No hay zonas configuradas.</div>';
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
                    <div class="mb-2">
                        <div class="flex justify-between items-end mb-4 border-b pb-2">
                            <div>
                                <span class="text-xs font-bold text-gray-600 uppercase tracking-widest">Editando Zona</span>
                                <h3 class="text-2xl font-bold text-gray-800">${zona.nombre}</h3>
                            </div>
                            <span class="text-sm text-gray-400 italic">Paso ${index + 1} de ${zonas.length}</span>
                        </div>

                        <div class="overflow-x-auto pb-4">
                            <div class="w-full min-w-[800px] grid gap-1 gap-y-1 text-sm shadow-sm p-1 border border-gray-200 rounded bg-gray-50" 
                                 style="grid-template-columns: repeat(${numCols}, minmax(0, 1fr));">
                                
                                <div class="col-span-2 bg-yellow-400 text-gray-900 text-center font-bold p-2 rounded-sm flex items-center justify-center">
                                    Subproductos
                                </div>
                                
                                ${diasSeleccionados.map(dia => `
                                                    <div class="col-span-1 bg-blue-700 text-white text-center p-2 rounded-sm">
                                                        <div class="font-bold">${dia.diaNombre}</div>
                                                        <div class="text-xs opacity-75">${dia.diaCorto}</div>
                                                    </div>
                                                `).join('')}

                                ${subproductos.map(sub => {
                                    return `
                                                    <div class="col-span-2 mt-1">
                                                        <div class="w-full bg-white border border-gray-300 rounded px-3 py-2 text-gray-700 font-medium h-full flex items-center">
                                                            ${sub.nombre}
                                                        </div>
                                                    </div>

                                                    ${diasSeleccionados.map((dia, i) => {
                                                        let valor = '';
                                                        if (lookup[zona.id] && lookup[zona.id][sub.id] && lookup[zona.id][sub.id][dia.fecha]) {
                                                            valor = lookup[zona.id][sub.id][dia.fecha];
                                                        }

                                                        return `
                                        <div class="col-span-1 mt-1">
                                            <input type="number" step="0.01" min="0" 
                                                name="valores[${zona.id}][${sub.id}][${dia.fecha}]"
                                                class="w-full valor-dia-${index} border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500 p-2 text-center font-bold text-gray-700 h-full shadow-sm"
                                                placeholder="-"
                                                value="${valor}"
                                                data-dia-index="${i}" 
                                                oninput="recalcularTotales(${index}, ${i})">
                                        </div>
                                        `;
                }).join('')
            }
            `;
                                }).join('')}

                                <div class="col-span-2 bg-green-500 text-white text-center font-bold p-2 rounded-sm mt-2">
                                    Total Día
                                </div>

                                ${diasSeleccionados.map((dia, i) => `
                                                    <div class="col-span-1 bg-white border border-gray-300 text-center font-bold p-2 rounded-sm mt-2 text-gray-800 shadow-sm">
                                                        <span id="total-zona-${index}-dia-${i}">0.00</span> 
                                                        <span class="text-xs text-gray-400 font-normal">kg</span>
                                                    </div>
                                                `).join('')}

                            </div>
                        </div>

                        <div class="flex justify-between mt-6 pt-4 border-t border-gray-100">
                            <button type="button" 
                                class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded shadow ${index === 0 ? 'invisible' : ''}"
                                onclick="cambiarPaso(${index - 1})">
                                <i class="fa-solid fa-arrow-left mr-2"></i> Anterior
                            </button>

                            ${esUltimo 
                                ? `<button type="button" onclick="confirmarGuardado()" class="bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 px-8 rounded shadow-lg uppercase text-sm transform hover:scale-105 transition">
                                                        Guardar Cambios <i class="fa-solid fa-save ml-2"></i>
                                                   </button>`
                                : `<button type="button" onclick="cambiarPaso(${index + 1})" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-8 rounded shadow uppercase text-sm">
                                                        Siguiente Zona <i class="fa-solid fa-arrow-right ml-2"></i>
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
                    title: '¿Confirmar cambios?',
                    text: "Se actualizarán los registros. Si dejó campos vacíos o en 0, se eliminarán/actualizarán a 0.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#1e293b',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, actualizar'
                }).then((result) => {
                    if (result.isConfirmed) document.getElementById('form-wizard').submit();
                });
            };
        </script>
    @endpush
</x-app-layout>
