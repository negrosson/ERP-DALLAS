<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CatalogoProducto;
use App\Models\Proveedor;
use App\Models\Bodega;
use App\Models\User;
use App\Models\Recepcion;
use App\Models\MapeoCodigo;
use App\Services\InventarioService;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CatalogoCcuCocaSeeder extends Seeder
{
    public function run(): void
    {
        $proveedorCcu = Proveedor::where('codigo', 'PROV-CCU')->first();
        $proveedorCoca = Proveedor::where('codigo', 'PROV-COCA')->first();
        $bodega = Bodega::where('codigo', 'B-SALA')->first();
        $user = User::first();

        // 1. Alcoholes y RTD (CCU / Otros)
        $alcoholes = [
            // 24 Meses
            ['nombre' => 'Capel Ice Citrus 275cc', 'constante' => 24, 'elab' => '14/04/2025', 'venc' => null],
            ['nombre' => 'Capel Ice Manzana 275cc', 'constante' => 24, 'elab' => '04/06/2026', 'venc' => null],
            ['nombre' => 'Capel Ice Berries 275cc', 'constante' => 24, 'elab' => '13/04/2026', 'venc' => null],
            ['nombre' => 'Capel Ice Sandía 275cc', 'constante' => 24, 'elab' => '09/01/2026', 'venc' => null],
            // 12 Meses
            ['nombre' => 'Stones Sandía Lata 470ml', 'constante' => 12, 'elab' => '26/09/2025', 'venc' => null],
            ['nombre' => 'Stones Lemon Lata 470ml', 'constante' => 12, 'elab' => '18/03/2026', 'venc' => null],
            ['nombre' => 'Stones Berries Lata 470ml', 'constante' => 12, 'elab' => '05/03/2026', 'venc' => null],
            ['nombre' => 'Stones Maracuya Lata 470ml', 'constante' => 12, 'elab' => '23/02/2026', 'venc' => null],
            ['nombre' => 'Stones Mango Lata 470ml', 'constante' => 12, 'elab' => '21/11/2026', 'venc' => null],
            ['nombre' => 'Mistral Ice Blend', 'constante' => 12, 'elab' => '26/03/2026', 'venc' => null],
            ['nombre' => 'Mistral Ice Super Fruit', 'constante' => 12, 'elab' => '28/03/2026', 'venc' => null],
            ['nombre' => 'Mistral Ice Dry', 'constante' => 12, 'elab' => '20/05/2026', 'venc' => null],
            ['nombre' => 'Mistral Ice Energy', 'constante' => 12, 'elab' => '05/08/2025', 'venc' => null],
            ['nombre' => 'Cristal Lata 470cc Pack', 'constante' => 12, 'elab' => '28/04/2026', 'venc' => null],
            ['nombre' => 'Escudo Lata 470cc', 'constante' => 12, 'elab' => '17/03/2026', 'venc' => null],
            ['nombre' => 'Royal Guard Lata 470cc', 'constante' => 12, 'elab' => '11/02/2026', 'venc' => null],
            ['nombre' => 'Patagonia Hoppy Lager Lata 470cc', 'constante' => 12, 'elab' => '06/12/2025', 'venc' => null],
            ['nombre' => 'Patagonia Red Lager Lata 470cc', 'constante' => 12, 'elab' => '06/10/2026', 'venc' => null],
            ['nombre' => 'Patagonia Black Lager Lata 470cc', 'constante' => 12, 'elab' => '06/03/2026', 'venc' => null],
            ['nombre' => 'Patagonia West Coast Lager Lata 470cc', 'constante' => 12, 'elab' => '27/01/2026', 'venc' => null],
            ['nombre' => 'Escudo Lata 710cc', 'constante' => 12, 'elab' => '04/06/2026', 'venc' => null],
            ['nombre' => 'Cristal Ultra Liviana Lata 710cc', 'constante' => 12, 'elab' => '10/01/2026', 'venc' => null],
            ['nombre' => 'Cristal Clásica Lata 710cc', 'constante' => 12, 'elab' => '25/05/2026', 'venc' => null],
            ['nombre' => 'Royal Guard Lata 710cc', 'constante' => 12, 'elab' => '05/02/2026', 'venc' => null],
            ['nombre' => 'Royal Golden Lager Lata 710cc', 'constante' => 12, 'elab' => '17/03/2026', 'venc' => null],
            ['nombre' => 'Miller Genuine Draft Lata 710cc', 'constante' => 12, 'elab' => '05/04/2027', 'venc' => null],
            ['nombre' => 'Cerveza Sol Vidrio 650ml', 'constante' => 12, 'elab' => '20/03/2026', 'venc' => null],
            ['nombre' => 'Cerveza Royal Vidrio 650ml', 'constante' => 12, 'elab' => '07/04/2026', 'venc' => null],
            ['nombre' => 'Pack Cristal Zero Radler Vidrio x6', 'constante' => 12, 'elab' => '09/02/2026', 'venc' => null],
            ['nombre' => 'Pack Royal Vidrio 355ml x6', 'constante' => 12, 'elab' => '27/04/2026', 'venc' => null],
            ['nombre' => 'Pack Royal Sin Alcohol x6', 'constante' => 12, 'elab' => '03/03/2026', 'venc' => null],
            ['nombre' => 'Pack Sol Vidrio x6', 'constante' => 12, 'elab' => '19/05/2026', 'venc' => null],
            // 9 Meses
            ['nombre' => 'Coors Lata 470ml', 'constante' => 9, 'elab' => '27/01/2026', 'venc' => null],
            ['nombre' => 'Pack Coors Vidrio 355ml', 'constante' => 9, 'elab' => 'Pendiente', 'venc' => null],
            ['nombre' => 'Coors Vidrio 620ml', 'constante' => 9, 'elab' => 'Pendiente', 'venc' => null],
            // 8 Meses
            ['nombre' => 'Austral Torres del Paine 500cc', 'constante' => 8, 'elab' => '04/05/2026', 'venc' => null],
            ['nombre' => 'Austral Calafate 500cc', 'constante' => 8, 'elab' => '22/04/2026', 'venc' => null],
            ['nombre' => 'Austral Lager Verde 500cc', 'constante' => 8, 'elab' => '05/01/2026', 'venc' => null],
            ['nombre' => 'Pack Austral Torres del Paine x4', 'constante' => 8, 'elab' => '11/08/2025', 'venc' => null],
            ['nombre' => 'Pack Austral Calafate x4', 'constante' => 8, 'elab' => '13/11/2025', 'venc' => null],
            // 6 Meses
            ['nombre' => 'Heineken Lata 470cc', 'constante' => 6, 'elab' => '03/03/2026', 'venc' => null],
            ['nombre' => 'Heineken Lata 710cc', 'constante' => 6, 'elab' => '07/01/2026', 'venc' => null],
            // Vencimiento Directo (NULL)
            ['nombre' => 'Dolbek Maqui 500cc', 'constante' => null, 'elab' => null, 'venc' => '06/12/2026'],
            ['nombre' => 'Valdivia Pale Lager 500cc', 'constante' => null, 'elab' => null, 'venc' => '31/01/2027'],
            ['nombre' => 'Dolbek Maqui Lata 470cc', 'constante' => null, 'elab' => null, 'venc' => '08/12/2026'],
            ['nombre' => 'Pack Dolbek Maqui x4', 'constante' => null, 'elab' => null, 'venc' => '07/11/2026'],
            ['nombre' => 'Kunstmann Gran Torobayo 500cc', 'constante' => null, 'elab' => null, 'venc' => '14/02/2027'],
            ['nombre' => 'Kunstmann Miel 500cc', 'constante' => null, 'elab' => null, 'venc' => '28/11/2026'],
            ['nombre' => 'Kunstmann Clásica Torobayo 500cc', 'constante' => null, 'elab' => null, 'venc' => '03/02/2027'],
            ['nombre' => 'Kunstmann Radler Limón Lata 470cc', 'constante' => null, 'elab' => null, 'venc' => '08/08/2026'],
            ['nombre' => 'Kunstmann Pomelo Lata 470cc', 'constante' => null, 'elab' => null, 'venc' => '18/08/2026'],
            ['nombre' => 'Pack Kunstmann Torobayo x4', 'constante' => null, 'elab' => null, 'venc' => '16/01/2027'],
            ['nombre' => 'Pack Kunstmann Arándano x4', 'constante' => null, 'elab' => null, 'venc' => '12/08/2026'],
            ['nombre' => 'Pack Kunstmann Miel x4', 'constante' => null, 'elab' => null, 'venc' => '20/12/2026'],
            ['nombre' => 'Pack Kunstmann Lager Blanc x4', 'constante' => null, 'elab' => null, 'venc' => 'Pendiente'],
            ['nombre' => 'Cerveza Miller Vidrio 650ml', 'constante' => null, 'elab' => null, 'venc' => '10/08/2026'],
            ['nombre' => 'Pack Sol 355ml x6', 'constante' => null, 'elab' => null, 'venc' => '02/11/2026'],
            ['nombre' => 'Leyendas de Origen Pincoya', 'constante' => null, 'elab' => null, 'venc' => '17/09/2026'],
            ['nombre' => 'Leyendas de Origen Trauco', 'constante' => null, 'elab' => null, 'venc' => '13/12/2026'],
            ['nombre' => 'Budweiser Lata/Botella', 'constante' => null, 'elab' => null, 'venc' => '11/12/2026'],
            ['nombre' => 'Cusqueña', 'constante' => null, 'elab' => null, 'venc' => '22/09/2026'],
            ['nombre' => 'Quilmes', 'constante' => null, 'elab' => null, 'venc' => '14/08/2026'],
            ['nombre' => 'Becker', 'constante' => null, 'elab' => null, 'venc' => '19/09/2026'],
            ['nombre' => 'Stella Artois', 'constante' => null, 'elab' => null, 'venc' => '11/09/2026'],
            ['nombre' => 'Corona Lata', 'constante' => null, 'elab' => null, 'venc' => '16/01/2027'],
            ['nombre' => 'Corona Vidrio 620ml', 'constante' => null, 'elab' => null, 'venc' => '27/10/2026'],
            ['nombre' => 'Becker Lata 710cc', 'constante' => null, 'elab' => null, 'venc' => '28/11/2026'],
            ['nombre' => 'Michelob Ultra Lata 710cc', 'constante' => null, 'elab' => null, 'venc' => '30/11/2026'],
            ['nombre' => 'Quilmes Lata 710cc', 'constante' => null, 'elab' => null, 'venc' => '27/01/2027'],
            ['nombre' => 'Budweiser Lata 710cc', 'constante' => null, 'elab' => null, 'venc' => '28/12/2026'],
            ['nombre' => 'Cusqueña Lata 710cc', 'constante' => null, 'elab' => null, 'venc' => '25/09/2026'],
            // Pendientes
            ['nombre' => 'Cerveza 1000cc (Royal Guard, Heineken, Cristal, Escudo)', 'constante' => null, 'elab' => null, 'venc' => 'Pendiente'],
        ];

        // 2. Bebidas Representativas CCU
        $bebidasCcu = [
            ['nombre' => 'Canada Dry Lata 350ml', 'constante' => 12],
            ['nombre' => 'Pap Lata 350ml', 'constante' => 12],
            ['nombre' => 'Kem Xtreme Lata 350ml', 'constante' => 12],
            ['nombre' => 'Agua Purificada Cachantún Sin Gas', 'constante' => 9],
            ['nombre' => 'Agua Purificada Manantial Blanca Sin Gas', 'constante' => 9],
            ['nombre' => 'Pepsi Regular Lata 350ml', 'constante' => 6],
            ['nombre' => 'Crush Lata 350ml', 'constante' => 6],
            ['nombre' => 'Agua Cachantún Normal Gasificada', 'constante' => 6],
            ['nombre' => 'Agua Cachantún Verde', 'constante' => 6],
            ['nombre' => 'Kem Xtreme 1.5L', 'constante' => 6],
            ['nombre' => 'Pepsi Zero Lata 350ml', 'constante' => 4],
            ['nombre' => 'Bilz PET Familiar 1.5L', 'constante' => 4],
            ['nombre' => 'Pap Retornable 2.5L', 'constante' => 4],
            ['nombre' => 'Cachantún Strong', 'constante' => 4],
            ['nombre' => 'Agua Saborizada Cachantún Citrus', 'constante' => 4],
            ['nombre' => 'Pepsi Regular PET 600ml', 'constante' => 3],
            ['nombre' => 'Limón Soda PET 600ml', 'constante' => 3],
            ['nombre' => 'Pepsi Regular PET Familiar 2L', 'constante' => 3],
            ['nombre' => 'Jugos Watt\'s Damasco', 'constante' => null],
            ['nombre' => 'Red Bull Energética', 'constante' => null],
            ['nombre' => 'Cachantún Limonada Jengibre', 'constante' => null],
        ];

        // 3. Bebidas Representativas COCA-COLA
        $bebidasCoca = [
            ['nombre' => 'Agua Purificada Benedictino Natural Sin Gas', 'constante' => 12],
            ['nombre' => 'Coca-Cola Original Botella Express Vidrio 237ml', 'constante' => 9],
            ['nombre' => 'Coca-Cola Original Botella Vidrio 1L', 'constante' => 9],
            ['nombre' => 'Coca-Cola Retornable 2L', 'constante' => 9],
            ['nombre' => 'Sprite Retornable PET 2.5L', 'constante' => 7],
            ['nombre' => 'Fanta Botella Vidrio 1L', 'constante' => 6],
            ['nombre' => 'Coca-Cola Zero Retornable', 'constante' => 6],
            ['nombre' => 'Agua Aquarius Manzana', 'constante' => 6],
            ['nombre' => 'Coca-Cola Original PET Familiar 2L', 'constante' => 4],
            ['nombre' => 'Benedictino Gasificada 1.5L', 'constante' => 4],
            ['nombre' => 'Fanta PET Pequeño 500ml', 'constante' => 3],
            ['nombre' => 'Sprite PET Pequeño 590ml', 'constante' => 3],
            ['nombre' => 'Coca-Cola Lata 350ml', 'constante' => null],
            ['nombre' => 'Gatorade Naranja', 'constante' => null],
            ['nombre' => 'Jugo Guallarauco', 'constante' => null],
            ['nombre' => 'Monster Energy', 'constante' => null],
        ];

        $inventarioService = new InventarioService();

        // Función Helper para crear producto y recepcion
        $crearProductos = function ($lista, $proveedor, $tipo) use ($bodega, $user, $inventarioService) {
            $detallesParaFactura = [];
            
            foreach ($lista as $item) {
                // Crear producto
                $sku = 'SKU-' . $tipo . '-' . Str::slug(substr($item['nombre'], 0, 20)) . rand(100,999);
                
                $producto = CatalogoProducto::firstOrCreate(
                    ['nombre' => $item['nombre']],
                    [
                        'sku' => $sku,
                        'precio_venta' => rand(1500, 4000),
                        'unidad_medida' => 'UN',
                        'activo' => true,
                        'constante_vencimiento_meses' => $item['constante']
                    ]
                );

                MapeoCodigo::firstOrCreate([
                    'proveedor_id' => $proveedor->id,
                    'catalogo_producto_id' => $producto->id,
                ], [
                    'codigo_proveedor' => 'PROV-' . $sku,
                    'factor_conversion' => 1
                ]);

                // Generar fechas
                $elabDate = null;
                $vencDate = null;

                if (isset($item['elab']) && $item['elab'] && $item['elab'] !== 'Pendiente') {
                    $elabDate = Carbon::createFromFormat('d/m/Y', $item['elab'])->format('Y-m-d');
                }
                if (isset($item['venc']) && $item['venc'] && $item['venc'] !== 'Pendiente') {
                    $vencDate = Carbon::createFromFormat('d/m/Y', $item['venc'])->format('Y-m-d');
                }

                // Si es bebida y no se pasaron fechas, generamos unas aleatorias
                if (!isset($item['elab']) && !isset($item['venc']) && $item['nombre'] !== 'Pendiente') {
                    $elabDate = Carbon::now()->subMonths(rand(1, 3))->format('Y-m-d');
                    if ($item['constante']) {
                        $vencDate = Carbon::parse($elabDate)->addMonths($item['constante'])->format('Y-m-d');
                    } else {
                        $vencDate = Carbon::now()->addMonths(rand(2, 6))->format('Y-m-d');
                    }
                }

                // Si son pendientes en el array, los pasamos nulos
                if (isset($item['elab']) && $item['elab'] === 'Pendiente') { $elabDate = null; $vencDate = null; }
                if (isset($item['venc']) && $item['venc'] === 'Pendiente') { $elabDate = null; $vencDate = null; }

                $detallesParaFactura[] = [
                    'prod' => $producto,
                    'elab' => $elabDate,
                    'venc' => $vencDate
                ];
            }

            // Crear una Recepción Confirmada para que se generen los Lotes
            $rec = Recepcion::create([
                'proveedor_id' => $proveedor->id,
                'bodega_id' => $bodega->id,
                'user_id' => $user->id,
                'numero_factura' => 'FACT-NEW-' . rand(1000, 9999),
                'fecha_recepcion' => now()->subDays(rand(1, 15)),
                'estado' => \App\Enums\EstadoRecepcion::BORRADOR->value
            ]);

            foreach ($detallesParaFactura as $det) {
                $rec->detalles()->create([
                    'catalogo_producto_id' => $det['prod']->id,
                    'codigo_proveedor_usado' => 'PROV-' . $det['prod']->sku,
                    'cantidad' => rand(10, 30),
                    'precio_unitario' => rand(500, 2000),
                    'fecha_elaboracion' => $det['elab'],
                    'fecha_vencimiento' => $det['venc']
                ]);
            }

            // Confirmar y crear lotes
            // Ojo: los productos que tengan fechas nulas quedarán con fecha de vencimiento nula, 
            // pero en nuestro sistema real la confirmación fallaría si faltan fechas. 
            // Sin embargo InventarioService podría dejar pasar lotes sin fecha.
            // Para asegurar, forzaremos las fechas faltantes solo para el seeder si el estado se confirma.
            foreach ($rec->detalles as $detalle) {
                if (empty($detalle->fecha_vencimiento)) {
                    // Evitamos que falle si el seeder obliga, los dejamos pendientes
                }
            }

            try {
                $inventarioService->confirmarRecepcion($rec);
            } catch (\Exception $e) {
                // Si la validación prohíbe confirmar sin fechas, lo dejamos en PENDIENTE_FECHA
                $rec->update(['estado' => \App\Enums\EstadoRecepcion::PENDIENTE_FECHA->value]);
            }
        };

        $crearProductos($alcoholes, $proveedorCcu, 'ALC');
        $crearProductos($bebidasCcu, $proveedorCcu, 'BEB-CCU');
        $crearProductos($bebidasCoca, $proveedorCoca, 'BEB-COCA');
    }
}
