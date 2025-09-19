<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Institutos;
use App\Models\Zona;
use Illuminate\Http\Request;

class ZonaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $instituto = auth()->user()->instituto; // Instituto del usuario logueado

        // Solo las zonas asociadas a ese instituto
        $zonas = Zona::where('instituto_id', $instituto->id)->get();

        return view('admin.zonas.index', compact('zonas'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $instituto = auth()->user()->instituto;
        $areas = Area::where('instituto_id', $instituto->id)->get();

        return view('admin.zonas.create', compact('areas', 'instituto'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'unique:zonas,nombre'],
            'descripcion' => 'nullable',
            'areas' => 'nullable|array',
        ]);

        // Se crea la zona  
        $zona = Zona::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'instituto_id' => auth()->user()->instituto_id,
        ]);

        // Accedemos a la zona y su relacion con area para sincronizar los datos
        if ($request->filled('areas')) {
            $zona->areas()->attach($request->areas);
        }

        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Hecho!',
            'text' => 'Se ha creado una nueva zona',
        ]);

        return redirect()->route('admin.zonas.edit', $zona);
    }

    /**
     * Display the specified resource.
     */
    public function show(Zona $zona)
    {
        return view('admin.zonas.show', compact('zona'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Zona $zona)
    {
        $institutos = Institutos::all();

        $areas = $zona->areas();
        $areas = Area::all();

        return view('admin.zonas.edit', compact('zona', 'institutos', 'areas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Zona $zona)
    {
        // Ponemos una validacion requerida y unica en la tabla roles, en el campo name excluyendo el id que estamos editando ($role->id) 
        $request->validate([
            'nombre' => ['required', 'unique:zonas,nombre,' . $zona->id],
            'descripcion' => 'nullable',
            'instituto_id' => 'required|exists:institutos,id',
        ]);


        // Se actualiza la zona de lo que llegue en el formulario
        $zona->update($request->all());

        /* Sincronizamos la zona con las areas para que el metodo se encargue de agregar los registros
        ** en la tabla intermedia. Para llamar a la relacion se usa $zona->zonas_areas()
        */
        $zona->areas()->sync($request->areas);

        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Hecho!',
            'text' => 'La zona se ha actualizado correctamente',
        ]);

        return redirect()->route('admin.zonas.edit', $zona);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Zona $zona)
    {
        $zona->delete();

        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Hecho!',
            'text' => 'La zona se ha eliminado correctamente',
        ]);

        return redirect()->route('admin.zonas.index');
    }
}
