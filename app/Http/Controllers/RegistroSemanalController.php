<?php

namespace App\Http\Controllers;

use App\Exports\RegistroSemanalExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\GenSemanal;
use App\Models\Zona;
use App\Models\Area;
use App\Models\ZonasAreas;
use App\Models\Subproducto;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RegistroSemanalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $institutoId = auth()->user()->instituto_id;

        // Obtenemos el filtro de 'tiempo' de la URL. Si no hay, 'general' es el default.
        $tiempo = $request->input('tiempo', 'general');

        $queryBase = GenSemanal::join('zonas_areas', 'gen_semanals.zonas_areas_id', '=', 'zonas_areas.id')
            ->join('zonas', 'zonas_areas.zona_id', '=', 'zonas.id')
            ->where('zonas.instituto_id', $institutoId);

        $viewName = '';

        if ($tiempo == 'zonas_areas') {
            // --- FILTRO 1: VISTA DETALLADA (La que ya tenemos) ---
            // Agrupada por Fecha, Turno, Zona y Área
            $registros = $queryBase->select(
                'gen_semanals.fecha',
                'gen_semanals.turno',
                'zonas.nombre as zona',
                'areas.nombre as areaAsignada',
                DB::raw('SUM(gen_semanals.kilos) as total_kilos_area')
            )
                ->join('areas', 'zonas_areas.area_id', '=', 'areas.id')
                ->groupBy('gen_semanals.fecha', 'gen_semanals.turno', 'zonas.nombre', 'areas.nombre')
                ->orderBy('gen_semanals.fecha', 'DESC')
                ->get();

            $viewName = 'gensemanal.partials.table-detalle'; // Usaremos una vista para esta tabla

        } else if ($tiempo == 'zonas_conteo') {
            // --- FILTRO 2: VISTA POR ZONAS (Tu "Zona y numero de areas") ---
            // Agrupada solo por Zona, sumando todo
            $registros = $queryBase->select(
                'zonas.nombre as zona',
                DB::raw('SUM(gen_semanals.kilos) as total_kilos_zona'),
                DB::raw('COUNT(DISTINCT zonas_areas.area_id) as conteo_areas') // Contar áreas únicas
            )
                ->groupBy('zonas.nombre')
                ->orderBy('zonas.nombre')
                ->get();

            $viewName = 'gensemanal.partials.table-zonas'; // Nueva vista

        } else {
            // --- FILTRO 3: VISTA GENERAL (POR SEMANA CALENDARIO) ---
            $registros = $queryBase->select(

                // 1. Calcula el Lunes
                DB::raw('DATE_ADD(MIN(gen_semanals.fecha), INTERVAL(-WEEKDAY(MIN(gen_semanals.fecha))) DAY) as fecha_inicio'),
                // 2. Calcula el Domingo
                DB::raw('DATE_ADD(MIN(gen_semanals.fecha), INTERVAL(6 - WEEKDAY(MIN(gen_semanals.fecha))) DAY) as fecha_final'),

                DB::raw('SUM(gen_semanals.kilos) as total_kilos_semana'),
                DB::raw('YEARWEEK(gen_semanals.fecha, 1) as anio_semana') // gen_semanals.fecha aquí
            )
                ->groupBy('anio_semana')
                ->orderBy(DB::raw('MIN(gen_semanals.fecha)'), 'DESC') // gen_semanals.fecha aquí
                ->get();

            $viewName = 'gensemanal.partials.table-general';
        }

        // Si la petición es AJAX (de la búsqueda), solo devolvemos la tabla
        if ($request->ajax()) {
            return view($viewName, compact('registros'));
        }

        // Si es una carga de página normal, devolvemos la página completa
        return view('gensemanal.index', compact('registros', 'viewName', 'tiempo'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->check()) {
            return redirect()->route('login')->withErrors(['msg' => 'Su sesión ha expirado. Por favor, inicie sesión nuevamente.']);
        }
        $instituto = auth()->user()->instituto;
        if (!auth()->user()->instituto_id) {
            return redirect()->back()->withErrors(['msg' => 'Para guardar una evidencia necesita tener una universidad asociada.']);
        }

        // === NUEVA CONSULTA ===
        // 1. Obtiene las Zonas del instituto
        $zonas = Zona::where('instituto_id', $instituto->id)
            ->orderBy('nombre')
            ->with([
                // 2. Por cada Zona, carga sus Áreas (usando la tabla 'zonas_areas')
                //    y también carga los subproductos de CADA una de esas áreas
                'areas.subproductos' => function ($query) {
                    $query->orderBy('subproductos.nombre');
                }
            ])
            ->get();

        // Nota: La relación 'areas' en el modelo 'Zona.php' debe estar definida
        // para que 'areas.subproductos' funcione.

        return view('gensemanal.create', compact('instituto', 'zonas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. VALIDACIÓN ACTUALIZADA
        // Ahora validamos fecha_inicial y fecha_final, no 'fecha'
        $validated = $request->validate([
            'fecha_inicial' => 'required|date_format:d/m/Y',
            'fecha_final'   => 'required|date_format:d/m/Y|after_or_equal:fecha_inicial',
            'turno'         => 'required|string|max:255',
            'valor_kg'      => 'required|array', // Este es el array 4D de la vista
        ]);

        $turno = $validated['turno'];
        $bitacoraCompleta = $validated['valor_kg']; // Contiene [fecha][zona][area][subproducto]

        // 2. VERIFICACIÓN DE REGISTROS EXISTENTES (MEJORADA)
        // Obtenemos las fechas del array que envió el JS (ya están en YYYY-MM-DD)
        $fechasEnviadas = array_keys($bitacoraCompleta);

        // Verificamos si ya existe algún registro en CUALQUIERA de esos días y ese turno
        $registroExistente = GenSemanal::whereIn('fecha', $fechasEnviadas)
            ->where('turno', $turno)
            ->exists();

        if ($registroExistente) {
            return redirect()->back()->withErrors([
                'msg' => "Ya existe un registro para al menos una de las fechas y turno seleccionados. Por favor, edite los registros existentes en lugar de crear nuevos.",
            ]);
        }

        // 3. LÓGICA DE GUARDADO (4 BUCLES)
        // Iterar sobre los datos de generación semanal

        // Nivel 1: Iterar por DÍA (ej. '2025-09-30')
        foreach ($bitacoraCompleta as $fechaDelRegistro => $zonas) {

            // Nivel 2: Iterar por ZONA (ej. 'zona_1')
            foreach ($zonas as $zonaId => $areas) {

                // Nivel 3: Iterar por ÁREA (ej. 'area_10')
                foreach ($areas as $areaId => $subproductos) {

                    // Buscamos el ID de la relación zona-área UNA VEZ por área
                    $zonaArea = ZonasAreas::where('zona_id', $zonaId)
                        ->where('area_id', $areaId)
                        ->first();

                    // Si esta combinación de zona-área no es válida, saltamos
                    if (!$zonaArea) {
                        continue;
                    }

                    // Nivel 4: Iterar por SUBPRODUCTO (ej. 'sub_1')
                    foreach ($subproductos as $subproductoId => $kilos) {

                        // Evitar guardar valores vacíos, nulos o ceros
                        if (empty($kilos) || !is_numeric($kilos) || $kilos <= 0) {
                            continue;
                        }

                        // Crear el registro con la NUEVA estructura de BD
                        GenSemanal::create([
                            'zonas_areas_id' => $zonaArea->id,
                            'fecha'          => $fechaDelRegistro, // Fecha Y-m-d del bucle
                            'turno'          => $turno,
                            'subproducto_id' => $subproductoId,  // <--- ¡NUEVA COLUMNA!
                            'kilos'          => $kilos,          // <--- ¡COLUMNA RENOMBRADA!
                        ]);
                    }
                }
            }
        }

        // 4. RESPUESTA DE ÉXITO (Sin cambios)
        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Hecho!',
            'text' => 'Los datos se han guardado con éxito',
        ]);

        return redirect()->route('gensemanal.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $fecha) // Recibe la fecha de UN día
    {
        if (!auth()->check()) { /* ... tu código de auth ... */
        }
        $instituto = auth()->user()->instituto;
        if (!$instituto) { /* ... tu código de instituto ... */
        }

        // 1. CALCULAR LA SEMANA COMPLETA
        $fechaCarbon = Carbon::createFromFormat('Y-m-d', $fecha);
        $fechaInicioSemana = $fechaCarbon->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $fechaFinSemana = $fechaCarbon->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

        // 2. OBTENER LA ESTRUCTURA (Zonas -> Areas -> Subproductos) - Sin cambios
        $zonas = Zona::where('instituto_id', $instituto->id)
            ->orderBy('nombre')
            ->with(['areas.subproductos' => function ($query) {
                $query->orderBy('subproductos.nombre');
            }])
            ->get();

        // 3. OBTENER LOS DATOS DE TODA LA SEMANA
        $datosSemana = GenSemanal::whereBetween('fecha', [$fechaInicioSemana, $fechaFinSemana])
            // ->where('turno', $turno) // ¿Filtramos por turno o mostramos ambos? Por ahora lo quito.
            ->join('zonas_areas', 'gen_semanals.zonas_areas_id', '=', 'zonas_areas.id')
            ->join('zonas', 'zonas_areas.zona_id', '=', 'zonas.id')
            ->where('zonas.instituto_id', $instituto->id)
            ->select(
                'gen_semanals.fecha', // <-- Necesitamos la fecha de cada registro
                'gen_semanals.turno', // <-- Necesitamos el turno
                'gen_semanals.kilos',
                'zonas_areas.area_id',
                'gen_semanals.subproducto_id',
                'zonas.id as zona_id',
                'zonas.nombre as zona_nombre'
            )
            ->orderBy('fecha') // Ordenamos por fecha para agrupar
            ->get();

        // 4. TRANSFORMAR LOS DATOS PARA LA VISTA
        $lookupDataSemanal = []; // Estructura: $lookup[fecha][area_id][subproducto_id] = kilos
        $totalPorZonaSemana = [];
        $totalGeneradoSemana = 0;

        foreach ($datosSemana as $registro) {
            $fechaRegistro = $registro->fecha; // Fecha específica del registro (ej. '2025-08-26')

            // Agrupamos por fecha, luego área, luego subproducto
            if (!isset($lookupDataSemanal[$fechaRegistro])) {
                $lookupDataSemanal[$fechaRegistro] = [];
            }
            if (!isset($lookupDataSemanal[$fechaRegistro][$registro->area_id])) {
                $lookupDataSemanal[$fechaRegistro][$registro->area_id] = [];
            }
            $lookupDataSemanal[$fechaRegistro][$registro->area_id][$registro->subproducto_id] = $registro->kilos;

            // Sumas totales de la semana
            $totalGeneradoSemana += $registro->kilos;
            if (!isset($totalPorZonaSemana[$registro->zona_nombre])) {
                $totalPorZonaSemana[$registro->zona_nombre] = 0;
            }
            $totalPorZonaSemana[$registro->zona_nombre] += $registro->kilos;
        }

        // 5. CALCULAR ZONA CON MAYOR GENERACIÓN (DE LA SEMANA)
        $zonaMayorNombreSemana = 'N/A';
        $zonaMayorTotalSemana = 0;
        if (!empty($totalPorZonaSemana)) {
            arsort($totalPorZonaSemana);
            $zonaMayorNombreSemana = key($totalPorZonaSemana);
            $zonaMayorTotalSemana = current($totalPorZonaSemana);
        }

        // 6. Formatear fechas para mostrar
        $fechaInicioFormateada = Carbon::parse($fechaInicioSemana)->format('d/m/Y');
        $fechaFinFormateada = Carbon::parse($fechaFinSemana)->format('d/m/Y');
        // $fechaUrl = $fecha; // Mantenemos la fecha original por si la necesitamos para algo más

        // 7. Pasamos las variables a la vista
        return view('gensemanal.show', compact(
            'instituto',
            'zonas',
            'lookupDataSemanal',
            'fechaInicioFormateada',
            'fechaFinFormateada',
            'totalGeneradoSemana',
            'zonaMayorNombreSemana',
            'zonaMayorTotalSemana',
            'fechaInicioSemana' // <-- AÑADE ESTA LÍNEA
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    /**
     * Muestra el formulario para editar una semana completa.
     */
    public function editWeek(Request $request, $fecha)
    {
        if (!auth()->check()) { /* ... tu código de auth ... */
        }
        $instituto = auth()->user()->instituto;
        if (!$instituto) { /* ... tu código de instituto ... */
        }

        // 1. CALCULAR LA SEMANA COMPLETA
        $fechaCarbon = Carbon::createFromFormat('Y-m-d', $fecha); // Aseguramos formato
        $fechaInicioSemana = $fechaCarbon->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $fechaFinSemana = $fechaCarbon->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

        // 2. OBTENER LA ESTRUCTURA (Zonas -> Areas -> Subproductos)
        $zonas = Zona::where('instituto_id', $instituto->id)
            ->orderBy('nombre')
            ->with(['areas.subproductos' => function ($query) {
                $query->orderBy('subproductos.nombre');
            }])
            ->get();

        // 3. OBTENER LOS DATOS GUARDADOS DE TODA LA SEMANA
        $datosSemana = GenSemanal::whereBetween('fecha', [$fechaInicioSemana, $fechaFinSemana])
            ->join('zonas_areas', 'gen_semanals.zonas_areas_id', '=', 'zonas_areas.id')
            ->join('zonas', 'zonas_areas.zona_id', '=', 'zonas.id')
            ->where('zonas.instituto_id', $instituto->id)
            ->select('gen_semanals.fecha', 'gen_semanals.turno', 'gen_semanals.kilos', 'zonas_areas.area_id', 'gen_semanals.subproducto_id', 'zonas.id as zona_id')
            ->get();

        // 4. TRANSFORMAR LOS DATOS EN UN LOOKUP MULTIDIMENSIONAL
        $lookupDataSemanal = [];
        $turnoSemana = null;
        foreach ($datosSemana as $registro) {
            $fechaRegistro = $registro->fecha;
            if (!$turnoSemana) $turnoSemana = $registro->turno;

            if (!isset($lookupDataSemanal[$fechaRegistro])) $lookupDataSemanal[$fechaRegistro] = [];
            if (!isset($lookupDataSemanal[$fechaRegistro][$registro->zona_id])) $lookupDataSemanal[$fechaRegistro][$registro->zona_id] = [];
            if (!isset($lookupDataSemanal[$fechaRegistro][$registro->zona_id][$registro->area_id])) $lookupDataSemanal[$fechaRegistro][$registro->zona_id][$registro->area_id] = [];

            $lookupDataSemanal[$fechaRegistro][$registro->zona_id][$registro->area_id][$registro->subproducto_id] = $registro->kilos;
        }

        if (!$turnoSemana) {
            $turnoSemana = 'Matutino';
        }

        // 5. Formatear fechas para mostrar
        $fechaInicioFormateada = Carbon::parse($fechaInicioSemana)->format('d/m/Y');
        $fechaFinFormateada = Carbon::parse($fechaFinSemana)->format('d/m/Y');

        // 6. Pasamos las variables a la vista (usaremos una nueva vista 'editWeek')
        return view('gensemanal.editWeek', compact(
            'instituto',
            'zonas',
            'lookupDataSemanal',
            'fechaInicioFormateada',
            'fechaFinFormateada',
            'fechaInicioSemana',
            'fechaFinSemana',
            'turnoSemana'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateAll(Request $request)
    {
        $institutoId = auth()->user()->instituto_id;

        // 1. VALIDAR LOS DATOS DEL FORMULARIO
        $request->validate([
            'fecha_inicial' => 'required|date_format:d/m/Y',
            'fecha_final' => 'required|date_format:d/m/Y',
            'turno' => 'required|string',
            'valor_kg' => 'nullable|array', // El array de todos los inputs
        ]);

        // 2. PARSEAR FECHAS Y TURNO
        // Convertimos 'd/m/Y' a 'Y-m-d' para la base de datos
        $fechaInicioYMD = Carbon::createFromFormat('d/m/Y', $request->fecha_inicial)->format('Y-m-d');
        $fechaFinYMD = Carbon::createFromFormat('d/m/Y', $request->fecha_final)->format('Y-m-d');
        $turno = $request->turno;

        // 3. OBTENER EL MAPA DE ZONAS_AREAS (Igual que en el método 'store')
        $zonas_areas_map = ZonasAreas::whereHas('zona', function ($query) use ($institutoId) {
            $query->where('instituto_id', $institutoId);
        })->get()->keyBy(function ($item) {
            // Creamos una clave única 'zona_id-area_id'
            return $item->zona_id . '-' . $item->area_id;
        })->map(function ($item) {
            // Mapeamos a solo el ID
            return $item->id;
        });

        // 4. USAR UNA TRANSACCIÓN (Muy importante)
        // Esto asegura que si algo falla, no nos quedemos sin datos.
        try {
            DB::beginTransaction();

            // 5. BORRAR TODOS LOS REGISTROS ANTIGUOS de esa semana/turno/instituto
            // Obtenemos los IDs de zonas_areas que pertenecen al instituto
            $zonasAreasIdsDelInstituto = ZonasAreas::join('zonas', 'zonas_areas.zona_id', '=', 'zonas.id')
                ->where('zonas.instituto_id', $institutoId)
                ->pluck('zonas_areas.id');

            // Borramos solo los registros que coincidan
            GenSemanal::whereIn('zonas_areas_id', $zonasAreasIdsDelInstituto)
                ->where('turno', $turno)
                ->whereBetween('fecha', [$fechaInicioYMD, $fechaFinYMD])
                ->delete();

            // 6. VOLVER A INSERTAR LOS DATOS (Lógica de 'store')
            $datosParaInsertar = [];
            $datos_kg = $request->input('valor_kg', []);

            foreach ($datos_kg as $fecha => $zonas) {
                foreach ($zonas as $zona_id => $areas) {
                    foreach ($areas as $area_id => $subproductos) {

                        // Buscamos el ID de la relación zona-área
                        $zonas_areas_id = $zonas_areas_map[$zona_id . '-' . $area_id] ?? null;

                        if ($zonas_areas_id) {
                            foreach ($subproductos as $subproducto_id => $kilos) {
                                // Guardamos solo si el valor es numérico y mayor a 0
                                if (is_numeric($kilos) && $kilos > 0) {
                                    $datosParaInsertar[] = [
                                        'zonas_areas_id' => $zonas_areas_id,
                                        'subproducto_id' => $subproducto_id,
                                        'fecha' => $fecha,
                                        'turno' => $turno,
                                        'kilos' => $kilos,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ];
                                }
                            }
                        }
                    }
                }
            }

            // Insertamos todos los nuevos registros de golpe
            if (!empty($datosParaInsertar)) {
                GenSemanal::insert($datosParaInsertar);
            }

            // Si todo salió bien, confirmamos los cambios
            DB::commit();
        } catch (\Exception $e) {
            // Si algo falló, revertimos todo
            DB::rollBack();
            // (Opcional: registrar el error $e->getMessage())
            session()->flash('swal', [
                'icon' => 'error',
                'title' => '¡Error!',
                'text' => 'Hubo un problema al guardar los cambios. Inténtelo de nuevo.',
            ]);
            return redirect()->back();
        }

        // 7. REDIRIGIR CON ÉXITO
        session()->flash('swal', [
            'icon' => 'success',
            'title' => '¡Actualizado!',
            'text' => 'Los registros de la semana se han actualizado correctamente.',
        ]);

        return redirect()->route('gensemanal.index');
    }

    /**
     * Busca registros por fecha o turno para la petición AJAX.
     */
    public function search(Request $request)
    {
        $institutoId = auth()->user()->instituto_id;
        $query = $request->get('query');
        $tiempo = $request->input('tiempo', 'general'); // Obtiene el filtro 'tiempo'

        // Define la consulta base que se usará en todos los filtros
        $queryBase = GenSemanal::join('zonas_areas', 'gen_semanals.zonas_areas_id', '=', 'zonas_areas.id')
            ->join('zonas', 'zonas_areas.zona_id', '=', 'zonas.id')
            ->where('zonas.instituto_id', $institutoId);

        $viewName = '';

        if ($tiempo == 'zonas_areas') {
            // --- FILTRO 1: BÚSQUEDA DETALLADA (Sin cambios) ---
            $registros = $queryBase->select(
                'gen_semanals.fecha',
                'gen_semanals.turno',
                'zonas.nombre as zona',
                'areas.nombre as areaAsignada',
                DB::raw('SUM(gen_semanals.kilos) as total_kilos_area')
            )
                ->join('areas', 'zonas_areas.area_id', '=', 'areas.id')
                ->where(function ($q) use ($query) {
                    $q->where('gen_semanals.fecha', 'LIKE', "%{$query}%")
                        ->orWhere('gen_semanals.turno', 'LIKE', "%{$query}%")
                        ->orWhere('zonas.nombre', 'LIKE', "%{$query}%")
                        ->orWhere('areas.nombre', 'LIKE', "%{$query}%");
                })
                ->groupBy('gen_semanals.fecha', 'gen_semanals.turno', 'zonas.nombre', 'areas.nombre')
                ->orderBy('gen_semanals.fecha', 'DESC')
                ->get();
            $viewName = 'gensemanal.partials.table-detalle';
        } else if ($tiempo == 'zonas_conteo') {
            // --- FILTRO 2: BÚSQUEDA POR ZONAS (Sin cambios) ---
            $registros = $queryBase->select(
                'zonas.nombre as zona',
                DB::raw('SUM(gen_semanals.kilos) as total_kilos_zona'),
                DB::raw('COUNT(DISTINCT zonas_areas.area_id) as conteo_areas')
            )
                ->where('zonas.nombre', 'LIKE', "%{$query}%")
                ->groupBy('zonas.nombre')
                ->orderBy('zonas.nombre')
                ->get();
            $viewName = 'gensemanal.partials.table-zonas';
        } else {
            // --- FILTRO 3: BÚSQUEDA GENERAL (POR SEMANA) ---
            // Esta es la lógica nueva y mejorada

            // 1. Buscamos semanas donde CUALQUIER fecha coincida
            $subQueryFecha = $queryBase->select(
                DB::raw('MIN(gen_semanals.fecha) as fecha_inicio'),
                DB::raw('MAX(gen_semanals.fecha) as fecha_final'),
                DB::raw('SUM(gen_semanals.kilos) as total_kilos_semana'),
                DB::raw('YEARWEEK(gen_semanals.fecha, 1) as anio_semana')
            )
                ->where('gen_semanals.fecha', 'LIKE', "%{$query}%") // Busca por cualquier fecha
                ->groupBy('anio_semana');

            // 2. Buscamos semanas donde el TOTAL de kilos coincida
            // (Necesita una consulta separada con HAVING)
            $subQueryTotal = $queryBase->select(
                DB::raw('MIN(gen_semanals.fecha) as fecha_inicio'),
                DB::raw('MAX(gen_semanals.fecha) as fecha_final'),
                DB::raw('SUM(gen_semanals.kilos) as total_kilos_semana'),
                DB::raw('YEARWEEK(gen_semanals.fecha, 1) as anio_semana')
            )
                ->groupBy('anio_semana')
                ->having('total_kilos_semana', 'LIKE', "%{$query}%"); // Busca por el total

            // 3. Unimos las dos búsquedas (evita duplicados)
            $registros = $subQueryFecha->union($subQueryTotal)
                ->orderBy('fecha_inicio', 'DESC')
                ->get();

            $viewName = 'gensemanal.partials.table-general';
        }

        // La búsqueda AJAX siempre devuelve solo la tabla
        return view($viewName, compact('registros'));
    }

    use AuthorizesRequests;

    public function destroyWeek($fecha)
    {
        // 1. AUTORIZACIÓN: Revisa el permiso
        $this->authorize('Eliminar Registros');

        // 2. OBTENER IDs DEL INSTITUTO (PARA BORRADO SEGURO)
        $institutoId = auth()->user()->instituto_id;
        $zonasAreasIdsDelInstituto = ZonasAreas::join('zonas', 'zonas_areas.zona_id', '=', 'zonas.id')
            ->where('zonas.instituto_id', $institutoId)
            ->pluck('zonas_areas.id');

        // 3. CALCULAR EL RANGO DE LA SEMANA
        // $fecha que recibimos es el Lunes (fecha_inicio)
        $fechaInicio = Carbon::parse($fecha)->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $fechaFin = Carbon::parse($fecha)->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

        // 4. LÓGICA DE BORRADO (CORREGIDA)
        // Borra todos los registros DENTRO de esa semana,
        // que pertenezcan a las zonas de ESE instituto.
        GenSemanal::whereIn('zonas_areas_id', $zonasAreasIdsDelInstituto)
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->delete();

        // 5. REDIRECCIÓN:
        return redirect()->route('gensemanal.index')->with('success', 'Semana eliminada correctamente.');
    }

    /**
     * Genera un reporte PDF para una semana completa.
     */
    public function GenerarPDF(Request $request, $fecha) // Ya no recibe $turno
    {
        // --- INICIO: COPIAMOS LA LÓGICA DEL MÉTODO 'show()' ---
        if (!auth()->check()) { /* ... */
        }
        $instituto = auth()->user()->instituto;
        if (!$instituto) { /* ... */
        }

        // 1. CALCULAR LA SEMANA COMPLETA
        $fechaCarbon = Carbon::createFromFormat('Y-m-d', $fecha);
        $fechaInicioSemana = $fechaCarbon->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $fechaFinSemana = $fechaCarbon->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

        // 2. OBTENER LA ESTRUCTURA (Zonas -> Areas -> Subproductos)
        $zonas = Zona::where('instituto_id', $instituto->id)
            ->orderBy('nombre')
            ->with(['areas.subproductos' => function ($query) {
                $query->orderBy('subproductos.nombre');
            }])
            ->get();

        // 3. OBTENER LOS DATOS DE TODA LA SEMANA
        $datosSemana = GenSemanal::whereBetween('fecha', [$fechaInicioSemana, $fechaFinSemana])
            ->join('zonas_areas', 'gen_semanals.zonas_areas_id', '=', 'zonas_areas.id')
            ->join('zonas', 'zonas_areas.zona_id', '=', 'zonas.id')
            ->where('zonas.instituto_id', $instituto->id)
            ->select(
                'gen_semanals.fecha',
                'gen_semanals.turno',
                'gen_semanals.kilos',
                'zonas_areas.area_id',
                'gen_semanals.subproducto_id',
                'zonas.id as zona_id',
                'zonas.nombre as zona_nombre'
            )
            ->orderBy('fecha')
            ->get();

        // 4. TRANSFORMAR LOS DATOS PARA LA VISTA
        $lookupDataSemanal = [];
        $totalPorZonaSemana = [];
        $totalGeneradoSemana = 0;

        foreach ($datosSemana as $registro) {
            $fechaRegistro = $registro->fecha;
            if (!isset($lookupDataSemanal[$fechaRegistro])) $lookupDataSemanal[$fechaRegistro] = [];
            if (!isset($lookupDataSemanal[$fechaRegistro][$registro->area_id])) $lookupDataSemanal[$fechaRegistro][$registro->area_id] = [];

            // Corrección: El lookup del controlador 'editWeek' era por zona_id, pero el de 'show' era por area_id.
            // Usaremos el de 'show' (area_id -> subproducto_id) que es más simple para la plantilla.
            $lookupDataSemanal[$fechaRegistro][$registro->area_id][$registro->subproducto_id] = $registro->kilos;

            $totalGeneradoSemana += $registro->kilos;
            if (!isset($totalPorZonaSemana[$registro->zona_nombre])) $totalPorZonaSemana[$registro->zona_nombre] = 0;
            $totalPorZonaSemana[$registro->zona_nombre] += $registro->kilos;
        }

        // 5. CALCULAR ZONA CON MAYOR GENERACIÓN
        $zonaMayorNombreSemana = 'N/A';
        $zonaMayorTotalSemana = 0;
        if (!empty($totalPorZonaSemana)) {
            arsort($totalPorZonaSemana);
            $zonaMayorNombreSemana = key($totalPorZonaSemana);
            $zonaMayorTotalSemana = current($totalPorZonaSemana);
        }

        // 6. Formatear fechas para mostrar
        $fechaInicioFormateada = Carbon::parse($fechaInicioSemana)->format('d/m/Y');
        $fechaFinFormateada = Carbon::parse($fechaFinSemana)->format('d/m/Y');
        // --- FIN: LÓGICA COPIADA DEL MÉTODO 'show()' ---


        // 7. PREPARAR LOS DATOS PARA EL PDF
        $data = [
            'instituto' => $instituto,
            'zonas' => $zonas,
            'lookupDataSemanal' => $lookupDataSemanal,
            'fechaInicioFormateada' => $fechaInicioFormateada,
            'fechaFinFormateada' => $fechaFinFormateada,
            'totalGeneradoSemana' => $totalGeneradoSemana,
            'zonaMayorNombreSemana' => $zonaMayorNombreSemana,
            'zonaMayorTotalSemana' => $zonaMayorTotalSemana,
            'datosSemana' => $datosSemana,
        ];

        // 8. GENERAR Y DEVOLVER EL PDF
        // Usamos la nueva plantilla que creamos en el Paso 1
        $pdf = Pdf::loadView('gensemanal.pdf-template', $data);

        // (Opcional: Cambiar la orientación si la tabla es muy ancha)
        // $pdf->setPaper('a4', 'landscape');

        // Nombre del archivo
        $fileName = 'Reporte_Semanal_' . $instituto->nombre_corto . '_' . $fechaInicioSemana . '.pdf';

        // Devuelve el PDF para ver en el navegador (stream) o descargar (download)
        return $pdf->stream($fileName);
    }

    public function GenerarExcel(Request $request, $fecha) // Ya no recibe $turno
    {
        if (!auth()->check()) { /* ... */
        }
        $instituto = auth()->user()->instituto;
        if (!$instituto) { /* ... */
        }

        // 1. Calcular la semana completa
        $fechaCarbon = Carbon::createFromFormat('Y-m-d', $fecha);
        $fechaInicioSemana = $fechaCarbon->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $fechaFinSemana = $fechaCarbon->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

        // 2. OBTENER LOS DATOS DETALLADOS DE LA SEMANA
        //    (Similar a la consulta en SemanalExport, pero aquí para pasarla)
        $registrosDetallados = GenSemanal::whereBetween('fecha', [$fechaInicioSemana, $fechaFinSemana])
            ->join('zonas_areas', 'gen_semanals.zonas_areas_id', '=', 'zonas_areas.id')
            ->join('zonas', 'zonas_areas.zona_id', '=', 'zonas.id')
            ->join('areas', 'zonas_areas.area_id', '=', 'areas.id')
            ->join('subproductos', 'gen_semanals.subproducto_id', '=', 'subproductos.id')
            ->where('zonas.instituto_id', $instituto->id)
            ->select(
                'gen_semanals.fecha',
                'gen_semanals.turno',
                'zonas.nombre as zona_nombre',
                'areas.nombre as area_nombre',
                'subproductos.nombre as subproducto_nombre',
                'gen_semanals.kilos'
            )
            ->orderBy('gen_semanals.fecha')
            ->orderBy('zonas.nombre')
            ->orderBy('areas.nombre')
            ->get(); // Obtenemos la colección

        // 3. Formatear fechas para el título y nombre de archivo
        $fechaInicioFormateada = Carbon::parse($fechaInicioSemana)->format('d-m-Y'); // Formato para nombre de archivo
        $fechaFinFormateada = Carbon::parse($fechaFinSemana)->format('d-m-Y');
        $tituloFecha = Carbon::parse($fechaInicioSemana)->format('d/m/Y') . ' al ' . Carbon::parse($fechaFinSemana)->format('d/m/Y');


        // 4. Nombre del archivo
        $fileName = 'Reporte_Semanal_' . $instituto->nombre_corto . '_' . $fechaInicioFormateada . '_al_' . $fechaFinFormateada . '.xlsx';

        // 5. Usar TU clase Export, pasándole la colección y el título
        return Excel::download(
            new RegistroSemanalExport($registrosDetallados, $tituloFecha), // Pasamos la colección y el rango para el título
            $fileName
        );
    }
}
