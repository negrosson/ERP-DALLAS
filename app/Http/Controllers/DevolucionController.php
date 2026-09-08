<?php

namespace App\Http\Controllers;

use App\Models\Devolucion;
use App\Models\Bodega;
use App\Models\CatalogoProducto;
use App\Models\LoteStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DevolucionController extends Controller
{
    public function index()
    {
        $devoluciones = Devolucion::with(['producto', 'bodega', 'user'])->latest()->paginate(10);
        return view('devoluciones.index', compact('devoluciones'));
    }

    public function create()
    {
        $bodegas = Bodega::where('activa', true)->get();
        $productos = CatalogoProducto::where('activo', true)->orderBy('nombre')->get();
        return view('devoluciones.create', compact('bodegas', 'productos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'catalogo_producto_id' => 'required|exists:catalogo_productos,id',
            'bodega_destino_id' => 'required|exists:bodegas,id',
            'cantidad' => 'required|numeric|min:0.01',
            'motivo' => 'required|string|max:255',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:today',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // Registrar devolución
                Devolucion::create([
                    'catalogo_producto_id' => $request->catalogo_producto_id,
                    'bodega_destino_id' => $request->bodega_destino_id,
                    'cantidad' => $request->cantidad,
                    'motivo' => $request->motivo,
                    'fecha_devolucion' => now(),
                    'user_id' => auth()->id(),
                ]);

                // Crear o incrementar lote de stock (Para simplificar, creamos un nuevo registro)
                // Usaremos una recepción genérica o nula (si se permitiera nulo en la BD).
                // Nota: la BD exige recepcion_detalle_id en lotes_stock.
                // Como workaround temporal para devoluciones que no tienen recepción, buscaremos el último lote de este producto y le sumaremos la cantidad, 
                // o podríamos hacer que recepcion_detalle_id sea nullable en una futura migración.
                // Por ahora, sumamos la cantidad al lote más nuevo de este producto en esa bodega.
                
                $lote = LoteStock::where('catalogo_producto_id', $request->catalogo_producto_id)
                                 ->where('bodega_id', $request->bodega_destino_id)
                                 ->orderBy('id', 'desc')
                                 ->first();
                
                if ($lote) {
                    $lote->cantidad_disponible += $request->cantidad;
                    if ($request->filled('fecha_vencimiento')) {
                        $lote->fecha_vencimiento = $request->fecha_vencimiento;
                    }
                    $lote->save();
                } else {
                    // Buscar lote previo de este producto para heredar costo y recepcion_detalle_id
                    $lotePrevio = LoteStock::where('catalogo_producto_id', $request->catalogo_producto_id)->latest()->first();
                    if ($lotePrevio) {
                        LoteStock::create([
                            'recepcion_detalle_id' => $lotePrevio->recepcion_detalle_id,
                            'catalogo_producto_id' => $request->catalogo_producto_id,
                            'bodega_id' => $request->bodega_destino_id,
                            'cantidad_inicial' => $request->cantidad,
                            'cantidad_disponible' => $request->cantidad,
                            'costo_unitario' => $lotePrevio->costo_unitario,
                            'fecha_vencimiento' => $request->fecha_vencimiento ?? $lotePrevio->fecha_vencimiento,
                        ]);
                    } else {
                        throw new \RuntimeException('No se puede procesar la devolución porque este producto no registra ningún lote previo en el catálogo de inventario.');
                    }
                }
            });

            return redirect()->route('devoluciones.index')->with('success', 'Devolución registrada y stock reintegrado exitosamente.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error registrando devolución: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error inesperado al registrar la devolución.')->withInput();
        }
    }
}
