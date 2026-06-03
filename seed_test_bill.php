<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\App\Models\MaintenanceBill::create([
    'block_id' => 1,
    'user_id' => 2, // Using a random user id assuming resident has one, or user_id for the resident flat 9
    'flat_id' => 9,
    'amount' => 1000,
    'penalty_amount' => 0,
    'total_amount' => 1000,
    'month' => 'May',
    'year' => 2026,
    'due_date' => '2026-05-15',
    'generated_date' => '2026-05-01',
    'status' => 'pending',
]);

echo "Created test past-due bill for May 2026.\n";
