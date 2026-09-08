<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogoProducto extends Model
{
    use HasFactory;

    protected $table = 'catalogo_productos';

    protected $fillable = [
        'sku',
        'nombre',
        'descripcion',
        'formato',
        'capacidad',
        'unidad_medida',
        'precio_compra_ref',
        'precio_venta',
        'constante_vencimiento_meses',
        'dias_alerta_vencimiento',
        'activo',
    ];

    protected $casts = [
        'precio_compra_ref' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'constante_vencimiento_meses' => 'integer',
        'activo' => 'boolean',
    ];

    // ── Relaciones ──────────────────────────────────────────────

    public function mapeoCodigos(): HasMany
    {
        return $this->hasMany(MapeoCodigo::class);
    }

    public function recepcionDetalles(): HasMany
    {
        return $this->hasMany(RecepcionDetalle::class);
    }

    public function lotesStock(): HasMany
    {
        return $this->hasMany(LoteStock::class);
    }

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // ── Helpers ─────────────────────────────────────────────────

    /**
     * Indica si este producto tiene autocalculo de vencimiento.
     */
    public function tieneAutocalculoVencimiento(): bool
    {
        return $this->constante_vencimiento_meses !== null
            && $this->constante_vencimiento_meses > 0;
    }
}
