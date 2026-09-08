<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Proveedor;
use App\Models\Bodega;
use App\Models\CatalogoProducto;
use App\Models\MapeoCodigo;
use App\Models\Recepcion;
use App\Models\RecepcionDetalle;
use App\Models\LoteStock;
use App\Services\InventarioService;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CervezaExcelSeeder extends Seeder
{
    public function run(): void
    {
        $bodega = Bodega::firstOrCreate(
            ['nombre' => 'Bodega Cervezas'],
            ['codigo' => 'B-CERVEZAS', 'activa' => true, 'es_refrigerada' => true]
        );

        $proveedor = Proveedor::firstOrCreate(
            ['rut' => '77.777.777-7'],
            ['nombre' => 'Distribuidora Cervezas', 'codigo' => 'PRV-CERV']
        );

        $recepcion = Recepcion::create([
            'proveedor_id' => $proveedor->id,
            'user_id' => 1,
            'bodega_id' => $bodega->id,
            'numero_factura' => 'FACT-CERV-001',
            'fecha_recepcion' => now(),
            'estado' => \App\Enums\EstadoRecepcion::CONFIRMADO,
            'observaciones' => 'Inventario inicial cervezas',
        ]);

        $jsonPath = base_path('cervezas.json');
        if (!file_exists($jsonPath)) {
            $this->command->error("No se encontró cervezas.json");
            return;
        }

        $data = json_decode(file_get_contents($jsonPath), true);
        $inventarioService = app(InventarioService::class);

        foreach ($data as $row) {
            $marca = $row['Marca'] ?? 'Desconocida';
            $variedad = $row['Producto / Variedad'] ?? '';
            $nombre = trim("$marca $variedad");
            
            $sku = 'CERV-' . strtoupper(Str::slug($nombre, ''));
            $sku = substr($sku, 0, 20);

            // Parsear cantidad
            $cantidadFisica = $row['Cantidad Física'] ?? '0';
            $unidades = $this->parseQuantity($cantidadFisica);

            // Parsear Vida útil
            $vidaUtilMeses = 0;
            $fefoMode = 'vencimiento_directo';
            if (str_contains(strtolower($row['Vida Útil']), 'meses')) {
                $fefoMode = 'autocalculado';
                $vidaUtilMeses = (int) filter_var($row['Vida Útil'], FILTER_SANITIZE_NUMBER_INT);
            }

            // Crear Producto
            $producto = CatalogoProducto::firstOrCreate(
                ['sku' => $sku],
                [
                    'nombre' => $nombre,
                    'descripcion' => "Cerveza $marca",
                    'constante_vencimiento_meses' => $vidaUtilMeses > 0 ? $vidaUtilMeses : null,
                ]
            );

            $baseMapCode = strtoupper(Str::slug($marca . ' ' . explode(' ', $variedad)[0], ''));
            MapeoCodigo::firstOrCreate([
                'codigo_proveedor' => $baseMapCode,
                'proveedor_id' => $proveedor->id,
            ], [
                'catalogo_producto_id' => $producto->id,
                'descripcion_proveedor' => "$nombre",
                'factor_conversion' => 1,
            ]);

            // Crear Lote/Recepcion Detalle
            if ($unidades > 0) {
                $detalle = RecepcionDetalle::create([
                    'recepcion_id' => $recepcion->id,
                    'catalogo_producto_id' => $producto->id,
                    'codigo_proveedor_usado' => $baseMapCode,
                    'cantidad' => $unidades,
                    'precio_unitario' => 0,
                ]);

                // Fechas
                $fechaVencimiento = null;
                $valFecha = $row['Vencimiento Calculado / Real'];
                if (is_numeric($valFecha)) {
                    $fechaVencimiento = Carbon::createFromTimestampMs($valFecha)->startOfDay();
                } elseif (is_string($valFecha) && str_contains($valFecha, '/')) {
                    // Extract date part from string like "17/01/2026 (Vencida)"
                    preg_match('/(\d{2})\/(\d{2})\/(\d{4})/', $valFecha, $matches);
                    if (count($matches) == 4) {
                        $fechaVencimiento = Carbon::createFromFormat('d/m/Y', $matches[0])->startOfDay();
                    }
                }

                if (!$fechaVencimiento) {
                    $fechaVencimiento = now()->addMonths(12);
                }

                LoteStock::create([
                    'catalogo_producto_id' => $producto->id,
                    'bodega_id' => $bodega->id,
                    'recepcion_detalle_id' => $detalle->id,
                    'cantidad_inicial' => $unidades,
                    'cantidad_disponible' => $unidades,
                    'fecha_vencimiento' => $fechaVencimiento,
                ]);
            }
        }
    }

    private function parseQuantity(string $str): int
    {
        if (preg_match('/\((\d+)\s*uds?\)/i', $str, $matches)) {
            return (int) $matches[1];
        }
        
        if (preg_match('/(\d+)\s*Disp(?:lay)?s?\s*de\s*(\d+)/i', $str, $matches)) {
            return ((int) $matches[1]) * ((int) $matches[2]);
        }
        
        if (preg_match('/(\d+)\s*Cajas?\s*de\s*(\d+)/i', $str, $matches)) {
            return ((int) $matches[1]) * ((int) $matches[2]);
        }

        if (preg_match('/^(\d+)/', $str, $matches)) {
            $num = (int) $matches[1];
            if (str_contains(strtolower($str), 'display') || str_contains(strtolower($str), 'caja')) {
                if ($num < 10) return $num * 24; 
            }
            return $num;
        }

        return 0;
    }
}
