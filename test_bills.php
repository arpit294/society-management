<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$bills = \App\Models\MaintenanceBill::all();
foreach ($bills as $bill) {
    echo "ID: " . $bill->id . " | Flat: " . $bill->flat_id . " | Month: " . $bill->month . " | Year: " . $bill->year . " | Status: " . $bill->status . "\n";
}
