<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maquina extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'bodega_id',
    ];

    public function bodega()
    {
        return $this->belongsTo(Bodega::class);
    }

    public function slots()
    {
        return $this->hasMany(MaquinaSlot::class);
    }
}
