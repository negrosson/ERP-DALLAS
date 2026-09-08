<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function global(Request $request)
    {
        $query = $request->input('q');

        if (!$query) {
            return response()->json(['productos' => [], 'lotes' => []]);
        }

        $productos = \App\Models\CatalogoProducto::where('nombre', 'like', "%{$query}%")
            ->orWhere('sku', 'like', "%{$query}%")
            ->limit(5)
            ->get(['id', 'nombre', 'sku']);

        $lotes = \App\Models\LoteStock::where(function($query_wrapper) use ($query) {
            $query_wrapper->whereHas('producto', function($q) use ($query) {
                $q->where('nombre', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%");
            })
            ->orWhere('id', 'like', "%{$query}%");
        })
            ->with(['producto:id,nombre', 'bodega:id,nombre'])
            ->where('cantidad_disponible', '>', 0)
            ->limit(5)
            ->get()
            ->map(function($lote) {
                return [
                    'id' => $lote->id,
                    'producto_nombre' => $lote->producto->nombre ?? 'N/A',
                    'bodega_nombre' => $lote->bodega->nombre ?? 'N/A',
                    'cantidad' => $lote->cantidad_disponible,
                    'vencimiento' => $lote->fecha_vencimiento ? \Carbon\Carbon::parse($lote->fecha_vencimiento)->format('d/m/Y') : 'Sin fecha',
                ];
            });

        return response()->json([
            'productos' => $productos,
            'lotes' => $lotes,
        ]);
    }
}
