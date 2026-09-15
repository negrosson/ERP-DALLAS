<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// 1. Get the first LoteStock
$lote = \App\Models\LoteStock::first();
echo "Lote ID: " . ($lote ? $lote->id : 'None') . "\n";

if ($lote) {
    // 2. Test the route matching for this specific Lote ID
    $request = Illuminate\Http\Request::create('/lotes/' . $lote->id . '/fechas', 'PUT');
    try {
        $route = app('router')->getRoutes()->match($request);
        echo "Route Matched: " . $route->action['uses'] . "\n";
    } catch (\Exception $e) {
        echo "Route Error: " . $e->getMessage() . "\n";
    }
}
