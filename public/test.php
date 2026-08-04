<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$flatLastBilled = [];
$latestBills = \Illuminate\Support\Facades\DB::table('maintenance_bills')
    ->where('maintenance_bills.status', 'paid')
    ->join('maintenances', 'maintenance_bills.maintenance_id', '=', 'maintenances.id')
    ->select('maintenance_bills.flat_id', 'maintenances.month', 'maintenances.year')
    ->get();
foreach ($latestBills as $bill) {
    $date = \Carbon\Carbon::parse("1 {$bill->month} {$bill->year}");
    if (!isset($flatLastBilled[$bill->flat_id]) || $date->gt($flatLastBilled[$bill->flat_id])) {
        $flatLastBilled[$bill->flat_id] = $date;
    }
}
$nextBilledDates = [];
foreach (\App\Models\Resident::all() as $res) {
    if (isset($flatLastBilled[$res->flat_id])) {
        $nextBilledDates[$res->id] = $flatLastBilled[$res->flat_id]->copy()->addMonth()->format('Y-m');
    }
}
echo json_encode($nextBilledDates);
