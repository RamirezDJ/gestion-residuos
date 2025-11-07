<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Institutos;
use App\Models\Subproducto;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $instituto = auth()->user()->instituto;

        $areas = Area::where('instituto_id', $instituto->id)->get();

        return view('admin.areas.index', compact('areas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $institutos = Institutos::all();

        $subproductos_todos = Subproducto::orderBy('nombre')->get();


        return view('admin.areas.create', compact('institutos', 'subproductos_todos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. VALIDACIÓN (Añadimos 'subproductos')
        $request->validate([
            'nombre' => ['required', 'unique:areas,nombre'],
            'descripcion' => 'nullable',
            'instituto_id' => 'required|exists:institutos,id',
            'subproductos' => 'nullable|array' // <-- AÑADIDO
        ]);

        // 2. CREAR EL ÁREA (Tu código original)
        $area = Area::create($request->all());

        // 3. GUARDAR SUBPRODUCTOS (Nuevo)
        // Después de crear el área, le "adjuntamos" los subproductos
        $subproductosIDs = $request->input('subproductos', []);
        if (!empty($subproductosIDs)) {
            $area->subproductos()->attach($subproductosIDs); // Usamos attach() para crear
        }
        // --- FIN DE LO NUEVO ---

        // 4. RESPUESTA (Tu código original)
        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Hecho!',
            'text' => 'Se ha creado una nueva Area',
        ]);

        return redirect()->route('admin.areas.edit', $area); // Esto ya está bien
    }

    /**
     * Display the specified resource.
     */
    public function show(Area $area)
    {
        return view('admin.areas.show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Area $area)
    {
        $institutos = Institutos::all();

        $subproductos_todos = Subproducto::orderBy('nombre')->get();
        $subproductos_asignados = $area->subproductos->pluck('id')->toArray();

        return view('admin.areas.edit', compact('area', 'institutos', 'subproductos_todos', 'subproductos_asignados'));
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Area $area)
    {
        // 1. VALIDACIÓN (Añadimos 'subproductos')
        $request->validate([
            'nombre' => ['required', 'unique:areas,nombre,' . $area->id], // Corregí tu validación 'unique' para que ignore el ID actual
            'descripcion' => 'nullable',
            'instituto_id' => 'required|exists:institutos,id',
            'subproductos' => 'nullable|array' // <-- AÑADIDO: Valida que 'subproductos' sea un array si se envía
        ]);

        // 2. ACTUALIZA EL ÁREA (Tu código original)
        $area->update($request->all());

        // 3. ACTUALIZA LOS SUBPRODUCTOS (Nuevo)
        // Obtenemos el array de IDs de subproductos del formulario
        $subproductosIDs = $request->input('subproductos', []);

        // Usamos sync() para actualizar la tabla 'area_subproducto'
        $area->subproductos()->sync($subproductosIDs);
        // --- FIN DE LO NUEVO ---

        // 4. RESPUESTA (Tu código original)
        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Hecho!',
            'text' => 'El Area se ha actualizado correctamente',
        ]);

        return redirect()->route('admin.areas.edit', $area);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Area $area)
    {
        $area->delete();

        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Hecho!',
            'text' => 'El Area se ha eliminado correctamente',
        ]);

        return redirect()->route('admin.areas.index');
    }

    public function editSubproductos(Area $area)
    {
        // 1. Cargamos todos los subproductos (la lista global)
        $subproductos_todos = Subproducto::orderBy('nombre')->get();

        // 2. Cargamos los IDs de los subproductos que ESTA área ya tiene
        //    (Asume que tu modelo Area.php tiene la relación 'subproductos()')
        $subproductos_asignados = $area->subproductos->pluck('id')->toArray();

        return view('admin.areas.editSubproductos', compact('area', 'subproductos_todos', 'subproductos_asignados'));
    }

    /**
     * Actualiza la relación de Subproductos para un Área.
     * (Esta es la función de 'updateSubproductos' que creamos en la ruta)
     */
    public function updateSubproductos(Request $request, Area $area)
    {
        // 1. Obtenemos el array de IDs de subproductos del formulario
        $subproductosIDs = $request->input('subproductos', []);

        // 2. Usamos sync() para actualizar la tabla 'area_subproducto'
        // sync() lo hace todo: añade los nuevos y borra los que se desmarcaron.
        $area->subproductos()->sync($subproductosIDs);

        session()->flash('swal', [
            'icon' => 'success',
            'title' => '¡Hecho!',
            'text' => 'Subproductos actualizados para el área.',
        ]);

        return redirect()->route('admin.areas.index');
    }
}
