<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subproducto;
use App\Models\Categoria; // ¡IMPORTANTE! Añade la importación del modelo Categoria
use Illuminate\Http\Request;

class SubprodcutosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Optimizando para cargar la relación 'categoria'
        $subproductos = Subproducto::with('categoria')->get();

        return view('admin.subproductos.index', compact('subproductos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // 1. Obtener todas las categorías para el formulario
        $categorias = Categoria::all();

        // 2. Pasar las categorías a la vista
        return view('admin.subproductos.create', compact('categorias'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            // 3. Validar que categoria_id es requerido y existe en la tabla
            'categoria_id' => 'required|exists:categorias,id',
        ]);

        Subproducto::create([
            'nombre' => $request->nombre,
            'categoria_id' => $request->categoria_id, // 4. Guardar el ID de la categoría
        ]);

        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Hecho!',
            'text' => 'El subproducto se ha creado correctamente!'
        ]);

        return redirect()->route('admin.subproductos.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Subproducto $subproducto)
    {
        return view('admin.subproductos.show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subproducto $subproducto)
    {
        // Obtener todas las categorías para el formulario de edición
        $categorias = Categoria::all();

        // Pasar la categoría y el subproducto a la vista
        return view('admin.subproductos.edit', compact('subproducto', 'categorias'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subproducto $subproducto)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            // Validar que categoria_id es requerido y existe
            'categoria_id' => 'required|exists:categorias,id',
        ]);

        $subproducto->nombre = $request->nombre;
        $subproducto->categoria_id = $request->categoria_id; // 5. Actualizar el ID de la categoría
        $subproducto->save();

        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Hecho!',
            'text' => 'El subproducto se ha actualizado correctamente!'
        ]);

        return redirect()->route('admin.subproductos.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subproducto $subproducto)
    {
        // 1. Eliminar el subproducto de la base de datos
        $subproducto->delete();

        // 2. Mostrar un mensaje de éxito al usuario
        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Hecho!',
            'text' => 'El subproducto se ha eliminado correctamente.'
        ]);

        // 3. Redirigir al usuario al índice de subproductos
        return redirect()->route('admin.subproductos.index');
    }
}
