<?php

namespace App\Http\Controllers;

use App\DataTables\FlatDocumentsDataTable;
use App\Models\Block;
use App\Models\FlatDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FlatDocumentController extends Controller
{
    public function index(FlatDocumentsDataTable $dataTable)
    {
        $blocks = Block::all();

        return $dataTable->render('flat_documents.index', compact('blocks'));
    }

    public function create()
    {
        $blocks = Block::all();

        return view('flat_documents.create', compact('blocks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'block_id' => 'required|exists:blocks,id',
            'flat_id' => 'required|exists:flats,id',
            'resident_type' => 'required|in:owner,rental,both',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120', // 5MB max
        ]);

        $file = $request->file('document');
        $fileName = time().'_'.$file->getClientOriginalName();
        $filePath = $file->storeAs('documents/flats/'.$request->flat_id, $fileName, 'public');

        FlatDocument::create([
            'flat_id' => $request->flat_id,
            'resident_type' => $request->resident_type,
            'uploaded_by' => auth()->id() ?? 1, // fallback to 1 if not logged in (for testing)
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $filePath,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded successfully.',
        ]);
    }

    public function download(FlatDocument $flatDocument)
    {
        $filePath = storage_path('app/public/'.$flatDocument->file_path);

        if (! file_exists($filePath)) {
            abort(404, 'File not found');
        }

        return response()->download($filePath, $flatDocument->title.'.'.$flatDocument->file_type);
    }

    public function destroy(FlatDocument $flatDocument)
    {
        // Delete file from storage
        if (Storage::disk('public')->exists($flatDocument->file_path)) {
            Storage::disk('public')->delete($flatDocument->file_path);
        }

        $flatDocument->delete();

        return response()->json([
            'success' => true,
            'message' => 'Document deleted successfully.',
        ]);
    }
}
