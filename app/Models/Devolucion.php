<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Devolucion extends Model
{
    /** @use HasFactory<\Database\Factories\DevolucionFactory> */
    use HasFactory;

    protected $fillable = [
        'catalogo_producto_id',
        'bodega_destino_id',
        'cantidad',
        'motivo',
        'fecha_devolucion',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'fecha_devolucion' => 'datetime',
        ];
    }

    public function producto()
    {
        return $this->belongsTo(CatalogoProducto::class, 'catalogo_producto_id');
    }

    public function bodega()
    {
        return $this->belongsTo(Bodega::class, 'bodega_destino_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
