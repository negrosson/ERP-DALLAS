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
        
        // Solo formatos/capacidades que existan en ESTA bodega (fix bug #1)
        $formatos = \App\Models\CatalogoProducto::select('formato')
                        ->whereHas('lotesStock', fn($q) => $q->where('bodega_id', $bodega->id))
                        ->whereNotNull('formato')
                        ->where('formato', '!=', '')
                        ->distinct()
                        ->orderBy('formato')
                        ->pluck('formato');
                        
        $capacidades = \App\Models\CatalogoProducto::select('capacidad')
                        ->whereHas('lotesStock', fn($q) => $q->where('bodega_id', $bodega->id))
                        ->whereNotNull('capacidad')
                        ->where('capacidad', '!=', '')
                        ->distinct()
                        ->orderBy('capacidad')
                        ->pluck('capacidad');

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
            } elseif (in_array($request->vencimiento, ['7_dias', '14_dias', '20_dias', '30_dias'])) {
                $dias = (int) str_replace('_dias', '', $request->vencimiento);
                $limite = now()->addDays($dias)->endOfDay();
                $query->whereHas('lotesStock', function ($sub) use ($bodega, $hoy, $limite) {
                    $sub->where('bodega_id', $bodega->id)
                        ->where('cantidad_disponible', '>', 0)
                        ->whereNotNull('fecha_vencimiento')
                        ->where('fecha_vencimiento', '>=', $hoy)
                        ->where('fecha_vencimiento', '<=', $limite);
                });
            } elseif ($request->vencimiento === 'buen_estado') {
                $limite = now()->addDays(30)->endOfDay();
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
            
        $historial = \App\Models\HistorialAjuste::whereHas('lote', function($q) use ($bodega) {
            $q->where('bodega_id', $bodega->id);
        })->with(['lote.producto', 'usuario'])->latest()->take(50)->get();

        return view('bodegas.show', compact('bodega', 'productos', 'historial', 'formatos', 'capacidades'));
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
            'cantidad' => 'required|numeric|min:0.01',
            'fecha_elaboracion' => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_elaboracion',
        ]);

        // 1. Encontrar o Crear Producto
        $productoId = $request->catalogo_producto_id;
        if (!$productoId) {
            $producto = \App\Models\CatalogoProducto::firstOrCreate(
                [
                    'nombre' => $validated['nombre'],
                    'formato' => $validated['formato'] ?? null,
                    'capacidad' => $validated['capacidad'] ?? null,
                ],
                ['sku' => 'SKU-' . strtoupper(uniqid())] // Auto-generate SKU if needed
            );
            $productoId = $producto->id;
        }

        // 2. Buscar si ya existe un lote con la misma fecha de vencimiento en esta bodega
        $loteExistente = \App\Models\LoteStock::where('bodega_id', $bodega->id)
            ->where('catalogo_producto_id', $productoId)
            ->where('fecha_vencimiento', $validated['fecha_vencimiento'])
            ->first();

        if ($loteExistente) {
            // Auto-merge: Sumar al lote existente
            $cantidadAnterior = $loteExistente->cantidad_disponible;
            $loteExistente->cantidad_disponible += $validated['cantidad'];
            $loteExistente->cantidad_inicial += $validated['cantidad'];
            $loteExistente->save();

            \App\Models\HistorialAjuste::create([
                'lote_stock_id' => $loteExistente->id,
                'user_id' => auth()->id(),
                'cantidad_anterior' => $cantidadAnterior,
                'cantidad_nueva' => $loteExistente->cantidad_disponible,
                'diferencia' => $validated['cantidad'],
                'motivo' => 'Ingreso Rápido (Auto-Merge)',
                'es_reversion' => false,
            ]);

            return redirect()->back()->with('success', 'Stock fusionado correctamente con el lote existente.');
        } else {
            // Crear nuevo lote
            $lote = \App\Models\LoteStock::create([
                'catalogo_producto_id' => $productoId,
                'bodega_id' => $bodega->id,
                'cantidad_inicial' => $validated['cantidad'],
                'cantidad_disponible' => $validated['cantidad'],
                'fecha_elaboracion' => $validated['fecha_elaboracion'] ?? null,
                'fecha_vencimiento' => $validated['fecha_vencimiento'] ?? null,
            ]);

            \App\Models\HistorialAjuste::create([
                'lote_stock_id' => $lote->id,
                'user_id' => auth()->id(),
                'cantidad_anterior' => 0,
                'cantidad_nueva' => $validated['cantidad'],
                'diferencia' => $validated['cantidad'],
                'motivo' => 'Ingreso Rápido en Bodega',
                'es_reversion' => false,
            ]);

            return redirect()->back()->with('success', 'Nuevo lote ingresado correctamente a la bodega.');
        }
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

        return redirect()->back()->with('success', 'Inventario ajustado correctamente.');
    }

    public function revertirAjuste(Request $request, \App\Models\HistorialAjuste $ajuste)
    {
        if ($ajuste->es_reversion || $ajuste->reversiones()->exists()) {
            return redirect()->back()->with('error', 'Este ajuste ya fue revertido o es una reversión.');
        }
        
        abort_unless($ajuste->lote->bodega->activa, 403, 'No puede revertir ajustes en una bodega inactiva.');

        $lote = $ajuste->lote;
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

        return redirect()->back()->with('success', 'Ajuste revertido correctamente.');
    }
}
