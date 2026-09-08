<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/bodegas/6', 'GET');
$response = $kernel->handle($request);

echo "STATUS CODE: " . $response->getStatusCode() . "\n";
echo "CONTENT LENGTH: " . strlen($response->getContent()) . "\n";
echo "START OF CONTENT:\n";
echo substr($response->getContent(), 0, 1000) . "\n";
