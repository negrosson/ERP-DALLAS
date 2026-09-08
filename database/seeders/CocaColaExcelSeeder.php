<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CatalogoProducto;
use App\Models\Recepcion;
use App\Models\RecepcionDetalle;
use App\Models\LoteStock;
use App\Models\Bodega;
use App\Models\Proveedor;
use App\Enums\EstadoRecepcion;

class CocaColaExcelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = file_get_contents(base_path('coca_data.json'));
        $data = json_decode($json, true);
        
        $proveedor = Proveedor::firstOrCreate(['codigo' => 'COCA-COLA'], ['nombre' => 'Coca-Cola Company', 'rut' => '80.000.000-0', 'contacto_nombre' => 'Distribuidor']);
        $bodega = Bodega::firstOrCreate(['nombre' => 'Bodega Coca-Cola'], ['codigo' => 'B-COCA', 'activa' => true]);
        
        // Crear una recepción para estos ingresos
        $recepcion = Recepcion::create([
            'proveedor_id' => $proveedor->id,
            'bodega_id' => $bodega->id,
            'user_id' => 1,
            'numero_factura' => 'CARGA-EXCEL-' . time(),
            'fecha_recepcion' => now(),
            'observaciones' => 'Carga inicial desde excel inventario_coca_cola_completo.xlsx',
            'estado' => EstadoRecepcion::CONFIRMADO->value,
        ]);
        
        foreach($data as $row) {
            $marca = $row['Marca'];
            $nombre = $row['Producto'];
            if($nombre == 'Variedad no especificada' || str_contains($nombre, 'Producto(s)')) continue;
            
            $formato = $row['Formato'];
            $unidades = floatval($row['Total unidades']);
            
            if(!$unidades || $unidades <= 0) continue;
            
            $sku = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $marca), 0, 3) . '-' . substr(md5($nombre . $formato), 0, 4) . '-' . str_replace([' ', ','], '', $formato));
            
            $producto = CatalogoProducto::firstOrCreate(
                ['nombre' => $nombre . ' ' . $formato],
                [
                    'sku' => $sku,
                    'descripcion' => $marca . ' ' . $row['Presentación'],
                    'unidad_medida' => 'UN',
                    'precio_venta' => 1000,
                    'constante_vencimiento_meses' => 6,
                    'activo' => true
                ]
            );
            
            $fecha_vencimiento = null;
            if ($row['Vencimiento']) {
                if (is_numeric($row['Vencimiento'])) {
                    $fecha_vencimiento = date('Y-m-d', $row['Vencimiento'] / 1000);
                } else if (is_string($row['Vencimiento'])) {
                    if (str_contains($row['Vencimiento'], '/')) {
                        $parts = explode('/', $row['Vencimiento']);
                        if (count($parts) == 2) {
                            $fecha_vencimiento = $parts[1] . '-' . $parts[0] . '-01';
                        } else if (count($parts) == 3) {
                            $fecha_vencimiento = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                        }
                    } else if (preg_match('/^\d{4}-\d{2}-\d{2}/', $row['Vencimiento'])) {
                        $fecha_vencimiento = substr($row['Vencimiento'], 0, 10);
                    }
                }
            }
            
            // Create detalle
            $detalle = RecepcionDetalle::create([
                'recepcion_id' => $recepcion->id,
                'catalogo_producto_id' => $producto->id,
                'codigo_proveedor_usado' => $sku,
                'cantidad' => $unidades,
                'fecha_vencimiento' => $fecha_vencimiento,
                'precio_unitario' => 500
            ]);
            
            // Create lote
            LoteStock::create([
                'catalogo_producto_id' => $producto->id,
                'bodega_id' => $bodega->id,
                'recepcion_detalle_id' => $detalle->id,
                'cantidad_inicial' => $unidades,
                'cantidad_disponible' => $unidades,
                'fecha_vencimiento' => $detalle->fecha_vencimiento,
                'fecha_elaboracion' => null
            ]);
        }
    }
}
