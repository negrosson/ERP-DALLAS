<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MapeoCodigo extends Model
{
    use HasFactory;

    protected $table = 'mapeo_codigos';

    protected $fillable = [
        'proveedor_id',
        'catalogo_producto_id',
        'codigo_proveedor',
        'descripcion_proveedor',
    ];

    // ── Relaciones ──────────────────────────────────────────────

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(CatalogoProducto::class, 'catalogo_producto_id');
    }

    // ── Helpers ─────────────────────────────────────────────────

    /**
     * Resuelve un código de proveedor al producto del catálogo maestro.
     */
    public static function resolverProducto(int $proveedorId, string $codigoProveedor): ?CatalogoProducto
    {
        $mapeo = static::where('proveedor_id', $proveedorId)
            ->where('codigo_proveedor', $codigoProveedor)
            ->first();

        return $mapeo?->catalogoProducto;
    }
}
