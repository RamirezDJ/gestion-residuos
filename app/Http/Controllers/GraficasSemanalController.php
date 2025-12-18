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

    

    
    private function getTop3Generado($query, $institutoId = null)
    {
        $q = $query->clone();

        
        $q->join('zonas_areas', 'gen_semanals.zonas_areas_id', '=', 'zonas_areas.id')
            ->join('zonas', 'zonas_areas.zona_id', '=', 'zonas.id')
            
            ->join('categorias', 'gen_semanals.categoria_id', '=', 'categorias.id');

        if ($institutoId) {
            $q->where('zonas.instituto_id', $institutoId);
        }

        return $q->select(
            'categorias.nombre as nombre',
            DB::raw('SUM(gen_semanals.kilos) as total_kg')
        )
            
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

        
        $q->join('zonas_areas', 'gen_semanals.zonas_areas_id', '=', 'zonas_areas.id')
            ->join('zonas', 'zonas_areas.zona_id', '=', 'zonas.id');

        
        $q->join('categorias', 'gen_semanals.categoria_id', '=', 'categorias.id');

        if ($institutoId) {
            $q->where('zonas.instituto_id', $institutoId);
        }

        return $q->select(
            'gen_semanals.fecha',
            'categorias.nombre as nombre_categoria', 
            DB::raw('SUM(gen_semanals.kilos) as total_kg')
        )
            
            ->groupBy('gen_semanals.fecha', 'categorias.id', 'categorias.nombre')
            ->orderBy('gen_semanals.fecha', 'ASC')
            ->get()
            ->map(function ($item) {
                return [
                    'fecha' => $item->fecha,
                    
                    'nombre' => $item->nombre_categoria,
                    'total_kg' => (float) $item->total_kg
                ];
            });
    }
}
