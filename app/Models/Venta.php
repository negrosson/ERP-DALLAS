<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    /** @use HasFactory<\Database\Factories\VentaFactory> */
    use HasFactory;

    protected $fillable = [
        'fecha_venta',
        'total',
        'origen_archivo',
        'bodega_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_venta' => 'datetime',
            'total' => 'decimal:2',
        ];
    }

    public function bodega()
    {
        return $this->belongsTo(Bodega::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function detalles()
    {
        return $this->hasMany(VentaDetalle::class);
    }
}
