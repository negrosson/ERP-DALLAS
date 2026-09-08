<?php
use App\Models\LoteStock;
use App\Models\CatalogoProducto;
use App\Models\Bodega;
use App\Models\Proveedor;
use App\Models\Recepcion;
use App\Models\RecepcionDetalle;
use Illuminate\Support\Str;
use Carbon\Carbon;

$bodegaCCU = Bodega::firstOrCreate(['nombre' => 'Bodega CCU'], ['codigo' => 'BOD-CCU']);
$bodegaKOF = Bodega::firstOrCreate(['nombre' => 'Bodega Coca-Cola'], ['codigo' => 'BOD-KOF']);

$proveedorDummy = Proveedor::firstOrCreate(['rut' => '11.111.111-1'], ['nombre' => 'Ajustes Sistema', 'codigo' => 'PROV-AJUSTE']);
$recepcion = Recepcion::create([
    'proveedor_id' => $proveedorDummy->id,
    'bodega_id' => $bodegaCCU->id,
    'user_id' => 1,
    'estado' => 'CONFIRMADO',
    'fecha_recepcion' => Carbon::now(),
    'numero_factura' => 'AJUSTE-' . date('YmdHis'),
    'observaciones' => 'Ajuste manual a pedido',
]);

function createLote($nombre, $cantidad, $fechaVenc, $bodegaId, $recepcion) {
    $sku = strtoupper(Str::slug($nombre, '-'));
    $producto = CatalogoProducto::firstOrCreate(
        ['nombre' => $nombre],
        [
            'sku' => $sku,
            'unidad_medida' => 'unidades',
            'precio_venta' => 1500,
            'precio_compra_ref' => 900,
        ]
    );

    $detalle = RecepcionDetalle::create([
        'recepcion_id' => $recepcion->id,
        'catalogo_producto_id' => $producto->id,
        'codigo_proveedor_usado' => $sku,
        'cantidad' => $cantidad,
        'precio_unitario' => 900,
        'fecha_vencimiento' => Carbon::createFromFormat('d/m/Y', $fechaVenc),
    ]);

    LoteStock::create([
        'catalogo_producto_id' => $producto->id,
        'bodega_id' => $bodegaId,
        'recepcion_detalle_id' => $detalle->id,
        'cantidad_inicial' => $cantidad,
        'cantidad_disponible' => $cantidad,
        'fecha_vencimiento' => Carbon::createFromFormat('d/m/Y', $fechaVenc),
    ]);
    
    echo "✓ $nombre: $cantidad uds, Vence: $fechaVenc\n";
}

// 1. Crush Orange Lata 350 ml
$pCrush = CatalogoProducto::where('nombre', 'like', '%Crush Orange%Lata%')->first();
if ($pCrush) { LoteStock::where('catalogo_producto_id', $pCrush->id)->delete(); }
createLote('Crush Orange Lata 350 ml', 18, '11/12/2026', $bodegaCCU->id, $recepcion);

// 2. Pepsi Zero 3 L
$pPepsi = CatalogoProducto::where('nombre', 'like', '%Pepsi Zero%3%L%')->first();
if ($pPepsi) { LoteStock::where('catalogo_producto_id', $pPepsi->id)->delete(); }
createLote('Pepsi Zero 3 L', 2, '24/10/2026', $bodegaCCU->id, $recepcion);

// 3. Kem Lata 350 ml
$pKem = CatalogoProducto::where('nombre', 'like', '%Kem%Lata%')->first();
if ($pKem) { LoteStock::where('catalogo_producto_id', $pKem->id)->delete(); }
createLote('Kem Lata 350 ml', 24, '19/06/2027', $bodegaCCU->id, $recepcion);

// 4. Cachantún Strong Gas 600 ml
$pStrong = CatalogoProducto::where('nombre', 'like', '%Cachant%Strong Gas%')->first();
if ($pStrong) { LoteStock::where('catalogo_producto_id', $pStrong->id)->delete(); }
createLote('Cachantún Strong Gas 600 ml', 12, '09/09/2026', $bodegaCCU->id, $recepcion);
createLote('Cachantún Strong Gas 600 ml', 18, '09/08/2026', $bodegaCCU->id, $recepcion);

// 5. Cachantún suavemente gasificada 600 ml
$pSuave = CatalogoProducto::where('nombre', 'like', '%Cachant%suavemente gasificada%')->first();
if ($pSuave) { LoteStock::where('catalogo_producto_id', $pSuave->id)->delete(); }
createLote('Cachantún suavemente gasificada 600 ml', 18, '04/12/2026', $bodegaCCU->id, $recepcion);
createLote('Cachantún suavemente gasificada 600 ml', 6, '05/11/2026', $bodegaCCU->id, $recepcion);
createLote('Cachantún suavemente gasificada 600 ml', 18, '06/11/2026', $bodegaCCU->id, $recepcion);

// Andina
$andinaItems = [
    ['Néctar Andina Piña 1,5 L', 12, '21/12/2026'],
    ['Néctar Andina Durazno 1,5 L', 6, '13/12/2026'],
    ['Néctar Andina Durazno 1,5 L', 6, '05/02/2027'],
    ['Néctar Andina Durazno Zero 1,5 L', 6, '15/04/2027'],
    ['Néctar Andina Manzana 1,75 L', 6, '21/12/2026'],
    ['Néctar Andina Naranja 1,75 L', 6, '08/12/2026'],
    ['Néctar Andina Piña 400 ml', 6, '01/10/2026'],
    ['Néctar Andina Piña 400 ml', 6, '24/12/2026'],
    ['Néctar Andina Durazno 400 ml', 6, '27/04/2027'],
    ['Néctar Andina Frutilla 400 ml', 6, '29/10/2026'],
    ['Néctar Andina Frutilla 400 ml', 6, '02/01/2027'],
    ['Néctar Andina Naranja 400 ml', 6, '25/03/2027'],
];

foreach ($andinaItems as $item) {
    $pAndina = CatalogoProducto::where('nombre', $item[0])->first();
    if ($pAndina) { LoteStock::where('catalogo_producto_id', $pAndina->id)->delete(); }
}

foreach ($andinaItems as $item) {
    createLote($item[0], $item[1], $item[2], $bodegaKOF->id, $recepcion);
}

echo "Proceso terminado.\n";
