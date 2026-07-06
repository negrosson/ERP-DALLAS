<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Bodega;
use App\Models\CatalogoProducto;
use Illuminate\Http\Request;
use App\Services\InventarioService;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function index()
    {
        $ventas = Venta::with(['bodega', 'user'])->latest()->paginate(10);
        return view('ventas.index', compact('ventas'));
    }

    public function create()
    {
        $bodegas = Bodega::where('activa', true)->get();
        return view('ventas.create', compact('bodegas'));
    }

    public function store(Request $request, InventarioService $inventarioService)
    {
        $request->validate([
            'bodega_id' => 'required|exists:bodegas,id',
            'archivo_ventas' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('archivo_ventas');
        $csvData = file_get_contents($file->getRealPath());
        $rows = array_map('str_getcsv', explode("\n", $csvData));
        $header = array_shift($rows);

        // Validar formato del CSV
        $skuIndex = array_search('sku', array_map('strtolower', $header));
        $qtyIndex = array_search('cantidad', array_map('strtolower', $header));

        if ($skuIndex === false || $qtyIndex === false) {
            return back()->with('error', 'El archivo CSV debe contener las columnas "sku" y "cantidad".');
        }

        try {
            DB::beginTransaction();

            $venta = Venta::create([
                'fecha_venta' => now(),
                'total' => 0,
                'origen_archivo' => $file->getClientOriginalName(),
                'bodega_id' => $request->bodega_id,
                'user_id' => auth()->id(),
            ]);

            $totalVenta = 0;
            $errores = [];

            foreach ($rows as $index => $row) {
                if (empty(trim(implode('', $row)))) continue; // Saltar filas vacías

                $sku = trim($row[$skuIndex]);
                $cantidad = (float) trim($row[$qtyIndex]);

                if ($cantidad <= 0) continue;

                $producto = CatalogoProducto::where('sku', $sku)->first();

                if (!$producto) {
                    $errores[] = "Línea " . ($index + 2) . ": Producto con SKU '{$sku}' no encontrado en el catálogo.";
                    continue;
                }

                // Intentar descontar stock
                try {
                    $lotesAfectados = $inventarioService->descontarStock($producto->id, $request->bodega_id, $cantidad);
                    
                    // Precio de venta (idealmente del CSV, por ahora usamos el del catálogo)
                    $precioUnitario = $producto->precio_venta > 0 ? $producto->precio_venta : 1000; // Placeholder
                    $subtotal = $cantidad * $precioUnitario;

                    $venta->detalles()->create([
                        'catalogo_producto_id' => $producto->id,
                        'cantidad' => $cantidad,
                        'precio_unitario' => $precioUnitario,
                        'subtotal' => $subtotal,
                    ]);

                    $totalVenta += $subtotal;

                } catch (\RuntimeException $e) {
                    $errores[] = "Línea " . ($index + 2) . ": " . $e->getMessage() . " (SKU: {$sku})";
                }
            }

            if (!empty($errores)) {
                DB::rollBack();
                return back()->with('error', 'Se encontraron errores en el archivo. No se procesó ninguna venta.')->with('errores_csv', $errores);
            }

            $venta->update(['total' => $totalVenta]);
            
            DB::commit();
            return redirect()->route('ventas.index')->with('success', 'Ventas importadas y stock deducido exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error inesperado: ' . $e->getMessage());
        }
    }

    public function show(Venta $venta)
    {
        $venta->load(['bodega', 'user', 'detalles.producto']);
        return view('ventas.show', compact('venta'));
    }
}
