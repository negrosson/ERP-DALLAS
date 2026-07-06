<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class ProveedorSeeder extends Seeder
{
    public function run(): void
    {
        $proveedores = [
            [
                'codigo' => 'CCU',
                'nombre' => 'Compañía Cervecerías Unidas S.A.',
                'rut' => '90.413.000-1',
                'contacto_nombre' => 'Representante CCU',
                'contacto_telefono' => '+56 2 2427 3000',
                'contacto_email' => 'contacto@ccu.cl',
                'direccion' => 'Av. Vitacura 2670, Las Condes, Santiago',
                'activo' => true,
            ],
            [
                'codigo' => 'EMB',
                'nombre' => 'Coca-Cola Embonor S.A.',
                'rut' => '93.281.000-K',
                'contacto_nombre' => 'Representante Embonor',
                'contacto_telefono' => '+56 2 2350 0000',
                'contacto_email' => 'contacto@embonor.cl',
                'direccion' => 'Av. Matta 245, Arica',
                'activo' => true,
            ],
            [
                'codigo' => 'DMK',
                'nombre' => 'DIMAK Distribuidora',
                'rut' => '76.123.456-7',
                'contacto_nombre' => 'Representante DIMAK',
                'contacto_telefono' => '+56 9 8765 4321',
                'contacto_email' => 'ventas@dimak.cl',
                'direccion' => 'Zona Industrial Norte, Antofagasta',
                'activo' => true,
            ],
        ];

        foreach ($proveedores as $data) {
            Proveedor::updateOrCreate(
                ['codigo' => $data['codigo']],
                $data
            );
        }
    }
}
