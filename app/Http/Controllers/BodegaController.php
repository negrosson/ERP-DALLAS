<?php

namespace App\Http\Controllers;

use App\Models\Bodega;
use Illuminate\Http\Request;

class BodegaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bodegas = Bodega::latest()->paginate(10);
        return view('bodegas.index', compact('bodegas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('bodegas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\App\Http\Requests\StoreBodegaRequest $request)
    {
        $validated = $request->validated();
        $validated['es_refrigerada'] = $request->has('es_refrigerada');
        $validated['activa'] = $request->has('activa');
        
        Bodega::create($validated);
        return redirect()->route('bodegas.index')->with('success', 'Bodega creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Bodega $bodega, Request $request)
    {
        // Filtro contextual de marcas por tipo de bodega
        $nombreBodega = mb_strtolower($bodega->nombre);

        $marcasCervezas = [
            'Cristal' => 'Cristal',
            'Escudo' => 'Escudo',
            'Royal Guard' => 'Royal Guard',
            'Heineken' => 'Heineken',
            'Austral' => 'Austral',
            'Kunstmann' => 'Kunstmann',
            'Corona' => 'Corona',
            'Budweiser' => 'Budweiser',
            'Becker' => 'Becker',
            'Stella Artois' => 'Stella Artois',
            'Coors' => 'Coors',
            'Sol' => 'Sol',
            'Cusqueña' => 'Cusqueña',
            'Báltica' => 'Báltica',
            'Blue Moon' => 'Blue Moon',
            'Miller' => 'Miller',
        ];

        $marcasCcu = [
            'Cachantun' => 'Cachantún',
            'Pepsi' => 'Pepsi',
            'Bilz' => 'Bilz',
            'Pap' => 'Pap',
            'Kem' => 'Kem',
            'Limonsoda' => 'Limonsoda',
            'Crush' => 'Crush',
            'Canada Dry' => 'Canada Dry',
            'Gatorade' => 'Gatorade',
            'Red Bull' => 'Red Bull',
            'Mas' => 'Mas',
            'Watt' => 'Watt\'s',
        ];

        $marcasCoca = [
            'Coca-Cola' => 'Coca-Cola',
            'Fanta' => 'Fanta',
            'Sprite' => 'Sprite',
            'Vital' => 'Vital',
            'Aquarius' => 'Aquarius',
            'Benedictino' => 'Benedictino',
            'Monster' => 'Monster',
            'Powerade' => 'Powerade',
            'Andina del Valle' => 'Andina del Valle',
            'Nordic' => 'Nordic',
            'Kapo' => 'Kapo',
            'Fuze Tea' => 'Fuze Tea',
        ];

        if (str_contains($nombreBodega, 'cerveza') || str_contains($nombreBodega, 'beer')) {
            $gruposMarcas = ['Cervezas' => $marcasCervezas];
            $placeholderMarca = 'Todas las Cervezas';
        } elseif (str_contains($nombreBodega, 'ccu')) {
            $gruposMarcas = ['Bebidas CCU' => $marcasCcu];
            $placeholderMarca = 'Todas las Marcas CCU';
        } elseif (str_contains($nombreBodega, 'coca') || str_contains($nombreBodega, 'embonor') || str_contains($nombreBodega, 'andina')) {
            $gruposMarcas = ['Coca-Cola Company' => $marcasCoca];
            $placeholderMarca = 'Todas las Marcas Coca-Cola';
        } else {
            $gruposMarcas = [
                'Cervezas' => $marcasCervezas,
                'CCU' => $marcasCcu,
                'Coca-Cola' => $marcasCoca,
            ];
            $placeholderMarca = 'Cualquier Marca/Bebida';
        }

        // Detección dinámica: Si en esta bodega existen productos con marcas adicionales, incorporarlas automáticamente
        $productosEnBodega = \App\Models\CatalogoProducto::whereHas('lotesStock', function($q) use ($bodega) {
            $q->where('bodega_id', $bodega->id)->where('cantidad_disponible', '>', 0);
        })->get(['id', 'nombre', 'sku', 'formato', 'capacidad']);

        foreach ($productosEnBodega as $prod) {
            $marcaInferida = $prod->marca;
            if ($marcaInferida && $marcaInferida !== 'Sin Marca') {
                $grupoClave = array_key_first($gruposMarcas);
                if (!isset($gruposMarcas[$grupoClave][$marcaInferida])) {
                    $gruposMarcas[$grupoClave][$marcaInferida] = $marcaInferida;
                }
            }
        }

        $query = \App\Models\CatalogoProducto::query();

        // 0. Filtro base: Solo mostrar productos que tengan stock en esta bodega
        $query->whereHas('lotesStock', function($q) use ($bodega) {
            $q->where('bodega_id', $bodega->id);
        });

        // Aplicar filtros
        // 1. Filtro por BÃºsqueda (Texto en SKU o Nombre) -> heredado del catÃ¡logo
        if ($request->filled('search')) {
            $searchStr = str_replace([' ', '-'], '', trim($request->search));
            $search = "%{$searchStr}%";
            $query->where(function($q) use ($search) {
                $q->whereRaw("REPLACE(REPLACE(nombre, ' ', ''), '-', '') LIKE ?", [$search])
                  ->orWhereRaw("REPLACE(REPLACE(sku, ' ', ''), '-', '') LIKE ?", [$search]);
            });
        }
        
        // 2. Filtro por Marca / Tipo (Texto en SKU o Nombre)
        if ($request->filled('marca')) {
            $marcaStr = str_replace([' ', '-'], '', trim($request->marca));
            $marca = "%{$marcaStr}%";
            $query->where(function($q) use ($marca) {
                $q->whereRaw("REPLACE(REPLACE(nombre, ' ', ''), '-', '') LIKE ?", [$marca])
                  ->orWhereRaw("REPLACE(REPLACE(sku, ' ', ''), '-', '') LIKE ?", [$marca]);
            });
        }

        // 3. Filtro por Estado (Activo / Inactivo)
        if ($request->filled('estado')) {
            $query->where('activo', $request->estado);
        }

        // 4. Filtro por Vencimiento (Aplicado al stock de esta bodega)
        if ($request->filled('vencimiento')) {
            $hoy = now()->startOfDay();
            if ($request->vencimiento === 'vencidos') {
                $query->whereHas('lotesStock', function ($sub) use ($bodega, $hoy) {
                    $sub->where('bodega_id', $bodega->id)
                        ->where('cantidad_disponible', '>', 0)
                        ->whereNotNull('fecha_vencimiento')
                        ->where('fecha_vencimiento', '<', $hoy);
                });
            } elseif (in_array($request->vencimiento, ['7_dias', '14_dias', '20_dias', '30_dias', '1_mes', '2_meses', '3_meses', '6_meses', '12_meses'])) {
                $diasMap = [
                    '7_dias' => 7,
                    '14_dias' => 14,
                    '20_dias' => 20,
                    '30_dias' => 30,
                    '1_mes' => 30,
                    '2_meses' => 60,
                    '3_meses' => 90,
                    '6_meses' => 180,
                    '12_meses' => 365,
                ];
                $dias = $diasMap[$request->vencimiento] ?? 30;
                $limite = now()->addDays($dias)->endOfDay();
                $query->whereHas('lotesStock', function ($sub) use ($bodega, $hoy, $limite) {
                    $sub->where('bodega_id', $bodega->id)
                        ->where('cantidad_disponible', '>', 0)
                        ->whereNotNull('fecha_vencimiento')
                        ->where('fecha_vencimiento', '>=', $hoy)
                        ->where('fecha_vencimiento', '<=', $limite);
                });
            } elseif (in_array($request->vencimiento, ['buen_estado', 'optimos', 'mas_12_meses'])) {
                $limite = now()->addDays(365)->endOfDay();
                $query->whereHas('lotesStock', function ($sub) use ($bodega, $limite) {
                    $sub->where('bodega_id', $bodega->id)
                        ->where('cantidad_disponible', '>', 0)
                        ->where(function ($orSub) use ($limite) {
                            $orSub->whereNull('fecha_vencimiento')
                                  ->orWhere('fecha_vencimiento', '>', $limite);
                        });
                });
            }
        }
        
        if ($request->filled('formato')) {
            $query->where('formato', $request->formato);
        }
        
        if ($request->filled('capacidad')) {
            $query->where('capacidad', $request->capacidad);
        }

        // Regla: mostrar TODOS los productos del catálogo (filtrados),
        // pero cargar SÓLO los lotes que corresponden a ESTA bodega.
        $productos = $query->with(['lotesStock' => function($q) use ($bodega) {
            $q->where('bodega_id', $bodega->id)
              ->orderBy('fecha_vencimiento', 'asc')
              ->with('recepcionDetalle.recepcion');
        }])->orderBy('nombre')->get();
        
        if ($request->ajax()) {
            return view('bodegas.partials.table', compact('productos', 'bodega'))->render();
        }

        // Formatos y capacidades obtenidos en memoria a partir de los productos con stock (0 consultas SQL adicionales)
        $formatos = $productosEnBodega->pluck('formato')->filter(fn($f) => !empty($f))->unique()->sort()->values();
        $capacidades = $productosEnBodega->pluck('capacidad')->filter(fn($c) => !empty($c))->unique()->sort()->values();
            
        $historial = \App\Models\HistorialAjuste::whereHas('lote', function($q) use ($bodega) {
            $q->where('bodega_id', $bodega->id);
        })->with(['lote.producto', 'usuario'])->latest()->take(50)->get();

        return view('bodegas.show', compact('bodega', 'productos', 'historial', 'formatos', 'capacidades', 'gruposMarcas', 'placeholderMarca'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bodega $bodega)
    {
        return view('bodegas.edit', compact('bodega'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(\App\Http\Requests\UpdateBodegaRequest $request, Bodega $bodega)
    {
        $validated = $request->validated();
        $validated['es_refrigerada'] = $request->has('es_refrigerada');
        $validated['activa'] = $request->has('activa');
        
        $bodega->update($validated);
        return redirect()->route('bodegas.index')->with('success', 'Bodega actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bodega $bodega)
    {
        try {
            $bodega->delete();
            return redirect()->route('bodegas.index')->with('success', 'Bodega eliminada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('bodegas.index')->with('error', 'No se puede eliminar la bodega porque tiene registros asociados (ej. inventario o recepciones).');
        }
    }

    public function quickAddLote(Request $request, Bodega $bodega)
    {
        $validated = $request->validate([
            'catalogo_producto_id' => 'nullable|exists:catalogo_productos,id',
            'nombre' => 'required_without:catalogo_producto_id|string|max:255',
            'formato' => 'nullable|string|max:255',
            'capacidad' => 'nullable|string|max:255',
            // Soporte de múltiples lotes con distintas fechas
            'lotes' => 'nullable|array',
            'lotes.*.cantidad' => 'required_with:lotes|numeric|min:0.01',
            'lotes.*.lote_stock_id' => 'nullable|exists:lotes_stock,id',
            'lotes.*.fecha_elaboracion' => 'nullable|date',
            'lotes.*.fecha_vencimiento' => 'nullable|date',
            // Fallback para lote único
            'cantidad' => 'nullable|numeric|min:0.01',
            'lote_stock_id' => 'nullable|exists:lotes_stock,id',
            'fecha_elaboracion' => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date',
        ]);

        // 1. Encontrar o Crear Producto
        $productoId = $request->catalogo_producto_id;
        if (!$productoId) {
            $producto = \App\Models\CatalogoProducto::firstOrCreate(
                [
                    'nombre' => $validated['nombre'],
                    'formato' => $validated['formato'] ?? 'Botella',
                    'capacidad' => $validated['capacidad'] ?? null,
                ],
                ['sku' => 'SKU-' . strtoupper(uniqid())] // Auto-generate SKU if needed
            );
            $productoId = $producto->id;
        } else {
            // Actualizar capacidad y/o formato en el catálogo si fueron editados
            $producto = \App\Models\CatalogoProducto::find($productoId);
            if ($producto) {
                $cambios = [];
                if (!empty($validated['capacidad']) && $producto->capacidad !== $validated['capacidad']) {
                    $cambios['capacidad'] = $validated['capacidad'];
                }
                if (!empty($validated['formato']) && (empty($producto->formato) || $producto->formato !== $validated['formato'])) {
                    $cambios['formato'] = $validated['formato'];
                }
                if (!empty($cambios)) {
                    $producto->update($cambios);
                }
            }
        }

        // 2. Normalizar la lista de lotes a procesar
        $itemsLotes = [];
        if ($request->has('lotes') && is_array($request->lotes) && count($request->lotes) > 0) {
            foreach ($request->lotes as $loteData) {
                if (!empty($loteData['cantidad']) && (float)$loteData['cantidad'] > 0) {
                    $itemsLotes[] = [
                        'lote_stock_id' => $loteData['lote_stock_id'] ?? null,
                        'cantidad' => (float)$loteData['cantidad'],
                        'fecha_elaboracion' => $loteData['fecha_elaboracion'] ?? null,
                        'fecha_vencimiento' => $loteData['fecha_vencimiento'] ?? null,
                    ];
                }
            }
        }

        // Si no vino array 'lotes', usar los campos individuales de lote único
        if (empty($itemsLotes)) {
            $cant = (float)($request->cantidad ?? 0);
            if ($cant > 0) {
                $itemsLotes[] = [
                    'lote_stock_id' => $request->lote_stock_id ?? null,
                    'cantidad' => $cant,
                    'fecha_elaboracion' => $request->fecha_elaboracion ?? null,
                    'fecha_vencimiento' => $request->fecha_vencimiento ?? null,
                ];
            }
        }

        if (empty($itemsLotes)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Debes ingresar al menos un lote con cantidad mayor a 0.'], 422);
            }
            return redirect()->back()->with('error', 'Debes ingresar al menos un lote con cantidad mayor a 0.');
        }

        // 3. Procesar cada lote (sumar a existentes o crear nuevos)
        $totalUnidadesIngresadas = 0;
        $lotesCreados = 0;
        $lotesFusionados = 0;

        foreach ($itemsLotes as $item) {
            $cantidad = $item['cantidad'];
            $totalUnidadesIngresadas += $cantidad;

            // Determinar si se suma a un lote existente (por ID explícito o coincidencia de vencimiento)
            $loteExistente = null;
            if (!empty($item['lote_stock_id'])) {
                $loteExistente = \App\Models\LoteStock::where('id', $item['lote_stock_id'])
                    ->where('bodega_id', $bodega->id)
                    ->first();
            }

            if (!$loteExistente && !empty($item['fecha_vencimiento'])) {
                $loteExistente = \App\Models\LoteStock::where('bodega_id', $bodega->id)
                    ->where('catalogo_producto_id', $productoId)
                    ->where('fecha_vencimiento', $item['fecha_vencimiento'])
                    ->first();
            }

            if ($loteExistente) {
                // Auto-merge: Sumar al lote existente
                $cantidadAnterior = $loteExistente->cantidad_disponible;
                $loteExistente->cantidad_disponible += $cantidad;
                $loteExistente->cantidad_inicial += $cantidad;
                $loteExistente->save();

                \App\Models\HistorialAjuste::create([
                    'lote_stock_id' => $loteExistente->id,
                    'user_id' => auth()->id(),
                    'cantidad_anterior' => $cantidadAnterior,
                    'cantidad_nueva' => $loteExistente->cantidad_disponible,
                    'diferencia' => $cantidad,
                    'motivo' => 'Ingreso Rápido (Sumado a Lote #' . $loteExistente->id . ')',
                    'es_reversion' => false,
                ]);

                $lotesFusionados++;
            } else {
                // Crear nuevo lote
                $nuevoLote = \App\Models\LoteStock::create([
                    'catalogo_producto_id' => $productoId,
                    'bodega_id' => $bodega->id,
                    'cantidad_inicial' => $cantidad,
                    'cantidad_disponible' => $cantidad,
                    'fecha_elaboracion' => $item['fecha_elaboracion'],
                    'fecha_vencimiento' => $item['fecha_vencimiento'],
                ]);

                \App\Models\HistorialAjuste::create([
                    'lote_stock_id' => $nuevoLote->id,
                    'user_id' => auth()->id(),
                    'cantidad_anterior' => 0,
                    'cantidad_nueva' => $cantidad,
                    'diferencia' => $cantidad,
                    'motivo' => 'Ingreso Rápido en Bodega (Nuevo Lote #' . $nuevoLote->id . ')',
                    'es_reversion' => false,
                ]);

                $lotesCreados++;
            }
        }

        $resumen = [];
        if ($lotesCreados > 0) $resumen[] = "{$lotesCreados} nuevo(s) lote(s)";
        if ($lotesFusionados > 0) $resumen[] = "{$lotesFusionados} lote(s) sumado(s)";

        $mensaje = 'Ingreso rápido exitoso: ' . implode(' y ', $resumen) . ' con un total de ' . number_format($totalUnidadesIngresadas, 0, ',', '.') . ' UN.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'total_unidades' => $totalUnidadesIngresadas,
            ]);
        }

        return redirect()->back()->with('success', $mensaje);
    }

    public function ajustarStock(Request $request, \App\Models\LoteStock $lote)
    {
        $validated = $request->validate([
            'cantidad_nueva' => 'required|numeric|min:0',
            'motivo' => 'required|string|max:255',
        ]);

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

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Stock ajustado correctamente.',
            ]);
        }

        return redirect()->back()->with('success', 'Stock ajustado correctamente.');
    }

    public function ajustarMasivoGlobal(Request $request, \App\Models\CatalogoProducto $producto)
    {
        return $this->ajustarMasivo($request, null, $producto);
    }

    public function ajustarMasivo(Request $request, ?Bodega $bodega, \App\Models\CatalogoProducto $producto)
    {
        $validated = $request->validate([
            'lotes' => 'required|array',
            'lotes.*.id' => 'nullable|exists:lotes_stock,id',
            'lotes.*.bodega_id' => 'nullable|exists:bodegas,id',
            'lotes.*.cantidad_nueva' => 'required|numeric|min:0',
            'lotes.*.fecha_elaboracion' => 'nullable|date',
            'lotes.*.fecha_vencimiento' => 'nullable|date|after_or_equal:lotes.*.fecha_elaboracion',
            'motivo' => 'nullable|string|max:255',
            'formato' => 'nullable|string|max:255',
            'capacidad' => 'nullable|string|max:255',
        ]);

        $motivo = $validated['motivo'] ?? 'Ajuste manual de inventario';
        
        $updateData = [];
        if (isset($validated['formato'])) {
            $updateData['formato'] = $validated['formato'];
        }
        if (isset($validated['capacidad'])) {
            $updateData['capacidad'] = $validated['capacidad'];
        }
        if (!empty($updateData)) {
            $producto->update($updateData);
        }

        foreach ($validated['lotes'] as $loteData) {
            $cantidadNueva = $loteData['cantidad_nueva'];

            if (!empty($loteData['id'])) {
                // Actualizar lote existente
                $lote = \App\Models\LoteStock::find($loteData['id']);
                // Ensure the lote belongs to this product (allow any bodega for global adjustments)
                if ($lote && $lote->catalogo_producto_id == $producto->id) {
                    $cantidadAnterior = $lote->cantidad_disponible;
                    $diferencia = $cantidadNueva - $cantidadAnterior;

                    // Update dates if they were changed
                    $lote->fecha_elaboracion = $loteData['fecha_elaboracion'] ?? $lote->fecha_elaboracion;
                    $lote->fecha_vencimiento = $loteData['fecha_vencimiento'] ?? $lote->fecha_vencimiento;
                    
                    if ($diferencia != 0 || $lote->isDirty()) {
                        $lote->cantidad_disponible = $cantidadNueva;
                        $lote->save();

                        if ($diferencia != 0) {
                            \App\Models\HistorialAjuste::create([
                                'lote_stock_id' => $lote->id,
                                'user_id' => auth()->id(),
                                'cantidad_anterior' => $cantidadAnterior,
                                'cantidad_nueva' => $cantidadNueva,
                                'diferencia' => $diferencia,
                                'motivo' => $motivo,
                                'es_reversion' => false,
                            ]);
                        }
                    }
                }
            } else {
                // Crear nuevo lote (o hacer auto-merge si la fecha coincide)
                if ($cantidadNueva > 0) {
                    $targetBodegaId = $loteData['bodega_id'] ?? ($bodega ? $bodega->id : null);
                    if (!$targetBodegaId) {
                        return redirect()->back()->with('error', 'Debe seleccionar una bodega para ingresar el nuevo lote.');
                    }

                    $loteExistente = \App\Models\LoteStock::where('bodega_id', $targetBodegaId)
                        ->where('catalogo_producto_id', $producto->id)
                        ->where('fecha_vencimiento', $loteData['fecha_vencimiento'])
                        ->first();

                    if ($loteExistente) {
                        $cantidadAnterior = $loteExistente->cantidad_disponible;
                        $loteExistente->cantidad_disponible += $cantidadNueva;
                        $loteExistente->save();

                        \App\Models\HistorialAjuste::create([
                            'lote_stock_id' => $loteExistente->id,
                            'user_id' => auth()->id(),
                            'cantidad_anterior' => $cantidadAnterior,
                            'cantidad_nueva' => $loteExistente->cantidad_disponible,
                            'diferencia' => $cantidadNueva,
                            'motivo' => $motivo . ' (Merge auto por coincidencia de fechas)',
                            'es_reversion' => false,
                        ]);
                    } else {
                        $nuevoLote = \App\Models\LoteStock::create([
                            'bodega_id' => $targetBodegaId,
                            'catalogo_producto_id' => $producto->id,
                            'cantidad_inicial' => $cantidadNueva,
                            'cantidad_original' => $cantidadNueva,
                            'cantidad_disponible' => $cantidadNueva,
                            'fecha_elaboracion' => $loteData['fecha_elaboracion'] ?? null,
                            'fecha_vencimiento' => $loteData['fecha_vencimiento'] ?? null,
                        ]);

                        \App\Models\HistorialAjuste::create([
                            'lote_stock_id' => $nuevoLote->id,
                            'user_id' => auth()->id(),
                            'cantidad_anterior' => 0,
                            'cantidad_nueva' => $cantidadNueva,
                            'diferencia' => $cantidadNueva,
                            'motivo' => $motivo . ' (Nuevo Lote)',
                            'es_reversion' => false,
                        ]);
                    }
                }
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Inventario ajustado correctamente.',
            ]);
        }

        return redirect()->back()->with('success', 'Inventario ajustado correctamente.');
    }

    public function revertirAjuste(Request $request, \App\Models\HistorialAjuste $ajuste)
    {
        if ($ajuste->es_reversion || $ajuste->reversiones()->exists()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Este ajuste ya fue revertido o es una reversión.'], 422);
            }
            return redirect()->back()->with('error', 'Este ajuste ya fue revertido o es una reversión.');
        }
        
        abort_unless($ajuste->lote->bodega->activa, 403, 'No puede revertir ajustes en una bodega inactiva.');

        $lote = $ajuste->lote;
        if ($lote->trashed()) {
            $lote->restore();
        }

        $cantidadAnterior = $lote->cantidad_disponible;
        $cantidadNueva = $cantidadAnterior - $ajuste->diferencia;

        $lote->update(['cantidad_disponible' => $cantidadNueva]);

        \App\Models\HistorialAjuste::create([
            'lote_stock_id' => $lote->id,
            'user_id' => auth()->id(),
            'cantidad_anterior' => $cantidadAnterior,
            'cantidad_nueva' => $cantidadNueva,
            'diferencia' => -$ajuste->diferencia,
            'motivo' => 'Reversión del ajuste #' . $ajuste->id,
            'es_reversion' => true,
            'reversion_de_id' => $ajuste->id,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Ajuste revertido correctamente.',
            ]);
        }

        return redirect()->back()->with('success', 'Ajuste revertido correctamente.');
    }

    /**
     * Eliminar un producto completo de la bodega dada de baja de existencias y registro en auditoría.
     */
    public function eliminarProducto(Request $request, Bodega $bodega, \App\Models\CatalogoProducto $producto)
    {
        abort_unless($bodega->activa, 403, 'No puede eliminar productos de una bodega inactiva.');

        $lotes = \App\Models\LoteStock::where('bodega_id', $bodega->id)
            ->where('catalogo_producto_id', $producto->id)
            ->get();

        if ($lotes->isEmpty()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'El producto no posee existencias ni lotes en esta bodega.'], 404);
            }
            return redirect()->back()->with('error', 'El producto no posee existencias ni lotes en esta bodega.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($lotes, $bodega, $producto) {
            foreach ($lotes as $lote) {
                $cantidadAnterior = (float)$lote->cantidad_disponible;

                \App\Models\HistorialAjuste::create([
                    'lote_stock_id' => $lote->id,
                    'user_id' => auth()->id(),
                    'cantidad_anterior' => $cantidadAnterior,
                    'cantidad_nueva' => 0,
                    'diferencia' => -$cantidadAnterior,
                    'motivo' => "Producto '{$producto->nombre}' eliminado de bodega '{$bodega->nombre}' (Baja de existencias)",
                    'es_reversion' => false,
                ]);

                $lote->cantidad_disponible = 0;
                $lote->save();
                $lote->delete(); // Soft delete para preservar integridad de auditoría
            }
        });

        $msg = "Producto '{$producto->nombre}' eliminado exitosamente de la bodega '{$bodega->nombre}'. Se dio de baja su stock y quedó registrado en auditoría.";
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Eliminar un lote físico individual de la bodega con registro en auditoría.
     */
    public function eliminarLote(Request $request, Bodega $bodega, \App\Models\LoteStock $lote)
    {
        abort_unless($bodega->activa, 403, 'No puede eliminar lotes de una bodega inactiva.');
        abort_if($lote->bodega_id !== $bodega->id, 404, 'El lote no pertenece a esta bodega.');

        $producto = $lote->producto;
        $cantidadAnterior = (float)$lote->cantidad_disponible;

        \Illuminate\Support\Facades\DB::transaction(function () use ($lote, $bodega, $producto, $cantidadAnterior) {
            \App\Models\HistorialAjuste::create([
                'lote_stock_id' => $lote->id,
                'user_id' => auth()->id(),
                'cantidad_anterior' => $cantidadAnterior,
                'cantidad_nueva' => 0,
                'diferencia' => -$cantidadAnterior,
                'motivo' => "Lote #{$lote->id} (" . ($producto ? $producto->nombre : 'Producto') . ") eliminado de bodega '{$bodega->nombre}'",
                'es_reversion' => false,
            ]);

            $lote->cantidad_disponible = 0;
            $lote->save();
            $lote->delete(); // Soft delete
        });

        $msg = "Lote #{$lote->id} eliminado exitosamente de la bodega y registrado en auditoría.";
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }
}
