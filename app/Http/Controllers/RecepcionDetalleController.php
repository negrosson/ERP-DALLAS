<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RecepcionDetalle;

class RecepcionDetalleController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(\App\Http\Requests\StoreRecepcionDetalleRequest $request, \App\Models\Recepcion $recepcion)
    {
        if ($recepcion->estado === \App\Enums\EstadoRecepcion::CONFIRMADO) {
            return back()->with('error', 'No se pueden agregar productos a una recepción confirmada.');
        }

        $validated = $request->validated();
        
        $producto = \App\Models\CatalogoProducto::findOrFail($validated['catalogo_producto_id']);
        
        // Autocalcular vencimiento si no se ingresó y el producto tiene constante
        if (empty($validated['fecha_vencimiento']) && $producto->tieneAutocalculoVencimiento()) {
            $validated['fecha_vencimiento'] = \App\Services\VencimientoService::calcularVencimiento(
                $recepcion->fecha_recepcion,
                $producto->constante_vencimiento_meses
            );
        }

        $recepcion->detalles()->create($validated);

        return back()->with('success', 'Producto agregado a la recepción.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(\App\Models\Recepcion $recepcion, RecepcionDetalle $detalle)
    {
        if ($recepcion->estado === \App\Enums\EstadoRecepcion::CONFIRMADO) {
            return back()->with('error', 'No se pueden eliminar productos de una recepción confirmada.');
        }

        if ($detalle->recepcion_id !== $recepcion->id) {
            abort(404);
        }

        $detalle->delete();

        return back()->with('success', 'Producto eliminado de la recepción.');
    }
}
