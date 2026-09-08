<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=control_erp', 'root', '1234');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Normalizar 'Plástico' a 'Desechable'
$stmt = $pdo->prepare("UPDATE catalogo_productos SET envase = 'Desechable' WHERE envase = 'Plástico' OR envase = 'Plastico'");
$stmt->execute();
echo "Filas actualizadas de Plástico a Desechable: " . $stmt->rowCount() . "\n";

// Verificar los tipos de envases actuales
$stmt = $pdo->query("SELECT envase, COUNT(*) as total FROM catalogo_productos GROUP BY envase");
$envases = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Envases actuales en el catálogo:\n";
foreach ($envases as $e) {
    echo "- " . ($e['envase'] ?: 'No especificado') . ": " . $e['total'] . "\n";
}
