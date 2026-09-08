<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Proveedor;
use App\Models\Bodega;
use App\Models\CatalogoProducto;
use App\Models\MapeoCodigo;
use App\Models\Recepcion;
use App\Models\RecepcionDetalle;
use App\Services\InventarioService;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@controlerp.cl'],
            ['name' => 'Admin ERP', 'password' => bcrypt('password123')]
        );

        $bodegas = [
            'SALA' => Bodega::firstOrCreate(['codigo' => 'B-SALA'], ['nombre' => 'Sala de Ventas', 'activa' => true, 'es_refrigerada' => false]),
            'FRIO' => Bodega::firstOrCreate(['codigo' => 'B-FRIO'], ['nombre' => 'Cámara de Frío', 'activa' => true, 'es_refrigerada' => true]),
            'CENTRAL' => Bodega::firstOrCreate(['codigo' => 'B-CENTRAL'], ['nombre' => 'Bodega Central', 'activa' => true, 'es_refrigerada' => false]),
        ];

        $proveedoresData = [
            'CCU' => ['rut' => '76.123.456-1', 'nombre' => 'Bebidas CCU', 'email' => 'ventas@ccu.cl'],
            'COCA' => ['rut' => '77.222.333-4', 'nombre' => 'Embonor Coca Cola', 'email' => 'ventas@embonor.cl'],
            'ROTIS' => ['rut' => '78.555.666-2', 'nombre' => 'PF Alimentos (Rotisería)', 'email' => 'pedidos@pf.cl'],
            'CONG' => ['rut' => '79.777.888-3', 'nombre' => 'Frigorífico Pacífico (Congelados)', 'email' => 'ventas@congelados.cl'],
        ];
        
        $proveedores = [];
        foreach ($proveedoresData as $code => $data) {
            $proveedores[$code] = Proveedor::firstOrCreate(['rut' => $data['rut']], [
                'codigo' => 'PROV-' . $code,
                'nombre' => $data['nombre'],
                'activo' => true,
                'contacto_email' => $data['email']
            ]);
        }

        // Leer productos parseados
        $path = "C:\\Users\\ngrtx\\.gemini\\antigravity-ide\\brain\\ad473875-a1fc-450e-9184-f0bba90ae26b\\scratch\\parsed_products.json";
        $productosParseados = json_decode(file_get_contents($path), true);

        // Agruparlos en recepciones para simular lotes
        // Crearemos unas 4 recepciones confirmadas (Activas) con estos productos, 
        // para que tengan las fechas y se muestren en FEFO
        $recepciones = [
            'CCU' => [],
            'COCA' => []
        ];

        foreach ($productosParseados as $p) {
            $provCode = $p['provider']; // 'CCU' o 'COCA'
            $provModel = $proveedores[$provCode];
            
            $sku = $provCode . '-' . Str::slug($p['name']);
            
            $catalogoProducto = CatalogoProducto::firstOrCreate(
                ['sku' => $sku],
                [
                    'nombre' => mb_convert_encoding($p['name'], 'UTF-8', 'auto'),
                    'precio_venta' => rand(1000, 3000),
                    'unidad_medida' => 'UN',
                    'activo' => true,
                    'constante_vencimiento_meses' => 12
                ]
            );

            MapeoCodigo::firstOrCreate([
                'proveedor_id' => $provModel->id,
                'catalogo_producto_id' => $catalogoProducto->id,
            ], [
                'codigo_proveedor' => 'PROV-' . $sku,
                'factor_conversion' => 1
            ]);

            // Parse Dates
            $elab = ($p['elab'] == '—' || $p['elab'] == 'â€”') ? null : Carbon::createFromFormat('d/m/Y', $p['elab']);
            
            if (strlen($p['venc']) == 7) { // format mm/yyyy
                $venc = Carbon::createFromFormat('m/Y', $p['venc'])->endOfMonth();
            } else {
                $venc = Carbon::createFromFormat('d/m/Y', $p['venc']);
            }

            $recepciones[$provCode][] = [
                'prod' => $catalogoProducto,
                'elab' => $elab,
                'venc' => $venc
            ];
        }

        $inventarioService = new InventarioService();

        // Crear Recepciones Confirmadas
        foreach (['CCU', 'COCA'] as $provCode) {
            $chunks = array_chunk($recepciones[$provCode], 40); // 40 prod por factura
            foreach ($chunks as $idx => $chunk) {
                $rec = Recepcion::create([
                    'proveedor_id' => $proveedores[$provCode]->id,
                    'bodega_id' => $bodegas['SALA']->id,
                    'user_id' => $user->id,
                    'numero_factura' => 'FACT-' . $provCode . '-' . rand(100, 999),
                    'fecha_recepcion' => now()->subDays(rand(1, 30)),
                    'estado' => \App\Enums\EstadoRecepcion::BORRADOR->value
                ]);

                foreach ($chunk as $item) {
                    $rec->detalles()->create([
                        'catalogo_producto_id' => $item['prod']->id,
                        'codigo_proveedor_usado' => 'PROV-' . $item['prod']->sku,
                        'cantidad' => rand(10, 50),
                        'precio_unitario' => $item['prod']->precio_venta * 0.6,
                        'fecha_elaboracion' => $item['elab'],
                        'fecha_vencimiento' => $item['venc']
                    ]);
                }
                $inventarioService->confirmarRecepcion($rec);
            }
        }

        // Crear Recepciones PENDIENTE_FECHA (sin fechas)
        // Para demostrar la máquina de estados y el Dashboard
        $recPendiente = Recepcion::create([
            'proveedor_id' => $proveedores['CCU']->id,
            'bodega_id' => $bodegas['CENTRAL']->id,
            'user_id' => $user->id,
            'numero_factura' => 'FACT-CIEGO-001',
            'fecha_recepcion' => now(),
            'estado' => \App\Enums\EstadoRecepcion::PENDIENTE_FECHA->value
        ]);

        // Tomar 5 productos al azar
        $productosAzar = CatalogoProducto::inRandomOrder()->limit(5)->get();
        foreach ($productosAzar as $prod) {
            $recPendiente->detalles()->create([
                'catalogo_producto_id' => $prod->id,
                'codigo_proveedor_usado' => 'PROV-CIEGO',
                'cantidad' => rand(10, 20),
                'precio_unitario' => 1000,
                'fecha_elaboracion' => null,
                'fecha_vencimiento' => null
            ]);
        }
        
        // Confirmar la recepción. Como el inventarioService creará Lotes, 
        // estos lotes tendrán NULL en fecha_vencimiento, y no aparecerán en FEFO aún.
        $inventarioService->confirmarRecepcion($recPendiente);
        
        $recPendiente->update(['estado' => \App\Enums\EstadoRecepcion::PENDIENTE_FECHA->value]);

        $this->call(CatalogoCcuCocaSeeder::class);
        $this->call(CervezaExcelSeeder::class);

        $this->command->info('Seeding completado con productos reales y recepciones ciegas.');
    }
}
