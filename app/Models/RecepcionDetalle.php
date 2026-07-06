<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RecepcionDetalle extends Model
{
    use HasFactory;

    protected $table = 'recepcion_detalles';

    protected $fillable = [
        'recepcion_id',
        'catalogo_producto_id',
        'codigo_proveedor_usado',
        'cantidad',
        'precio_unitario',
        'fecha_elaboracion',
        'fecha_vencimiento',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'fecha_elaboracion' => 'date',
        'fecha_vencimiento' => 'date',
    ];

    // ── Relaciones ──────────────────────────────────────────────

    public function recepcion(): BelongsTo
    {
        return $this->belongsTo(Recepcion::class);
    }

    public function catalogoProducto(): BelongsTo
    {
        return $this->belongsTo(CatalogoProducto::class);
    }

    public function loteStock(): HasOne
    {
        return $this->hasOne(LoteStock::class);
    }

    // ── Helpers ─────────────────────────────────────────────────

    public function subtotal(): float
    {
        return (float) $this->cantidad * (float) $this->precio_unitario;
    }
}
