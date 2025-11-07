<?php

namespace App\Http\Controllers;

use App\Models\GenSemanal;
use App\Models\Zona;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PrediccionesZonasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $institutoId = Auth::user()->instituto_id;
        $zonas = Zona::where('instituto_id', $institutoId)->get();

        // --- LÓGICA DINÁMICA DE AÑO ---
        $anoActual = Carbon::now()->year; // -> Obtendrá 2025
        $anoAnterior = $anoActual - 1;   // -> Obtendrá 2024

        // 1. Total Residuos (del año actual)
        $totalResiduosAnoActual = GenSemanal::whereHas('zonaArea.zona', function ($query) use ($institutoId) {
            $query->where('instituto_id', $institutoId);
        })->whereYear('fecha', $anoActual)->sum('kilos'); // <-- Usa $anoActual

        // 2. Crecimiento Anual (comparando actual vs anterior)
        $totalResiduosAnoAnterior = GenSemanal::whereHas('zonaArea.zona', function ($query) use ($institutoId) {
            $query->where('instituto_id', $institutoId);
        })->whereYear('fecha', $anoAnterior)->sum('kilos'); // <-- Usa $anoAnterior

        if ($totalResiduosAnoAnterior > 0) {
            $crecimientoAnual = (($totalResiduosAnoActual - $totalResiduosAnoAnterior) / $totalResiduosAnoAnterior) * 100;
        } else if ($totalResiduosAnoActual > 0) {
            $crecimientoAnual = 100.0; // Crecimiento "infinito" (de 0 a >0)
        } else {
            $crecimientoAnual = 0.0; // 0 en ambos años
        }

        // 3. Zona con más Generación (Histórico - esto no cambia)
        $zonaMasGeneracion = GenSemanal::join('zonas_areas', 'gen_semanals.zonas_areas_id', '=', 'zonas_areas.id')
            ->join('zonas', 'zonas_areas.zona_id', '=', 'zonas.id')
            ->selectRaw('zonas.nombre, SUM(gen_semanals.kilos) as total_kilos')
            ->where('zonas.instituto_id', $institutoId)
            ->groupBy('zonas.nombre')
            ->orderBy('total_kilos', 'DESC')
            ->first();

        // --- FIN DE LA LÓGICA ---

        // Pasamos las nuevas variables a la vista
        return view('prediccionesZonas.index', compact(
            'zonas',
            'totalResiduosAnoActual', // <-- Cambiamos el nombre de la variable
            'crecimientoAnual',
            'zonaMasGeneracion',
            'anoActual',      // <-- Pasamos el año actual
            'anoAnterior'     // <-- Pasamos el año anterior
        ));
    }

    public function obtenerPredicciones(Request $request)
    {
        $request->validate([
            'zona_id' => 'required|exists:zonas,id'
        ]);

        $institutoId = Auth::user()->instituto_id;

        // Promedio movil para obtener valores de predicción de los próximos 7 días

        $datos = GenSemanal::join('zonas_areas', 'gen_semanals.zonas_areas_id', '=', 'zonas_areas.id')
            ->join('zonas', 'zonas_areas.zona_id', '=', 'zonas.id')
            // --- LÍNEA CORREGIDA ---
            ->selectRaw('zonas.id as zona_id, zonas.nombre as zona, DATE(gen_semanals.fecha) as fecha, SUM(gen_semanals.kilos) as total_kg')
            // -------------------------
            ->where('zonas.id', $request->zona_id)
            ->where('zonas.instituto_id', $institutoId)
            ->groupBy('zonas.id', 'zonas.nombre', 'fecha')
            ->orderBy('fecha')
            ->get();


        // dd($datos);

        if ($datos->isEmpty()) {
            return response()->json(['message' => 'No hay datos para esta zona']);
        }

        // Calcular la predicción para los próximos 7 días (promedio móvil)
        $predicciones = collect();
        $promedio_kg = $datos->avg('total_kg');
        $ultimaFecha = Carbon::parse($datos->last()->fecha);

        for ($i = 1; $i <= 7; $i++) {
            $predicciones->push([
                'zona_id' => $datos->first()->zona_id,
                'zona' => $datos->first()->zona,
                'fecha' => $ultimaFecha->addDay()->toDateString(),
                'total_kg' => round($promedio_kg, 2)
            ]);
        }

        // Combinar datos históricos y predicciones
        $datos = collect($datos);
        $resultado = $datos->merge($predicciones);

        return response()->json($resultado);
    }

    // public function obtenerTodasLasPredicciones()
    // {
    //     $zonas = Zona::all();
    //     $predicciones = [];

    //     foreach ($zonas as $zona) {
    //         $datos = GenSemanal::join('zonas_areas', 'gen_semanals.zonas_areas_id', '=', 'zonas_areas.id')
    //             ->join('zonas', 'zonas_areas.zona_id', '=', 'zonas.id')
    //             ->selectRaw('zonas.id as zona_id, zonas.nombre as zona, DATE(gen_semanals.fecha) as fecha, SUM(gen_semanals.valor_kg) as total_kg')
    //             ->where('zonas.id', $zona->id)
    //             ->groupBy('zonas.id', 'zonas.nombre', 'fecha')
    //             ->orderBy('fecha')
    //             ->get();

    //         if ($datos->isEmpty()) {
    //             continue;
    //         }

    //         // Calcular la predicción para los próximos 7 días (promedio móvil)
    //         $prediccionesZona = [];
    //         $promedio_kg = $datos->avg('total_kg');
    //         $ultimaFecha = Carbon::parse($datos->last()->fecha);

    //         for ($i = 1; $i <= 7; $i++) {
    //             $prediccionesZona[] = [
    //                 'zona_id' => $datos->first()->zona_id,
    //                 'zona' => $datos->first()->zona,
    //                 'fecha' => $ultimaFecha->addDay()->toDateString(),
    //                 'total_kg' => round($promedio_kg, 2)
    //             ];
    //         }

    //         // Combinar datos históricos y predicciones
    //         $resultado = $datos->toArray();
    //         $resultado = array_merge($resultado, $prediccionesZona);

    //         $predicciones[$zona->id] = $resultado;
    //     }

    //     return view('prediccionesZonas.index', compact('zonas', 'predicciones'));
    // }

    /**
     * Show the form for creating a new resource.
     */

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
    }
}
