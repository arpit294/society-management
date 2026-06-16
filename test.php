<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c = new \App\Http\Controllers\MaintenanceBillController;
$ref = new ReflectionMethod($c, 'getSettingValues');
$ref->setAccessible(true);
echo json_encode($ref->invoke($c, 'penalty'), JSON_PRETTY_PRINT);
