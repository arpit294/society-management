<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;

class FinanceController extends Controller
{
    /**
     * Display a listing of the resource or redirect to primary finance dashboard.
     */
    public function index()
    {
        return redirect()->route('maintenance-bills.index');
    }
}
