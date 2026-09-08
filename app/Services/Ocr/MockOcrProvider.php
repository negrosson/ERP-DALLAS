<?php

namespace App\Services\Ocr;

use App\Contracts\OcrProviderInterface;
use Illuminate\Http\UploadedFile;

class MockOcrProvider implements OcrProviderInterface
{
    public function extractInvoiceData(UploadedFile $file, string $proveedorNombre): array
    {
        // Simular latencia de red e inferencia de IA (2 segundos)
        sleep(2);

        $proveedorNombre = strtolower($proveedorNombre);

        if (str_contains($proveedorNombre, 'ccu')) {
            return [
                [
                    'codigo' => '870400',
                    'descripcion' => 'RED BULL TRADIC 4PFx6-LAT250',
                    'cantidad' => 3,
                    'precio_unitario' => 24844,
                    'match' => true 
                ],
                [
                    'codigo' => '870402',
                    'descripcion' => 'RED BULL TRADIC 12PC-LATA473CC',
                    'cantidad' => 1,
                    'precio_unitario' => 22008,
                    'match' => true
                ],
                [
                    'codigo' => '870837',
                    'descripcion' => 'RB SUMMER ACAI 24PF-LAT250',
                    'cantidad' => 2,
                    'precio_unitario' => 24844,
                    'match' => true
                ],
                [
                    'codigo' => '451084',
                    'descripcion' => 'KUNSTM-MIEL VNR500CCX12-TC',
                    'cantidad' => 1,
                    'precio_unitario' => 17292,
                    'match' => false 
                ],
                [
                    'codigo' => '9999',
                    'descripcion' => 'Flete de Mercaderías',
                    'cantidad' => 1,
                    'precio_unitario' => 10389,
                    'match' => false
                ]
            ];
        }

        if (str_contains($proveedorNombre, 'coca') || str_contains($proveedorNombre, 'embonor')) {
            return [
                [
                    'codigo' => '1978',
                    'descripcion' => 'MONSTER MANGO LOCO X06 LATA 473CC',
                    'cantidad' => 4,
                    'precio_unitario' => 4993,
                    'match' => true
                ],
                [
                    'codigo' => '1963',
                    'descripcion' => 'MONSTER ENERGY X06 473 CC',
                    'cantidad' => 4,
                    'precio_unitario' => 4701,
                    'match' => true
                ],
                [
                    'codigo' => '0377',
                    'descripcion' => 'COCA COLA SIN AZUCAR X06 PET 1.500 CC',
                    'cantidad' => 1,
                    'precio_unitario' => 7267,
                    'match' => true
                ],
                [
                    'codigo' => '0354',
                    'descripcion' => 'COCA COLA LIGTH X06 PET 3000 CC',
                    'cantidad' => 1,
                    'precio_unitario' => 11098,
                    'match' => true
                ]
            ];
        }

        // Genérico si el proveedor es otro
        return [
            [
                'codigo' => 'GEN-01',
                'descripcion' => 'Producto Extraído Genérico (Simulación)',
                'cantidad' => 10,
                'precio_unitario' => 1500,
                'match' => false
            ]
        ];
    }
}
