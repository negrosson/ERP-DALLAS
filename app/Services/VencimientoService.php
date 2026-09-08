<?php

namespace App\Services;

use App\Models\CatalogoProducto;
use Carbon\Carbon;

class VencimientoService
{
    /**
     * Calcula la fecha de vencimiento a partir de la fecha de elaboración
     * y la constante de meses del producto.
     *
     * @return Carbon|null Null si el producto no tiene constante de vencimiento.
     */
    public function calcular(int $catalogoProductoId, Carbon $fechaElaboracion): ?Carbon
    {
        $producto = CatalogoProducto::findOrFail($catalogoProductoId);

        if (! $producto->tieneAutocalculoVencimiento()) {
            return null;
        }

        return $fechaElaboracion->copy()->addMonths($producto->constante_vencimiento_meses);
    }

    /**
     * Calcula el vencimiento directamente desde la constante de meses.
     * Útil cuando ya tienes el valor sin consultar la BD.
     */
    public function calcularDesdeConstante(Carbon $fechaElaboracion, int $meses): Carbon
    {
        return $fechaElaboracion->copy()->addMonths($meses);
    }

    /**
     * Helper estático para calcular vencimiento a partir de una fecha base y meses.
     */
    public static function calcularVencimiento(Carbon|string $fechaBase, int $meses): Carbon
    {
        $fecha = is_string($fechaBase) ? Carbon::parse($fechaBase) : $fechaBase->copy();
        return $fecha->addMonths($meses);
    }
}
