<?php

namespace App\Http\Controllers;

use App\Models\Bodega;
use Illuminate\Http\Request;

class BodegaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bodegas = Bodega::latest()->paginate(10);
        return view('bodegas.index', compact('bodegas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('bodegas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\App\Http\Requests\StoreBodegaRequest $request)
    {
        $validated = $request->validated();
        $validated['es_refrigerada'] = $request->has('es_refrigerada');
        $validated['activa'] = $request->has('activa');
        
        Bodega::create($validated);
        return redirect()->route('bodegas.index')->with('success', 'Bodega creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Bodega $bodega)
    {
        return view('bodegas.show', compact('bodega'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bodega $bodega)
    {
        return view('bodegas.edit', compact('bodega'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(\App\Http\Requests\UpdateBodegaRequest $request, Bodega $bodega)
    {
        $validated = $request->validated();
        $validated['es_refrigerada'] = $request->has('es_refrigerada');
        $validated['activa'] = $request->has('activa');
        
        $bodega->update($validated);
        return redirect()->route('bodegas.index')->with('success', 'Bodega actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bodega $bodega)
    {
        try {
            $bodega->delete();
            return redirect()->route('bodegas.index')->with('success', 'Bodega eliminada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('bodegas.index')->with('error', 'No se puede eliminar la bodega porque tiene registros asociados (ej. inventario o recepciones).');
        }
    }
}
