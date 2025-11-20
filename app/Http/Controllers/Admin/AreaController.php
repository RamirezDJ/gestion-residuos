<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Institutos;
use App\Models\Subproducto;
use App\Models\Categoria;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function index()
    {
        $instituto = auth()->user()->instituto;
        $areas = Area::where('instituto_id', $instituto->id)->get();
        return view('admin.areas.index', compact('areas'));
    }

    public function create()
    {
        $institutos = Institutos::all();
        // Necesitamos las categorías para crear los checkboxes agrupados
        $categorias = Categoria::with('subproductos')->get();

        return view('admin.areas.create', compact('institutos', 'categorias'));
    }

    public function store(Request $request)
    {
        // 1. VALIDACIÓN
        $request->validate([
            'nombre' => ['required', 'unique:areas,nombre'],
            'descripcion' => 'nullable',
            'instituto_id' => 'required|exists:institutos,id',
            'categorias' => 'nullable|array' // Cambiamos validación a categorías
        ]);

        // 2. CREAR EL ÁREA
        $area = Area::create($request->only(['nombre', 'descripcion', 'instituto_id']));

        // 3. LOGICA DE CATEGORÍAS -> SUBPRODUCTOS
        // Si el usuario seleccionó categorías, buscamos TODOS los subproductos de esas categorías
        if ($request->has('categorias')) {
            $subproductosIds = Subproducto::whereIn('categoria_id', $request->categorias)
                                ->pluck('id')
                                ->toArray();
            
            // Guardamos los subproductos encontrados
            $area->subproductos()->attach($subproductosIds);
        }

        // 4. RESPUESTA
        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Hecho!',
            'text' => 'Se ha creado una nueva Area y asignado sus subproductos por categoría.',
        ]);

        return redirect()->route('admin.areas.edit', $area);
    }

    public function show(Area $area)
    {
        return view('admin.areas.show');
    }

    public function edit(Area $area)
    {
        $institutos = Institutos::all();
        $categorias = Categoria::with('subproductos')->get();
        
        // Cargamos subproductos para poder verificar cuáles están activos en la vista
        $area->load('subproductos');

        return view('admin.areas.edit', compact('area', 'institutos', 'categorias'));
    }

    public function update(Request $request, Area $area)
    {
        // 1. VALIDACIÓN
        $request->validate([
            'nombre' => ['required', 'unique:areas,nombre,' . $area->id],
            'descripcion' => 'nullable',
            'instituto_id' => 'required|exists:institutos,id',
            'categorias' => 'nullable|array'
        ]);

        // 2. ACTUALIZA EL ÁREA
        $area->update($request->only(['nombre', 'descripcion', 'instituto_id']));

        // 3. LOGICA DE CATEGORÍAS -> SUBPRODUCTOS
        $subproductosIds = [];
        
        if ($request->has('categorias')) {
            // Traducimos: "Si seleccionó Categoría X, dame todos los Subproductos de X"
            $subproductosIds = Subproducto::whereIn('categoria_id', $request->categorias)
                                ->pluck('id')
                                ->toArray();
        }

        // Sincronizamos (Esto borra los anteriores y pone los nuevos basados en las categorías actuales)
        $area->subproductos()->sync($subproductosIds);

        // 4. RESPUESTA
        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Hecho!',
            'text' => 'El Area se ha actualizado correctamente',
        ]);

        return redirect()->route('admin.areas.edit', $area);
    }

    public function destroy(Area $area)
    {
        // Limpiamos tabla pivote antes de borrar para mantener integridad
        $area->subproductos()->detach();
        $area->delete();

        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Hecho!',
            'text' => 'El Area se ha eliminado correctamente',
        ]);

        return redirect()->route('admin.areas.index');
    }
}