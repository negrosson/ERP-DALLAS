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
            if ($request->estado === 'papelera') {
                $query->onlyTrashed();
            } else {
                $query->where('estado', $request->estado);
            }
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('numero_factura', 'like', "%{$search}%")
                  ->orWhere('documento_referencia', 'like', "%{$search}%")
                  ->orWhereHas('proveedor', function($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%");
                  });
            });
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
     * Procesa la imagen de la factura mediante OCR y crea una Recepción PENDIENTE_FECHA
     */
    public function processOcr(Request $request, \App\Services\OcrService $ocrService)
    {
        $request->validate([
            'factura' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'proveedor_id' => 'required|exists:proveedores,id',
            'bodega_id' => 'required|exists:bodegas,id',
        ]);

        try {
            // 1. Extraer datos con el servicio OCR
            $extractedItems = $ocrService->processInvoiceImage($request->file('factura'), $request->proveedor_id);

            // 2. Crear la Recepción en estado PENDIENTE_FECHA
            $recepcion = Recepcion::create([
                'proveedor_id' => $request->proveedor_id,
                'bodega_id' => $request->bodega_id,
                'user_id' => auth()->id(),
                'fecha_recepcion' => now(),
                'numero_factura' => 'OCR-' . strtoupper(uniqid()),
                'estado' => \App\Enums\EstadoRecepcion::PENDIENTE_FECHA->value,
                'documento_referencia' => 'FACT-' . strtoupper(uniqid()),
                'observaciones' => 'Recepción creada vía OCR Automático',
            ]);

            // 3. Crear los detalles
            foreach ($extractedItems as $item) {
                $productoId = null;
                $mapeo = \App\Models\MapeoCodigo::where('proveedor_id', $request->proveedor_id)
                                ->where('codigo_proveedor', $item['codigo'])
                                ->first();
                
                if ($mapeo) {
                    $productoId = $mapeo->catalogo_producto_id;
                } else {
                    $producto = \App\Models\CatalogoProducto::where('sku', $item['codigo'])->first();
                    if ($producto) {
                        $productoId = $producto->id;
                    } else {
                        // Crear producto temporal inactivo
                        $nuevoProd = \App\Models\CatalogoProducto::create([
                            'sku' => $item['codigo'],
                            'nombre' => $item['descripcion'] . ' (Pendiente Revisión)',
                            'unidad_medida' => 'UN',
                            'precio_compra_ref' => 0,
                            'precio_venta' => 0,
                            'activo' => false
                        ]);
                        $productoId = $nuevoProd->id;
                    }
                }

                \App\Models\RecepcionDetalle::create([
                    'recepcion_id' => $recepcion->id,
                    'catalogo_producto_id' => $productoId,
                    'codigo_proveedor_usado' => $item['codigo'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                ]);
            }

            return response()->json([
                'success' => true,
                'redirect_url' => route('recepciones.conciliar', $recepcion->id)
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error procesando OCR: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Ocurrió un error inesperado al procesar la factura.'], 500);
        }
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
        
        // Manejo de Proveedor Nuevo
        if (!is_numeric($validated['proveedor_id'])) {
            $existing = \App\Models\Proveedor::where('nombre', $validated['proveedor_id'])->first();
            if ($existing) {
                $validated['proveedor_id'] = $existing->id;
            } else {
                $nuevoProveedor = \App\Models\Proveedor::create([
                    'nombre' => $validated['proveedor_id'],
                    'rut' => 'S/N',
                    'codigo' => 'PROV-' . uniqid(),
                    'activo' => true
                ]);
                $validated['proveedor_id'] = $nuevoProveedor->id;
            }
        }

        $validated['user_id'] = auth()->id();
        $validated['fecha_recepcion'] = now();
        $validated['estado'] = \App\Enums\EstadoRecepcion::BORRADOR->value;
        $validated['numero_factura'] = $validated['numero_factura'] ?? 'S/N';
        
        $recepcion = Recepcion::create($validated);
        
        return redirect()->route('recepciones.show', $recepcion)->with('success', 'Recepción en borrador creada. Proceda a agregar los productos.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Recepcion $recepcion)
    {
        $recepcion->load(['proveedor', 'bodega', 'user', 'detalles.catalogoProducto']);
        
        $bodegaId = $recepcion->bodega_id;
        $productos = \App\Models\CatalogoProducto::where('activo', true)
            ->with(['lotesStock' => function ($q) use ($bodegaId) {
                $q->where('bodega_id', $bodegaId);
            }])
            ->orderBy('nombre')
            ->get();
            
        $productosArray = $productos->map(function($prod) {
            return [
                "id" => $prod->id,
                "text" => "[" . $prod->sku . "] " . $prod->nombre . " " . ($prod->formato ? "- " . $prod->formato : "") . " " . ($prod->capacidad ? $prod->capacidad : ""),
                "fefo" => $prod->constante_vencimiento_meses ? "auto" : "manual",
                "constante_vencimiento_meses" => $prod->constante_vencimiento_meses,
                "formato" => $prod->formato ?? "",
                "sku" => $prod->sku,
                "nombre" => $prod->nombre,
                "lotes" => $prod->lotesStock
            ];
        })->toArray();
        
        $productosJson = json_encode($productosArray, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG);
        
        return view('recepciones.show', compact('recepcion', 'productos', 'productosJson'));
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
            \Illuminate\Support\Facades\Log::error('Error al confirmar recepción: ' . $e->getMessage());
            return back()->with('error', 'Error inesperado al confirmar la recepción.');
        }
    }

    public function conciliar(Recepcion $recepcion)
    {
        if ($recepcion->estado !== \App\Enums\EstadoRecepcion::PENDIENTE_FECHA) {
            return redirect()->route('recepciones.index')->with('error', 'Esta recepción no está pendiente de fechas.');
        }

        $recepcion->load(['detalles.catalogoProducto', 'proveedor']);
        return view('recepciones.conciliar', compact('recepcion'));
    }

    public function guardarConciliacion(Request $request, Recepcion $recepcion)
    {
        if ($recepcion->estado !== \App\Enums\EstadoRecepcion::PENDIENTE_FECHA) {
            return redirect()->route('recepciones.index')->with('error', 'Esta recepción no está pendiente de fechas.');
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

        return redirect()->route('dashboard')->with('success', 'Fechas conciliadas. Los lotes ya están disponibles para FEFO.');
    }

    public function edit(Recepcion $recepcion)
    {
        if ($recepcion->estado === \App\Enums\EstadoRecepcion::CONFIRMADO->value) {
            return back()->with('error', 'No se puede editar una recepción confirmada.');
        }

        $proveedores = \App\Models\Proveedor::where('activo', true)->orderBy('nombre')->get();
        $bodegas = \App\Models\Bodega::where('activa', true)->orderBy('nombre')->get();
        return view('recepciones.edit', compact('recepcion', 'proveedores', 'bodegas'));
    }

    public function update(Request $request, Recepcion $recepcion)
    {
        if ($recepcion->estado === \App\Enums\EstadoRecepcion::CONFIRMADO->value) {
            return back()->with('error', 'No se puede editar una recepción confirmada.');
        }

        $validated = $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'bodega_id' => 'required|exists:bodegas,id',
            'numero_factura' => 'nullable|string|max:50',
            'documento_referencia' => 'nullable|string|max:50',
            'observaciones' => 'nullable|string',
        ]);

        $recepcion->update($validated);

        return redirect()->route('recepciones.show', $recepcion)->with('success', 'Cabecera de la Recepción actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Recepcion $recepcion)
    {
        if ($recepcion->estado === \App\Enums\EstadoRecepcion::CONFIRMADO->value) {
            return redirect()->route('recepciones.index')->with('error', 'No se puede eliminar una recepción confirmada (los lotes ya se generaron).');
        }
        
        $recepcion->delete();
        return redirect()->route('recepciones.index')->with('success', 'Recepción enviada a la papelera.');
    }

    public function restore($id)
    {
        $recepcion = Recepcion::onlyTrashed()->findOrFail($id);
        $recepcion->restore();
        return redirect()->route('recepciones.index')->with('success', 'Recepción restaurada exitosamente.');
    }

    public function forceDelete($id)
    {
        $recepcion = Recepcion::onlyTrashed()->findOrFail($id);
        
        \Illuminate\Support\Facades\DB::transaction(function () use ($recepcion) {
            $detallesIds = \App\Models\RecepcionDetalle::where('recepcion_id', $recepcion->id)->pluck('id');
            \App\Models\LoteStock::whereIn('recepcion_detalle_id', $detallesIds)->delete();
            \App\Models\RecepcionDetalle::whereIn('id', $detallesIds)->delete();
            $recepcion->forceDelete();
        });
        
        return redirect()->route('recepciones.index', ['estado' => 'papelera'])->with('success', 'Recepción eliminada permanentemente.');
    }

    /**
     * Show the manual entry page (Single Page Form).
     */
    public function manual()
    {
        $proveedores = \App\Models\Proveedor::where('activo', true)->orderBy('nombre')->get();
        $bodegas = \App\Models\Bodega::where('activa', true)->orderBy('nombre')->get();
        $productos = \App\Models\CatalogoProducto::where('activo', true)
            ->select('id', 'sku', 'nombre', 'formato', 'capacidad', 'unidad_medida', 'constante_vencimiento_meses')
            ->orderBy('nombre')->get();

        return view('recepciones.manual', compact('proveedores', 'bodegas', 'productos'));
    }

    /**
     * Store and immediately confirm a manual reception (Single Page Form).
     */
    public function storeManual(Request $request, \App\Services\InventarioService $inventarioService)
    {
        $validated = $request->validate([
            'proveedor_id' => 'required',
            'bodega_id' => 'required|exists:bodegas,id',
            'documento_referencia' => 'nullable|string|max:50',
            'observaciones' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.catalogo_producto_id' => 'required|exists:catalogo_productos,id',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.precio_unitario' => 'nullable|numeric|min:0',
            'items.*.fecha_elaboracion' => 'nullable|date',
            'items.*.fecha_vencimiento' => 'required|date',
        ]);

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $inventarioService) {
                // Manejo de Proveedor Nuevo
                if (!is_numeric($validated['proveedor_id'])) {
                    $existing = \App\Models\Proveedor::where('nombre', $validated['proveedor_id'])->first();
                    if ($existing) {
                        $validated['proveedor_id'] = $existing->id;
                    } else {
                        $nuevoProveedor = \App\Models\Proveedor::create([
                            'nombre' => $validated['proveedor_id'],
                            'rut' => 'S/N',
                            'codigo' => 'PROV-' . uniqid(),
                            'activo' => true
                        ]);
                        $validated['proveedor_id'] = $nuevoProveedor->id;
                    }
                }

                // 1. Create Recepción
                $recepcion = Recepcion::create([
                    'proveedor_id' => $validated['proveedor_id'],
                    'bodega_id' => $validated['bodega_id'],
                    'user_id' => auth()->id(),
                    'numero_factura' => $validated['documento_referencia'] ?? 'S/N',
                    'estado' => \App\Enums\EstadoRecepcion::BORRADOR->value,
                    'fecha_recepcion' => now(),
                    'observaciones' => $validated['observaciones'] ?? null,
                ]);

                // 2. Create Detalles
                foreach ($validated['items'] as $item) {
                    \App\Models\RecepcionDetalle::create([
                        'recepcion_id' => $recepcion->id,
                        'catalogo_producto_id' => $item['catalogo_producto_id'],
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $item['precio_unitario'] ?? 0,
                        'fecha_elaboracion' => $item['fecha_elaboracion'] ?? null,
                        'fecha_vencimiento' => $item['fecha_vencimiento'],
                    ]);
                }

                // 3. Confirm (Creates LotesStock)
                $inventarioService->confirmarRecepcion($recepcion);
            });

            return redirect()->route('dashboard')->with('success', 'Mercadería ingresada y stock actualizado correctamente.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error en Ingreso Manual: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al procesar el ingreso.')->withInput();
        }
    }
}
