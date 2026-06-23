<?php

namespace App\Http\Controllers;

use App\DataTables\FlatDocumentsDataTable;
use App\Models\Block;
use App\Models\FlatDocument;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FlatDocumentController extends Controller
{
    public function index(FlatDocumentsDataTable $dataTable)
    {
        abort_if(! \Auth::user()->can('flat_document_view'), 403);
        $blocks = Block::all();

        return $dataTable->render('flat_documents.index', compact('blocks'));
    }

    public function create()
    {
        abort_if(! \Auth::user()->can('flat_document_create'), 403);
        $blocks = Block::all();
        $settings = Setting::pluck('value', 'key')->toArray();
        $documentRequirements = $this->documentRequirements();

        return view('flat_documents.create', compact('blocks', 'settings', 'documentRequirements'));
    }

    public function store(Request $request)
    {
        abort_if(! \Auth::user()->can('flat_document_create'), 403);
        $validated = $request->validate([
            'block_id' => 'required|exists:blocks,id',
            'flat_id' => 'required|exists:flats,id',
            'user_id' => 'required|exists:users,id',
            'resident_type' => 'required|in:owner,rental',
        ]);

        $residentType = $validated['resident_type'];
        $requiredDocuments = $this->enabledDocumentsFor($residentType);

        $fileRules = [];
        foreach ($requiredDocuments as $key => $label) {
            $fileRules[$key] = 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120';
        }

        if ($fileRules) {
            $request->validate($fileRules);
        }

        $flatDocument = FlatDocument::where('flat_id', $validated['flat_id'])
                            ->where('user_id', $validated['user_id'])
                            ->where('resident_type', $residentType)
                            ->first();

        $documents = $flatDocument ? ($flatDocument->documents ?? []) : [];
        $filesUploaded = 0;

        foreach ($requiredDocuments as $key => $label) {
            $file = $request->file($key);

            if (! $file || is_array($file)) {
                continue;
            }

            $fileName = time().'_'.$file->getClientOriginalName();
            $filePath = $file->storeAs('documents/flats/'.$validated['flat_id'].'/'.$validated['user_id'], $fileName, 'public');

            $documents[$key] = [
                'title' => $label,
                'file_path' => $filePath,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'original_name' => $file->getClientOriginalName(),
            ];
            $filesUploaded++;
        }

        if ($filesUploaded === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Please select at least one document to upload.',
            ], 422);
        }

        if ($flatDocument) {
            $flatDocument->update([
                'documents' => $documents,
                'uploaded_by' => auth()->id() ?? 1,
            ]);
        } else {
            FlatDocument::create([
                'flat_id' => $validated['flat_id'],
                'user_id' => $validated['user_id'],
                'resident_type' => $residentType,
                'uploaded_by' => auth()->id() ?? 1,
                'documents' => $documents,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Documents uploaded successfully.',
        ]);
    }

    public function show(FlatDocument $flatDocument)
    {
        abort_if(! \Auth::user()->can('flat_document_view'), 403);
        return view('flat_documents.show', compact('flatDocument'));
    }

    public function download(FlatDocument $flatDocument, $doc_key)
    {
        abort_if(! \Auth::user()->can('flat_document_view'), 403);
        $documents = $flatDocument->documents ?? [];
        if (!isset($documents[$doc_key])) {
            abort(404, 'File not found in submission');
        }

        $doc = $documents[$doc_key];
        $filePath = storage_path('app/public/'.$doc['file_path']);

        if (! file_exists($filePath)) {
            abort(404, 'File not found on disk');
        }

        return response()->download($filePath, $doc['original_name']);
    }

    public function destroy(FlatDocument $flatDocument)
    {
        abort_if(! \Auth::user()->can('flat_document_delete'), 403);
        $documents = $flatDocument->documents ?? [];
        foreach ($documents as $doc) {
            if (isset($doc['file_path']) && Storage::disk('public')->exists($doc['file_path'])) {
                Storage::disk('public')->delete($doc['file_path']);
            }
        }

        $flatDocument->delete();

        return response()->json([
            'success' => true,
            'message' => 'Document submission deleted successfully.',
        ]);
    }

    private function documentRequirements(): array
    {
        return [
            'owner' => [
                'passport_photo' => 'Passport Size Photo',
                'adhar_card' => 'Aadhar Card',
                'pan_card' => 'PAN Card',
                'index_copy' => 'Index Copy',
                'possession_letter' => 'Possession Letter',
                'tax_bill' => 'Copy of Tax Bill',
                'contact_no' => 'Contact No Document',
                'email' => 'Email Address Document',
            ],
            'rental' => [
                'passport_photo' => 'Passport Size Photo',
                'adhar_card' => 'Aadhar Card',
                'pan_card' => 'PAN Card',
                'rent_agreement' => 'Rent Agreement',
                'police_verification' => 'Police Verification',
                'permanent_address_proof' => 'Permanent Address Proof',
                'contact_no' => 'Contact Number Document',
                'email' => 'Email Address Document',
            ],
        ];
    }

    private function enabledDocumentsFor(string $residentType): array
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $documents = $this->documentRequirements()[$residentType] ?? [];
        $enabledDocuments = [];

        foreach ($documents as $key => $label) {
            $settingKey = 'req_doc_'.$residentType.'_'.$key;

            if (($settings[$settingKey] ?? '0') == '1') {
                $enabledDocuments[$settingKey] = $label;
            }
        }

        return $enabledDocuments;
    }
}
