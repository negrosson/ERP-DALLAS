<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaquinaSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'maquina_id',
        'seccion',
        'piso',
        'posicion',
        'catalogo_producto_id',
        'capacidad_maxima',
        'cantidad_actual',
    ];

    public function maquina()
    {
        return $this->belongsTo(Maquina::class);
    }

    public function producto()
    {
        return $this->belongsTo(CatalogoProducto::class, 'catalogo_producto_id');
    }
}
