<?php

namespace App\Http\Controllers;

use App\Exports\RegistroSubproductosExport;
use App\Models\GenSemanal;
use App\Models\GenSubproducto;
use App\Models\Subproducto;
use App\Models\ZonasAreas;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class RegistroSubproductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tiempo = $request->input('tiempo', 'semanal');
        $institutoId = auth()->user()->instituto_id;

        switch ($tiempo) {
            case 'semanal':
                $registroPeriodo = GenSubproducto::selectRaw(
                    'YEAR(fecha) as year, 
                    WEEK(fecha) as semana, 
                    MIN(fecha) as fecha_inicio, 
                    MAX(fecha) as fecha_final, 
                    SUM(valor_kg) as total_kg,
                    instituto_id'
                )
                    ->where('instituto_id', $institutoId)
                    ->groupBy('year', 'semana', 'instituto_id')
                    ->orderBy('year', 'asc')
                    ->orderBy('semana', 'asc')
                    ->get();
                break;

            case 'mensual':
                $registroPeriodo = GenSubproducto::selectRaw(
                    'YEAR(fecha) as year, 
                    MONTH(fecha) as mes, 
                    DATE_FORMAT(MIN(fecha), "%Y-%m-01") as fecha_inicio, 
                    LAST_DAY(MAX(fecha)) as fecha_final, 
                    SUM(valor_kg) as total_kg, 
                    instituto_id'
                )
                    ->where('instituto_id', $institutoId)
                    ->groupBy('year', 'mes', 'instituto_id')
                    ->orderBy('year', 'asc')
                    ->orderBy('mes', 'asc')
                    ->get();
                break;

            case 'anual':
                $registroPeriodo = GenSubproducto::selectRaw(
                    'YEAR(fecha) as year, 
                    DATE_FORMAT(MIN(fecha), "%Y-01-01") as fecha_inicio, 
                    DATE_FORMAT(MAX(fecha), "%Y-12-31") as fecha_final, 
                    SUM(valor_kg) as total_kg, 
                    instituto_id'
                )
                    ->where('instituto_id', $institutoId)
                    ->groupBy('year', 'instituto_id')
                    ->orderBy('year', 'asc')
                    ->get();
                break;

            default:
                abort(400, 'Período no válido');
        }

        return view('gensubproductos.index', compact('registroPeriodo'));
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
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validación básica
        $request->validate([
            'valores' => 'required|array',
        ]);

        $institutoId = auth()->user()->instituto_id;

        // La estructura que llega es: valores[zona_id][subproducto_id][fecha] = cantidad
        $valores = $request->input('valores', []);

        $batchData = [];
        $now = now();

        if (count($valores) > 0) {
            // El primer nivel del array tiene la ZONA como clave ($zonaId)
            foreach ($valores as $zonaId => $subproductos) {
                foreach ($subproductos as $subproductoId => $fechas) {
                    foreach ($fechas as $fecha => $kilos) {

                        // Validamos que sea número y mayor a 0
                        if (is_numeric($kilos) && $kilos > 0) {
                            $batchData[] = [
                                'instituto_id'   => $institutoId,
                                'zona_id'        => $zonaId, // <--- ¡ESTA LÍNEA ES LA QUE FALTABA!
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

        // Insertamos
        if (count($batchData) > 0) {
            // Usamos una transacción para mayor seguridad
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
        // Validaciones...
        if (!Auth::check()) return redirect()->route('login');

        $inicio = \Carbon\Carbon::parse($inicio);
        $final = \Carbon\Carbon::parse($final);

        $datosGenerados = \App\Models\GenSubproducto::select(
            // Usamos DB::raw para campos calculados o complejos
            \DB::raw('COALESCE(zonas.nombre, "Sin Zona Asignada") as zona_nombre'),
            'subproductos.nombre as subproducto_nombre',
            'gen_subproductos.fecha',
            // AQUÍ ESTÁ LA CLAVE: Sumamos 'valor_kg' y lo renombramos 'total_kg'
            \DB::raw('SUM(gen_subproductos.valor_kg) as total_kg')
        )
            ->join('subproductos', 'gen_subproductos.subproducto_id', '=', 'subproductos.id')
            ->leftJoin('zonas', 'gen_subproductos.zona_id', '=', 'zonas.id')
            ->where('gen_subproductos.instituto_id', Auth::user()->instituto_id)
            ->whereBetween('gen_subproductos.fecha', [$inicio, $final])
            ->groupBy('zonas.nombre', 'subproductos.nombre', 'gen_subproductos.fecha')
            ->orderBy('zonas.nombre')
            ->orderBy('subproductos.nombre')
            ->get(); // <--- El get() es importante para convertirlo en colección

        $datosAgrupados = $datosGenerados->groupBy(['zona_nombre', 'subproducto_nombre']);

        return view('gensubproductos.show', [
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
        // 1. Parsear fechas
        $inicioDate = \Carbon\Carbon::parse($inicio);
        $finalDate = \Carbon\Carbon::parse($final);

        // 2. Auth Check
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $instituto = Auth::user()->instituto;

        // 3. Obtener Catálogos
        // NECESITAMOS LAS ZONAS para el Wizard
        $zonas = \App\Models\Zona::where('instituto_id', $instituto->id)->get();
        $subproductos = \App\Models\Subproducto::all();

        // 4. Obtener Datos Registrados (CRUDOS)
        // IMPORTANTE: Traemos el 'zona_id' para saber dónde pintar el dato
        $datosRegistrados = \App\Models\GenSubproducto::where('instituto_id', $instituto->id)
            ->whereBetween('fecha', [$inicioDate->startOfDay(), $finalDate->endOfDay()])
            ->select('zona_id', 'subproducto_id', 'fecha', 'valor_kg') // <--- Seleccionamos zona_id
            ->get();

        // Retornamos vista con datos limpios
        return view('gensubproductos.edit', [
            'instituto' => $instituto,
            'instituto_id' => $instituto->id,
            'inicio' => $inicioDate->format('Y-m-d'), // Formato estándar para JS
            'final' => $finalDate->format('Y-m-d'),
            'zonas' => $zonas,              // <--- Agregado
            'subproductos' => $subproductos,
            'datosRegistrados' => $datosRegistrados // <--- Enviamos la colección plana
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateMultiple(Request $request)
    {
        $request->validate([
            'instituto_id' => 'required|exists:institutos,id',
            'zonas_areas_id' => 'nullable|exists:zonas_areas,id', // <--- NUEVO
            'inicio' => 'required|date|before_or_equal:final',
            'final' => 'required|date|after_or_equal:inicio',
            'subproducto' => 'required|array',
            'subproducto.*.valor_kg.*' => 'nullable|numeric|regex:/^\d+(\.\d{1,3})?$/|max:100',
        ]);

        $instituto_id = $request->input('instituto_id');
        $zona_id = $request->input('zona_id'); // <--- CAPTURAR ZONA

        $inicio = Carbon::createFromFormat('m/d/Y', $request->input('inicio'));
        $final = Carbon::createFromFormat('m/d/Y', $request->input('final'));

        // ... (Lógica de fechas se mantiene igual) ...
        $inicio->copy();
        $inicioSemana = $inicio->dayOfWeek;
        $inicio = $inicio->subDays($inicioSemana);

        // ... (Generación del array $dias se mantiene igual) ...
        $dias = [];
        $currentDate = $inicio->copy();
        while ($currentDate->lte($final)) {
            $dias[] = $currentDate->copy();
            $currentDate->addDay();
        }

        // Iterar y actualizar
        foreach ($request->input('subproducto') as $subproducto_id => $subproducto) {
            $valoresKg = $subproducto['valor_kg'];

            foreach ($dias as $index => $fecha) {
                $diaKey = 'dia_' . ($index + 1);
                $valorKg = $valoresKg[$diaKey] ?? null;
                $fechaCalculada = $fecha;

                // BÚSQUEDA DEL REGISTRO EXISTENTE
                // Aquí agregamos el filtro de zona para no editar el registro de otra área por error
                $query = GenSubproducto::where('instituto_id', $instituto_id)
                    ->where('subproducto_id', $subproducto_id)
                    ->whereDate('fecha', $fechaCalculada->toDateString());

                if ($zona_id) {
                    $query->where('zona_id', $zona_id); // Solo busca en esa zona
                } else {
                    $query->whereNull('zona_id'); // Busca los que no tienen zona
                }

                $registroExistente = $query->first(); // Ejecutamos la búsqueda

                if ($registroExistente) {
                    if (is_null($valorKg) || $valorKg === '') {
                        $registroExistente->delete();
                    } else {
                        $registroExistente->update([
                            'valor_kg' => $valorKg,
                            // No hace falta actualizar zona o instituto, ya coinciden
                        ]);
                    }
                } else {
                    if (!is_null($valorKg) && $valorKg !== '') {
                        GenSubproducto::create([
                            'fecha' => $fecha->toDateString(),
                            'valor_kg' => $valorKg,
                            'instituto_id' => $instituto_id,
                            'subproducto_id' => $subproducto_id,
                            'zona_id' => $zona_id, // <--- Importante poner la zona al crear
                        ]);
                    }
                }
            }
        }

        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Hecho!',
            'text' => 'Los datos se han actualizado con éxito',
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

            // Parsear fecha y calcular fin de semana
            $startOfWeek = Carbon::parse($fechaInicio)->startOfWeek();
            $endOfWeek = Carbon::parse($fechaInicio)->endOfWeek();

            // Eliminar directamente usando el rango de fechas e instituto
            $borrados = GenSubproducto::where('instituto_id', $institutoId)
                ->whereBetween('fecha', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
                ->delete();

            if ($borrados > 0) {
                return redirect()->route('gensubproducto.index')->with('swal', [
                    'icon' => 'success',
                    'title' => 'Eliminado',
                    'text' => 'La semana de subproductos ha sido eliminada correctamente.'
                ]);
            } else {
                return redirect()->route('gensubproducto.index')->with('swal', [
                    'icon' => 'info',
                    'title' => 'Info',
                    'text' => 'No se encontraron registros para eliminar en esa semana.'
                ]);
            }
        } catch (\Exception $e) {
            return redirect()->route('gensubproducto.index')->with('swal', [
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'No se pudo eliminar la semana: ' . $e->getMessage()
            ]);
        }
    }

    public function search(Request $request)
    {
        $busqueda = $request->input('query');
        $tiempo = $request->input('tiempo', 'semanal');
        $institutoId = auth()->user()->instituto_id;

        if (empty($busqueda)) {
            return redirect()->route('gensubproducto.index');
        }

        // CORRECCIÓN: Usar GenSubproducto, no GenSemanal
        $query = GenSubproducto::query()->where('instituto_id', $institutoId);

        // Filtramos por nombre del subproducto
        $query->whereHas('subproducto', function ($q) use ($busqueda) {
            $q->where('nombre', 'LIKE', "%{$busqueda}%");
        });

        // Agrupación (Igual que tu index)
        if ($tiempo === 'mensual') {
            $registros = $query->selectRaw(
                'YEAR(fecha) as year, MONTH(fecha) as mes, MIN(fecha) as fecha_inicio, MAX(fecha) as fecha_final, SUM(valor_kg) as total_kg, instituto_id'
            )
                ->groupBy('year', 'mes', 'instituto_id')
                ->orderBy('year', 'desc')->orderBy('mes', 'desc')
                ->get();
        } else {
            $registros = $query->selectRaw(
                'YEAR(fecha) as year, WEEK(fecha, 1) as semana, MIN(fecha) as fecha_inicio, MAX(fecha) as fecha_final, SUM(valor_kg) as total_kg, instituto_id'
            )
                ->groupBy('year', 'semana', 'instituto_id')
                ->orderBy('year', 'desc')->orderBy('semana', 'desc')
                ->get();
        }

        if ($request->ajax()) {
            $viewName = ($tiempo === 'mensual')
                ? 'gensubproductos.partials.table_mensual'
                : 'gensubproductos.partials.table_semanal';
            return view($viewName, compact('registros'))->render();
        }

        return view('gensubproductos.index', compact('registros', 'tiempo'));
    }

    public function GenerarPDF(Request $request, $instituto_id, $inicio, $final)
    {
        // 1. Validar sesión
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $user = Auth::user();

        // 2. CORRECCIÓN DE FECHAS: Usamos startOfDay y endOfDay
        // Esto asegura que si filtras hasta "hoy", incluya todo lo de hoy.
        $inicio = \Carbon\Carbon::parse($inicio)->startOfDay();
        $final = \Carbon\Carbon::parse($final)->endOfDay();

        // 3. Consulta con LeftJoin y GroupBy ZONA (Igual que en el show)
        $datosGenerados = \App\Models\GenSubproducto::select(
            \DB::raw('COALESCE(zonas.nombre, "Sin Zona Asignada") as zona_nombre'),
            'subproductos.nombre as subproducto_nombre',
            'gen_subproductos.fecha',
            // Obtenemos el valor real para sumar
            'gen_subproductos.valor_kg'
        )
            ->join('subproductos', 'gen_subproductos.subproducto_id', '=', 'subproductos.id')
            ->leftJoin('zonas', 'gen_subproductos.zona_id', '=', 'zonas.id')
            ->where('gen_subproductos.instituto_id', $user->instituto_id)
            ->whereBetween('gen_subproductos.fecha', [$inicio, $final])

            // Ordenamos para mantener el orden visual
            ->orderBy('zonas.nombre')
            ->orderBy('subproductos.nombre')
            ->orderBy('gen_subproductos.fecha')
            ->get();

        // 4. AGRUPACIÓN DOBLE: Zona -> Subproducto
        // Esto es crucial para que el bucle @foreach de tu PDF funcione
        $datosAgrupados = $datosGenerados->groupBy(['zona_nombre', 'subproducto_nombre']);

        // NOTA: Para los totales dentro del grupo, calculamos la suma en la vista o mapeamos aquí
        // Para simplificar, enviaremos la colección tal cual y dejaremos que la vista sume.

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('gensubproductos.pdf', [
            'datosAgrupados' => $datosAgrupados,
            'inicio' => $inicio,
            'final' => $final,
            'instituto' => $user->instituto
        ]);

        return $pdf->stream('reporte_subproductos.pdf');
    }


    public function GenerarExcel(Request $request, $instituto_id, $inicio, $final)
    {
        $inicio = \Carbon\Carbon::parse($inicio)->startOfDay();
        $final = \Carbon\Carbon::parse($final)->endOfDay();

        // 1. Verificar sesión
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $user = Auth::user();

        // (Opcional) Validar que el usuario pertenezca al instituto
        // if ($user->instituto_id != $instituto_id) abort(403);

        // 2. Consulta corregida (Igual que en el PDF)
        $datosGenerados = \App\Models\GenSubproducto::select(
            // Agregamos la columna de ZONA
            \DB::raw('COALESCE(zonas.nombre, "Sin Zona Asignada") as zona_nombre'),
            'subproductos.nombre as subproducto_nombre',
            'gen_subproductos.fecha',
            // Sumamos los kilos agrupados
            \DB::raw('SUM(gen_subproductos.valor_kg) as total_kg')
        )
            ->join('subproductos', 'gen_subproductos.subproducto_id', '=', 'subproductos.id')
            // Usamos LeftJoin para incluir registros viejos sin zona
            ->leftJoin('zonas', 'gen_subproductos.zona_id', '=', 'zonas.id')

            // Filtramos por el instituto del usuario para seguridad
            ->where('gen_subproductos.instituto_id', $user->instituto_id)
            ->whereBetween('gen_subproductos.fecha', [$inicio, $final])

            // Agrupamos incluyendo la Zona
            ->groupBy('zonas.nombre', 'subproductos.id', 'subproductos.nombre', 'gen_subproductos.fecha')

            // Ordenamos
            ->orderBy('zonas.nombre')
            ->orderBy('subproductos.nombre')
            ->orderBy('gen_subproductos.fecha')
            ->get();

        return Excel::download(new RegistroSubproductosExport($datosGenerados, $inicio, $final), 'registro-subproductos.xlsx');
    }
}
