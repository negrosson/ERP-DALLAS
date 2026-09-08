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
        if ($producto->tieneAutocalculoVencimiento()) {
            if (!empty($validated['fecha_elaboracion'])) {
                $validated['fecha_vencimiento'] = \App\Services\VencimientoService::calcularVencimiento(
                    $validated['fecha_elaboracion'],
                    $producto->constante_vencimiento_meses
                );
            } else {
                return back()->with('error', 'El producto requiere una fecha de elaboración para calcular su vencimiento.');
            }
        } else {
            // Si el producto no tiene autocalculo, nos aseguramos que venga la fecha de vencimiento, a menos que no tenga reglas FEFO (vencimiento_meses null y sin alerta).
            // Por simplicidad de momento permitiremos que quede null si no la ingresan.
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
