<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$activeResidents = \App\Models\Resident::with(['flat.flatType'])->get();
foreach ($activeResidents as $resident) {
    echo "Resident Flat: " . $resident->flat_id . "\n";
    echo "Move out: " . $resident->move_out_date . "\n";
    if ($resident->flat && $resident->flat->flatType) {
        echo "Fee: " . $resident->flat->flatType->maintenance_fee . "\n";
    } else {
        echo "No flat or flat type\n";
    }
}
