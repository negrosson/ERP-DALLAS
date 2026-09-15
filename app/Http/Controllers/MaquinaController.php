<?php

namespace App\Http\Controllers;

use App\Models\Maquina;
use App\Models\MaquinaSlot;
use App\Models\CatalogoProducto;
use App\Models\Bodega;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaquinaController extends Controller
{
    public function index()
    {
        $maquinas = Maquina::with(['slots'])->get();
        
        $maquinasStats = $maquinas->map(function($maquina) {
            $totalCapacidad = $maquina->slots->sum('capacidad_maxima');
            $totalActual = $maquina->slots->sum('cantidad_actual');
            $porcentaje = $totalCapacidad > 0 ? round(($totalActual / $totalCapacidad) * 100) : 0;
            
            return [
                'id' => $maquina->id,
                'nombre' => $maquina->nombre,
                'bodega_id' => $maquina->bodega_id,
                'total_capacidad' => $totalCapacidad,
                'total_actual' => $totalActual,
                'porcentaje_llenado' => $porcentaje,
                'secciones' => $maquina->slots->unique('seccion')->count(),
                'pisos' => $maquina->slots->unique('piso')->count(),
            ];
        });

        return view('maquinas.index', compact('maquinasStats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'secciones' => 'required|integer|min:1|max:10',
            'pisos' => 'required|integer|min:1|max:20',
            'caras' => 'required|integer|min:1|max:20',
            'fondo' => 'required|integer|min:1|max:50',
        ]);

        DB::transaction(function () use ($request) {
            $bodegaDefault = Bodega::where('activa', true)->first();

            $maquina = Maquina::create([
                'nombre' => $request->nombre,
                'bodega_id' => $bodegaDefault ? $bodegaDefault->id : null,
            ]);

            $slotsData = [];
            for ($seccion = 1; $seccion <= $request->secciones; $seccion++) {
                for ($piso = 1; $piso <= $request->pisos; $piso++) {
                    for ($posicion = 1; $posicion <= $request->caras; $posicion++) {
                        $slotsData[] = [
                            'maquina_id' => $maquina->id,
                            'seccion' => $seccion,
                            'piso' => $piso,
                            'posicion' => $posicion,
                            'capacidad_maxima' => $request->fondo,
                            'cantidad_actual' => 0,
                            'displays_puestos' => 0,
                            'catalogo_producto_id' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            MaquinaSlot::insert($slotsData);
        });

        return redirect()->route('maquinas.index')->with('success', 'Máquina creada y estructura generada correctamente.');
    }

    public function show($id)
    {
        $maquina = Maquina::with(['slots.producto'])->findOrFail($id);
        
        // Agrupar slots: seccion -> piso -> productos consolidados
        $estructura = [];
        foreach ($maquina->slots as $slot) {
            $key = $slot->seccion . '-' . $slot->piso;
            if (!isset($estructura[$slot->seccion])) {
                $estructura[$slot->seccion] = [];
            }
            if (!isset($estructura[$slot->seccion][$slot->piso])) {
                $estructura[$slot->seccion][$slot->piso] = [];
            }
            $estructura[$slot->seccion][$slot->piso][] = $slot;
        }

        // Ordenar
        ksort($estructura);
        foreach ($estructura as &$pisos) {
            ksort($pisos);
        }

        // Consolidar por producto en cada piso
        $pisoConsolidado = [];
        foreach ($estructura as $seccion => $pisos) {
            foreach ($pisos as $piso => $slots) {
                $productos = [];
                foreach ($slots as $slot) {
                    $prodId = $slot->catalogo_producto_id ?? 'empty';
                    if (!isset($productos[$prodId])) {
                        $productos[$prodId] = [
                            'slot_ids' => [],
                            'producto' => $slot->producto,
                            'catalogo_producto_id' => $slot->catalogo_producto_id,
                            'cantidad_total' => 0,
                            'capacidad_total' => 0,
                            'displays_puestos' => 0,
                        ];
                    }
                    $productos[$prodId]['slot_ids'][] = $slot->id;
                    $productos[$prodId]['cantidad_total'] += $slot->cantidad_actual;
                    $productos[$prodId]['capacidad_total'] += $slot->capacidad_maxima;
                    $productos[$prodId]['displays_puestos'] += $slot->displays_puestos;
                }
                $pisoConsolidado[$seccion][$piso] = array_values($productos);
            }
        }
        
        $productos = CatalogoProducto::orderBy('nombre')->get();

        return view('maquinas.show', compact('maquina', 'estructura', 'pisoConsolidado', 'productos'));
    }

    /**
     * Actualizar un piso completo vía AJAX (rápido, sin recarga)
     */
    public function updateFloor(Request $request, $maquinaId)
    {
        $request->validate([
            'seccion' => 'required|integer',
            'piso' => 'required|integer',
            'productos' => 'required|array|min:1',
            'productos.*.catalogo_producto_id' => 'required|exists:catalogo_productos,id',
            'productos.*.cantidad' => 'required|integer|min:0',
            'productos.*.displays' => 'nullable|integer|min:0',
        ]);

        $maquina = Maquina::findOrFail($maquinaId);
        
        // Obtener todos los slots de este piso/sección
        $slots = MaquinaSlot::where('maquina_id', $maquina->id)
            ->where('seccion', $request->seccion)
            ->where('piso', $request->piso)
            ->orderBy('posicion')
            ->get();

        if ($slots->isEmpty()) {
            return response()->json(['error' => 'No se encontraron slots para este piso'], 404);
        }

        $productosInput = $request->productos;
        $totalSlots = $slots->count();
        $slotsPerProduct = max(1, intdiv($totalSlots, count($productosInput)));
        
        DB::transaction(function () use ($slots, $productosInput, $slotsPerProduct) {
            $slotIndex = 0;
            
            foreach ($productosInput as $i => $prodData) {
                // Distribuir slots equitativamente entre productos
                $slotsForThis = ($i === count($productosInput) - 1) 
                    ? $slots->count() - $slotIndex  // último producto toma el resto
                    : $slotsPerProduct;
                
                $cantidadPorSlot = $slotsForThis > 0 ? intdiv($prodData['cantidad'], $slotsForThis) : 0;
                $resto = $slotsForThis > 0 ? $prodData['cantidad'] % $slotsForThis : 0;
                $displaysPorSlot = $slotsForThis > 0 ? intdiv($prodData['displays'] ?? 0, $slotsForThis) : 0;
                $displaysResto = $slotsForThis > 0 ? ($prodData['displays'] ?? 0) % $slotsForThis : 0;
                
                for ($j = 0; $j < $slotsForThis && $slotIndex < $slots->count(); $j++) {
                    $slot = $slots[$slotIndex];
                    $slot->catalogo_producto_id = $prodData['catalogo_producto_id'];
                    $slot->cantidad_actual = $cantidadPorSlot + ($j === 0 ? $resto : 0);
                    $slot->displays_puestos = $displaysPorSlot + ($j === 0 ? $displaysResto : 0);
                    $slot->save();
                    $slotIndex++;
                }
            }
            
            // Vaciar slots sobrantes
            for (; $slotIndex < $slots->count(); $slotIndex++) {
                $slot = $slots[$slotIndex];
                $slot->catalogo_producto_id = null;
                $slot->cantidad_actual = 0;
                $slot->displays_puestos = 0;
                $slot->save();
            }
        });

        // Devolver datos actualizados del piso
        $slotsActualizados = MaquinaSlot::with('producto')
            ->where('maquina_id', $maquina->id)
            ->where('seccion', $request->seccion)
            ->where('piso', $request->piso)
            ->get();

        $consolidado = [];
        foreach ($slotsActualizados as $slot) {
            $prodId = $slot->catalogo_producto_id ?? 'empty';
            if (!isset($consolidado[$prodId])) {
                $consolidado[$prodId] = [
                    'producto_nombre' => $slot->producto ? $slot->producto->nombre : null,
                    'catalogo_producto_id' => $slot->catalogo_producto_id,
                    'cantidad_total' => 0,
                    'capacidad_total' => 0,
                    'displays_puestos' => 0,
                ];
            }
            $consolidado[$prodId]['cantidad_total'] += $slot->cantidad_actual;
            $consolidado[$prodId]['capacidad_total'] += $slot->capacidad_maxima;
            $consolidado[$prodId]['displays_puestos'] += $slot->displays_puestos;
        }

        return response()->json([
            'success' => true,
            'message' => 'Piso actualizado correctamente',
            'productos' => array_values($consolidado),
        ]);
    }

    public function assignProduct(Request $request, $slotId)
    {
        $request->validate([
            'catalogo_producto_id' => 'required|exists:catalogo_productos,id'
        ]);

        $slot = MaquinaSlot::findOrFail($slotId);
        $slot->catalogo_producto_id = $request->catalogo_producto_id;
        $slot->save();

        return back()->with('success', 'Producto asignado a la posición correctamente.');
    }

    public function unassignProduct($slotId)
    {
        $slot = MaquinaSlot::findOrFail($slotId);
        $slot->catalogo_producto_id = null;
        $slot->cantidad_actual = 0;
        $slot->displays_puestos = 0;
        $slot->save();

        return back()->with('success', 'Posición vaciada y desasignada.');
    }

    public function addStock($slotId)
    {
        $slot = MaquinaSlot::findOrFail($slotId);
        
        if (!$slot->catalogo_producto_id) {
            return back()->with('error', 'Debe asignar un producto primero.');
        }

        if ($slot->cantidad_actual < $slot->capacidad_maxima) {
            $slot->cantidad_actual++;
            $slot->save();
            return back()->with('success', 'Stock agregado.');
        }

        return back()->with('error', 'El slot ya está en su capacidad máxima.');
    }

    public function removeStock($slotId)
    {
        $slot = MaquinaSlot::findOrFail($slotId);
        
        if ($slot->cantidad_actual > 0) {
            $slot->cantidad_actual--;
            $slot->save();
            return back()->with('success', 'Stock retirado.');
        }

        return back()->with('error', 'El slot ya está vacío.');
    }

    public function destroy($id)
    {
        $maquina = Maquina::findOrFail($id);
        $maquina->slots()->delete();
        $maquina->delete();

        return redirect()->route('maquinas.index')->with('success', 'Máquina eliminada correctamente.');
    }
}
