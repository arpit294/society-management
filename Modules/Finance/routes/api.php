<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Finance API Routes
|--------------------------------------------------------------------------
*/

Route::get('/health', function () {
    return response()->json(['module' => 'Finance', 'status' => 'active']);
});
