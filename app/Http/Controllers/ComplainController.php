<?php

namespace App\Http\Controllers;

use App\DataTables\ComplainsDataTable;
use App\Models\Complain;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ComplainController extends Controller
{
    /**
     * Display a listing of complaints.
     *
     * @return mixed
     */
    public function index(ComplainsDataTable $dataTable)
    {
        return $dataTable->render('complains.index');
    }

    /**
     * Show the form for creating a new complaint.
     *
     * @return View
     */
    public function create()
    {
        $users = User::all();

        return view('complains.create', compact('users'));
    }

    /**
     * Store a newly created complaint in storage.
     *
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'category' => ['required', Rule::in([
                'Maintenance Issues',
                'Security Issues',
                'Cleanliness & Housekeeping',
                'Common Facilities',
                'other',
            ])],
        ]);

        Complain::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Complaint created successfully.',
        ]);
    }

    /**
     * Show the form for editing the specified complaint.
     *
     * @return View
     */
    public function edit(Complain $complain)
    {
        $users = User::all();

        return view('complains.edit', compact('complain', 'users'));
    }

    /**
     * Update the specified complaint in storage.
     *
     * @return JsonResponse
     */
    public function update(Request $request, Complain $complain)
    {
        $validatedData = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'category' => ['required', Rule::in([
                'Maintenance Issues',
                'Security Issues',
                'Cleanliness & Housekeeping',
                'Common Facilities',
                'other',
            ])],
            'status' => 'required|in:pending,in-progress,resolved',
            'resolution_notes' => 'nullable|string',
        ]);

        $complain->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Complaint updated successfully.',
        ]);
    }

    /**
     * Remove the specified complaint from storage.
     *
     * @return JsonResponse
     */
    public function destroy(Complain $complain)
    {
        $complain->delete();

        return response()->json([
            'success' => true,
            'message' => 'Complaint deleted successfully.',
        ]);
    }
}
