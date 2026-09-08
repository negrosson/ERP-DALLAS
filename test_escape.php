<?php
$producto = ['nombre' => 'Coca "Zero"'];
$json = json_encode($producto);
echo "JSON: " . $json . "\n";
echo "Blade escape: " . htmlspecialchars($json, ENT_QUOTES, 'UTF-8', true) . "\n";
