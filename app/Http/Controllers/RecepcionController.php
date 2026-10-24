<?php

namespace App\Http\Controllers;

use App\Models\Recepcion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $proveedorSeleccionado = \App\Models\Proveedor::findOrFail($request->proveedor_id);
        $bodegaSeleccionada = \App\Models\Bodega::findOrFail($request->bodega_id);

        try {
            // 1. Extraer datos con el servicio OCR / IA (Google Gemini)
            $extractedData = $ocrService->processInvoiceImage($request->file('factura'), $request->proveedor_id);

            // Separar metadata de la factura e ítems
            $numeroFactura = null;
            $fechaEmision = now();
            $items = [];

            if (isset($extractedData['items']) && is_array($extractedData['items'])) {
                // Formato retornado por GeminiOcrProvider
                $numeroFactura = !empty($extractedData['numero_factura']) ? trim($extractedData['numero_factura']) : null;
                $fechaEmision = !empty($extractedData['fecha_emision']) ? $extractedData['fecha_emision'] : now();
                $items = $extractedData['items'];
            } elseif (is_array($extractedData)) {
                // Formato plano (fallback o mock)
                $items = $extractedData;
            }

            // =========================================================================
            // REGLA 0: VALIDACIÓN DE COHERENCIA ENTRE FACTURA, PROVEEDOR Y BODEGA
            // Evita ingresar facturas de CCU seleccionando Coca-Cola o en bodegas cruzadas
            // =========================================================================
            $marcaFactura = strtoupper(trim($extractedData['marca_detectada'] ?? ''));
            $emisorFactura = trim($extractedData['emisor_razon_social'] ?? '');
            $rutFactura = trim($extractedData['rut_emisor'] ?? '');

            // Fallback: Si marca_detectada vino vacía o como OTRO, inferir por razón social
            if (empty($marcaFactura) || $marcaFactura === 'OTRO') {
                $emisorUpper = strtoupper($emisorFactura);
                if (str_contains($emisorUpper, 'CCU') || str_contains($emisorUpper, 'CERVECER') || str_contains($emisorUpper, 'UNIDAS')) {
                    $marcaFactura = 'CCU';
                } elseif (str_contains($emisorUpper, 'COCA') || str_contains($emisorUpper, 'EMBONOR') || str_contains($emisorUpper, 'ANDINA')) {
                    $marcaFactura = 'COCA_COLA';
                } elseif (str_contains($emisorUpper, 'DIMAK')) {
                    $marcaFactura = 'DIMAK';
                }
            }

            $grupoProveedor = $this->obtenerGrupoMarcaProveedor($proveedorSeleccionado);
            $grupoBodega = $this->obtenerGrupoMarcaBodega($bodegaSeleccionada);

            // 1. Validar discrepancia de Proveedor (ej: Factura CCU con Proveedor Coca-Cola)
            if ($marcaFactura !== '' && $marcaFactura !== 'OTRO' && $grupoProveedor !== 'OTRO') {
                if ($marcaFactura !== $grupoProveedor) {
                    $nombreMarcaFactura = $marcaFactura === 'CCU' ? 'Compañía Cervecerías Unidas (CCU)' : ($marcaFactura === 'COCA_COLA' ? 'Coca-Cola (Embonor / Andina)' : $marcaFactura);
                    $nombreMarcaSeleccionada = $grupoProveedor === 'CCU' ? 'CCU' : ($grupoProveedor === 'COCA_COLA' ? 'Coca-Cola' : $grupoProveedor);
                    
                    $detalleEmisor = $emisorFactura ? " de \"{$emisorFactura}\"" : '';
                    if ($rutFactura) {
                        $detalleEmisor .= " [RUT: {$rutFactura}]";
                    }

                    return response()->json([
                        'success' => false,
                        'tipo_error' => 'discrepancia_proveedor',
                        'message' => "⚠️ Discrepancia detectada: Has seleccionado el proveedor \"{$proveedorSeleccionado->nombre}\" ({$nombreMarcaSeleccionada}), pero el documento físico escaneado corresponde a una factura{$detalleEmisor} de {$nombreMarcaFactura}. Por favor, corrige la selección del proveedor antes de procesar."
                    ], 422);
                }
            }

            // 2. Validar discrepancia de Bodega (ej: Factura CCU con Bodega Coca-Cola)
            if ($marcaFactura !== '' && $marcaFactura !== 'OTRO' && $grupoBodega !== null) {
                if ($marcaFactura !== $grupoBodega) {
                    $nombreMarcaFactura = $marcaFactura === 'CCU' ? 'CCU' : 'Coca-Cola';
                    $nombreBodegaRecomendada = $marcaFactura === 'CCU' ? 'Bodega CCU' : 'Bodega Coca-Cola';

                    return response()->json([
                        'success' => false,
                        'tipo_error' => 'discrepancia_bodega',
                        'message' => "⚠️ Discrepancia de Bodega detectada: La factura escaneada corresponde a mercadería de {$nombreMarcaFactura}, pero has seleccionado la bodega destino \"{$bodegaSeleccionada->nombre}\" ([{$bodegaSeleccionada->codigo}]). Debes almacenar esta mercadería en {$nombreBodegaRecomendada} o en una bodega general."
                    ], 422);
                }
            }

            // =========================================================================
            // REGLA 1: VALIDACIÓN ANTI-DUPLICADOS DE FACTURA
            // Evitar escanear o ingresar una factura que ya fue procesada anteriormente
            // =========================================================================
            if (!empty($numeroFactura) && strtoupper($numeroFactura) !== 'S/N') {
                $facturaExistente = Recepcion::where('proveedor_id', $request->proveedor_id)
                    ->whereRaw('LOWER(numero_factura) = ?', [strtolower($numeroFactura)])
                    ->withTrashed() // Revisa incluso si está en papelera para máxima seguridad
                    ->first();

                if ($facturaExistente) {
                    $estado = $facturaExistente->trashed() ? 'en Papelera' : $facturaExistente->estado->value;
                    $fecha = $facturaExistente->fecha_recepcion ? \Carbon\Carbon::parse($facturaExistente->fecha_recepcion)->format('d/m/Y') : 'previamente';
                    return response()->json([
                        'success' => false,
                        'message' => "La Factura N° {$numeroFactura} ya existe en el sistema (Ingresada el {$fecha}, Recepción #{$facturaExistente->id}, Estado: {$estado}). No se permite volver a ingresar una factura duplicada."
                    ], 422);
                }
            }

            if (empty($items)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron líneas de productos válidas en la factura analizada.'
                ], 422);
            }

            // =========================================================================
            // REGLA 2: FILTRO DE SERVICIOS (Flete de Mercaderías / Código 9999)
            // No son productos físicos de inventario, por lo que no deben generar lotes
            // =========================================================================
            $itemsFiltrados = array_filter($items, function ($item) {
                $codigo = trim($item['codigo'] ?? '');
                $desc = strtolower($item['descripcion'] ?? '');
                return $codigo !== '9999' && !str_contains($desc, 'flete');
            });

            // =========================================================================
            // REGLA 3: CONSOLIDACIÓN DE PRODUCTOS REPETIDOS DENTRO DE LA FACTURA
            // Si el mismo producto aparece en más de una línea, sumamos sus cantidades
            // =========================================================================
            $productosConsolidados = [];
            foreach ($itemsFiltrados as $item) {
                $cod = trim($item['codigo'] ?? '');
                if (empty($cod)) continue;

                if (isset($productosConsolidados[$cod])) {
                    // Producto repetido en la factura: sumamos la cantidad
                    $productosConsolidados[$cod]['cantidad'] += (float)$item['cantidad'];
                } else {
                    $productosConsolidados[$cod] = $item;
                }
            }

            // 2. Crear la Recepción en estado PENDIENTE_FECHA
            $folioFinal = $numeroFactura ?: ('OCR-' . strtoupper(uniqid()));
            $recepcion = Recepcion::create([
                'proveedor_id' => $request->proveedor_id,
                'bodega_id' => $request->bodega_id,
                'user_id' => auth()->id(),
                'fecha_recepcion' => $fechaEmision,
                'numero_factura' => $folioFinal,
                'estado' => \App\Enums\EstadoRecepcion::PENDIENTE_FECHA->value,
                'documento_referencia' => $folioFinal,
                'observaciones' => 'Recepción creada vía escaneo OCR',
            ]);

            // 3. Crear los detalles consolidados sin duplicados usando vinculación inteligente
            $matcher = app(\App\Services\ProductMatcherService::class);

            foreach ($productosConsolidados as $item) {
                $codigo = trim($item['codigo'] ?? '');
                $descripcion = trim($item['descripcion'] ?? '');
                
                // Intentar vincular inteligentemente con producto existente
                $productoExistente = $matcher->match($codigo, $descripcion, (int)$request->proveedor_id);
                $productoId = $productoExistente ? $productoExistente->id : null;

                \App\Models\RecepcionDetalle::create([
                    'recepcion_id' => $recepcion->id,
                    'catalogo_producto_id' => $productoId,
                    'codigo_proveedor_usado' => $codigo,
                    'descripcion_proveedor' => $descripcion,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'] ?? 0,
                ]);
            }

            return response()->json([
                'success' => true,
                'redirect_url' => route('recepciones.conciliar', $recepcion->id)
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error procesando OCR: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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

        $recepcion->load(['detalles.catalogoProducto', 'proveedor', 'bodega']);
        $productosCatalogo = \App\Models\CatalogoProducto::activos()->orderBy('nombre')->get();
        $matcher = app(\App\Services\ProductMatcherService::class);

        // Analizar y enriquecer cada detalle con vinculación inteligente y atributos
        foreach ($recepcion->detalles as $det) {
            $descOriginal = $det->descripcion_proveedor 
                ?: ($det->catalogoProducto ? str_replace(' (Pendiente Revisión)', '', $det->catalogoProducto->nombre) : $det->codigo_proveedor_usado);
            
            $det->descripcion_limpia = $descOriginal;
            $det->parsed_info = $matcher->parseAttributes($descOriginal);

            // Verificar si el producto actual es válido y activo
            $esActivo = $det->catalogoProducto && $det->catalogoProducto->activo;

            if (!$esActivo) {
                // Intentar matcher con catálogo activo
                $matched = $matcher->match($det->codigo_proveedor_usado, $descOriginal, $recepcion->proveedor_id);
                if ($matched) {
                    $det->catalogo_producto_id = $matched->id;
                    $det->setRelation('catalogoProducto', $matched);
                    $det->es_nuevo = false;
                } else {
                    $det->catalogo_producto_id = null;
                    $det->es_nuevo = true;
                }
            } else {
                $det->es_nuevo = false;
            }
        }

        return view('recepciones.conciliar', compact('recepcion', 'productosCatalogo'));
    }

    public function guardarConciliacion(Request $request, Recepcion $recepcion)
    {
        if ($recepcion->estado !== \App\Enums\EstadoRecepcion::PENDIENTE_FECHA) {
            return redirect()->route('recepciones.index')->with('error', 'Esta recepción no está pendiente de fechas.');
        }

        $validated = $request->validate([
            'detalles' => 'required|array|min:1',
            'detalles.*.id' => 'nullable',
            'detalles.*.catalogo_producto_id' => 'required|exists:catalogo_productos,id',
            'detalles.*.cantidad' => 'required|numeric|min:0.01',
            'detalles.*.precio_unitario' => 'nullable|numeric|min:0',
            'detalles.*.fecha_elaboracion' => 'nullable|date',
            'detalles.*.fecha_vencimiento' => 'required|date',
            'detalles.*.formato' => 'nullable|string|max:50',
            'detalles.*.capacidad' => 'nullable|string|max:50',
        ], [
            'detalles.*.fecha_vencimiento.required' => 'La fecha de vencimiento es obligatoria para cada lote.',
            'detalles.*.cantidad.min' => 'La cantidad ingresada debe ser mayor a 0.',
        ]);

        try {
            DB::beginTransaction();

            $totalUnidades = 0;
            $detallesProcesadosIds = [];

            // Consolidar duplicados idénticos (mismo producto y misma fecha) para no crear lotes repetidos
            $consolidados = [];
            foreach ($validated['detalles'] as $itemData) {
                $prodId = (int)$itemData['catalogo_producto_id'];
                $venc = $itemData['fecha_vencimiento'];
                $clave = "{$prodId}_{$venc}";

                if (isset($consolidados[$clave])) {
                    $consolidados[$clave]['cantidad'] += (float)$itemData['cantidad'];
                } else {
                    $consolidados[$clave] = $itemData;
                }
            }

            foreach ($consolidados as $itemData) {
                $producto = \App\Models\CatalogoProducto::find($itemData['catalogo_producto_id']);
                if (!$producto) continue;

                // Actualizar formato y capacidad en catálogo maestro si se ajustaron
                $actualizacionesProd = [];
                if (!empty($itemData['formato']) && $producto->formato !== $itemData['formato']) {
                    $actualizacionesProd['formato'] = $itemData['formato'];
                }
                if (!empty($itemData['capacidad']) && $producto->capacidad !== $itemData['capacidad']) {
                    $actualizacionesProd['capacidad'] = $itemData['capacidad'];
                }
                if (!empty($actualizacionesProd)) {
                    $producto->update($actualizacionesProd);
                }

                $cantidad = (float)$itemData['cantidad'];
                $precioUnitario = isset($itemData['precio_unitario']) ? (float)$itemData['precio_unitario'] : (float)$producto->precio_compra_ref;
                $fechaElab = !empty($itemData['fecha_elaboracion']) ? $itemData['fecha_elaboracion'] : null;
                $fechaVenc = $itemData['fecha_vencimiento'];

                // Buscar o crear el detalle de recepción
                $detalle = null;
                if (!empty($itemData['id']) && is_numeric($itemData['id'])) {
                    $detalle = \App\Models\RecepcionDetalle::where('id', $itemData['id'])
                        ->where('recepcion_id', $recepcion->id)
                        ->first();
                }

                if ($detalle) {
                    $detalle->update([
                        'catalogo_producto_id' => $producto->id,
                        'cantidad' => $cantidad,
                        'precio_unitario' => $precioUnitario,
                        'fecha_elaboracion' => $fechaElab,
                        'fecha_vencimiento' => $fechaVenc,
                    ]);
                } else {
                    $detalle = \App\Models\RecepcionDetalle::create([
                        'recepcion_id' => $recepcion->id,
                        'catalogo_producto_id' => $producto->id,
                        'codigo_proveedor_usado' => $producto->sku,
                        'cantidad' => $cantidad,
                        'precio_unitario' => $precioUnitario,
                        'fecha_elaboracion' => $fechaElab,
                        'fecha_vencimiento' => $fechaVenc,
                    ]);
                }

                $detallesProcesadosIds[] = $detalle->id;

                // Generar o actualizar el LoteStock en la bodega de la recepción para rotación FEFO
                \App\Models\LoteStock::updateOrCreate(
                    ['recepcion_detalle_id' => $detalle->id],
                    [
                        'catalogo_producto_id' => $producto->id,
                        'bodega_id' => $recepcion->bodega_id,
                        'cantidad_inicial' => $cantidad,
                        'cantidad_disponible' => $cantidad,
                        'fecha_elaboracion' => $fechaElab,
                        'fecha_vencimiento' => $fechaVenc,
                    ]
                );

                // Auto-aprendizaje: si se usó un código de proveedor, persistir el mapeo
                if (!empty($detalle->codigo_proveedor_usado) && $recepcion->proveedor_id) {
                    $existeMapeo = \App\Models\MapeoCodigo::where('proveedor_id', $recepcion->proveedor_id)
                        ->where(function($q) use ($detalle, $producto) {
                            $q->where('codigo_proveedor', $detalle->codigo_proveedor_usado)
                              ->orWhere('catalogo_producto_id', $producto->id);
                        })
                        ->exists();

                    if (!$existeMapeo) {
                        \App\Models\MapeoCodigo::create([
                            'proveedor_id' => $recepcion->proveedor_id,
                            'codigo_proveedor' => $detalle->codigo_proveedor_usado,
                            'catalogo_producto_id' => $producto->id,
                            'descripcion_proveedor' => $producto->nombre,
                        ]);
                    }
                }

                $totalUnidades += $cantidad;
            }

            // Eliminar detalles que el usuario haya descartado en el formulario
            $detallesAEliminar = \App\Models\RecepcionDetalle::where('recepcion_id', $recepcion->id)
                ->whereNotIn('id', $detallesProcesadosIds)
                ->get();

            foreach ($detallesAEliminar as $d) {
                \App\Models\LoteStock::where('recepcion_detalle_id', $d->id)->delete();
                $d->delete();
            }

            // Confirmar la recepción
            $recepcion->update(['estado' => \App\Enums\EstadoRecepcion::CONFIRMADO->value]);

            DB::commit();

            return redirect()->route('recepciones.show', $recepcion)->with('success', "¡Recepción confirmada con éxito! Se ingresaron {$totalUnidades} unidades en lotes listos para rotación FEFO.");

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error guardando conciliación: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al guardar los lotes: ' . $e->getMessage())->withInput();
        }
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
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No se puede eliminar una recepción confirmada (los lotes ya se generaron).'], 422);
            }
            return redirect()->route('recepciones.index')->with('error', 'No se puede eliminar una recepción confirmada (los lotes ya se generaron).');
        }
        
        $recepcion->delete();
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Recepción enviada a la papelera.']);
        }
        return redirect()->route('recepciones.index')->with('success', 'Recepción enviada a la papelera.');
    }

    public function restore($id)
    {
        $recepcion = Recepcion::onlyTrashed()->findOrFail($id);
        $recepcion->restore();
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Recepción restaurada exitosamente.']);
        }
        return redirect()->route('recepciones.index', ['estado' => 'papelera'])->with('success', 'Recepción restaurada exitosamente.');
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
        
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Recepción eliminada permanentemente.']);
        }
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

        // Validación Anti-Duplicados de Factura/Documento en Ingreso Manual
        $docRef = trim($validated['documento_referencia'] ?? '');
        if (!empty($docRef) && strtoupper($docRef) !== 'S/N' && is_numeric($validated['proveedor_id'])) {
            $existeDoc = Recepcion::where('proveedor_id', $validated['proveedor_id'])
                ->whereRaw('LOWER(numero_factura) = ?', [strtolower($docRef)])
                ->withTrashed()
                ->first();

            if ($existeDoc) {
                $estado = $existeDoc->trashed() ? 'en Papelera' : $existeDoc->estado->value;
                $fecha = $existeDoc->fecha_recepcion ? \Carbon\Carbon::parse($existeDoc->fecha_recepcion)->format('d/m/Y') : 'previamente';
                return back()->with('error', "El Documento / Factura N° {$docRef} ya fue ingresado el {$fecha} (Recepción #{$existeDoc->id}, Estado: {$estado}). No se permite volver a ingresar un documento duplicado.")->withInput();
            }
        }

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

                // 1. Crear Recepción
                $recepcion = Recepcion::create([
                    'proveedor_id' => $validated['proveedor_id'],
                    'bodega_id' => $validated['bodega_id'],
                    'user_id' => auth()->id(),
                    'numero_factura' => !empty($validated['documento_referencia']) ? trim($validated['documento_referencia']) : 'S/N',
                    'estado' => \App\Enums\EstadoRecepcion::BORRADOR->value,
                    'fecha_recepcion' => now(),
                    'observaciones' => $validated['observaciones'] ?? null,
                ]);

                // 2. Consolidar productos repetidos para evitar filas duplicadas del mismo ítem y fecha
                $itemsConsolidados = [];
                foreach ($validated['items'] as $item) {
                    $prodId = $item['catalogo_producto_id'];
                    $venc = $item['fecha_vencimiento'];
                    $key = $prodId . '_' . $venc;

                    if (isset($itemsConsolidados[$key])) {
                        $itemsConsolidados[$key]['cantidad'] += (float)$item['cantidad'];
                    } else {
                        $itemsConsolidados[$key] = $item;
                    }
                }

                // 3. Crear Detalles consolidados
                foreach ($itemsConsolidados as $item) {
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

    /**
     * Búsqueda AJAX de producto para el Modo Escáner (por código de barras SKU o mapeo de proveedor).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscarProductoScanner(Request $request)
    {
        $codigo = trim($request->get('codigo', ''));
        $proveedorId = $request->get('proveedor_id');

        if (empty($codigo)) {
            return response()->json(['success' => false, 'message' => 'Código no proporcionado.'], 400);
        }

        // 1. Buscar coincidencia directa por SKU maestro en el catálogo
        $producto = \App\Models\CatalogoProducto::where('sku', $codigo)
            ->where('activo', true)
            ->first();

        // 2. Si no se encuentra por SKU directo, buscar por el código que asigna el proveedor en MapeoCodigos
        if (!$producto) {
            $mapeoQuery = \App\Models\MapeoCodigo::where('codigo_proveedor', $codigo);
            if ($proveedorId) {
                $mapeoQuery->where('proveedor_id', $proveedorId);
            }
            $mapeo = $mapeoQuery->with('catalogoProducto')->first();
            if ($mapeo && $mapeo->catalogoProducto && $mapeo->catalogoProducto->activo) {
                $producto = $mapeo->catalogoProducto;
            }
        }

        if (!$producto) {
            return response()->json([
                'success' => false,
                'message' => "Código [{$codigo}] no encontrado en el catálogo de productos ni en los códigos del proveedor."
            ], 404);
        }

        return response()->json([
            'success' => true,
            'producto' => [
                'id' => $producto->id,
                'sku' => $producto->sku,
                'nombre' => $producto->nombre,
                'formato' => $producto->formato ?? '',
                'capacidad' => $producto->capacidad ?? '',
                'unidad_medida' => $producto->unidad_medida,
                'precio_compra_ref' => (float)$producto->precio_compra_ref,
                'constante_vencimiento_meses' => $producto->constante_vencimiento_meses,
            ]
        ]);
    }

    /**
     * Comprueba en tiempo real si una factura ya fue ingresada para un proveedor dado (incluyendo papelera).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkFacturaExistente(Request $request)
    {
        $proveedorId = $request->get('proveedor_id');
        $numeroFactura = trim($request->get('numero_factura', ''));

        if (empty($proveedorId) || empty($numeroFactura) || strtoupper($numeroFactura) === 'S/N') {
            return response()->json(['exists' => false]);
        }

        $existente = Recepcion::where('proveedor_id', $proveedorId)
            ->whereRaw('LOWER(numero_factura) = ?', [strtolower($numeroFactura)])
            ->withTrashed()
            ->first();

        if ($existente) {
            $estado = $existente->trashed() ? 'en Papelera' : $existente->estado->value;
            $fecha = $existente->fecha_recepcion ? \Carbon\Carbon::parse($existente->fecha_recepcion)->format('d/m/Y') : 'previamente';
            return response()->json([
                'exists' => true,
                'message' => "La Factura N° {$numeroFactura} ya existe en el sistema (Registrada el {$fecha}, Recepción #{$existente->id}, Estado: {$estado})."
            ]);
        }

        return response()->json(['exists' => false]);
    }

    /**
     * Almacena una recepción originada desde el Modo Escáner (pistola USB o cámara móvil).
     * Aplica reglas estrictas de anti-duplicados tanto a nivel de documento como de consolidación de filas.
     *
     * @param Request $request
     * @param \App\Services\InventarioService $inventarioService
     * @return \Illuminate\Http\JsonResponse
     */
    public function guardarScanner(Request $request, \App\Services\InventarioService $inventarioService)
    {
        $validated = $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'bodega_id' => 'required|exists:bodegas,id',
            'documento_referencia' => 'nullable|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.catalogo_producto_id' => 'required|exists:catalogo_productos,id',
            'items.*.cantidad' => 'required|numeric|min:1',
            'items.*.precio_unitario' => 'nullable|numeric|min:0',
        ]);

        $docRef = trim($validated['documento_referencia'] ?? '');

        // =========================================================================
        // REGLA 1: PREVENCIÓN DE DUPLICADOS DE FACTURA
        // No permitir ingresar una factura que ya fue procesada anteriormente para este proveedor
        // =========================================================================
        if (!empty($docRef) && strtoupper($docRef) !== 'S/N') {
            $facturaExistente = Recepcion::where('proveedor_id', $validated['proveedor_id'])
                ->whereRaw('LOWER(numero_factura) = ?', [strtolower($docRef)])
                ->withTrashed()
                ->first();

            if ($facturaExistente) {
                $estado = $facturaExistente->trashed() ? 'en Papelera' : $facturaExistente->estado->value;
                $fecha = $facturaExistente->fecha_recepcion ? \Carbon\Carbon::parse($facturaExistente->fecha_recepcion)->format('d/m/Y') : 'previamente';
                return response()->json([
                    'success' => false,
                    'message' => "La Factura N° {$docRef} ya fue ingresada el {$fecha} (Recepción #{$facturaExistente->id}, Estado: {$estado}). No se permite volver a ingresar una factura duplicada."
                ], 422);
            }
        }

        try {
            $redirectUrl = '';

            \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $docRef, $inventarioService, &$redirectUrl) {
                // =========================================================================
                // REGLA 2: CONSOLIDACIÓN DE FILAS DUPLICADAS DEL MISMO PRODUCTO
                // Si el escáner envió múltiples veces el mismo producto, sumamos sus cantidades
                // =========================================================================
                $itemsConsolidados = [];
                foreach ($validated['items'] as $item) {
                    $prodId = (int)$item['catalogo_producto_id'];
                    $cant = (float)$item['cantidad'];
                    $precio = isset($item['precio_unitario']) ? (float)$item['precio_unitario'] : 0;

                    if (isset($itemsConsolidados[$prodId])) {
                        $itemsConsolidados[$prodId]['cantidad'] += $cant;
                    } else {
                        $itemsConsolidados[$prodId] = [
                            'catalogo_producto_id' => $prodId,
                            'cantidad' => $cant,
                            'precio_unitario' => $precio,
                        ];
                    }
                }

                $folioFinal = !empty($docRef) ? $docRef : ('SCAN-' . strtoupper(uniqid()));

                // Crear la recepción
                $recepcion = Recepcion::create([
                    'proveedor_id' => $validated['proveedor_id'],
                    'bodega_id' => $validated['bodega_id'],
                    'user_id' => auth()->id(),
                    'numero_factura' => $folioFinal,
                    'documento_referencia' => $folioFinal,
                    'fecha_recepcion' => now(),
                    'estado' => \App\Enums\EstadoRecepcion::BORRADOR->value,
                    'observaciones' => 'Ingreso rápido registrado mediante Modo Escáner',
                ]);

                // Determinar si todos los productos tienen constante de vencimiento automática
                $todosTienenFefoAuto = true;
                $detallesCreados = [];

                foreach ($itemsConsolidados as $item) {
                    $producto = \App\Models\CatalogoProducto::find($item['catalogo_producto_id']);
                    
                    $fechaElab = now()->format('Y-m-d');
                    $fechaVenc = null;

                    if ($producto && $producto->constante_vencimiento_meses > 0) {
                        $fechaVenc = now()->addMonths((int)$producto->constante_vencimiento_meses)->format('Y-m-d');
                    } else {
                        $todosTienenFefoAuto = false;
                    }

                    $detalle = \App\Models\RecepcionDetalle::create([
                        'recepcion_id' => $recepcion->id,
                        'catalogo_producto_id' => $producto->id,
                        'codigo_proveedor_usado' => $producto->sku,
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $item['precio_unitario'] ?: ($producto->precio_compra_ref ?? 0),
                        'fecha_elaboracion' => $fechaElab,
                        'fecha_vencimiento' => $fechaVenc,
                    ]);

                    $detallesCreados[] = $detalle;
                }

                // Si todos los productos calculan vencimiento automático, confirmamos e ingresamos stock de una vez
                if ($todosTienenFefoAuto && count($detallesCreados) > 0) {
                    $inventarioService->confirmarRecepcion($recepcion);
                    $redirectUrl = route('recepciones.show', $recepcion->id);
                } else {
                    // Si hay productos que requieren fecha manual, enviamos a la máquina de conciliación de fechas
                    $recepcion->update(['estado' => \App\Enums\EstadoRecepcion::PENDIENTE_FECHA->value]);
                    $redirectUrl = route('recepciones.conciliar', $recepcion->id);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Recepción creada exitosamente.',
                'redirect_url' => $redirectUrl,
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al guardar recepción escáner: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado al guardar la recepción: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Determina la marca o familia comercial de un proveedor (CCU, COCA_COLA, etc.).
     */
    protected function obtenerGrupoMarcaProveedor(\App\Models\Proveedor $proveedor): string
    {
        $codigo = strtoupper(trim($proveedor->codigo ?? ''));
        $nombre = strtoupper(trim($proveedor->nombre ?? ''));

        if (str_contains($codigo, 'CCU') || str_contains($nombre, 'CCU') || str_contains($nombre, 'CERVECER') || str_contains($nombre, 'UNIDAS')) {
            return 'CCU';
        }

        if (str_contains($codigo, 'COCA') || str_contains($codigo, 'EMB') || str_contains($nombre, 'COCA') || str_contains($nombre, 'EMBONOR') || str_contains($nombre, 'ANDINA')) {
            return 'COCA_COLA';
        }

        if (str_contains($codigo, 'DMK') || str_contains($nombre, 'DIMAK')) {
            return 'DIMAK';
        }

        return 'OTRO';
    }

    /**
     * Determina si una bodega está dedicada a una marca específica (ej: B-COCA, BCCU)
     * o si es una bodega general (devuelve null).
     */
    protected function obtenerGrupoMarcaBodega(\App\Models\Bodega $bodega): ?string
    {
        $codigo = strtoupper(trim($bodega->codigo ?? ''));
        $nombre = strtoupper(trim($bodega->nombre ?? ''));

        if (str_contains($codigo, 'CCU') || str_contains($nombre, 'CCU')) {
            return 'CCU';
        }

        if (str_contains($codigo, 'COCA') || str_contains($nombre, 'COCA')) {
            return 'COCA_COLA';
        }

        return null; // Bodega general o mixta (ej: Sala de Ventas, Bodega Central)
    }
}
