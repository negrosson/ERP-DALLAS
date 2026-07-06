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
}
