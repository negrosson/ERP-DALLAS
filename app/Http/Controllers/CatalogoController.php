<?php

namespace App\Http\Controllers;

use App\Models\CatalogoProducto;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productos = CatalogoProducto::latest()->paginate(10);
        return view('catalogo.index', compact('productos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('catalogo.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\App\Http\Requests\StoreCatalogoRequest $request)
    {
        $validated = $request->validated();
        $validated['activo'] = $request->has('activo'); // Handle checkbox
        
        CatalogoProducto::create($validated);
        return redirect()->route('catalogo.index')->with('success', 'Producto creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CatalogoProducto $catalogo)
    {
        $catalogo->load([
            'lotesStock.bodega',
            'recepcionDetalles.recepcion.proveedor',
            'recepcionDetalles.recepcion.bodega'
        ]);

        return view('catalogo.show', compact('catalogo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CatalogoProducto $catalogo)
    {
        return view('catalogo.edit', compact('catalogo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(\App\Http\Requests\UpdateCatalogoRequest $request, CatalogoProducto $catalogo)
    {
        $validated = $request->validated();
        $validated['activo'] = $request->has('activo'); // Handle checkbox
        
        $catalogo->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Catálogo actualizado correctamente',
                'data' => $catalogo
            ]);
        }

        return redirect()->route('catalogo.index')->with('success', 'Producto actualizado exitosamente en el catÃ¡logo.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CatalogoProducto $catalogo)
    {
        try {
            $catalogo->delete();
            return redirect()->route('catalogo.index')->with('success', 'Producto eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('catalogo.index')->with('error', 'No se puede eliminar el producto porque tiene registros asociados (ej. recepciones o stock).');
        }
    }
}
