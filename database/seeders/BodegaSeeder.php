<?php

namespace Database\Seeders;

use App\Models\Bodega;
use Illuminate\Database\Seeder;

class BodegaSeeder extends Seeder
{
    public function run(): void
    {
        $bodegas = [
            [
                'nombre' => 'Sala de Ventas',
                'codigo' => 'SV',
                'descripcion' => 'Área principal de exposición y venta al público',
                'activa' => true,
            ],
            [
                'nombre' => 'Bodega CCU',
                'codigo' => 'BCCU',
                'descripcion' => 'Bodega refrigerada para productos CCU',
                'activa' => true,
            ],
            [
                'nombre' => 'Bodega General',
                'codigo' => 'BGEN',
                'descripcion' => 'Bodega principal de almacenamiento seco',
                'activa' => true,
            ],
            [
                'nombre' => 'Bodega Refrigerada',
                'codigo' => 'BREF',
                'descripcion' => 'Bodega con cadena de frío para productos perecibles',
                'activa' => true,
            ],
        ];

        foreach ($bodegas as $data) {
            Bodega::updateOrCreate(
                ['codigo' => $data['codigo']],
                $data
            );
        }
    }
}
