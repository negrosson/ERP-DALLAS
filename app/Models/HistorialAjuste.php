<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialAjuste extends Model
{
    protected $fillable = [
        'lote_stock_id',
        'user_id',
        'cantidad_anterior',
        'cantidad_nueva',
        'diferencia',
        'motivo',
        'es_reversion',
        'reversion_de_id',
    ];

    public function lote()
    {
        return $this->belongsTo(LoteStock::class, 'lote_stock_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reversionOriginal()
    {
        return $this->belongsTo(HistorialAjuste::class, 'reversion_de_id');
    }

    public function reversiones()
    {
        return $this->hasMany(HistorialAjuste::class, 'reversion_de_id');
    }
}
