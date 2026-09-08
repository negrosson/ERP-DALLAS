<?php
$bodega = App\Models\Bodega::first();
$request = new Illuminate\Http\Request();
$controller = new App\Http\Controllers\BodegaController();
$view = $controller->show($bodega, $request);
$html = $view->render();
file_put_contents('test_html.html', $html);
