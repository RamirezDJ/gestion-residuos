<?php

namespace App\Http\Controllers;

use App\Models\GenSemanal;
use App\Models\Institutos;
use App\Models\RegistroPerCapita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MetaAnualController extends Controller
{
    /**
     * Muestra la vista principal con el resumen anual.
     */
    public function index()
    {
        $institutoId = auth()->user()->instituto_id;

        $instituto = Institutos::where('id', $institutoId)
            ->select('id', 'nombre', 'meta_anual', 'total_personas')
            ->first();


        $queryBase = GenSemanal::whereHas('zonaArea.zona', function ($query) use ($institutoId) {
            $query->where('instituto_id', $institutoId);
        });

        $totalResiduos = $queryBase->sum('kilos');
        $diasUnicosConRegistro = $queryBase->distinct('fecha')->count('fecha');

        $promedioKilosPorDia = $diasUnicosConRegistro > 0 ? $totalResiduos / $diasUnicosConRegistro : 0;
        $promedioPercapitaDiario = $instituto->total_personas > 0 ? $promedioKilosPorDia / $instituto->total_personas : 0;

        $excedeMeta = $promedioPercapitaDiario > $instituto->meta_anual;

        $registroConMayorGeneracion = GenSemanal::whereHas('zonaArea.zona.instituto', function ($query) use ($institutoId) {
            $query->where('id', $institutoId);
        })->orderBy('kilos', 'desc')->paginate(5, ['*'], 'gen_page');


        $registrosPerCapita = RegistroPerCapita::where('instituto_id', $institutoId)
            ->orderBy('fecha', 'desc')
            ->paginate(5, ['*'], 'per_capita_page');

        $promedioManual = RegistroPerCapita::where('instituto_id', $institutoId)->avg('per_capita');


        $datosGrafica = RegistroPerCapita::where('instituto_id', $institutoId)
            ->orderBy('fecha', 'asc')
            ->get()
            ->map(function ($registro) {
                return [
                    'x' => $registro->fecha,
                    'y' => $registro->per_capita
                ];
            });

        return view('metaAnual.index', compact(
            'instituto',
            'totalResiduos',
            'promedioPercapitaDiario',
            'excedeMeta',
            'registroConMayorGeneracion',
            'registrosPerCapita',
            'promedioManual',
            'datosGrafica'
        ));
    }

    /**
     * Muestra la vista independiente de Per Cápita.
     */
    public function perCapitaIndex()
    {
        $institutoId = auth()->user()->instituto_id;

        $instituto = Institutos::where('id', $institutoId)->first();


        $queryBase = GenSemanal::whereHas('zonaArea.zona', function ($query) use ($institutoId) {
            $query->where('instituto_id', $institutoId);
        });

        $totalResiduos = $queryBase->sum('kilos');
        $diasUnicosConRegistro = $queryBase->distinct('fecha')->count('fecha');
        $promedioKilosPorDia = $diasUnicosConRegistro > 0 ? $totalResiduos / $diasUnicosConRegistro : 0;

        $promedioPercapitaDiario = $instituto->total_personas > 0 ? $promedioKilosPorDia / $instituto->total_personas : 0;
        $excedeMeta = $promedioPercapitaDiario > $instituto->meta_anual;


        $registrosPerCapita = RegistroPerCapita::where('instituto_id', $institutoId)
            ->orderBy('fecha', 'desc')
            ->paginate(10);


        $promedioManual = RegistroPerCapita::where('instituto_id', $institutoId)->avg('per_capita');


        $datosGrafica = RegistroPerCapita::where('instituto_id', $institutoId)
            ->orderBy('fecha', 'asc') 
            ->get()
            ->map(function ($registro) {
                return [
                    'x' => $registro->fecha,       
                    'y' => $registro->per_capita   
                ];
            });

        return view('metaAnual.perCapita.index', compact(
            'instituto',
            'registrosPerCapita',
            'promedioPercapitaDiario',
            'excedeMeta',
            'promedioManual',
            'datosGrafica'
        ));
    }

    public function perCapitaCreate()
    {
        return view('metaAnual.perCapita.create');
    }

    public function perCapitaStore(Request $request)
    {
        $institutoId = auth()->user()->instituto_id;

        $existeRegistro = RegistroPerCapita::where('instituto_id', $institutoId)
            ->where('fecha', $request->fecha)
            ->exists();

        if ($existeRegistro) {
            return back()->withInput()->withErrors([
                'fecha' => 'Ya existe un reporte registrado para esta fecha.'
            ]);
        }

        $request->validate([
            'fecha' => 'required|date',
            'visitantes' => 'required|integer|min:0',
            'trabajadores' => 'required|integer|min:0',
            'kilos' => 'required|numeric|min:0',
        ]);

        $totalPersonas = $request->visitantes + $request->trabajadores;
        $perCapita = $totalPersonas > 0 ? ($request->kilos / $totalPersonas) : 0;

        RegistroPerCapita::create([
            'instituto_id' => $institutoId,
            'fecha' => $request->fecha,
            'visitantes' => $request->visitantes,
            'trabajadores' => $request->trabajadores,
            'kilos_residuos' => $request->kilos,
            'per_capita' => $perCapita,
        ]);

        return redirect()->route('metaAnual.percapita.index')
            ->with('swal', [
                'icon' => 'success',
                'title' => '¡Registrado!',
                'text' => 'El registro diario se ha guardado correctamente.'
            ]);
    }

    public function perCapitaEdit($id)
    {
        $registro = RegistroPerCapita::findOrFail($id);

        if ($registro->instituto_id != auth()->user()->instituto_id) {
            abort(403, 'No autorizado');
        }

        return view('metaAnual.perCapita.edit', compact('registro'));
    }

    public function perCapitaUpdate(Request $request, $id)
    {
        $registro = RegistroPerCapita::findOrFail($id);

        if ($registro->instituto_id != auth()->user()->instituto_id) {
            abort(403, 'No autorizado');
        }

        $request->validate([
            'fecha' => 'required|date',
            'visitantes' => 'required|integer|min:0',
            'trabajadores' => 'required|integer|min:0',
            'kilos' => 'required|numeric|min:0',
        ]);

        if ($request->fecha != $registro->fecha) {
            $existe = RegistroPerCapita::where('instituto_id', $registro->instituto_id)
                ->where('fecha', $request->fecha)
                ->exists();
            if ($existe) {
                return back()->withInput()->withErrors(['fecha' => 'Ya existe otro registro con esta fecha.']);
            }
        }

        $totalPersonas = $request->visitantes + $request->trabajadores;
        $perCapita = $totalPersonas > 0 ? ($request->kilos / $totalPersonas) : 0;

        $registro->update([
            'fecha' => $request->fecha,
            'visitantes' => $request->visitantes,
            'trabajadores' => $request->trabajadores,
            'kilos_residuos' => $request->kilos,
            'per_capita' => $perCapita,
        ]);

        return redirect()->route('metaAnual.percapita.index')
            ->with('swal', [
                'icon' => 'success',
                'title' => '¡Actualizado!',
                'text' => 'El registro se ha actualizado correctamente.'
            ]);
    }

    public function perCapitaDestroy($id)
    {
        $registro = RegistroPerCapita::findOrFail($id);

        if ($registro->instituto_id != auth()->user()->instituto_id) {
            abort(403, 'No autorizado');
        }

        $registro->delete();

        return redirect()->route('metaAnual.percapita.index')
            ->with('swal', [
                'icon' => 'success',
                'title' => '¡Eliminado!',
                'text' => 'El registro ha sido eliminado.'
            ]);
    }

    public function perCapitaShow($id)
    {
        return redirect()->route('metaAnual.percapita.index');
    }
}
