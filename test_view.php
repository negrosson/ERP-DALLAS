<?php
$bodega = App\Models\Bodega::first();
$request = new Illuminate\Http\Request();
$controller = new App\Http\Controllers\BodegaController();
try {
    $view = $controller->show($bodega, $request);
    echo "SUCCESS: " . strlen($view->render());
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine();
}
