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
        
        // Calculate fill percentage and stats per machine
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
            // Asignar a la primera bodega activa por defecto (o ajustar si se requiere seleccionar)
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
        
        // Agrupar slots por seccion y luego por piso
        $estructura = [];
        foreach ($maquina->slots as $slot) {
            $estructura[$slot->seccion][$slot->piso][] = $slot;
        }

        // Ordenar posiciones dentro de cada piso
        foreach ($estructura as $sec => &$pisos) {
            foreach ($pisos as $piso => &$slots) {
                usort($slots, function($a, $b) {
                    return $a->posicion <=> $b->posicion;
                });
            }
            ksort($pisos);
        }
        ksort($estructura);
        
        // All products for assigning to slots
        $productos = CatalogoProducto::orderBy('nombre')->get();

        return view('maquinas.show', compact('maquina', 'estructura', 'productos'));
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
        $slot->cantidad_actual = 0; // Empty the slot if unassigned
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
        
        // Las relaciones (slots) se eliminan en cascada si la migración tiene onDelete('cascade'), 
        // pero por seguridad lo hacemos manual si no lo tiene.
        $maquina->slots()->delete();
        $maquina->delete();

        return redirect()->route('maquinas.index')->with('success', 'Máquina eliminada correctamente.');
    }
}
