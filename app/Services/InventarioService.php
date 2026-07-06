<?php

namespace App\Services;

use App\Models\LoteStock;
use Illuminate\Support\Facades\DB;

class InventarioService
{
    /**
     * Descuenta stock siguiendo la lógica FEFO estricta.
     * (First Expired, First Out — el lote que vence primero se descuenta primero)
     *
     * @param  int    $catalogoProductoId  ID del producto en catálogo maestro
     * @param  int    $bodegaId            ID de la bodega
     * @param  float  $cantidad            Cantidad a descontar
     *
     * @throws \RuntimeException Si no hay stock suficiente
     * @return array  Detalle de los lotes afectados
     */
    public function descontarStock(int $catalogoProductoId, int $bodegaId, float $cantidad): array
    {
        return DB::transaction(function () use ($catalogoProductoId, $bodegaId, $cantidad) {
            // Obtener lotes con stock, ordenados FEFO, con lock para evitar race conditions
            $lotes = LoteStock::where('catalogo_producto_id', $catalogoProductoId)
                ->where('bodega_id', $bodegaId)
                ->where('cantidad_disponible', '>', 0)
                ->orderBy('fecha_vencimiento', 'asc')
                ->lockForUpdate()
                ->get();

            $totalDisponible = $lotes->sum('cantidad_disponible');

            if ($totalDisponible < $cantidad) {
                throw new \RuntimeException(
                    "Stock insuficiente. Disponible: {$totalDisponible}, Solicitado: {$cantidad}"
                );
            }

            $restante = $cantidad;
            $lotesAfectados = [];

            foreach ($lotes as $lote) {
                if ($restante <= 0) {
                    break;
                }

                $descontar = min($restante, (float) $lote->cantidad_disponible);
                $lote->cantidad_disponible -= $descontar;
                $lote->save();

                $lotesAfectados[] = [
                    'lote_id' => $lote->id,
                    'cantidad_descontada' => $descontar,
                    'cantidad_restante' => $lote->cantidad_disponible,
                    'fecha_vencimiento' => $lote->fecha_vencimiento->format('Y-m-d'),
                ];

                $restante -= $descontar;
            }

            return $lotesAfectados;
        });
    }

    /**
     * Confirma una recepción, cambia su estado y genera los registros en Lotes de Stock.
     */
    public function confirmarRecepcion(\App\Models\Recepcion $recepcion): void
    {
        DB::transaction(function () use ($recepcion) {
            $recepcion->estado = \App\Enums\EstadoRecepcion::CONFIRMADO->value;
            $recepcion->save();

            foreach ($recepcion->detalles as $detalle) {
                LoteStock::create([
                    'catalogo_producto_id' => $detalle->catalogo_producto_id,
                    'bodega_id' => $recepcion->bodega_id,
                    'recepcion_detalle_id' => $detalle->id,
                    'cantidad_inicial' => $detalle->cantidad,
                    'cantidad_disponible' => $detalle->cantidad,
                    'fecha_elaboracion' => $detalle->fecha_elaboracion,
                    'fecha_vencimiento' => $detalle->fecha_vencimiento ?? now()->addYears(10),
                ]);
            }
        });
    }

    /**
     * Consulta el stock total disponible de un producto en una bodega.
     */
    public function stockDisponible(int $catalogoProductoId, int $bodegaId): float
    {
        return (float) LoteStock::where('catalogo_producto_id', $catalogoProductoId)
            ->where('bodega_id', $bodegaId)
            ->where('cantidad_disponible', '>', 0)
            ->sum('cantidad_disponible');
    }

    /**
     * Consulta el stock total disponible de un producto en todas las bodegas.
     */
    public function stockTotalProducto(int $catalogoProductoId): float
    {
        return (float) LoteStock::where('catalogo_producto_id', $catalogoProductoId)
            ->where('cantidad_disponible', '>', 0)
            ->sum('cantidad_disponible');
    }
}
