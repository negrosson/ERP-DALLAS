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
        $query = \App\Models\LoteStock::with(['producto', 'bodega'])
                    ->disponibleFefo();

        // Optional filtering by bodega
        if ($request->has('bodega_id') && $request->bodega_id != '') {
            $query->where('bodega_id', $request->bodega_id);
        }

        // Optional filtering by proveedor
        if ($request->has('proveedor_id') && $request->proveedor_id != '') {
            $proveedorId = $request->proveedor_id;
            $query->whereHas('producto.mapeoCodigos', function($q) use ($proveedorId) {
                $q->where('proveedor_id', $proveedorId);
            });
        }

        // Optional filtering by product name or SKU
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('producto', function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filtering by alert state
        if ($request->has('alerta') && $request->alerta != '') {
            $alerta = $request->alerta;
            $hoy = now()->startOfDay();
            if ($alerta === 'vencido') {
                $query->where('fecha_vencimiento', '<', $hoy);
            } elseif ($alerta === 'critico') {
                $query->where('fecha_vencimiento', '>=', $hoy)
                      ->where('fecha_vencimiento', '<', now()->addDays(7)->endOfDay());
            } elseif ($alerta === 'alto') {
                $query->where('fecha_vencimiento', '>', now()->addDays(7)->endOfDay())
                      ->where('fecha_vencimiento', '<=', now()->addDays(14)->endOfDay());
            } elseif ($alerta === 'medio') {
                $query->where('fecha_vencimiento', '>', now()->addDays(14)->endOfDay())
                      ->where('fecha_vencimiento', '<=', now()->addDays(21)->endOfDay());
            } elseif ($alerta === 'bajo') {
                $query->where('fecha_vencimiento', '>', now()->addDays(21)->endOfDay())
                      ->where('fecha_vencimiento', '<=', now()->addDays(30)->endOfDay());
            }
        }

        $lotes = $query->paginate(15)->withQueryString();
        $bodegas = \App\Models\Bodega::where('activa', true)->orderBy('nombre')->get();
        $proveedores = \App\Models\Proveedor::where('activo', true)->orderBy('nombre')->get();

        if ($request->ajax()) {
            return view('lotes.partials.table', compact('lotes'))->render();
        }

        return view('lotes.index', compact('lotes', 'bodegas', 'proveedores'));
    }
}
