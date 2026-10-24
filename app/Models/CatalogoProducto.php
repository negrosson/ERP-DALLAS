<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogoProducto extends Model
{
    use HasFactory;

    protected $table = 'catalogo_productos';

    protected $fillable = [
        'sku',
        'nombre',
        'descripcion',
        'formato',
        'capacidad',
        'unidad_medida',
        'precio_compra_ref',
        'precio_venta',
        'constante_vencimiento_meses',
        'dias_alerta_vencimiento',
        'activo',
    ];

    protected $casts = [
        'precio_compra_ref' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'constante_vencimiento_meses' => 'integer',
        'activo' => 'boolean',
    ];

    protected $appends = [
        'marca',
    ];

    // ── Relaciones ──────────────────────────────────────────────

    public function mapeoCodigos(): HasMany
    {
        return $this->hasMany(MapeoCodigo::class);
    }

    public function recepcionDetalles(): HasMany
    {
        return $this->hasMany(RecepcionDetalle::class);
    }

    public function lotesStock(): HasMany
    {
        return $this->hasMany(LoteStock::class);
    }

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // ── Helpers ─────────────────────────────────────────────────

    /**
     * Infiere la marca comercial a partir del nombre o SKU.
     */
    public function getMarcaAttribute(): string
    {
        $nombre = strtoupper($this->nombre ?? '');
        $marcas = [
            'COCA-COLA' => 'Coca-Cola',
            'COCA COLA' => 'Coca-Cola',
            'FANTA' => 'Fanta',
            'SPRITE' => 'Sprite',
            'VITAL' => 'Vital',
            'AQUARIUS' => 'Aquarius',
            'ACUARIOS' => 'Aquarius',
            'BENEDICTINO' => 'Benedictino',
            'MONSTER' => 'Monster',
            'POWERADE' => 'Powerade',
            'ANDINA DEL VALLE' => 'Andina del Valle',
            'DEL VALLE' => 'Andina del Valle',
            'NORDIC' => 'Nordic',
            'KAPO' => 'Kapo',
            'FUZE TEA' => 'Fuze Tea',
            'CRISTAL' => 'Cristal',
            'ESCUDO' => 'Escudo',
            'ROYAL GUARD' => 'Royal Guard',
            'ROYAL' => 'Royal Guard',
            'HEINEKEN' => 'Heineken',
            'AUSTRAL' => 'Austral',
            'KUNSTMANN' => 'Kunstmann',
            'CORONA' => 'Corona',
            'BUDWEISER' => 'Budweiser',
            'BECKER' => 'Becker',
            'STELLA ARTOIS' => 'Stella Artois',
            'STELLA' => 'Stella Artois',
            'COORS' => 'Coors',
            'SOL' => 'Sol',
            'CUSQUEÑA' => 'Cusqueña',
            'CUSQUENA' => 'Cusqueña',
            'BÁLTICA' => 'Báltica',
            'BALTICA' => 'Báltica',
            'BLUE MOON' => 'Blue Moon',
            'MILLER' => 'Miller',
            'CACHANTUN' => 'Cachantún',
            'CACHANTÚN' => 'Cachantún',
            'PEPSI' => 'Pepsi',
            'BILZ' => 'Bilz',
            'PAP' => 'Pap',
            'KEM' => 'Kem',
            'LIMONSODA' => 'Limonsoda',
            'LIMON SODA' => 'Limonsoda',
            'CRUSH' => 'Crush',
            'CANADA DRY' => 'Canada Dry',
            'GATORADE' => 'Gatorade',
            'RED BULL' => 'Red Bull',
            'MAS' => 'Mas',
            'WATT' => 'Watt\'s',
        ];

        foreach ($marcas as $key => $val) {
            if (str_contains($nombre, $key)) {
                return $val;
            }
        }

        $partes = preg_split('/\s+/', trim($this->nombre ?? ''));
        return !empty($partes[0]) ? ucfirst(strtolower($partes[0])) : 'Sin Marca';
    }

    /**
     * Indica si este producto tiene autocalculo de vencimiento.
     */
    public function tieneAutocalculoVencimiento(): bool
    {
        return $this->constante_vencimiento_meses !== null
            && $this->constante_vencimiento_meses > 0;
    }
}
