<?php

namespace App\Http\Controllers;

use App\Models\IndiceCalidadAgua;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndicesCalidadController extends Controller
{

    public function index()
    {
        return view('indices_calidad.dashboard');
    }


    public function show(Request $request, $tipo)
    {
        if (!in_array($tipo, ['fisicos', 'quimicos'])) {
            abort(404);
        }

        $tiempo = $request->get('tiempo', 'general');
        $registros = [];

        
        if ($tiempo == 'general') {
            $registros = IndiceCalidadAgua::select(
                'fecha_muestreo',
                DB::raw('count(*) as total_muestras'),
                
                $tipo == 'fisicos' ? DB::raw('avg(turbidez) as promedio_dato') : DB::raw('avg(ph) as promedio_dato')
            )
                ->groupBy('fecha_muestreo')
                ->orderBy('fecha_muestreo', 'desc')
                ->paginate(10);
        } elseif ($tiempo == 'zonas_conteo') {
            $registros = IndiceCalidadAgua::select(
                'punto_muestreo',
                DB::raw('count(*) as total_registros'),
                DB::raw('max(fecha_muestreo) as ultima_fecha')
            )
                ->groupBy('punto_muestreo')
                ->orderBy('punto_muestreo', 'asc')
                ->paginate(10);
        }

        
        if ($tipo == 'fisicos') {
            
            $viewName = 'indices_calidad.partials.table-fisicos';
            return view('indices_calidad.show_fisicos', compact('registros', 'tiempo', 'viewName', 'tipo'));
        } else {
            $viewName = 'indices_calidad.partials.table-quimicos';
            return view('indices_calidad.show_quimicos', compact('registros', 'tiempo', 'viewName', 'tipo'));
        }
    }


    public function search(Request $request)
    {
        $query = $request->get('query');
        $tiempo = $request->get('tiempo', 'general');

        if ($tiempo == 'general') {
            $registros = IndiceCalidadAgua::select('fecha_muestreo', DB::raw('count(*) as total_muestras'), DB::raw('avg(ph) as promedio_ph'))
                ->where('fecha_muestreo', 'like', "%{$query}%")
                ->groupBy('fecha_muestreo')
                ->orderBy('fecha_muestreo', 'desc')
                ->get();

            return view('indices_calidad.partials.table-general', ['registros' => $registros])->render();
        }
    }

    public function create($tipo)
    {

        if (!in_array($tipo, ['fisicos', 'quimicos'])) {
            abort(404);
        }


        if ($tipo == 'fisicos') {
            return view('indices_calidad.create_fisicos', compact('tipo'));
        } else {
            return view('indices_calidad.create_quimicos', compact('tipo'));
        }
    }
    public function store(Request $request)
    {

        $request->validate([
            'punto_muestreo' => 'required|string',
            'fecha_muestreo' => 'required|date',
            'hora_muestreo' => 'required',
            'numero_muestra' => 'required|integer',
        ]);


        $registro = new IndiceCalidadAgua();



        $datos = $request->all();


        $datos['responsable_id'] = auth()->id();

        $registro->fill($datos);


        $registro->save();


        return redirect()->route('indicesCalidad.index');
    }
}
