<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoteStockController extends Controller
{
    /**
     * Display a listing of the available stock.
     */
    public function index(Request $request)
    {
        $bodegas = \App\Models\Bodega::where('activa', true)->orderBy('nombre')->get();
        $proveedores = \App\Models\Proveedor::where('activo', true)->orderBy('nombre')->get();

        // Subquery for Lotes filters
        $lotesFilter = function ($q) use ($request) {
            $q->where('cantidad_disponible', '>=', 0);

            if ($request->filled('bodega_id')) {
                $q->where('bodega_id', $request->bodega_id);
            }

            if ($request->filled('proveedor_id')) {
                $q->whereHas('recepcionDetalle.recepcion', function($sub) use ($request) {
                    $sub->where('proveedor_id', $request->proveedor_id);
                });
            }

            if ($request->filled('alerta')) {
                // Si buscamos por alertas de vencimiento, sólo consideramos stock real (>0)
                $q->where('cantidad_disponible', '>', 0);
                
                $alerta = $request->alerta;
                $hoy = now()->startOfDay();
                if ($alerta === 'vencido') {
                    $q->where('fecha_vencimiento', '<', $hoy);
                } elseif ($alerta === 'critico') {
                    $q->where('fecha_vencimiento', '>=', $hoy)
                      ->where('fecha_vencimiento', '<', now()->addDays(7)->endOfDay());
                } elseif ($alerta === 'alto') {
                    $q->where('fecha_vencimiento', '>', now()->addDays(7)->endOfDay())
                      ->where('fecha_vencimiento', '<=', now()->addDays(14)->endOfDay());
                } elseif ($alerta === 'medio') {
                    $q->where('fecha_vencimiento', '>', now()->addDays(14)->endOfDay())
                      ->where('fecha_vencimiento', '<=', now()->addDays(21)->endOfDay());
                } elseif ($alerta === 'bajo') {
                    $q->where('fecha_vencimiento', '>', now()->addDays(21)->endOfDay())
                      ->where('fecha_vencimiento', '<=', now()->addDays(30)->endOfDay());
                }
            }
        };

        $query = \App\Models\CatalogoProducto::query();

        // 1. Filtrar Productos que tengan lotes cumpliendo las condiciones SÓLO si se busca por alertas
        // (Para bodegas o proveedores, queremos ver todos los productos aunque tengan stock 0 en esa bodega/proveedor)
        $hasLoteFilters = $request->filled('alerta');
        
        if ($hasLoteFilters) {
            $query->whereHas('lotesStock', $lotesFilter);
        }

        // 2. Filtrar por Nombre o SKU del Producto
        if ($request->filled('search')) {
            $searchStr = str_replace([' ', '-'], '', trim($request->search));
            $search = "%{$searchStr}%";
            $query->where(function($q) use ($search) {
                $q->whereRaw("REPLACE(REPLACE(nombre, ' ', ''), '-', '') LIKE ?", [$search])
                  ->orWhereRaw("REPLACE(REPLACE(sku, ' ', ''), '-', '') LIKE ?", [$search]);
            });
        }

        // 3. Eager load the filtered lots and sort them
        $productos = $query->with(['lotesStock' => function($q) use ($lotesFilter) {
            $lotesFilter($q);
            $q->orderBy('fecha_vencimiento', 'asc')->with('bodega');
        }])->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return view('lotes.partials.table', compact('productos'))->render();
        }

        return view('lotes.index', compact('productos', 'bodegas', 'proveedores'));
    }

    /**
     * Update the elaboration and expiration dates of a lot.
     */
    public function updateFechas(Request $request, \App\Models\LoteStock $lote)
    {
        $validated = $request->validate([
            'fecha_elaboracion' => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_elaboracion',
        ]);

        $lote->update($validated);

        // Also update the related RecepcionDetalle if it exists to maintain consistency
        if ($lote->recepcionDetalle) {
            $lote->recepcionDetalle->update([
                'fecha_elaboracion' => $validated['fecha_elaboracion'],
                'fecha_vencimiento' => $validated['fecha_vencimiento'],
            ]);
        }

        return back()->with('status', 'Fechas del lote actualizadas correctamente.');
    }

    /**
     * Update dates and adjust stock simultaneously.
     */
    public function updateFull(Request $request, \App\Models\LoteStock $lote)
    {
        $validated = $request->validate([
            'fecha_elaboracion' => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_elaboracion',
            'cantidad_nueva' => 'required|numeric|min:0',
            'motivo' => 'required|string|max:255',
        ]);

        // 1. Update dates
        $lote->update([
            'fecha_elaboracion' => $validated['fecha_elaboracion'],
            'fecha_vencimiento' => $validated['fecha_vencimiento'],
        ]);

        if ($lote->recepcionDetalle) {
            $lote->recepcionDetalle->update([
                'fecha_elaboracion' => $validated['fecha_elaboracion'],
                'fecha_vencimiento' => $validated['fecha_vencimiento'],
            ]);
        }

        // 2. Adjust stock if it changed
        $cantidadAnterior = $lote->cantidad_disponible;
        $cantidadNueva = $validated['cantidad_nueva'];
        $diferencia = $cantidadNueva - $cantidadAnterior;

        if ($diferencia != 0) {
            $lote->update(['cantidad_disponible' => $cantidadNueva]);

            \App\Models\HistorialAjuste::create([
                'lote_stock_id' => $lote->id,
                'user_id' => auth()->id(),
                'cantidad_anterior' => $cantidadAnterior,
                'cantidad_nueva' => $cantidadNueva,
                'diferencia' => $diferencia,
                'motivo' => $validated['motivo'],
                'es_reversion' => false,
            ]);
        }

        return back()->with('status', 'Lote actualizado correctamente.');
    }

    /**
     * Transfer a lot to a different bodega.
     */
    public function transferirBodega(Request $request, \App\Models\LoteStock $lote)
    {
        $validated = $request->validate([
            'bodega_destino_id' => 'required|exists:bodegas,id',
            'motivo' => 'required|string|max:255',
        ]);

        if ($lote->bodega_id == $validated['bodega_destino_id']) {
            return back()->with('error', 'El lote ya se encuentra en la bodega seleccionada.');
        }

        $bodegaOrigen = $lote->bodega->nombre ?? 'Desconocida';
        $bodegaDestino = \App\Models\Bodega::find($validated['bodega_destino_id'])->nombre;

        $lote->update(['bodega_id' => $validated['bodega_destino_id']]);

        // Registrar en historial como un movimiento de bodega
        \App\Models\HistorialAjuste::create([
            'lote_stock_id' => $lote->id,
            'user_id' => auth()->id(),
            'cantidad_anterior' => $lote->cantidad_disponible,
            'cantidad_nueva' => $lote->cantidad_disponible,
            'diferencia' => 0,
            'motivo' => "Transferencia: {$bodegaOrigen} -> {$bodegaDestino}. Motivo: " . $validated['motivo'],
            'es_reversion' => false,
        ]);

        return back()->with('status', 'Lote transferido exitosamente a ' . $bodegaDestino);
    }
}
