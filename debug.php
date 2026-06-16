<?php
$flat = \App\Models\Flat::where('flat_no', '301')->whereHas('block', function($q) { $q->where('block_name', 'E'); })->first();
if ($flat) {
    echo "Flat ID: " . $flat->id . "\n";
    $residents = \App\Models\Resident::where('flat_id', $flat->id)->get();
    foreach($residents as $r) {
        echo "Resident ID: {$r->id}, Type: {$r->type}, User ID: {$r->user_id}, User Exists: " . (\App\Models\User::find($r->user_id) ? 'Yes' : 'No') . "\n";
    }
}
