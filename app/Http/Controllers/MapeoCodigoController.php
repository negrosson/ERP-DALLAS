<?php

namespace App\Http\Controllers;

use App\Models\MapeoCodigo;
use Illuminate\Http\Request;

class MapeoCodigoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mapeos = MapeoCodigo::with(['proveedor', 'producto'])->latest()->paginate(10);
        return view('mapeos.index', compact('mapeos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $proveedores = \App\Models\Proveedor::where('activo', true)->orderBy('nombre')->get();
        $productos = \App\Models\CatalogoProducto::where('activo', true)->orderBy('nombre')->get();
        return view('mapeos.create', compact('proveedores', 'productos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\App\Http\Requests\StoreMapeoCodigoRequest $request)
    {
        $validated = $request->validated();
        
        MapeoCodigo::create($validated);
        return redirect()->route('mapeos.index')->with('success', 'Mapeo creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MapeoCodigo $mapeo)
    {
        return view('mapeos.show', compact('mapeo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MapeoCodigo $mapeo)
    {
        $proveedores = \App\Models\Proveedor::where('activo', true)->orderBy('nombre')->get();
        $productos = \App\Models\CatalogoProducto::where('activo', true)->orderBy('nombre')->get();
        return view('mapeos.edit', compact('mapeo', 'proveedores', 'productos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(\App\Http\Requests\UpdateMapeoCodigoRequest $request, MapeoCodigo $mapeo)
    {
        $validated = $request->validated();
        
        $mapeo->update($validated);
        return redirect()->route('mapeos.index')->with('success', 'Mapeo actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MapeoCodigo $mapeo)
    {
        try {
            $mapeo->delete();
            return redirect()->route('mapeos.index')->with('success', 'Mapeo eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('mapeos.index')->with('error', 'No se puede eliminar este mapeo.');
        }
    }
}
