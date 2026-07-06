<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bodega extends Model
{
    use HasFactory;

    protected $table = 'bodegas';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    // ── Relaciones ──────────────────────────────────────────────

    public function recepciones(): HasMany
    {
        return $this->hasMany(Recepcion::class);
    }

    public function lotesStock(): HasMany
    {
        return $this->hasMany(LoteStock::class);
    }

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }
}
