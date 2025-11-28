<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReporteDiarioController extends Controller
{
    /**
     * Muestra el formulario para ingresar datos del reporte diario.
     */
    public function index()
    {
        return view('reporteDiario.index');
    }

    /**
     * Procesa los datos del formulario y genera el reporte Per Cápita.
     */
    public function generarReporte(Request $request)
    {
        // 1. Validación de datos
        $request->validate([
            'fecha' => 'required|date',
            'visitantes' => 'required|numeric|min:0',
            'trabajadores' => 'required|numeric|min:0',
            'residuos' => 'required|numeric|min:0',
        ]);

        $visitantes = $request->input('visitantes');
        $trabajadores = $request->input('trabajadores');
        $residuos = $request->input('residuos');
        $fecha = $request->input('fecha');

        // 2. Lógica del Cálculo
        $totalPersonas = $visitantes + $trabajadores;

        // Cálculo: Generación Per Cápita por día (kg de residuos / total de personas)
        $generacionPercapita = $totalPersonas > 0 ? $residuos / $totalPersonas : 0;

        // 3. Devolver la vista de resultados
        return view('reporteDiario.resultado', compact(
            'fecha',
            'visitantes',
            'trabajadores',
            'residuos',
            'totalPersonas',
            'generacionPercapita'
        ));
    }
}