<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$user = \App\Models\User::first();
Auth::login($user); // Ensure Auth::user() is available during the request

$request = Illuminate\Http\Request::create('/bodegas/6', 'GET');
$response = $kernel->handle($request);

file_put_contents('test_output2.html', $response->getContent());
echo "Done.";
