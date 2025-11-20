<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::all();
        return view('admin.categorias.index', compact('categorias'));
    }

    public function create()
    {
        return view('admin.categorias.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            // La validación ahora funciona gracias a la migración
            'nombre' => 'required|string|max:255|unique:categorias,nombre',
        ]);

        Categoria::create($request->all());

        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Hecho!',
            'text' => 'La categoría se ha creado correctamente!'
        ]);

        // CORRECCIÓN DE RUTA: Usar 'admin.categorias.index'
        return redirect()->route('admin.categorias.index');
    }

    public function edit(Categoria $categoria)
    {
        return view('admin.categorias.edit', compact('categoria'));
    }

    public function update(Request $request, Categoria $categoria)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias,nombre,' . $categoria->id,
        ]);

        $categoria->update($request->all());

        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Hecho!',
            'text' => 'La categoría se ha actualizado correctamente!'
        ]);

        // CORRECCIÓN DE RUTA: Usar 'admin.categorias.index'
        return redirect()->route('admin.categorias.index');
    }

    public function destroy(Categoria $categoria)
    {
        // Opcional: Validar si la categoría tiene subproductos antes de eliminar
        if ($categoria->subproductos()->count() > 0) {
            session()->flash('swal', [
                'icon' => 'error',
                'title' => 'Error!',
                'text' => 'No se puede eliminar la categoría porque tiene subproductos asociados.'
            ]);
            // CORRECCIÓN DE RUTA: Usar 'admin.categorias.index'
            return redirect()->route('admin.categorias.index');
        }

        $categoria->delete();

        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Hecho!',
            'text' => 'La categoría se ha eliminado correctamente.'
        ]);

        // CORRECCIÓN DE RUTA: Usar 'admin.categorias.index'
        return redirect()->route('admin.categorias.index');
    }
}
