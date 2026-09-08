<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merma extends Model
{
    /** @use HasFactory<\Database\Factories\MermaFactory> */
    use HasFactory;

    protected $fillable = [
        'catalogo_producto_id',
        'bodega_id',
        'cantidad',
        'motivo',
        'fecha_merma',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'fecha_merma' => 'datetime',
        ];
    }

    public function producto()
    {
        return $this->belongsTo(CatalogoProducto::class, 'catalogo_producto_id');
    }

    public function bodega()
    {
        return $this->belongsTo(Bodega::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
