<?php

namespace App\Http\Controllers;

use App\Models\GenSemanal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class GraficasSemanalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $baseQuery = GenSemanal::query();
        $institutoId = Auth::user()->instituto_id;
        $top3Generado = $this->getTop3Generado($baseQuery, $institutoId);

        // dd($top3Generado);

        return view('graficassemanal.index', compact('top3Generado'));
    }

    private function applyDataFilters($query, $periodo, $startDate, $endDate)
    {
        $campoFecha = 'gen_semanals.fecha';

        if ($startDate && $endDate) {
            $query->whereBetween($campoFecha, [
                Carbon::parse($startDate)->format('Y-m-d'),
                Carbon::parse($endDate)->format('Y-m-d')
            ]);
        } else {
            switch ($periodo) {
                case '7_dias':
                    $query->whereBetween($campoFecha, [now()->subDays(7), now()]);
                    break;
                case '30_dias':
                    $query->whereBetween($campoFecha, [now()->subDays(30), now()]);
                    break;
                case '90_dias':
                    $query->whereBetween($campoFecha, [now()->subDays(90), now()]);
                    break;
                default:
                    // Sin filtro de fecha
                    break;
            }
        }
    }

    public function fetchGraphData(Request $request)
    {
        $tipoGrafica = $request->input('tipoGrafico');
        $periodo = $request->input('periodo', 'Todo');

        $startDate = $request->input('inicio');
        $endDate = $request->input('final');

        $institutoId = Auth::user()->instituto_id;

        $baseQuery = GenSemanal::query();
        $this->applyDataFilters($baseQuery, $periodo, $startDate, $endDate);

        switch ($tipoGrafica) {
            case 'top3':
                $data = $this->getTop3Generado($baseQuery, $institutoId);
                break;
            case 'pieChart':
                $data = $this->getPorcentajeResiduos($baseQuery, $institutoId);
                break;
            case 'barChart':
                $data = $this->getGraficoTotalResiduos($baseQuery, $institutoId);
                break;
            case 'lineChart':
                $data = $this->getGraficoTendenciaResiduos($baseQuery, $institutoId);
                break;
            case 'all':
                // Usamos clone() para no afectar la query original en cada llamada
                $data = [
                    'top3' => $this->getTop3Generado($baseQuery->clone(), $institutoId),
                    'pieChart' => $this->getPorcentajeResiduos($baseQuery->clone(), $institutoId),
                    'barChart' => $this->getGraficoTotalResiduos($baseQuery->clone(), $institutoId),
                    'lineChart' => $this->getGraficoTendenciaResiduos($baseQuery->clone(), $institutoId)
                ];
                break;
            default:
                return response()->json(['error' => 'Tipo de gráfica no válido'], 400);
        }

        return response()->json($data);
    }

    // Funciones para obtener los datos de cada grafica de generacion de residuos

    // Obtener datos de top 3 zonas con mayor generacion
    private function getTop3Generado($query, $institutoId = null)
    {
        $q = $query->clone();

        // Unimos con zonas para filtrar por instituto
        $q->join('zonas_areas', 'gen_semanals.zonas_areas_id', '=', 'zonas_areas.id')
            ->join('zonas', 'zonas_areas.zona_id', '=', 'zonas.id')
            // CORRECCIÓN: Usamos 'categorias' y 'categoria_id' (que es lo que tienes en la BD)
            ->join('categorias', 'gen_semanals.categoria_id', '=', 'categorias.id');

        if ($institutoId) {
            $q->where('zonas.instituto_id', $institutoId);
        }

        return $q->select(
            'categorias.nombre as nombre',
            DB::raw('SUM(gen_semanals.kilos) as total_kg')
        )
            // Agrupamos por Categoría
            ->groupBy('categorias.id', 'categorias.nombre')
            ->orderByDesc('total_kg')
            ->limit(3)
            ->get()
            ->map(function ($item) {
                return [
                    'nombre' => $item->nombre,
                    'total_kg' => (float) $item->total_kg
                ];
            });
    }

    // Obtener datos de la grafica pastel de porcentaje de generacion por zonas
    private function getPorcentajeResiduos($query, $institutoId = null)
    {
        $q = $query->clone();

        $q->join('zonas_areas', 'gen_semanals.zonas_areas_id', '=', 'zonas_areas.id')
            ->join('zonas', 'zonas_areas.zona_id', '=', 'zonas.id');

        if ($institutoId) {
            $q->where('zonas.instituto_id', $institutoId);
        }

        return $q->select(
            'zonas.nombre as nombre',
            DB::raw('SUM(gen_semanals.kilos) as total_kg')
        )
            ->groupBy('zonas.id', 'zonas.nombre')
            ->get()
            ->map(function ($item) {
                return [
                    'nombre' => $item->nombre,
                    // IMPORTANTE: Forzamos número
                    'total_kg' => (float) $item->total_kg
                ];
            });
    }

    private function getGraficoTotalResiduos($query, $institutoId = null)
    {
        $query->join('zonas_areas', 'gen_semanals.zonas_areas_id', '=', 'zonas_areas.id')
            ->join('zonas', 'zonas_areas.zona_id', '=', 'zonas.id');

        if ($institutoId) {
            $query->where('zonas.instituto_id', $institutoId);
        }

        return $query->select(
            'zonas.id as zona_id',
            'zonas.nombre as zona',
            // --- LÍNEA CORREGIDA ---
            DB::raw('SUM(gen_semanals.kilos) as total_kg')
        )
            ->groupBy('zonas.id', 'zonas.nombre')
            ->orderBy('total_kg', 'DESC')
            ->get()
            ->map(function ($item) {
                return [
                    'nombre' => $item->zona,
                    'total_kg' =>  $item->total_kg
                ];
            });
    }

    private function getGraficoTendenciaResiduos($query, $institutoId = null)
    {
        $q = $query->clone();

        // 1. Joins necesarios para llegar al Instituto (Filtro de seguridad)
        $q->join('zonas_areas', 'gen_semanals.zonas_areas_id', '=', 'zonas_areas.id')
            ->join('zonas', 'zonas_areas.zona_id', '=', 'zonas.id');

        // 2. Join con Categorías (Para obtener el nombre del subproducto)
        $q->join('categorias', 'gen_semanals.categoria_id', '=', 'categorias.id');

        if ($institutoId) {
            $q->where('zonas.instituto_id', $institutoId);
        }

        return $q->select(
            'gen_semanals.fecha',
            'categorias.nombre as nombre_categoria', // Ahora seleccionamos la categoría
            DB::raw('SUM(gen_semanals.kilos) as total_kg')
        )
            // --- CAMBIO CLAVE: Agrupamos por FECHA y CATEGORÍA ---
            ->groupBy('gen_semanals.fecha', 'categorias.id', 'categorias.nombre')
            ->orderBy('gen_semanals.fecha', 'ASC')
            ->get()
            ->map(function ($item) {
                return [
                    'fecha' => $item->fecha,
                    // Enviamos el nombre de la categoría como 'nombre' para que el JS no falle
                    'nombre' => $item->nombre_categoria,
                    'total_kg' => (float) $item->total_kg
                ];
            });
    }
}
