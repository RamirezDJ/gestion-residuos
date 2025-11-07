<?php

namespace App\Http\Controllers;

use App\Models\GenSemanal;
use App\Models\Institutos;
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
}
