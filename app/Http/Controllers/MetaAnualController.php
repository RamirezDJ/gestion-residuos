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
     * Display a listing of the resource.
     */
    public function index()
    {
        // llamar a los datos dependiendo del instituto
        $institutoId = auth()->user()->instituto_id;

        // Obtenemos los datos del instituto
        $instituto = Institutos::where('id', $institutoId)
            ->select('id', 'nombre', 'meta_anual', 'total_personas')
            ->first();

        // Creamos una consulta base para el instituto
        $queryBase = GenSemanal::whereHas('zonaArea.zona', function ($query) use ($institutoId) {
            $query->where('instituto_id', $institutoId);
        });

        // --- INICIO DE LA LÓGICA CORREGIDA ---

        // 1. Calcular el total de residuos generados
        $totalResiduos = $queryBase->sum('kilos');

        // 2. Contar los días únicos que tienen registros
        $diasUnicosConRegistro = $queryBase->distinct('fecha')->count('fecha');

        // 3. Calcular el promedio de kilos POR DÍA
        $promedioKilosPorDia = $diasUnicosConRegistro > 0 ? $totalResiduos / $diasUnicosConRegistro : 0;

        // 4. Calcular el promedio PER CAPITA por DÍA (Este es el cálculo correcto)
        $promedioPercapitaDiario = $instituto->total_personas > 0 ? $promedioKilosPorDia / $instituto->total_personas : 0;

        // --- FIN DE LA LÓGICA CORREGIDA ---

        // Porcentaje de cumplimiento de la meta anual
        // (Esta lógica compara tu meta anual con el promedio per cápita, puedes ajustarla si es necesario)
        $excedeMeta = $promedioPercapitaDiario > $instituto->meta_anual;

        // Obtener los registros de cada zona con mayor generacion
        $registroConMayorGeneracion = GenSemanal::whereHas('zonaArea.zona.instituto', function ($query) use ($institutoId) {
            $query->where('id', $institutoId);
        })->orderBy('kilos', 'desc')->paginate(5);

        return view('metaAnual.index', compact(
            'instituto',
            'totalResiduos',
            'promedioPercapitaDiario', // <-- Pasamos la nueva variable
            'excedeMeta',
            'registroConMayorGeneracion'
        ));
    }

    /**
     * Muestra la vista de índice para el detalle de Generación Per Cápita.
     */
    public function perCapitaIndex()
    {
        $institutoId = auth()->user()->instituto_id;

        // 1. Consultar los registros guardados en la tabla nueva
        $registrosPerCapita = RegistroPerCapita::where('instituto_id', $institutoId)
            ->orderBy('fecha', 'desc') // Los más recientes primero
            ->paginate(10);

        // 2. Calcular el promedio general histórico (Opcional, para la tarjeta de resumen)
        $promedioHistorico = RegistroPerCapita::where('instituto_id', $institutoId)->avg('per_capita');

        // 3. Enviar los datos a la vista
        return view('metaAnual.perCapita.index', compact('registrosPerCapita', 'promedioHistorico'));
    }

    public function perCapitaCreate()
    {
        return view('metaAnual.perCapita.create');
    }

    /**
     * Guarda el nuevo registro per cápita en la base de datos.
     */
    public function perCapitaStore(Request $request)
    {
        $institutoId = auth()->user()->instituto_id;

        // --- 1. VERIFICACIÓN DE DUPLICADOS (NUEVO) ---
        // Comprobamos si ya existe un registro con la misma fecha para este instituto
        $existeRegistro = RegistroPerCapita::where('instituto_id', $institutoId)
            ->where('fecha', $request->fecha)
            ->exists();

        if ($existeRegistro) {
            // Si ya existe, regresamos atrás mostrando un error en el campo 'fecha'
            // y devolviendo los datos (withInput) para que no tenga que escribirlos de nuevo.
            return back()
                ->withInput()
                ->withErrors([
                    'fecha' => 'Ya existe un reporte registrado para esta fecha (' . \Carbon\Carbon::parse($request->fecha)->format('d/m/Y') . '). Por favor elige otra o edita el existente.'
                ]);
        }

        // --- 2. VALIDACIÓN DE DATOS ---
        $request->validate([
            'fecha' => 'required|date',
            'visitantes' => 'required|integer|min:0',
            'trabajadores' => 'required|integer|min:0',
            'kilos' => 'required|numeric|min:0',
        ]);

        // --- 3. CÁLCULO ---
        $totalPersonas = $request->visitantes + $request->trabajadores;

        // Evitar división por cero
        $perCapita = $totalPersonas > 0 ? ($request->kilos / $totalPersonas) : 0;

        // --- 4. GUARDADO ---
        RegistroPerCapita::create([
            'instituto_id' => $institutoId,
            'fecha' => $request->fecha,
            'visitantes' => $request->visitantes,
            'trabajadores' => $request->trabajadores,
            'kilos_residuos' => $request->kilos,
            'per_capita' => $perCapita,
        ]);

        // --- 5. REDIRECCIÓN ---
        return redirect()->route('metaAnual.percapita.index')
            ->with('swal', [
                'icon' => 'success',
                'title' => '¡Registrado!',
                'text' => 'El registro diario se ha guardado correctamente.'
            ]);
    }
}
