<?php

namespace App\Http\Controllers;

use App\Models\Recepcion;
use Illuminate\Http\Request;

class RecepcionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Recepcion::with(['proveedor', 'bodega', 'user'])->latest();
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }
        $recepciones = $query->paginate(10)->withQueryString();
        return view('recepciones.index', compact('recepciones'));
    }

    /**
     * Show the Scanner Kiosk mode.
     */
    public function scanner()
    {
        $proveedores = \App\Models\Proveedor::where('activo', true)->orderBy('nombre')->get();
        $bodegas = \App\Models\Bodega::where('activa', true)->orderBy('nombre')->get();
        return view('recepciones.scanner', compact('proveedores', 'bodegas'));
    }

    /**
     * Show the OCR Invoice upload UI.
     */
    public function ocr()
    {
        $proveedores = \App\Models\Proveedor::where('activo', true)->orderBy('nombre')->get();
        $bodegas = \App\Models\Bodega::where('activa', true)->orderBy('nombre')->get();
        return view('recepciones.ocr', compact('proveedores', 'bodegas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $proveedores = \App\Models\Proveedor::where('activo', true)->orderBy('nombre')->get();
        $bodegas = \App\Models\Bodega::where('activa', true)->orderBy('nombre')->get();
        return view('recepciones.create', compact('proveedores', 'bodegas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\App\Http\Requests\StoreRecepcionRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();
        $validated['fecha_recepcion'] = now();
        $validated['estado'] = \App\Enums\EstadoRecepcion::BORRADOR->value;
        
        $recepcion = Recepcion::create($validated);
        
        return redirect()->route('recepciones.show', $recepcion)->with('success', 'Recepción en borrador creada. Proceda a agregar los productos.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Recepcion $recepcion)
    {
        $recepcion->load(['proveedor', 'bodega', 'user', 'detalles.producto']);
        
        // Cargar productos activos que pertenezcan al catálogo
        $productos = \App\Models\CatalogoProducto::where('activo', true)->orderBy('nombre')->get();
        
        return view('recepciones.show', compact('recepcion', 'productos'));
    }

    /**
     * Confirmar la recepción e ingresar el stock.
     */
    public function confirm(Recepcion $recepcion, \App\Services\InventarioService $inventarioService)
    {
        if ($recepcion->estado === \App\Enums\EstadoRecepcion::CONFIRMADO) {
            return back()->with('error', 'La recepción ya fue confirmada anteriormente.');
        }

        if ($recepcion->detalles->isEmpty()) {
            return back()->with('error', 'No se puede confirmar una recepción sin productos.');
        }

        try {
            $inventarioService->confirmarRecepcion($recepcion);
            return redirect()->route('recepciones.show', $recepcion)->with('success', 'Recepción confirmada y stock ingresado exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al confirmar la recepción: ' . $e->getMessage());
        }
    }

    public function conciliar(Recepcion $recepcion)
    {
        if ($recepcion->estado !== \App\Enums\EstadoRecepcion::PENDIENTE_FECHA->value) {
            return redirect()->route('recepciones.index')->with('error', 'Esta recepciÃ³n no estÃ¡ pendiente de fechas.');
        }

        $recepcion->load(['detalles.producto', 'proveedor']);
        return view('recepciones.conciliar', compact('recepcion'));
    }

    public function guardarConciliacion(Request $request, Recepcion $recepcion)
    {
        if ($recepcion->estado !== \App\Enums\EstadoRecepcion::PENDIENTE_FECHA->value) {
            return redirect()->route('recepciones.index')->with('error', 'Esta recepciÃ³n no estÃ¡ pendiente de fechas.');
        }

        $validated = $request->validate([
            'detalles' => 'required|array',
            'detalles.*.fecha_elaboracion' => 'nullable|date',
            'detalles.*.fecha_vencimiento' => 'required|date',
        ]);

        foreach ($validated['detalles'] as $detalleId => $fechas) {
            $detalle = \App\Models\RecepcionDetalle::find($detalleId);
            if ($detalle && $detalle->recepcion_id == $recepcion->id) {
                $detalle->update([
                    'fecha_elaboracion' => $fechas['fecha_elaboracion'],
                    'fecha_vencimiento' => $fechas['fecha_vencimiento'],
                ]);
                
                // Actualizar el lote correspondiente
                \App\Models\LoteStock::where('recepcion_detalle_id', $detalle->id)
                    ->update([
                        'fecha_elaboracion' => $fechas['fecha_elaboracion'],
                        'fecha_vencimiento' => $fechas['fecha_vencimiento'],
                    ]);
            }
        }

        $recepcion->update(['estado' => \App\Enums\EstadoRecepcion::CONFIRMADO->value]);

        return redirect()->route('dashboard')->with('success', 'Fechas conciliadas. Los lotes ya estÃ¡n disponibles para FEFO.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Recepcion $recepcion)
    {
        if ($recepcion->estado === \App\Enums\EstadoRecepcion::CONFIRMADO) {
            return back()->with('error', 'No se puede eliminar una recepción que ya ha sido confirmada y procesada.');
        }
        
        $recepcion->delete();
        return redirect()->route('recepciones.index')->with('success', 'Recepción eliminada exitosamente.');
    }
}
