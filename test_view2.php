<?php
$bodega = App\Models\Bodega::first();
$request = new Illuminate\Http\Request();
$controller = new App\Http\Controllers\BodegaController();
$view = $controller->show($bodega, $request);
$html = $view->render();
if (strpos($html, 'No se encontraron productos') !== false) {
    echo "Found 'No se encontraron productos'.";
} else {
    echo "Not found.";
}
