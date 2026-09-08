<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CatalogoProducto;
use App\Models\Proveedor;
use App\Models\MapeoCodigo;
use App\Models\LoteStock;
use Illuminate\Support\Facades\DB;

// Buscar proveedor CCU
$ccu = Proveedor::where('nombre', 'like', '%CCU%')->first();

if (!$ccu) {
    echo "Proveedor CCU no encontrado.\n";
} else {
    echo "Proveedor CCU encontrado: " . $ccu->nombre . " (ID: " . $ccu->id . ")\n";
    
    // Ver mapeos y productos
    $mapeos = MapeoCodigo::where('proveedor_id', $ccu->id)->with('producto')->get();
    echo "Productos mapeados a CCU: " . $mapeos->count() . "\n";
    
    $stockTotal = 0;
    foreach ($mapeos as $mapeo) {
        $prod = $mapeo->producto;
        if ($prod) {
            $stock = LoteStock::where('catalogo_producto_id', $prod->id)->sum('cantidad_actual');
            $stockTotal += $stock;
            echo "- " . $prod->nombre . " (SKU: " . $prod->sku . ") | Envase: " . $prod->envase . " | Stock actual: " . $stock . "\n";
        }
    }
    
    echo "Stock total CCU: " . $stockTotal . "\n";
}
