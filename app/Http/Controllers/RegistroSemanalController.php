<?php

namespace App\Http\Controllers;

use App\Exports\RegistroSemanalExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\GenSemanal;
use App\Models\Zona;
use App\Models\Categoria;
use App\Models\Area;
use App\Models\ZonasAreas;
use App\Models\Subproducto;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use stdClass;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RegistroSemanalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $institutoId = auth()->user()->instituto_id;

        $tiempo = $request->input('tiempo', 'general');

        $queryBase = GenSemanal::join('zonas_areas', 'gen_semanals.zonas_areas_id', '=', 'zonas_areas.id')
            ->join('zonas', 'zonas_areas.zona_id', '=', 'zonas.id')
            ->join('categorias', 'gen_semanals.categoria_id', '=', 'categorias.id')
            ->where('zonas.instituto_id', $institutoId);

        $viewName = '';

        if ($tiempo == 'zonas_areas') {

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

            $viewName = 'gensemanal.partials.table-detalle';
        } else if ($tiempo == 'zonas_conteo') {
            $registros = $queryBase->select(
                'zonas.nombre as zona',
                DB::raw('SUM(gen_semanals.kilos) as total_kilos_zona'),
                DB::raw('COUNT(DISTINCT zonas_areas.area_id) as conteo_areas')
            )
                ->groupBy('zonas.nombre')
                ->orderBy('zonas.nombre')
                ->get();

            $viewName = 'gensemanal.partials.table-zonas';
        } else {
            $registros = $queryBase->select(

                DB::raw('DATE_ADD(MIN(gen_semanals.fecha), INTERVAL(-WEEKDAY(MIN(gen_semanals.fecha))) DAY) as fecha_inicio'),
                DB::raw('DATE_ADD(MIN(gen_semanals.fecha), INTERVAL(6 - WEEKDAY(MIN(gen_semanals.fecha))) DAY) as fecha_final'),
                DB::raw('SUM(gen_semanals.kilos) as total_kilos_semana'),
                DB::raw('YEARWEEK(gen_semanals.fecha, 1) as anio_semana')
            )
                ->groupBy('anio_semana')
                ->orderBy(DB::raw('MIN(gen_semanals.fecha)'), 'DESC')
                ->get();

            $viewName = 'gensemanal.partials.table-general';
        }

        if ($request->ajax()) {
            return view($viewName, compact('registros'));
        }
        return view('gensemanal.index', compact('registros', 'viewName', 'tiempo'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // 1. Validaciones de Sesión
        if (!auth()->check()) {
            return redirect()->route('login')->withErrors(['msg' => 'Su sesión ha expirado. Por favor, inicie sesión nuevamente.']);
        }

        if (!auth()->user()->instituto_id) {
            return redirect()->back()->withErrors(['msg' => 'Para guardar una evidencia necesita tener una universidad asociada.']);
        }

        $instituto = auth()->user()->instituto;

        // 2. Cargar Zonas, Áreas y sus Subproductos con Categoría
        $zonas = Zona::where('instituto_id', auth()->user()->instituto_id)
            ->orderBy('nombre')
            ->with([
                'areas' => function ($q) {
                    $q->orderBy('nombre');
                },
                // ESTO ES LA CLAVE: Cargamos subproductos y SU categoría
                'areas.subproductos.categoria'
            ])
            ->get();

        // CORRECCIÓN: Eliminamos 'categorias' de aquí, ya no es necesaria.
        return view('gensemanal.create', compact('instituto', 'zonas'));
    }

    public function checkWeek(Request $request)
    {
        $inicio = $request->input('inicio');
        $final = $request->input('final');
        $turno = $request->input('turno');
        $institutoId = auth()->user()->instituto_id;

        $exists = DB::table('gen_semanals')
            ->join('zonas_areas', 'gen_semanals.zonas_areas_id', '=', 'zonas_areas.id')
            ->join('zonas', 'zonas_areas.zona_id', '=', 'zonas.id')
            ->where('zonas.instituto_id', $institutoId)
            ->where('gen_semanals.turno', $turno)
            ->whereBetween('gen_semanals.fecha', [$inicio, $final])
            ->exists();

        return response()->json(['exists' => $exists]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Obtener datos básicos
        $institutoId = auth()->user()->instituto_id; // Asumo que esto lo necesitas
        $data = $request->input('valor_kg', []);
        $turno = $request->input('turno');

        $zonasAreasMap = ZonasAreas::whereHas('zona', function ($q) use ($institutoId) {
            $q->where('instituto_id', $institutoId);
        })
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->zona_id . '-' . $item->area_id => $item->id];
            });

        $datosInsertar = []; // Array para guardar todo de golpe al final

        // 2. LÓGICA DE BUCLES
        // Nivel 1: Iterar por DÍA
        foreach ($data as $fechaDelRegistro => $zonas) {

            // Validación simple de fecha
            if (!strtotime($fechaDelRegistro)) continue;

            // Nivel 2: Iterar por ZONA
            foreach ($zonas as $zonaId => $areas) {

                foreach ($areas as $areaId => $inputs) {

                    $zonaAreaId = $zonasAreasMap[$zonaId . '-' . $areaId] ?? null;


                    if (!$zonaAreaId) continue;

                    foreach ($inputs as $key => $kilos) {

                        if (empty($kilos) || !is_numeric($kilos) || $kilos <= 0) {
                            continue;
                        }

                        if (str_starts_with($key, 'cat_')) {
                            $categoriaId = (int) str_replace('cat_', '', $key);

                            $datosInsertar[] = [
                                'fecha'          => $fechaDelRegistro,
                                'turno'          => $turno,
                                'zonas_areas_id' => $zonaAreaId,
                                'categoria_id'   => $categoriaId,
                                'kilos'          => $kilos,
                                'created_at'     => now(),
                                'updated_at'     => now(),
                            ];
                        }
                    }
                }
            }
        }

        if (count($datosInsertar) > 0) {
            GenSemanal::insert($datosInsertar);

            session()->flash('swal', [
                'icon' => 'success',
                'title' => 'Hecho!',
                'text' => 'Los datos se han guardado con éxito',
            ]);
        } else {
            // Opcional: Avisar si no se guardó nada
            session()->flash('swal', [
                'icon' => 'warning',
                'title' => 'Atención',
                'text' => 'No se registraron datos (quizás todos eran 0)',
            ]);
        }

        return redirect()->route('gensemanal.index');
    }


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
            ->with('areas')
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
                'gen_semanals.categoria_id',
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
            $lookupDataSemanal[$fechaRegistro][$registro->area_id][$registro->categoria_id] = $registro->kilos;

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
        $instituto = auth()->user()->instituto;
        $institutoId = $instituto->id; // Asegúrate de tener el ID

        // 1. CALCULAR LA SEMANA
        $fechaCarbon = Carbon::createFromFormat('Y-m-d', $fecha);
        $fechaInicioSemana = $fechaCarbon->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $fechaFinSemana = $fechaCarbon->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

        // 2. OBTENER LA ESTRUCTURA
        $zonas = Zona::where('instituto_id', $institutoId)
            ->orderBy('nombre')
            ->with('areas')
            ->get();

        // 3. OBTENER LOS DATOS GUARDADOS
        $registros = GenSemanal::whereBetween('fecha', [$fechaInicioSemana, $fechaFinSemana])
            ->join('zonas_areas', 'gen_semanals.zonas_areas_id', '=', 'zonas_areas.id')
            ->join('zonas', 'zonas_areas.zona_id', '=', 'zonas.id')
            ->where('zonas.instituto_id', $institutoId)
            ->select(
                'gen_semanals.fecha',
                'gen_semanals.turno',
                'gen_semanals.kilos',
                'zonas_areas.area_id',
                'gen_semanals.categoria_id as subproducto_id', // Alias para mantener compatibilidad
                'zonas.id as zona_id'
            )
            ->get();

        // 4. TRANSFORMAR LOS DATOS (Empaquetar para el JS)
        $lookupDataSemanal = [];
        $turnoSemana = null;

        foreach ($registros as $registro) {
            if (!$turnoSemana) $turnoSemana = $registro->turno;

            // <--- CAMBIO 2: Quitamos el nivel de [zona_id] para que el JS lo lea fácil
            // Estructura: [FECHA][AREA][CATEGORIA] = KILOS
            $lookupDataSemanal[$registro->fecha][$registro->area_id][$registro->subproducto_id] = $registro->kilos;
        }

        if (!$turnoSemana) {
            $turnoSemana = 'Matutino';
        }

        // 5. Formatos de fecha
        $fechaInicioFormateada = Carbon::parse($fechaInicioSemana)->format('d/m/Y');
        $fechaFinFormateada = Carbon::parse($fechaFinSemana)->format('d/m/Y');

        // 6. Enviar a la vista
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

        // 1. VALIDAR DATOS
        $request->validate([
            'fecha_inicial' => 'required',
            'fecha_final' => 'required',
            'turno' => 'required',
            'valor_kg' => 'nullable|array',
        ]);

        // 2. PREPARAR FECHAS
        // Intentamos leer el formato d/m/Y que viene del formulario
        try {
            $fechaInicioYMD = Carbon::createFromFormat('d/m/Y', $request->fecha_inicial)->format('Y-m-d');
            $fechaFinYMD = Carbon::createFromFormat('d/m/Y', $request->fecha_final)->format('Y-m-d');
        } catch (\Exception $e) {
            // Si falla (porque ya viene como Y-m-d), lo usamos directo
            $fechaInicioYMD = Carbon::parse($request->fecha_inicial)->format('Y-m-d');
            $fechaFinYMD = Carbon::parse($request->fecha_final)->format('Y-m-d');
        }

        $turno = $request->turno;

        // 3. OBTENER MAPA DE ZONAS_AREAS (Para optimizar y obtener IDs reales)
        $zonas_areas_map = ZonasAreas::whereHas('zona', function ($query) use ($institutoId) {
            $query->where('instituto_id', $institutoId);
        })->get()->keyBy(function ($item) {
            // Clave compuesta para búsqueda rápida: 'zona_id-area_id'
            return $item->zona_id . '-' . $item->area_id;
        })->map->id;

        // 4. TRANSACCIÓN DE BASE DE DATOS
        try {
            DB::beginTransaction();

            // A. BORRAMOS TODO LO VIEJO DE ESA SEMANA (Limpieza total)
            // Primero buscamos los IDs de relación que pertenecen a este instituto
            $idsRelacion = ZonasAreas::whereHas('zona', fn($q) => $q->where('instituto_id', $institutoId))->pluck('id');

            // Borramos los registros existentes en ese rango de fechas
            GenSemanal::whereIn('zonas_areas_id', $idsRelacion)
                ->whereBetween('fecha', [$fechaInicioYMD, $fechaFinYMD])
                // ->where('turno', $turno) // Opcional: Descomenta si quieres borrar solo el turno actual
                ->delete();

            // B. RECOLECTAMOS LOS NUEVOS DATOS
            $datosParaInsertar = [];
            $datos_kg = $request->input('valor_kg', []);

            // Estructura del input: [fecha][zona][area][cat_ID]
            foreach ($datos_kg as $fecha => $zonas) {
                foreach ($zonas as $zona_id => $areas) {
                    foreach ($areas as $area_id => $inputs) {

                        // Buscamos el ID real de la tabla zonas_areas
                        $zonas_areas_id = $zonas_areas_map[$zona_id . '-' . $area_id] ?? null;

                        if ($zonas_areas_id) {
                            foreach ($inputs as $keyCategoria => $kilos) {

                                // 1. SI BORRASTE EL CAMPO (VACÍO O 0), LO SALTAMOS
                                // Esto evita errores y "borra" el dato efectivamente
                                if (!is_numeric($kilos) || $kilos <= 0) {
                                    continue;
                                }

                                // 2. LIMPIAMOS EL ID (Quitamos 'cat_')
                                if (str_starts_with($keyCategoria, 'cat_')) {
                                    $categoria_id = (int) str_replace('cat_', '', $keyCategoria);

                                    // Preparamos el registro
                                    $datosParaInsertar[] = [
                                        'zonas_areas_id' => $zonas_areas_id,
                                        'categoria_id'   => $categoria_id, // <--- ¡Aquí ya va el número limpio!
                                        'fecha'          => $fecha,
                                        'turno'          => $turno,
                                        'kilos'          => $kilos,
                                        'created_at'     => now(),
                                        'updated_at'     => now(),
                                    ];
                                }
                            }
                        }
                    }
                }
            }

            // C. INSERTAMOS TODO DE GOLPE (Más rápido)
            if (!empty($datosParaInsertar)) {
                GenSemanal::insert($datosParaInsertar);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al guardar: ' . $e->getMessage());
        }

        // 5. SALIDA EXITOSA
        session()->flash('swal', [
            'icon' => 'success',
            'title' => '¡Actualizado!',
            'text' => 'La semana se ha actualizado correctamente.',
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
        try {
            // 1. AUTORIZACIÓN
            $this->authorize('Eliminar Registros');

            // 2. OBTENER IDs DEL INSTITUTO
            $institutoId = auth()->user()->instituto_id;

            // Asegúrate de importar el modelo ZonasAreas arriba: use App\Models\ZonasAreas;
            $zonasAreasIdsDelInstituto = ZonasAreas::join('zonas', 'zonas_areas.zona_id', '=', 'zonas.id')
                ->where('zonas.instituto_id', $institutoId)
                ->pluck('zonas_areas.id');

            // 3. CALCULAR EL RANGO DE LA SEMANA
            $fechaInicio = Carbon::parse($fecha)->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
            $fechaFin = Carbon::parse($fecha)->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

            // 4. LÓGICA DE BORRADO
            // Guardamos en $borrados la cantidad de registros eliminados
            $borrados = GenSemanal::whereIn('zonas_areas_id', $zonasAreasIdsDelInstituto)
                ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->delete();

            // 5. REDIRECCIÓN CON ALERTAS
            if ($borrados > 0) {
                return redirect()->route('gensemanal.index')->with('swal', [
                    'icon' => 'success',
                    'title' => 'Eliminado',
                    'text' => 'La semana de generación ha sido eliminada correctamente.'
                ]);
            } else {
                return redirect()->route('gensemanal.index')->with('swal', [
                    'icon' => 'info',
                    'title' => 'Info',
                    'text' => 'No se encontraron registros para eliminar en esa semana.'
                ]);
            }
        } catch (\Exception $e) {
            return redirect()->route('gensemanal.index')->with('swal', [
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'No se pudo eliminar la semana: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Genera un reporte PDF para una semana completa.
     */
    public function GenerarPDF(Request $request, $fecha)
    {
        // --- INICIO: LÓGICA DEL MÉTODO 'show()' ---
        if (!auth()->check()) {
            return redirect('/');
        }
        $instituto = auth()->user()->instituto;
        if (!$instituto) {
            return redirect()->back();
        }

        // ************************************************
        // 1. OBTENER EL TURNO DESDE LA PETICIÓN
        // Asumimos que el turno se envía en un campo llamado 'turno' en el formulario.
        // Usamos 'N/A' como valor por defecto si no se encuentra, pero te sugiero 
        // usar el turno más común si no se especifica.
        $turnoSemana = $request->input('turno', 'Matutino');
        // ************************************************

        // 2. CALCULAR LA SEMANA COMPLETA
        $fechaCarbon = Carbon::createFromFormat('Y-m-d', $fecha);
        $fechaInicioSemana = $fechaCarbon->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $fechaFinSemana = $fechaCarbon->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

        // 3. OBTENER LA ESTRUCTURA (Zonas -> Areas -> Subproductos para filtrar)
        $zonas = Zona::where('instituto_id', $instituto->id)
            ->orderBy('nombre')
            ->with(['areas' => function ($query) {
                $query->orderBy('nombre');
            }])
            ->get();

        // 4. OBTENER LOS DATOS DE TODA LA SEMANA
        $datosSemana = GenSemanal::whereBetween('fecha', [$fechaInicioSemana, $fechaFinSemana])
            ->join('zonas_areas', 'gen_semanals.zonas_areas_id', '=', 'zonas_areas.id')
            ->join('zonas', 'zonas_areas.zona_id', '=', 'zonas.id')
            ->where('zonas.instituto_id', $instituto->id)
            ->select(
                'gen_semanals.fecha',
                'gen_semanals.turno',
                'gen_semanals.kilos',
                'zonas_areas.area_id',
                'gen_semanals.categoria_id',
                'zonas.id as zona_id',
                'zonas.nombre as zona_nombre'
            )
            ->orderBy('fecha')
            ->get();

        // 5. TRANSFORMAR LOS DATOS PARA LA VISTA Y CALCULAR TOTALES INTERMEDIOS
        $lookupDataSemanal = [];
        $totalPorZonaSemana = [];
        $totalGeneradoSemana = 0;
        $totalPorAreaDia = []; // [fecha][area_id] => kilos
        $totalPorZonaDia = []; // [fecha][zona_id] => kilos

        foreach ($datosSemana as $registro) {
            $fechaRegistro = $registro->fecha;
            $areaId = $registro->area_id;
            $zonaId = $registro->zona_id;

            // --- 5.1. Almacenamiento de datos (para mostrar en la tabla) ---
            // Estructura: [fecha][zona_id][area_id][categoria_id] = [kilos, turno]
            if (!isset($lookupDataSemanal[$fechaRegistro])) {
                $lookupDataSemanal[$fechaRegistro] = [];
            }
            if (!isset($lookupDataSemanal[$fechaRegistro][$zonaId])) {
                $lookupDataSemanal[$fechaRegistro][$zonaId] = [];
            }
            if (!isset($lookupDataSemanal[$fechaRegistro][$zonaId][$areaId])) {
                $lookupDataSemanal[$fechaRegistro][$zonaId][$areaId] = [];
            }

            $lookupDataSemanal[$fechaRegistro][$zonaId][$areaId][$registro->categoria_id] = [
                'kilos' => $registro->kilos,
                'turno' => $registro->turno
            ];

            // --- 5.2. Cálculos de Totales ---
            $totalGeneradoSemana += $registro->kilos;

            // Subtotal semanal por Zona (para el resumen)
            if (!isset($totalPorZonaSemana[$registro->zona_nombre])) {
                $totalPorZonaSemana[$registro->zona_nombre] = 0;
            }
            $totalPorZonaSemana[$registro->zona_nombre] += $registro->kilos;

            // Subtotal por Área y Día
            if (!isset($totalPorAreaDia[$fechaRegistro])) {
                $totalPorAreaDia[$fechaRegistro] = [];
            }
            if (!isset($totalPorAreaDia[$fechaRegistro][$areaId])) {
                $totalPorAreaDia[$fechaRegistro][$areaId] = 0;
            }
            $totalPorAreaDia[$fechaRegistro][$areaId] += $registro->kilos;

            // Subtotal por Zona y Día
            if (!isset($totalPorZonaDia[$fechaRegistro])) {
                $totalPorZonaDia[$fechaRegistro] = [];
            }
            if (!isset($totalPorZonaDia[$fechaRegistro][$zonaId])) {
                $totalPorZonaDia[$fechaRegistro][$zonaId] = 0;
            }
            $totalPorZonaDia[$fechaRegistro][$zonaId] += $registro->kilos;
        }

        // 6. CALCULAR ZONA CON MAYOR GENERACIÓN
        $zonaMayorNombreSemana = 'N/A';
        $zonaMayorTotalSemana = 0;
        if (!empty($totalPorZonaSemana)) {
            arsort($totalPorZonaSemana);
            $zonaMayorNombreSemana = key($totalPorZonaSemana);
            $zonaMayorTotalSemana = current($totalPorZonaSemana);
        }

        // 7. Formatear fechas para mostrar
        $fechaInicioFormateada = Carbon::parse($fechaInicioSemana)->format('d/m/Y');
        $fechaFinFormateada = Carbon::parse($fechaFinSemana)->format('d/m/Y');

        // 8. PREPARAR LOS DATOS PARA EL PDF
        $data = [
            'instituto' => $instituto,
            'zonas' => $zonas, // Estructura maestra
            'categorias' => Categoria::all()->pluck('nombre', 'id')->toArray(), // Nombres de Categorías
            'lookupDataSemanal' => $lookupDataSemanal, // Datos detallados
            'fechaInicioFormateada' => $fechaInicioFormateada,
            'fechaFinFormateada' => $fechaFinFormateada,
            'fechaInicioSemana' => $fechaInicioSemana,
            'fechaFinSemana' => $fechaFinSemana,
            'totalGeneradoSemana' => $totalGeneradoSemana,
            'zonaMayorNombreSemana' => $zonaMayorNombreSemana,
            'zonaMayorTotalSemana' => $zonaMayorTotalSemana,
            // NUEVAS VARIABLES
            'totalPorAreaDia' => $totalPorAreaDia,
            'totalPorZonaDia' => $totalPorZonaDia,
            'turnoSemana' => $turnoSemana, // <--- VARIABLE AGREGADA Y NECESARIA
        ];

        // 9. GENERAR Y DEVOLVER EL PDF
        $pdf = Pdf::loadView('gensemanal.pdf-template', $data);

        // Opcional: Orientación
        // Si la orientación horizontal es necesaria, descomenta esta línea:
        // $pdf->setPaper('a4', 'landscape');

        $fileName = 'Reporte_Semanal_' . $instituto->nombre_corto . '_' . $fechaInicioSemana . '.pdf';

        return $pdf->stream($fileName);
    }

    public function GenerarExcel(Request $request, $fecha)
    {
        if (!auth()->check()) {
            return redirect('/');
        }
        $instituto = auth()->user()->instituto;
        if (!$instituto) {
            return redirect()->back();
        }

        // 1. CALCULAR LA SEMANA
        // Usamos startOfDay para evitar problemas de horas
        $fechaCarbon = Carbon::createFromFormat('Y-m-d', $fecha)->startOfDay();
        $fechaInicioSemana = $fechaCarbon->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $fechaFinSemana = $fechaCarbon->copy()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

        // 2. OBTENER ESTRUCTURA (Zonas -> Áreas -> Categorías)
        // Mantenemos 'subproductos' para filtrar categorías por área correctamente
        $zonas = Zona::where('instituto_id', $instituto->id)
            ->orderBy('nombre')
            ->with(['areas.subproductos' => function ($query) {
                $query->orderBy('subproductos.nombre');
            }])
            ->get();

        // 3. OBTENER DATOS CAPTURADOS
        $datosDB = GenSemanal::whereBetween('fecha', [$fechaInicioSemana, $fechaFinSemana])
            ->join('zonas_areas', 'gen_semanals.zonas_areas_id', '=', 'zonas_areas.id')
            ->join('zonas', 'zonas_areas.zona_id', '=', 'zonas.id')
            ->where('zonas.instituto_id', $instituto->id)
            ->select(
                'gen_semanals.fecha',
                'gen_semanals.turno',
                'gen_semanals.kilos',
                'zonas_areas.area_id',
                'gen_semanals.categoria_id'
            )
            ->get();

        // --- LÓGICA DE TURNO PREDETERMINADO ---
        // Buscamos si existe algún registro capturado para usar su turno como default
        $primerRegistro = $datosDB->first();
        $turnoDefault = $primerRegistro ? $primerRegistro->turno : 'Matutino';

        // 4. MAPEAR DATOS PARA BÚSQUEDA RÁPIDA
        // Diccionario: [FECHA][AREA][CATEGORIA] = Registro
        $lookup = [];
        foreach ($datosDB as $d) {
            $lookup[$d->fecha][$d->area_id][$d->categoria_id] = $d;
        }

        // 5. CONSTRUIR LA LISTA COMPLETA (Incluyendo Ceros)
        $listaCompleta = new Collection();

        $fechaIter = Carbon::parse($fechaInicioSemana)->startOfDay();
        $fechaFin = Carbon::parse($fechaFinSemana)->endOfDay();

        // Bucle 1: Días (Lunes a Domingo)
        while ($fechaIter->lte($fechaFin)) {
            $fechaStr = $fechaIter->format('Y-m-d');

            // Bucle 2: Zonas
            foreach ($zonas as $zona) {
                // Bucle 3: Áreas
                foreach ($zona->areas as $area) {

                    // Obtenemos categorías únicas del área (Filtrado inteligente)
                    $categoriasDelArea = $area->subproductos
                        ->pluck('categoria')
                        ->unique('id')
                        ->sortBy('nombre');

                    // Bucle 4: Categorías
                    foreach ($categoriasDelArea as $categoria) {
                        if ($categoria) {
                            // Buscamos si existe el dato real en la BD
                            $registroReal = $lookup[$fechaStr][$area->id][$categoria->id] ?? null;

                            // Creamos un OBJETO (stdClass) que imita a un registro de BD
                            // Así tu archivo Export no nota la diferencia
                            $fila = new stdClass();

                            $fila->fecha = $fechaStr; // Formato Y-m-d (Tu export lo formatea después)

                            // TURNO: Si existe registro usamos su turno, si no, el default
                            $fila->turno = $registroReal ? $registroReal->turno : $turnoDefault;

                            // DATOS DESCRIPTIVOS
                            $fila->zona_nombre = $zona->nombre;
                            $fila->area_nombre = $area->nombre;
                            $fila->subproducto_nombre = $categoria->nombre; // Usamos nombre de categoría

                            // KILOS: Si existe usamos el valor, si no, 0
                            $fila->kilos = $registroReal ? $registroReal->kilos : 0;

                            // Agregamos a la lista final
                            $listaCompleta->push($fila);
                        }
                    }
                }
            }
            $fechaIter->addDay();
        }

        // 6. EXPORTAR
        // Preparamos el título y nombre del archivo
        $tituloFecha = Carbon::parse($fechaInicioSemana)->format('d/m/Y') . ' al ' . Carbon::parse($fechaFinSemana)->format('d/m/Y');
        $nombreArchivo = 'Reporte_Semanal_' . $instituto->nombre_corto . '_' . $fechaInicioSemana . '.xlsx';

        // Enviamos la lista "inflada" a tu exportador original
        return Excel::download(new RegistroSemanalExport($listaCompleta, $tituloFecha), $nombreArchivo);
    }
}
