<?php

namespace App\Models;

use App\Enums\EstadoRecepcion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\SoftDeletes;

class Recepcion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'recepciones';

    protected $fillable = [
        'proveedor_id',
        'bodega_id',
        'user_id',
        'numero_factura',
        'fecha_recepcion',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_recepcion' => 'date',
        'estado' => EstadoRecepcion::class,
    ];

    // ── Relaciones ──────────────────────────────────────────────

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function bodega(): BelongsTo
    {
        return $this->belongsTo(Bodega::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(RecepcionDetalle::class);
    }

    // ── Helpers ─────────────────────────────────────────────────

    public function esBorrador(): bool
    {
        return $this->estado === EstadoRecepcion::BORRADOR;
    }

    public function estaConfirmada(): bool
    {
        return $this->estado === EstadoRecepcion::CONFIRMADO;
    }

    /**
     * Calcula el total de la recepción sumando (cantidad * precio_unitario) de cada detalle.
     */
    public function totalMonto(): float
    {
        return $this->detalles->sum(fn ($d) => $d->cantidad * $d->precio_unitario);
    }
}
