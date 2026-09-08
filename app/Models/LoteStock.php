<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoteStock extends Model
{
    use HasFactory;

    protected $table = 'lotes_stock';

    protected $fillable = [
        'catalogo_producto_id',
        'bodega_id',
        'recepcion_detalle_id',
        'cantidad_inicial',
        'cantidad_disponible',
        'fecha_elaboracion',
        'fecha_vencimiento',
    ];

    protected $casts = [
        'cantidad_inicial' => 'decimal:2',
        'cantidad_disponible' => 'decimal:2',
        'fecha_elaboracion' => 'date',
        'fecha_vencimiento' => 'date',
    ];

    // ── Relaciones ──────────────────────────────────────────────

    public function producto(): BelongsTo
    {
        return $this->belongsTo(CatalogoProducto::class, 'catalogo_producto_id');
    }

    public function bodega(): BelongsTo
    {
        return $this->belongsTo(Bodega::class);
    }

    public function recepcionDetalle(): BelongsTo
    {
        return $this->belongsTo(RecepcionDetalle::class);
    }

    // ── Scopes ──────────────────────────────────────────────────

    /**
     * Lotes con stock disponible, ordenados FEFO (vence primero → sale primero).
     */
    public function scopeDisponibleFefo($query)
    {
        return $query->where('cantidad_disponible', '>', 0)
            ->whereNotNull('fecha_vencimiento')
            ->orderBy('fecha_vencimiento', 'asc');
    }

    /**
     * Lotes próximos a vencer (dentro de los próximos N días).
     */
    public function scopeProximosAVencer($query, int $dias = 30)
    {
        return $query->where('cantidad_disponible', '>', 0)
            ->where('fecha_vencimiento', '<=', now()->addDays($dias))
            ->orderBy('fecha_vencimiento', 'asc');
    }

    /**
     * Lotes ya vencidos con stock disponible.
     */
    public function scopeVencidos($query)
    {
        return $query->where('cantidad_disponible', '>', 0)
            ->where('fecha_vencimiento', '<', now()->startOfDay());
    }

    // ── Helpers ─────────────────────────────────────────────────

    public function estaVencido(): bool
    {
        return $this->fecha_vencimiento !== null && $this->fecha_vencimiento->lt(now()->startOfDay());
    }

    public function tieneStock(): bool
    {
        return $this->cantidad_disponible > 0;
    }
}
