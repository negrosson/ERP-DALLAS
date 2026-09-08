<?php

namespace App\Services;

use App\Models\CatalogoProducto;
use App\Models\LoteStock;
use App\Models\MapeoCodigo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DescuentoFefoService
{
    /**
     * Procesa las ventas diarias descontando el stock mediante la lógica FEFO.
     *
     * @param array $ventas Array asociativo con la estructura ['codigo_barras' => cantidad_vendida]
     * @param int|null $bodegaId ID de la bodega de donde descontar stock (opcional)
     * @return void
     */
    public function procesarVentasDiarias(array $ventas, ?int $bodegaId = null): void
    {
        if (empty($ventas)) {
            return;
        }

        DB::transaction(function () use ($ventas, $bodegaId) {
            $codigos = array_keys($ventas);

            // Optimización 1: Cargar en memoria todos los productos que tengan el SKU directo
            $productosPorSku = CatalogoProducto::whereIn('sku', $codigos)->get()->keyBy('sku');

            foreach ($ventas as $codigoBarras => $cantidadVendida) {
                // Validar que la cantidad vendida sea positiva
                if ($cantidadVendida <= 0) {
                    continue;
                }

                // Obtener el producto, primero por SKU directo
                $producto = $productosPorSku->get($codigoBarras);

                // Si no se encuentra por SKU, intentar buscar por MapeoCodigo (código del proveedor)
                if (!$producto) {
                    $mapeo = MapeoCodigo::where('codigo_proveedor', $codigoBarras)->with('catalogoProducto')->first();
                    if ($mapeo && $mapeo->catalogoProducto) {
                        $producto = $mapeo->catalogoProducto;
                    }
                }

                if (!$producto) {
                    Log::warning("FEFO: Producto con código {$codigoBarras} no encontrado en el sistema. Venta ignorada.");
                    continue;
                }

                // Optimización 2: Consultar solo lotes con stock, ordenados FEFO, con bloqueo de transacción
                // El CASE WHEN asegura compatibilidad cruzada (MySQL, SQLite, PostgreSQL) para mandar los NULL al final.
                $query = LoteStock::where('catalogo_producto_id', $producto->id)
                    ->where('cantidad_disponible', '>', 0);

                if ($bodegaId) {
                    $query->where('bodega_id', $bodegaId);
                }

                $lotes = $query->orderByRaw('CASE WHEN fecha_vencimiento IS NULL THEN 1 ELSE 0 END ASC')
                    ->orderBy('fecha_vencimiento', 'asc')
                    ->lockForUpdate() // Evita race conditions si hay múltiples cajas vendiendo simultáneamente
                    ->get();

                $pendienteADescontar = (float) $cantidadVendida;

                // Cascada Simple FEFO
                foreach ($lotes as $lote) {
                    if ($pendienteADescontar <= 0) {
                        break;
                    }

                    $disponible = (float) $lote->cantidad_disponible;

                    if ($pendienteADescontar <= $disponible) {
                        // El lote actual puede absorber la cantidad pendiente
                        $lote->cantidad_disponible = $disponible - $pendienteADescontar;
                        $lote->save();
                        $pendienteADescontar = 0;
                        break; 
                    } else {
                        // El lote actual no da abasto, se vacía y se descuenta lo posible
                        $lote->cantidad_disponible = 0;
                        $lote->save();
                        $pendienteADescontar -= $disponible;
                    }
                }

                // Requerimiento: No implementar lógica de inventario negativo. 
                // Si el loop termina y $pendienteADescontar > 0, significa que se vendió más de lo que el sistema
                // tenía registrado. Simplemente ignoramos ese déficit (el stock de todos los lotes quedó en 0).
                if ($pendienteADescontar > 0) {
                    Log::info("FEFO: Se vendieron {$pendienteADescontar} unidades de {$producto->nombre} sin respaldo de stock en sistema.");
                }
            }
        });
    }
}
