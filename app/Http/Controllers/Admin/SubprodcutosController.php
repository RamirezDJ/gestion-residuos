<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subproducto;
use Illuminate\Http\Request;

class SubprodcutosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subproductos = Subproducto::all();

        return view('admin.subproductos.index', compact('subproductos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.subproductos.create');
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        Subproducto::create([
            'nombre' => $request->nombre,
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
        return view('admin.subproductos.edit', compact('subproducto'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subproducto $subproducto)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $subproducto->nombre = $request->nombre;
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
        //
    }
}
