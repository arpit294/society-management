<?php

namespace App\Http\Controllers;

use App\DataTables\ComplainsDataTable;
use App\Models\Complain;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ComplainController extends Controller
{
    public function index(ComplainsDataTable $dataTable)
    {
        abort_if(! \Auth::user()->can('complain_view'), 403);
        return $dataTable->render('complains.index');
    }

    public function create()
    {
        abort_if(! \Auth::user()->can('complain_create'), 403);
        $users = User::query()->get();

        return response()->view('complains.create', compact('users'));
    }


    public function store(Request $request)
    {
        abort_if(! \Auth::user()->can('complain_create'), 403);
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

    public function edit(Complain $complain)
    {
        abort_if(! \Auth::user()->can('complain_edit'), 403);
        $users = User::query()->get();

        return response()->view('complains.edit', compact('complain', 'users'));
    }


    public function update(Request $request, Complain $complain)
    {
        abort_if(! \Auth::user()->can('complain_edit'), 403);
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

    public function destroy(Complain $complain)
    {
        abort_if(! \Auth::user()->can('complain_delete'), 403);
        $complain->delete();

        return response()->json([
            'success' => true,
            'message' => 'Complaint deleted successfully.',
        ]);
    }
}
