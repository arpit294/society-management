<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MaintenanceBill;
use App\Models\User;
use App\Models\Flat;
use App\Models\Block;
use App\DataTables\MaintenanceBillsDataTable;

class MaintenanceBillController extends Controller
{
    public function index(MaintenanceBillsDataTable $dataTable)
    {
        return $dataTable->render('maintenance_bills.index');
    }

    public function create()
    {
        $users = User::all();
        $flats = Flat::with('flatType')->get();
        $blocks = Block::all();
        return view('maintenance_bills.create', compact('users', 'flats', 'blocks'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'block_id' => 'required|exists:blocks,id',
            'user_id' => 'required|exists:users,id',
            'flat_id' => 'required|exists:flats,id',
            'amount' => 'required|numeric|min:0',
            'month' => 'required|string|max:50',
            'year' => 'required|integer|min:2000',
            'due_date' => 'required|date',
            'generated_date' => 'required|date',
            'status' => 'required|in:paid,due,pending',
        ]);

        $penaltyData = $this->calculatePenalty($validatedData);
        $validatedData['penalty_amount'] = $penaltyData['penalty_amount'];
        $validatedData['total_amount'] = $penaltyData['total_amount'];

        if ($validatedData['status'] === 'paid') {
            $validatedData['paid_at'] = now();
        }

        MaintenanceBill::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Maintenance Bill created successfully.',
        ]);
    }

    public function edit(MaintenanceBill $maintenanceBill)
    {
        $users = User::all();
        $flats = Flat::with('flatType')->get();
        $blocks = Block::all();
        return view('maintenance_bills.edit', compact('maintenanceBill', 'users', 'flats', 'blocks'));
    }

    public function update(Request $request, MaintenanceBill $maintenanceBill)
    {
        $validatedData = $request->validate([
            'block_id' => 'required|exists:blocks,id',
            'user_id' => 'required|exists:users,id',
            'flat_id' => 'required|exists:flats,id',
            'amount' => 'required|numeric|min:0',
            'month' => 'required|string|max:50',
            'year' => 'required|integer|min:2000',
            'due_date' => 'required|date',
            'generated_date' => 'required|date',
            'status' => 'required|in:paid,due,pending',
        ]);

        $penaltyData = $this->calculatePenalty($validatedData, $maintenanceBill);
        $validatedData['penalty_amount'] = $penaltyData['penalty_amount'];
        $validatedData['total_amount'] = $penaltyData['total_amount'];

        if ($validatedData['status'] === 'paid' && !$maintenanceBill->paid_at) {
            $validatedData['paid_at'] = now();
        } elseif ($validatedData['status'] !== 'paid') {
            $validatedData['paid_at'] = null;
        }

        $maintenanceBill->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Maintenance Bill updated successfully.',
        ]);
    }

    public function destroy(MaintenanceBill $maintenanceBill)
    {
        $maintenanceBill->delete();

        return response()->json([
            'success' => true,
            'message' => 'Maintenance Bill deleted successfully.',
        ]);
    }

    private function calculatePenalty($validatedData, $maintenanceBill = null)
    {
        $flat = \App\Models\Flat::with('flatType')->find($validatedData['flat_id']);
        $penaltyPerDay = $flat->flatType ? $flat->flatType->penalty_per_day : 0;
        
        $dueDate = \Carbon\Carbon::parse($validatedData['due_date'])->startOfDay();
        
        $paidAt = null;
        if ($validatedData['status'] === 'paid') {
            $paidAt = ($maintenanceBill && $maintenanceBill->paid_at) ? $maintenanceBill->paid_at : now();
        }

        $compareDate = $paidAt ? $paidAt->startOfDay() : now()->startOfDay();

        $lateDays = 0;
        if ($compareDate->gt($dueDate)) {
            $lateDays = $dueDate->diffInDays($compareDate);
        }

        $penaltyAmount = $lateDays * $penaltyPerDay;
        
        return [
            'penalty_amount' => $penaltyAmount,
            'total_amount' => $validatedData['amount'] + $penaltyAmount
        ];
    }

    public function getResidentInfo($userId)
    {
        $resident = \App\Models\Resident::with('flat.flatType')
            ->where('user_id', $userId)
            ->where(function($query) {
                $query->whereNull('move_out_date')
                      ->orWhere('move_out_date', '>=', now()->startOfDay());
            })
            ->first();

        if ($resident) {
            $amount = $resident->flat && $resident->flat->flatType ? $resident->flat->flatType->maintenance_fee : 0;
            return response()->json([
                'success' => true,
                'block_id' => $resident->block_id,
                'flat_id' => $resident->flat_id,
                'amount' => $amount
            ]);
        }

        return response()->json(['success' => false]);
    }
}
