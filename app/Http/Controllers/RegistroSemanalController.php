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
        
        if (!auth()->check()) {
            return redirect()->route('login')->withErrors(['msg' => 'Su sesión ha expirado. Por favor, inicie sesión nuevamente.']);
        }

        if (!auth()->user()->instituto_id) {
            return redirect()->back()->withErrors(['msg' => 'Para guardar una evidencia necesita tener una universidad asociada.']);
        }

        $instituto = auth()->user()->instituto;

        
        $zonas = Zona::where('instituto_id', auth()->user()->instituto_id)
            ->orderBy('nombre')
            ->with([
                'areas' => function ($q) {
                    $q->orderBy('nombre');
                },
                
                'areas.subproductos.categoria'
            ])
            ->get();

        
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
        
        $institutoId = auth()->user()->instituto_id; 
        $data = $request->input('valor_kg', []);
        $turno = $request->input('turno');

        $zonasAreasMap = ZonasAreas::whereHas('zona', function ($q) use ($institutoId) {
            $q->where('instituto_id', $institutoId);
        })
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->zona_id . '-' . $item->area_id => $item->id];
            });

        $datosInsertar = []; 

        
        
        foreach ($data as $fechaDelRegistro => $zonas) {

            
            if (!strtotime($fechaDelRegistro)) continue;

            
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
            
            session()->flash('swal', [
                'icon' => 'warning',
                'title' => 'Atención',
                'text' => 'No se registraron datos (quizás todos eran 0)',
            ]);
        }

        return redirect()->route('gensemanal.index');
    }


    public function show(Request $request, $fecha) 
    {
        if (!auth()->check()) { /* ... tu código de auth ... */
        }
        $instituto = auth()->user()->instituto;
        if (!$instituto) { /* ... tu código de instituto ... */
        }

        
        $fechaCarbon = Carbon::createFromFormat('Y-m-d', $fecha);
        $fechaInicioSemana = $fechaCarbon->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $fechaFinSemana = $fechaCarbon->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

        
        $zonas = Zona::where('instituto_id', $instituto->id)
            ->orderBy('nombre')
            ->with('areas')
            ->get();

        
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

        
        $lookupDataSemanal = []; 
        $totalPorZonaSemana = [];
        $totalGeneradoSemana = 0;

        foreach ($datosSemana as $registro) {
            $fechaRegistro = $registro->fecha; 

            
            if (!isset($lookupDataSemanal[$fechaRegistro])) {
                $lookupDataSemanal[$fechaRegistro] = [];
            }
            if (!isset($lookupDataSemanal[$fechaRegistro][$registro->area_id])) {
                $lookupDataSemanal[$fechaRegistro][$registro->area_id] = [];
            }
            $lookupDataSemanal[$fechaRegistro][$registro->area_id][$registro->categoria_id] = $registro->kilos;

            
            $totalGeneradoSemana += $registro->kilos;
            if (!isset($totalPorZonaSemana[$registro->zona_nombre])) {
                $totalPorZonaSemana[$registro->zona_nombre] = 0;
            }
            $totalPorZonaSemana[$registro->zona_nombre] += $registro->kilos;
        }

        
        $zonaMayorNombreSemana = 'N/A';
        $zonaMayorTotalSemana = 0;
        if (!empty($totalPorZonaSemana)) {
            arsort($totalPorZonaSemana);
            $zonaMayorNombreSemana = key($totalPorZonaSemana);
            $zonaMayorTotalSemana = current($totalPorZonaSemana);
        }

        
        $fechaInicioFormateada = Carbon::parse($fechaInicioSemana)->format('d/m/Y');
        $fechaFinFormateada = Carbon::parse($fechaFinSemana)->format('d/m/Y');
        

        
        return view('gensemanal.show', compact(
            'instituto',
            'zonas',
            'lookupDataSemanal',
            'fechaInicioFormateada',
            'fechaFinFormateada',
            'totalGeneradoSemana',
            'zonaMayorNombreSemana',
            'zonaMayorTotalSemana',
            'fechaInicioSemana' 
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
        $institutoId = $instituto->id; 

        
        $fechaCarbon = Carbon::createFromFormat('Y-m-d', $fecha);
        $fechaInicioSemana = $fechaCarbon->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $fechaFinSemana = $fechaCarbon->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

        
        $zonas = Zona::where('instituto_id', $institutoId)
            ->orderBy('nombre')
            ->with('areas')
            ->get();

        
        $registros = GenSemanal::whereBetween('fecha', [$fechaInicioSemana, $fechaFinSemana])
            ->join('zonas_areas', 'gen_semanals.zonas_areas_id', '=', 'zonas_areas.id')
            ->join('zonas', 'zonas_areas.zona_id', '=', 'zonas.id')
            ->where('zonas.instituto_id', $institutoId)
            ->select(
                'gen_semanals.fecha',
                'gen_semanals.turno',
                'gen_semanals.kilos',
                'zonas_areas.area_id',
                'gen_semanals.categoria_id as subproducto_id', 
                'zonas.id as zona_id'
            )
            ->get();

        
        $lookupDataSemanal = [];
        $turnoSemana = null;

        foreach ($registros as $registro) {
            if (!$turnoSemana) $turnoSemana = $registro->turno;

            
            
            $lookupDataSemanal[$registro->fecha][$registro->area_id][$registro->subproducto_id] = $registro->kilos;
        }

        if (!$turnoSemana) {
            $turnoSemana = 'Matutino';
        }

        
        $fechaInicioFormateada = Carbon::parse($fechaInicioSemana)->format('d/m/Y');
        $fechaFinFormateada = Carbon::parse($fechaFinSemana)->format('d/m/Y');

        
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

        
        $request->validate([
            'fecha_inicial' => 'required',
            'fecha_final' => 'required',
            'turno' => 'required',
            'valor_kg' => 'nullable|array',
        ]);

        
        
        try {
            $fechaInicioYMD = Carbon::createFromFormat('d/m/Y', $request->fecha_inicial)->format('Y-m-d');
            $fechaFinYMD = Carbon::createFromFormat('d/m/Y', $request->fecha_final)->format('Y-m-d');
        } catch (\Exception $e) {
            
            $fechaInicioYMD = Carbon::parse($request->fecha_inicial)->format('Y-m-d');
            $fechaFinYMD = Carbon::parse($request->fecha_final)->format('Y-m-d');
        }

        $turno = $request->turno;

        
        $zonas_areas_map = ZonasAreas::whereHas('zona', function ($query) use ($institutoId) {
            $query->where('instituto_id', $institutoId);
        })->get()->keyBy(function ($item) {
            
            return $item->zona_id . '-' . $item->area_id;
        })->map->id;

        
        try {
            DB::beginTransaction();

            
            
            $idsRelacion = ZonasAreas::whereHas('zona', fn($q) => $q->where('instituto_id', $institutoId))->pluck('id');

            
            GenSemanal::whereIn('zonas_areas_id', $idsRelacion)
                ->whereBetween('fecha', [$fechaInicioYMD, $fechaFinYMD])
                
                ->delete();

            
            $datosParaInsertar = [];
            $datos_kg = $request->input('valor_kg', []);

            
            foreach ($datos_kg as $fecha => $zonas) {
                foreach ($zonas as $zona_id => $areas) {
                    foreach ($areas as $area_id => $inputs) {

                        
                        $zonas_areas_id = $zonas_areas_map[$zona_id . '-' . $area_id] ?? null;

                        if ($zonas_areas_id) {
                            foreach ($inputs as $keyCategoria => $kilos) {

                                
                                
                                if (!is_numeric($kilos) || $kilos <= 0) {
                                    continue;
                                }

                                
                                if (str_starts_with($keyCategoria, 'cat_')) {
                                    $categoria_id = (int) str_replace('cat_', '', $keyCategoria);

                                    
                                    $datosParaInsertar[] = [
                                        'zonas_areas_id' => $zonas_areas_id,
                                        'categoria_id'   => $categoria_id, 
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

            
            if (!empty($datosParaInsertar)) {
                GenSemanal::insert($datosParaInsertar);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al guardar: ' . $e->getMessage());
        }

        
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
        $tiempo = $request->input('tiempo', 'general'); 

        
        $queryBase = GenSemanal::join('zonas_areas', 'gen_semanals.zonas_areas_id', '=', 'zonas_areas.id')
            ->join('zonas', 'zonas_areas.zona_id', '=', 'zonas.id')
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
            
            

            
            $subQueryFecha = $queryBase->select(
                DB::raw('MIN(gen_semanals.fecha) as fecha_inicio'),
                DB::raw('MAX(gen_semanals.fecha) as fecha_final'),
                DB::raw('SUM(gen_semanals.kilos) as total_kilos_semana'),
                DB::raw('YEARWEEK(gen_semanals.fecha, 1) as anio_semana')
            )
                ->where('gen_semanals.fecha', 'LIKE', "%{$query}%") 
                ->groupBy('anio_semana');

            
            
            $subQueryTotal = $queryBase->select(
                DB::raw('MIN(gen_semanals.fecha) as fecha_inicio'),
                DB::raw('MAX(gen_semanals.fecha) as fecha_final'),
                DB::raw('SUM(gen_semanals.kilos) as total_kilos_semana'),
                DB::raw('YEARWEEK(gen_semanals.fecha, 1) as anio_semana')
            )
                ->groupBy('anio_semana')
                ->having('total_kilos_semana', 'LIKE', "%{$query}%"); 

            
            $registros = $subQueryFecha->union($subQueryTotal)
                ->orderBy('fecha_inicio', 'DESC')
                ->get();

            $viewName = 'gensemanal.partials.table-general';
        }

        
        return view($viewName, compact('registros'));
    }

    use AuthorizesRequests;

    public function destroyWeek($fecha)
    {
        try {
            
            $this->authorize('Eliminar Registros');

            
            $institutoId = auth()->user()->instituto_id;

            
            $zonasAreasIdsDelInstituto = ZonasAreas::join('zonas', 'zonas_areas.zona_id', '=', 'zonas.id')
                ->where('zonas.instituto_id', $institutoId)
                ->pluck('zonas_areas.id');

            
            $fechaInicio = Carbon::parse($fecha)->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
            $fechaFin = Carbon::parse($fecha)->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

            
            
            $borrados = GenSemanal::whereIn('zonas_areas_id', $zonasAreasIdsDelInstituto)
                ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->delete();

            
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
        
        if (!auth()->check()) {
            return redirect('/');
        }
        $instituto = auth()->user()->instituto;
        if (!$instituto) {
            return redirect()->back();
        }

        
        
        
        
        
        $turnoSemana = $request->input('turno', 'Matutino');
        

        
        $fechaCarbon = Carbon::createFromFormat('Y-m-d', $fecha);
        $fechaInicioSemana = $fechaCarbon->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $fechaFinSemana = $fechaCarbon->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

        
        $zonas = Zona::where('instituto_id', $instituto->id)
            ->orderBy('nombre')
            ->with(['areas' => function ($query) {
                $query->orderBy('nombre');
            }])
            ->get();

        
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

        
        $lookupDataSemanal = [];
        $totalPorZonaSemana = [];
        $totalGeneradoSemana = 0;
        $totalPorAreaDia = []; 
        $totalPorZonaDia = []; 

        foreach ($datosSemana as $registro) {
            $fechaRegistro = $registro->fecha;
            $areaId = $registro->area_id;
            $zonaId = $registro->zona_id;

            
            
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

            
            $totalGeneradoSemana += $registro->kilos;

            
            if (!isset($totalPorZonaSemana[$registro->zona_nombre])) {
                $totalPorZonaSemana[$registro->zona_nombre] = 0;
            }
            $totalPorZonaSemana[$registro->zona_nombre] += $registro->kilos;

            
            if (!isset($totalPorAreaDia[$fechaRegistro])) {
                $totalPorAreaDia[$fechaRegistro] = [];
            }
            if (!isset($totalPorAreaDia[$fechaRegistro][$areaId])) {
                $totalPorAreaDia[$fechaRegistro][$areaId] = 0;
            }
            $totalPorAreaDia[$fechaRegistro][$areaId] += $registro->kilos;

            
            if (!isset($totalPorZonaDia[$fechaRegistro])) {
                $totalPorZonaDia[$fechaRegistro] = [];
            }
            if (!isset($totalPorZonaDia[$fechaRegistro][$zonaId])) {
                $totalPorZonaDia[$fechaRegistro][$zonaId] = 0;
            }
            $totalPorZonaDia[$fechaRegistro][$zonaId] += $registro->kilos;
        }

        
        $zonaMayorNombreSemana = 'N/A';
        $zonaMayorTotalSemana = 0;
        if (!empty($totalPorZonaSemana)) {
            arsort($totalPorZonaSemana);
            $zonaMayorNombreSemana = key($totalPorZonaSemana);
            $zonaMayorTotalSemana = current($totalPorZonaSemana);
        }

        
        $fechaInicioFormateada = Carbon::parse($fechaInicioSemana)->format('d/m/Y');
        $fechaFinFormateada = Carbon::parse($fechaFinSemana)->format('d/m/Y');

        
        $data = [
            'instituto' => $instituto,
            'zonas' => $zonas, 
            'categorias' => Categoria::all()->pluck('nombre', 'id')->toArray(), 
            'lookupDataSemanal' => $lookupDataSemanal, 
            'fechaInicioFormateada' => $fechaInicioFormateada,
            'fechaFinFormateada' => $fechaFinFormateada,
            'fechaInicioSemana' => $fechaInicioSemana,
            'fechaFinSemana' => $fechaFinSemana,
            'totalGeneradoSemana' => $totalGeneradoSemana,
            'zonaMayorNombreSemana' => $zonaMayorNombreSemana,
            'zonaMayorTotalSemana' => $zonaMayorTotalSemana,
            
            'totalPorAreaDia' => $totalPorAreaDia,
            'totalPorZonaDia' => $totalPorZonaDia,
            'turnoSemana' => $turnoSemana, 
        ];

        
        $pdf = Pdf::loadView('gensemanal.pdf-template', $data);

        
        
        

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

        
        
        $fechaCarbon = Carbon::createFromFormat('Y-m-d', $fecha)->startOfDay();
        $fechaInicioSemana = $fechaCarbon->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $fechaFinSemana = $fechaCarbon->copy()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

        
        
        $zonas = Zona::where('instituto_id', $instituto->id)
            ->orderBy('nombre')
            ->with(['areas.subproductos' => function ($query) {
                $query->orderBy('subproductos.nombre');
            }])
            ->get();

        
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

        
        
        $primerRegistro = $datosDB->first();
        $turnoDefault = $primerRegistro ? $primerRegistro->turno : 'Matutino';

        
        
        $lookup = [];
        foreach ($datosDB as $d) {
            $lookup[$d->fecha][$d->area_id][$d->categoria_id] = $d;
        }

        
        $listaCompleta = new Collection();

        $fechaIter = Carbon::parse($fechaInicioSemana)->startOfDay();
        $fechaFin = Carbon::parse($fechaFinSemana)->endOfDay();

        
        while ($fechaIter->lte($fechaFin)) {
            $fechaStr = $fechaIter->format('Y-m-d');

            
            foreach ($zonas as $zona) {
                
                foreach ($zona->areas as $area) {

                    
                    $categoriasDelArea = $area->subproductos
                        ->pluck('categoria')
                        ->unique('id')
                        ->sortBy('nombre');

                    
                    foreach ($categoriasDelArea as $categoria) {
                        if ($categoria) {
                            
                            $registroReal = $lookup[$fechaStr][$area->id][$categoria->id] ?? null;

                            
                            
                            $fila = new stdClass();

                            $fila->fecha = $fechaStr; 

                            
                            $fila->turno = $registroReal ? $registroReal->turno : $turnoDefault;

                            
                            $fila->zona_nombre = $zona->nombre;
                            $fila->area_nombre = $area->nombre;
                            $fila->subproducto_nombre = $categoria->nombre; 

                            
                            $fila->kilos = $registroReal ? $registroReal->kilos : 0;

                            
                            $listaCompleta->push($fila);
                        }
                    }
                }
            }
            $fechaIter->addDay();
        }

        
        
        $tituloFecha = Carbon::parse($fechaInicioSemana)->format('d/m/Y') . ' al ' . Carbon::parse($fechaFinSemana)->format('d/m/Y');
        $nombreArchivo = 'Reporte_Semanal_' . $instituto->nombre_corto . '_' . $fechaInicioSemana . '.xlsx';

        
        return Excel::download(new RegistroSemanalExport($listaCompleta, $tituloFecha), $nombreArchivo);
    }
}
