<?php

namespace App\Http\Controllers;

use App\Models\Muestreo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class IndicesCalidadController extends Controller
{
    public function index()
    {
        return view('indices_calidad.dashboard');
    }

    public function show(Request $request, $tipo)
    {
        $tiempo = $request->get('tiempo', 'general');
        $view = 'indices_calidad.show_' . $tipo;
        $institutoId = Auth::user()->instituto_id;

        $query = Muestreo::where('muestreos.instituto_id', $institutoId);

        $tablas = [
            'fisicos' => 'parametros_fisicos',
            'quimicos' => 'parametros_quimicos',
            'biologicos' => 'parametros_biologicos'
        ];

        $tablaHija = $tablas[$tipo] ?? abort(404);

        if ($tiempo !== 'general') {
            $query->join($tablaHija, 'muestreos.id', '=', $tablaHija . '.muestreo_id');
            if ($tiempo == 'zonas_conteo') {
                $query->select('muestreos.punto_muestreo')
                    ->groupBy('muestreos.punto_muestreo')
                    ->orderBy('muestreos.punto_muestreo', 'asc');
            } elseif ($tiempo == 'cronologico') {
                $query->selectRaw("DATE_FORMAT(fecha_muestreo, '%Y-%m-01') as fecha_grupo")
                    ->groupBy('fecha_grupo')
                    ->orderBy('fecha_grupo', 'desc');
            }
            if ($tipo == 'quimicos') {
                $query->selectRaw("AVG($tablaHija.ph) as promedio_ph, AVG($tablaHija.oxigeno_disuelto_ppm) as promedio_oxigeno");
            } elseif ($tipo == 'fisicos') {
                $query->selectRaw("
                AVG($tablaHija.turbidez) as promedio_turbidez, 
                COUNT(muestreos.id) as total_registros, 
                MAX(muestreos.fecha_muestreo) as ultima_fecha
            ");
            } elseif ($tipo == 'biologicos') {
                $query->selectRaw("
                    AVG($tablaHija.coliformes_totales) as promedio_coliformes, 
                    AVG($tablaHija.coliformes_fecales) as promedio_fecales,
                    COUNT(muestreos.id) as total_registros, 
                    MAX(muestreos.fecha_muestreo) as ultima_fecha
                ");
            }
        } else {
            $query->with(['responsable', $tipo])
                ->whereHas($tipo)
                ->select('muestreos.*')
                ->orderBy('fecha_muestreo', 'desc')
                ->orderBy('hora_muestreo', 'desc');
        }

        $registros = $query->paginate(10)->appends(['tiempo' => $tiempo]);

        return view($view, compact('registros', 'tiempo', 'tipo'));
    }

    public function search(Request $request)
    {
        $queryTexto = $request->get('query');
        $tipo = $request->get('tipo', 'fisicos');
        $institutoId = Auth::user()->instituto_id;
        $query = Muestreo::query()
            ->where('instituto_id', $institutoId);
        if ($queryTexto) {
            $query->where(function ($q) use ($queryTexto) {
                $q->where('fecha_muestreo', 'like', "%{$queryTexto}%")
                    ->orWhere('punto_muestreo', 'like', "%{$queryTexto}%");
            });
        }
        if ($tipo) {
            $query->whereHas($tipo)->with($tipo);
        }

        $registros = $query->orderBy('fecha_muestreo', 'desc')->get();
        return view('indices_calidad.partials.table-' . $tipo, ['registros' => $registros])->render();
    }

    public function showDetail($id)
    {
        $registro = Muestreo::with(['fisicos', 'quimicos', 'biologicos', 'responsable'])->findOrFail($id);

        if ($registro->biologicos) {
            return view('indices_calidad.detail_biologicos', compact('registro'));
        } elseif ($registro->quimicos) {
            return view('indices_calidad.detail_quimicos', compact('registro'));
        } else {
            return view('indices_calidad.detail_fisicos', compact('registro'));
        }
    }

    public function create($tipo)
    {
        switch ($tipo) {
            case 'fisicos':
                return view('indices_calidad.create_fisicos');

            case 'quimicos':
                return view('indices_calidad.create_quimicos');

            case 'biologicos':
                return view('indices_calidad.create_biologicos');

            default:
                abort(404);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'punto_muestreo' => 'required|string|max:255',
            'fecha_muestreo' => 'required|date',
            'hora_muestreo'  => 'required',
            'numero_muestra' => 'required|integer',
            'tipo_registro'  => 'required|in:fisicos,quimicos,biologicos',

            // Físicos
            'temperatura' => 'nullable|numeric',
            'turbidez'    => 'nullable|numeric',

            // Químicos
            'ph'                   => 'nullable|numeric|between:0,14',
            'oxigeno_disuelto_ppm' => 'nullable|numeric',
            'dbo'                  => 'nullable|numeric',
            'dqo'                  => 'nullable|numeric',
            'nitratos'             => 'nullable|numeric',
            'nitritos'             => 'nullable|numeric',
            'fosfatos'             => 'nullable|numeric',
            'cloro_libre'          => 'nullable|numeric',

            // Biológicos 
            'coliformes_totales'   => 'nullable|numeric',
            'coliformes_fecales'   => 'nullable|numeric',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $muestreo = Muestreo::create([
                    'instituto_id'   => Auth::user()->instituto_id,
                    'punto_muestreo' => $request->punto_muestreo,
                    'fecha_muestreo' => $request->fecha_muestreo,
                    'hora_muestreo'  => $request->hora_muestreo,
                    'numero_muestra' => $request->numero_muestra,
                    'responsable_id' => Auth::id(),
                    'observaciones'  => $request->observaciones,
                ]);

                switch ($request->tipo_registro) {
                    case 'fisicos':
                        $muestreo->fisicos()->create($request->only([
                            'temperatura',
                            'color',
                            'olor',
                            'sabor',
                            'turbidez',
                            'conductividad_electrica',
                            'solidos_disueltos',
                            'solidos_suspension'
                        ]));
                        break;

                    case 'quimicos':
                        $muestreo->quimicos()->create($request->only([
                            'ph',
                            'oxigeno_disuelto_ppm',
                            'dbo',
                            'dqo',
                            'nitratos',
                            'nitritos',
                            'fosfatos',
                            'cloro_libre'
                        ]));
                        break;

                    case 'biologicos':
                        $muestreo->biologicos()->create($request->only([
                            'coliformes_totales',
                            'coliformes_fecales'
                        ]));
                        break;
                }
            });

            return redirect()->route('indicesCalidad.show', ['tipo' => $request->tipo_registro])
                ->with('swal', [
                    'icon' => 'success',
                    'title' => '¡Guardado!',
                    'text' => 'El registro se ha creado correctamente.'
                ]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('swal', [
                    'icon' => 'error',
                    'title' => 'Error de Sistema',
                    'text' => 'No se pudo guardar: ' . $e->getMessage()
                ]);
        }
    }


    public function edit($id)
    {
        $muestreo = Muestreo::with(['fisicos', 'quimicos', 'biologicos'])->findOrFail($id);
        if ($muestreo->instituto_id !== Auth::user()->instituto_id) {
            abort(403, 'No tienes permiso para editar este registro.');
        }

        $tipo = 'fisicos';
        if ($muestreo->quimicos) {
            $tipo = 'quimicos';
        } elseif ($muestreo->biologicos) {
            $tipo = 'biologicos';
        }
        return view('indices_calidad.edit_' . $tipo, [
            'registro' => $muestreo
        ]);
    }


    public function update(Request $request, $id)
    {
        $muestreo = Muestreo::findOrFail($id);

        if ($muestreo->instituto_id !== Auth::user()->instituto_id) {
            abort(403, 'Acción no autorizada.');
        }
        $request->validate([
            'punto_muestreo' => 'required|string',
            'fecha_muestreo' => 'required|date',
        ]);

        DB::transaction(function () use ($request, $muestreo) {
            $muestreo->update([
                'punto_muestreo' => $request->punto_muestreo,
                'fecha_muestreo' => $request->fecha_muestreo,
                'hora_muestreo'  => $request->hora_muestreo,
                'numero_muestra' => $request->numero_muestra,
                'observaciones'  => $request->observaciones,
            ]);
            if ($muestreo->fisicos) {
                $muestreo->fisicos()->update([
                    'temperatura'             => $request->temperatura,
                    'color'                   => $request->color,
                    'olor'                    => $request->olor,
                    'sabor'                   => $request->sabor,
                    'turbidez'                => $request->turbidez,
                    'conductividad_electrica' => $request->conductividad_electrica,
                    'solidos_disueltos'       => $request->solidos_disueltos,
                    'solidos_suspension'      => $request->solidos_suspension,
                ]);
            } elseif ($muestreo->quimicos) {
                $muestreo->quimicos()->update([
                    'ph' => $request->ph,
                    'oxigeno_disuelto_ppm' => $request->oxigeno_disuelto_ppm,
                ]);
            } elseif ($muestreo->biologicos) {
                $muestreo->biologicos()->update([
                    'coliformes_totales' => $request->coliformes_totales,
                ]);
            }
        });

        $tipoRedirect = 'fisicos';
        if ($muestreo->quimicos) $tipoRedirect = 'quimicos';
        if ($muestreo->biologicos) $tipoRedirect = 'biologicos';

        return redirect()->route('indicesCalidad.show', ['tipo' => $tipoRedirect])
            ->with('success', 'Registro actualizado correctamente');
    }

    public function destroy($id)
    {
        try {
            $muestreo = Muestreo::with(['fisicos', 'quimicos', 'biologicos'])->findOrFail($id);

            if ($muestreo->instituto_id !== Auth::user()->instituto_id) {
                abort(403, 'No tienes permiso para eliminar este registro.');
            }

            $tipoRedireccion = 'fisicos';
            if ($muestreo->quimicos) {
                $tipoRedireccion = 'quimicos';
            } elseif ($muestreo->biologicos) {
                $tipoRedireccion = 'biologicos';
            }
            $muestreo->delete();

            return redirect()->route('indicesCalidad.show', ['tipo' => $tipoRedireccion])
                ->with('swal', [
                    'icon' => 'success',
                    'title' => '¡Eliminado!',
                    'text' => 'El registro ha sido eliminado correctamente.'
                ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('swal', [
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'No se pudo eliminar el registro: ' . $e->getMessage()
            ]);
        }
    }
}
