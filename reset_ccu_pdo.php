<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=control_erp', 'root', '1234');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Obtener proveedor CCU
$stmt = $pdo->query("SELECT * FROM proveedores WHERE nombre LIKE '%CCU%'");
$ccu = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ccu) {
    echo "Proveedor CCU no encontrado.\n";
    exit;
}

echo "Proveedor CCU: " . $ccu['nombre'] . "\n";

// Vaciar inventario (lote_stock) de productos mapeados a CCU
$stmt = $pdo->prepare("
    SELECT p.id, p.nombre, p.sku 
    FROM catalogo_productos p
    INNER JOIN mapeo_codigos m ON m.catalogo_producto_id = p.id
    WHERE m.proveedor_id = ?
");
$stmt->execute([$ccu['id']]);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Productos CCU encontrados: " . count($productos) . "\n";

$eliminados = 0;
foreach ($productos as $p) {
    // Eliminar lotes_stock
    $del = $pdo->prepare("DELETE FROM lotes_stock WHERE catalogo_producto_id = ?");
    $del->execute([$p['id']]);
    $eliminados += $del->rowCount();
}

echo "Se han eliminado $eliminados registros de lote_stock correspondientes a productos CCU.\n";
