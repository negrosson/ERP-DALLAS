<?php

namespace App\Http\Controllers;

use App\Models\CatalogoProducto;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $bodegas = \App\Models\Bodega::orderBy('nombre')->get();
        $query = CatalogoProducto::query();

        // Filtro por Búsqueda (Texto en SKU o Nombre)
        $query->when($request->search, function ($q) use ($request) {
            $searchStr = str_replace([' ', '-'], '', trim($request->search));
            $search = '%' . $searchStr . '%';
            $q->where(function ($sub) use ($search) {
                $sub->whereRaw("REPLACE(REPLACE(nombre, ' ', ''), '-', '') LIKE ?", [$search])
                    ->orWhereRaw("REPLACE(REPLACE(sku, ' ', ''), '-', '') LIKE ?", [$search]);
            });
        });

        // Filtro por Estado (Activo / Inactivo)
        $query->when($request->filled('estado'), function ($q) use ($request) {
            $q->where('activo', $request->estado);
        });

        // Filtro por Bodega (muestra productos que existen en esa bodega)
        $query->when($request->filled('bodega_id'), function ($q) use ($request) {
            $q->whereHas('lotesStock', function ($sub) use ($request) {
                $sub->where('bodega_id', $request->bodega_id);
            });
        });

        // Filtro por Vencimiento (Stock)
        $query->when($request->filled('vencimiento'), function ($q) use ($request) {
            $hoy = now()->startOfDay();
            
            if ($request->vencimiento === 'vencidos') {
                $q->whereHas('lotesStock', function ($sub) use ($hoy) {
                    $sub->where('cantidad_disponible', '>', 0)
                        ->whereNotNull('fecha_vencimiento')
                        ->where('fecha_vencimiento', '<', $hoy);
                });
            } elseif (in_array($request->vencimiento, ['7_dias', '14_dias', '20_dias', '30_dias'])) {
                $dias = (int) str_replace('_dias', '', $request->vencimiento);
                $limite = now()->addDays($dias)->endOfDay();
                
                $q->whereHas('lotesStock', function ($sub) use ($hoy, $limite) {
                    $sub->where('cantidad_disponible', '>', 0)
                        ->whereNotNull('fecha_vencimiento')
                        ->where('fecha_vencimiento', '>=', $hoy)
                        ->where('fecha_vencimiento', '<=', $limite);
                });
            } elseif ($request->vencimiento === 'buen_estado') {
                $limite = now()->addDays(30)->endOfDay();
                $q->whereHas('lotesStock', function ($sub) use ($limite) {
                    $sub->where('cantidad_disponible', '>', 0)
                        ->where(function ($orSub) use ($limite) {
                            $orSub->whereNull('fecha_vencimiento')
                                  ->orWhere('fecha_vencimiento', '>', $limite);
                        });
                });
            }
        });

        $productos = $query->latest()->with([
            'lotesStock' => fn($q) => $q
                ->where('cantidad_disponible', '>=', 0)
                ->orderBy('fecha_vencimiento', 'asc')
                ->with('bodega')
                ->select(['id', 'catalogo_producto_id', 'bodega_id', 'cantidad_disponible', 'fecha_elaboracion', 'fecha_vencimiento']),
        ])->paginate(10)->withQueryString();
        
        if ($request->wantsJson()) {
            return response()->json($productos);
        }

        if ($request->ajax()) {
            return view('catalogo.partials.table', compact('productos'))->render();
        }
        
        return view('catalogo.index', compact('productos', 'bodegas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('catalogo.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\App\Http\Requests\StoreCatalogoRequest $request)
    {
        $validated = $request->validated();
        $validated['activo'] = $request->has('activo'); // Handle checkbox
        
        CatalogoProducto::create($validated);
        return redirect()->route('catalogo.index')->with('success', 'Producto creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CatalogoProducto $catalogo)
    {
        $catalogo->load([
            'lotesStock.bodega',
            'recepcionDetalles.recepcion.proveedor',
            'recepcionDetalles.recepcion.bodega'
        ]);

        return view('catalogo.show', compact('catalogo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CatalogoProducto $catalogo)
    {
        $catalogo->load(['lotesStock.bodega', 'lotesStock.recepcionDetalle.recepcion']);

        $proximoLote = $catalogo->lotesStock()
            ->where('cantidad_disponible', '>', 0)
            ->whereNotNull('fecha_vencimiento')
            ->orderBy('fecha_vencimiento', 'asc')
            ->first();
            
        return view('catalogo.edit', compact('catalogo', 'proximoLote'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(\App\Http\Requests\UpdateCatalogoRequest $request, CatalogoProducto $catalogo)
    {
        $validated = $request->validated();
        $validated['activo'] = $request->has('activo'); // Handle checkbox
        
        $catalogo->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Catálogo actualizado correctamente',
                'data' => $catalogo
            ]);
        }

        return redirect()->route('catalogo.index')->with('success', 'Producto actualizado exitosamente en el catálogo.');
    }

    /**
     * Store a newly created product via AJAX quick modal.
     */
    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255|unique:catalogo_productos,sku',
            'formato' => 'nullable|string|max:255',
            'capacidad' => 'nullable|string|max:255',
            'precio_compra_ref' => 'nullable|numeric|min:0',
            'precio_venta' => 'nullable|numeric|min:0',
            'constante_vencimiento_meses' => 'nullable|integer|min:0',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'codigo_proveedor' => 'nullable|string|max:50',
        ]);

        // Generar SKU limpio si viene vacío
        if (empty($validated['sku'])) {
            $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $validated['nombre']), 0, 3));
            $sku = ($prefix ?: 'PRD') . '-' . strtoupper(substr(uniqid(), -4));
            $validated['sku'] = $sku;
        }

        $producto = CatalogoProducto::create([
            'nombre' => $validated['nombre'],
            'sku' => $validated['sku'],
            'formato' => $validated['formato'] ?? 'Lata',
            'capacidad' => $validated['capacidad'] ?? '350 cc',
            'unidad_medida' => 'UN',
            'precio_compra_ref' => $validated['precio_compra_ref'] ?? 0,
            'precio_venta' => $validated['precio_venta'] ?? 0,
            'constante_vencimiento_meses' => $validated['constante_vencimiento_meses'] ?? 6,
            'activo' => true,
        ]);

        // Registrar mapeo automático si se proporcionó código de proveedor
        if (!empty($validated['proveedor_id']) && !empty($validated['codigo_proveedor'])) {
            \App\Models\MapeoCodigo::updateOrCreate(
                [
                    'proveedor_id' => (int)$validated['proveedor_id'],
                    'codigo_proveedor' => $validated['codigo_proveedor'],
                ],
                [
                    'catalogo_producto_id' => $producto->id,
                    'descripcion_proveedor' => $producto->nombre,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Producto creado e incorporado al catálogo exitosamente.',
            'producto' => $producto
        ]);
    }

    /**
     * Create a new LoteStock manually from the catalog.
     */
    public function crearLote(\Illuminate\Http\Request $request, CatalogoProducto $catalogo)
    {
        $validated = $request->validate([
            'bodega_id' => 'required|exists:bodegas,id',
            'cantidad_disponible' => 'required|numeric|min:0.01',
            'fecha_elaboracion' => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_elaboracion',
            'es_display' => 'boolean',
            'unidades_display' => 'nullable|numeric|min:1',
            'motivo' => 'required|string|max:255',
        ]);

        $cantidadReal = $validated['cantidad_disponible'];
        if ($request->boolean('es_display') && !empty($validated['unidades_display'])) {
            $cantidadReal = $validated['cantidad_disponible'] * $validated['unidades_display'];
        }

        $lote = \App\Models\LoteStock::create([
            'bodega_id' => $validated['bodega_id'],
            'catalogo_producto_id' => $catalogo->id,
            'cantidad_inicial' => $cantidadReal,
            'cantidad_disponible' => $cantidadReal,
            'fecha_elaboracion' => $validated['fecha_elaboracion'],
            'fecha_vencimiento' => $validated['fecha_vencimiento'],
        ]);

        \App\Models\HistorialAjuste::create([
            'lote_stock_id' => $lote->id,
            'user_id' => auth()->id(),
            'cantidad_anterior' => 0,
            'cantidad_nueva' => $cantidadReal,
            'diferencia' => $cantidadReal,
            'motivo' => 'Creación manual de lote: ' . $validated['motivo'],
            'es_reversion' => false,
        ]);

        return back()->with('success', 'Lote creado exitosamente con ' . $cantidadReal . ' unidades.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CatalogoProducto $catalogo)
    {
        try {
            $catalogo->delete();
            return redirect()->route('catalogo.index')->with('success', 'Producto eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('catalogo.index')->with('error', 'No se puede eliminar el producto porque tiene registros asociados (ej. recepciones o stock).');
        }
    }

    /**
     * Get lotes of a product for AJAX
     */
    public function lotes(CatalogoProducto $catalogo)
    {
        $lotes = $catalogo->lotesStock()
            ->with(['bodega', 'recepcionDetalle.recepcion.proveedor'])
            ->orderByDesc('created_at')
            ->get();
            
        return response()->json($lotes);
    }
}
