<?php

namespace App\Http\Controllers;

use App\Exports\RegistroSubproductosExport;
use App\Models\GenSemanal;
use App\Models\GenSubproducto;
use App\Models\Zona;
use App\Models\Subproducto;
use App\Models\ZonasAreas;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use stdClass;

class RegistroSubproductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        
        $tiempo = $request->input('tiempo', 'general');
        $institutoId = auth()->user()->instituto_id;

        
        $query = GenSubproducto::where('instituto_id', $institutoId);

        switch ($tiempo) {
            case 'general':
                
                $registroPeriodo = $query->select(
                    DB::raw('MIN(fecha) as fecha_inicio'),
                    DB::raw('MAX(fecha) as fecha_final'),
                    DB::raw('SUM(valor_kg) as total_kg'),
                    'instituto_id',
                    DB::raw('YEARWEEK(fecha, 1) as semana_id')
                )
                    ->groupBy('instituto_id', 'semana_id')
                    ->orderBy('fecha_inicio', 'desc')
                    ->paginate(10);

                
                $registroPeriodo->getCollection()->transform(function ($item) {
                    $item->fecha_inicio = Carbon::parse($item->fecha_inicio)->startOfWeek()->format('Y-m-d');
                    $item->fecha_final = Carbon::parse($item->fecha_final)->endOfWeek()->format('Y-m-d');
                    return $item;
                });
                break;

            case 'zonas_conteo':
                
                $registroPeriodo = $query->select(
                    DB::raw('MIN(fecha) as fecha_inicio'),
                    DB::raw('MAX(fecha) as fecha_final'),
                    DB::raw('SUM(valor_kg) as total_kg'),
                    'instituto_id',
                    'zona_id' 
                )
                    ->with('zona') 
                    ->groupBy('instituto_id', 'zona_id', DB::raw('YEARWEEK(fecha, 1)'))
                    ->orderBy('fecha_inicio', 'desc')
                    ->paginate(10);
                break;

            case 'zonas_areas': 
                
                
                $registroPeriodo = $query->select(
                    DB::raw('MIN(fecha) as fecha_inicio'),
                    DB::raw('MAX(fecha) as fecha_final'),
                    DB::raw('SUM(valor_kg) as total_kg'),
                    'instituto_id',
                    'zona_id',
                    'subproducto_id' 
                )
                    ->with(['zona', 'subproducto']) 
                    ->groupBy('instituto_id', 'zona_id', 'subproducto_id', DB::raw('YEARWEEK(fecha, 1)'))
                    ->orderBy('fecha_inicio', 'desc')
                    ->paginate(10);
                break;

            default:
                $registroPeriodo = $query->paginate(10);
                break;
        }
        $viewName = 'gensubproductos.partials.table-general';

        if ($tiempo == 'zonas_conteo') {
            $viewName = 'gensubproductos.partials.table-zona';
        } elseif ($tiempo == 'zonas_areas') {
            $viewName = 'gensubproductos.partials.table-detalle';
        }


        return view('gensubproductos.index', compact('registroPeriodo', 'tiempo', 'viewName'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $institutoId = auth()->user()->instituto_id;

        $zonas = \App\Models\Zona::where('instituto_id', $institutoId)->get();
        $subproductos = \App\Models\Subproducto::all();

        return view('gensubproductos.create', compact('zonas', 'subproductos'));
    }

    

    public function checkWeek(Request $request)
    {
        
        $request->validate([
            'inicio' => 'required|date',
            'final' => 'required|date',
        ]);

        $inicio = \Carbon\Carbon::parse($request->inicio)->format('Y-m-d');
        $final = \Carbon\Carbon::parse($request->final)->format('Y-m-d');
        $institutoId = auth()->user()->instituto_id;

        
        $exists = \App\Models\GenSubproducto::where('instituto_id', $institutoId)
            ->whereBetween('fecha', [$inicio, $final])
            ->exists();

        
        return response()->json(['exists' => $exists]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        $request->validate([
            'valores' => 'required|array',
        ]);

        $institutoId = auth()->user()->instituto_id;

        
        $valores = $request->input('valores', []);

        $batchData = [];
        $now = now();

        if (count($valores) > 0) {
            
            foreach ($valores as $zonaId => $subproductos) {
                foreach ($subproductos as $subproductoId => $fechas) {
                    foreach ($fechas as $fecha => $kilos) {

                        
                        if (is_numeric($kilos) && $kilos > 0) {
                            $batchData[] = [
                                'instituto_id'   => $institutoId,
                                'zona_id'        => $zonaId, 
                                'subproducto_id' => $subproductoId,
                                'fecha'          => $fecha,
                                'valor_kg'       => $kilos,
                                'created_at'     => $now,
                                'updated_at'     => $now,
                            ];
                        }
                    }
                }
            }
        }

        
        if (count($batchData) > 0) {
            
            \DB::transaction(function () use ($batchData) {
                \App\Models\GenSubproducto::insert($batchData);
            });

            return redirect()->route('gensubproductos.index')->with('swal', [
                'icon' => 'success',
                'title' => '¡Guardado!',
                'text' => 'Los registros se han guardado correctamente.',
            ]);
        } else {
            return redirect()->back()->with('swal', [
                'icon' => 'warning',
                'title' => 'Sin datos',
                'text' => 'No ingresaste ninguna cantidad mayor a 0.',
            ]);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Request $request, $instituto_id, $inicio, $final)
    {
        if (!Auth::check()) return redirect()->route('login');

        $inicio = \Carbon\Carbon::parse($inicio);
        $final = \Carbon\Carbon::parse($final);
        $institutoId = Auth::user()->instituto_id;

        
        
        $zonas = \App\Models\Zona::where('instituto_id', $institutoId)->get();
        $subproductos = \App\Models\Subproducto::all();

        
        
        $datosRegistrados = \App\Models\GenSubproducto::where('instituto_id', $institutoId)
            ->whereBetween('fecha', [$inicio->format('Y-m-d'), $final->format('Y-m-d')])
            ->get();

        
        
        $datosAgrupados = $datosRegistrados->map(function ($item) {
            
            $item->subproducto_nombre = $item->subproducto->nombre ?? 'Desconocido';
            return $item;
        });

        return view('gensubproductos.show', [
            'zonas' => $zonas,                   
            'subproductos' => $subproductos,     
            'datosRegistrados' => $datosRegistrados, 
            'datosAgrupados' => $datosAgrupados, 
            'inicio' => $inicio,
            'final' => $final,
            'instituto' => Auth::user()->instituto
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $instituto_id, $inicio, $final)
    {
        
        $inicioDate = \Carbon\Carbon::parse($inicio);
        $finalDate = \Carbon\Carbon::parse($final);

        
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $instituto = Auth::user()->instituto;

        
        
        $zonas = \App\Models\Zona::where('instituto_id', $instituto->id)->get();
        $subproductos = \App\Models\Subproducto::all();

        
        
        $datosRegistrados = \App\Models\GenSubproducto::where('instituto_id', $instituto->id)
            ->whereBetween('fecha', [$inicioDate->startOfDay(), $finalDate->endOfDay()])
            ->select('zona_id', 'subproducto_id', 'fecha', 'valor_kg') 
            ->get();

        
        return view('gensubproductos.edit', [
            'instituto' => $instituto,
            'instituto_id' => $instituto->id,
            'inicio' => $inicioDate->format('Y-m-d'), 
            'final' => $finalDate->format('Y-m-d'),
            'zonas' => $zonas,              
            'subproductos' => $subproductos,
            'datosRegistrados' => $datosRegistrados 
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateMultiple(Request $request)
    {
        
        $request->validate([
            'inicio' => 'required',
            'final' => 'required',
            'instituto_id' => 'required|exists:institutos,id',
            'valores' => 'nullable|array'
        ]);

        $institutoId = $request->input('instituto_id');
        $valores = $request->input('valores') ?? [];

        
        if (!empty($valores) && is_array($valores)) {

            foreach ($valores as $zonaId => $subproductos) {
                if (!is_array($subproductos)) continue;

                foreach ($subproductos as $subproductoId => $fechas) {
                    if (!is_array($fechas)) continue;

                    foreach ($fechas as $fecha => $valor) {

                        
                        
                        $valorFloat = floatval($valor);

                        

                        
                        if ($valorFloat > 0) {
                            GenSubproducto::updateOrCreate(
                                [
                                    
                                    'instituto_id' => $institutoId,
                                    'zona_id' => $zonaId,
                                    'subproducto_id' => $subproductoId,
                                    'fecha' => $fecha,
                                ],
                                [
                                    
                                    'valor_kg' => $valorFloat
                                ]
                            );
                        }
                        
                        else {
                            
                            
                            GenSubproducto::where('instituto_id', $institutoId)
                                ->where('zona_id', $zonaId)
                                ->where('subproducto_id', $subproductoId)
                                ->where('fecha', $fecha)
                                ->delete();
                        }
                    }
                }
            }
        }

        session()->flash('swal', [
            'icon' => 'success',
            'title' => '¡Guardado!',
            'text' => 'Se han guardado solo los datos válidos. Los registros vacíos se han limpiado.',
        ]);

        return redirect()->route('gensubproductos.index');
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroyWeekSubproductos($fechaInicio)
    {
        try {
            $institutoId = auth()->user()->instituto_id;

            $startOfWeek = Carbon::parse($fechaInicio)->startOfWeek();
            $endOfWeek = Carbon::parse($fechaInicio)->endOfWeek();

            $borrados = GenSubproducto::where('instituto_id', $institutoId)
                ->whereBetween('fecha', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
                ->delete();

            if ($borrados > 0) {
                return redirect()->route('gensubproductos.index')->with('swal', [
                    'icon' => 'success',
                    'title' => 'Eliminado',
                    'text' => 'La semana de subproductos ha sido eliminada correctamente.'
                ]);
            } else {
                return redirect()->route('gensubproductos.index')->with('swal', [
                    'icon' => 'info',
                    'title' => 'Info',
                    'text' => 'No se encontraron registros para eliminar en esa semana.'
                ]);
            }
        } catch (\Exception $e) {
            return redirect()->route('gensubproductos.index')->with('swal', [
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'No se pudo eliminar la semana: ' . $e->getMessage()
            ]);
        }
    }

    public function search(Request $request)
    {
        
        $termino = $request->input('query');
        $tiempo = $request->input('tiempo', 'general'); 
        $institutoId = auth()->user()->instituto_id;

        
        $query = GenSubproducto::where('instituto_id', $institutoId);

        
        $viewName = 'gensubproductos.partials.table-general';

        
        switch ($tiempo) {
            case 'general':
                
                $query->select(
                    DB::raw('MIN(fecha) as fecha_inicio'),
                    DB::raw('MAX(fecha) as fecha_final'),
                    DB::raw('SUM(valor_kg) as total_kg'),
                    'instituto_id',
                    DB::raw('YEARWEEK(fecha, 1) as semana_id')
                )
                    ->groupBy('instituto_id', 'semana_id')
                    ->orderBy('fecha_inicio', 'desc');

                
                if ($termino) {
                    $query->where('fecha', 'like', "%{$termino}%");
                }

                $registroPeriodo = $query->paginate(10);

                
                $registroPeriodo->getCollection()->transform(function ($item) {
                    $item->fecha_inicio = Carbon::parse($item->fecha_inicio)->startOfWeek()->format('Y-m-d');
                    $item->fecha_final = Carbon::parse($item->fecha_final)->endOfWeek()->format('Y-m-d');
                    return $item;
                });

                $viewName = 'gensubproductos.partials.table-general';
                break;

            case 'zonas_conteo':
                
                $query->select(
                    DB::raw('MIN(fecha) as fecha_inicio'),
                    DB::raw('MAX(fecha) as fecha_final'),
                    DB::raw('SUM(valor_kg) as total_kg'),
                    'instituto_id',
                    'zona_id'
                )
                    ->with('zona') 
                    ->groupBy('instituto_id', 'zona_id', DB::raw('YEARWEEK(fecha, 1)'))
                    ->orderBy('fecha_inicio', 'desc');

                
                if ($termino) {
                    $query->whereHas('zona', function ($q) use ($termino) {
                        $q->where('nombre', 'like', "%{$termino}%");
                    });
                }

                $registroPeriodo = $query->paginate(10);
                $viewName = 'gensubproductos.partials.table-zona';
                break;

            case 'zonas_areas':
                
                $query->select(
                    DB::raw('MIN(fecha) as fecha_inicio'),
                    DB::raw('MAX(fecha) as fecha_final'),
                    DB::raw('SUM(valor_kg) as total_kg'),
                    'instituto_id',
                    'zona_id',
                    'subproducto_id'
                )
                    ->with(['zona', 'subproducto']) 
                    ->groupBy('instituto_id', 'zona_id', 'subproducto_id', DB::raw('YEARWEEK(fecha, 1)'))
                    ->orderBy('fecha_inicio', 'desc');

                
                if ($termino) {
                    $query->where(function ($mainQuery) use ($termino) {
                        $mainQuery->whereHas('zona', function ($q) use ($termino) {
                            $q->where('nombre', 'like', "%{$termino}%");
                        })
                            ->orWhereHas('subproducto', function ($q) use ($termino) {
                                $q->where('nombre', 'like', "%{$termino}%");
                            })
                            ->orWhere('fecha', 'like', "%{$termino}%");
                    });
                }

                $registroPeriodo = $query->paginate(10);
                $viewName = 'gensubproductos.partials.table-detalle';
                break;
        }

        
        if ($request->ajax()) {
            return view($viewName, ['registroPeriodo' => $registroPeriodo])->render();
        }

        
        return view('gensubproductos.index', compact('registroPeriodo', 'tiempo', 'viewName'));
    }


    public function GenerarPDF(Request $request, $instituto_id, $inicio, $final)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $user = Auth::user();
        $institutoId = $user->instituto_id;
        $inicio = \Carbon\Carbon::parse($inicio)->startOfDay();
        $final = \Carbon\Carbon::parse($final)->endOfDay();
        $zonas = \App\Models\Zona::where('instituto_id', $institutoId)->get();
        $subproductos = \App\Models\Subproducto::all();

        $datosRegistrados = \App\Models\GenSubproducto::where('instituto_id', $institutoId)
            ->whereBetween('fecha', [$inicio->format('Y-m-d'), $final->format('Y-m-d')])
            ->with(['subproducto', 'zona'])
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('gensubproductos.pdf', [
            'zonas' => $zonas,
            'subproductos' => $subproductos,
            'datosRegistrados' => $datosRegistrados,
            'inicio' => $inicio,
            'final' => $final,
            'instituto' => $user->instituto
        ]);

        return $pdf->stream('reporte_subproductos.pdf');
    }


    public function GenerarExcel(Request $request, $instituto_id, $inicio, $final)
    {
        $inicio_cleaned = preg_replace('/[^\d\/-]/', '', $inicio);
        $final_cleaned = preg_replace('/[^\d\/-]/', '', $final);

        $inicio = Carbon::parse($inicio_cleaned)->startOfDay();
        $final = Carbon::parse($final_cleaned)->endOfDay();

        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $instituteId = Auth::user()->instituto_id;
        $zonas = Zona::where('instituto_id', $instituteId)->get(['id', 'nombre']);
        $subproductos = Subproducto::all(['id', 'nombre']);
        $period = CarbonPeriod::create($inicio, $final);
        $dates = $period->toArray();
        $datosLookupRaw = GenSubproducto::select(
            'fecha',
            'zona_id',
            'subproducto_id',
            DB::raw('SUM(valor_kg) as total_kg')
        )
            ->where('instituto_id', $instituteId)
            ->whereBetween('fecha', [$inicio, $final])
            ->groupBy('fecha', 'zona_id', 'subproducto_id')
            ->get()
            ->toArray();

        $dataLookup = [];
        foreach ($datosLookupRaw as $registro) {
            $fechaDB = Carbon::parse($registro['fecha'])->format('Y-m-d');
            $zona_id_key = $registro['zona_id'] ?? 'NULL';
            $key = $fechaDB . '_' . $zona_id_key . '_' . $registro['subproducto_id'];
            $dataLookup[$key] = $registro['total_kg'];
        }

        $datosGenerados = collect();

        foreach ($dates as $date) {
            $fechaString = $date->format('Y-m-d');
            foreach ($zonas as $zona) {
                foreach ($subproductos as $subproducto) {

                    $zona_id_key = $zona->id ?? 'NULL';
                    $key = $fechaString . '_' . $zona_id_key . '_' . $subproducto->id;
                    $total_kg = $dataLookup[$key] ?? 0.00;
                    $fila = new stdClass();
                    $fila->fecha = $date->format('Y-m-d'); 
                    $fila->zona_nombre = $zona->nombre;
                    $fila->subproducto_nombre = $subproducto->nombre;
                    $fila->total_kg = number_format($total_kg, 2, '.', '');

                    $datosGenerados->push($fila);
                }
            }
        }

        return Excel::download(new RegistroSubproductosExport($datosGenerados, $inicio, $final), 'registro-subproductos.xlsx');
    }
}
