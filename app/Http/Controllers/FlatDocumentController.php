<?php

namespace App\Http\Controllers;

use App\DataTables\FlatDocumentsDataTable;
use App\Models\Block;
use App\Models\FlatDocument;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class FlatDocumentController extends Controller
{
    public function index(FlatDocumentsDataTable $dataTable)
    {
        abort_if(! \Auth::user()->can('flat_document_view'), 403);
        try {
            $blocks = Block::all();

            return $dataTable->render('flat_documents.index', compact('blocks'));
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in FlatDocumentController@index: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function create()
    {
        abort_if(! \Auth::user()->can('flat_document_create'), 403);
        try {
            $blocks = Block::all();
            $settings = Setting::getAll();
            $documentRequirements = $this->documentRequirements();

            return view('flat_documents.create', compact('blocks', 'settings', 'documentRequirements'));
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in FlatDocumentController@create: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        abort_if(! \Auth::user()->can('flat_document_create'), 403);
        try {
            $validated = $request->validate([
                'block_id' => 'required|exists:blocks,id',
                'flat_id' => 'required|exists:flats,id',
                'user_id' => 'required|exists:users,id',
                'resident_type' => 'required|in:owner,rental',
            ]);

            $residentType = $validated['resident_type'];
            $requiredDocuments = $this->enabledDocumentsFor($residentType);

            $maxSizeKb = (float) Setting::get('max_document_size', 2) * 1024;
            $fileRules = [];
            $hasAnyRequired = false;
            foreach ($requiredDocuments as $key => $docInfo) {
                $isRequired = is_array($docInfo) ? $docInfo['required'] : true;
                if ($isRequired) {
                    $fileRules[$key] = 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:' . $maxSizeKb;
                    $hasAnyRequired = true;
                } else {
                    $fileRules[$key] = 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:' . $maxSizeKb;
                }
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

            foreach ($requiredDocuments as $key => $docInfo) {
                $label = is_array($docInfo) ? $docInfo['label'] : $docInfo;
                $file = $request->file($key);

                if (! $file || is_array($file)) {
                    continue;
                }

                if (isset($documents[$key]['file_path']) && Storage::disk('public')->exists($documents[$key]['file_path'])) {
                    Storage::disk('public')->delete($documents[$key]['file_path']);
                }

                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('documents/flats/' . $validated['flat_id'] . '/' . $validated['user_id'], $fileName, 'public');

                $documents[$key] = [
                    'title' => $label,
                    'file_path' => $filePath,
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize(),
                    'original_name' => $file->getClientOriginalName(),
                ];
                $filesUploaded++;
            }

            if ($filesUploaded === 0 && $hasAnyRequired) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select required documents to upload.',
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
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in FlatDocumentController@store: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(FlatDocument $flatDocument)
    {
        abort_if(! \Auth::user()->can('flat_document_view'), 403);
        try {
            $flatDocument->load(['flat.block', 'user']);
            $expectedDocs = $this->enabledDocumentsFor($flatDocument->resident_type);
            return view('flat_documents.show', compact('flatDocument', 'expectedDocs'));
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in FlatDocumentController@show: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function download(FlatDocument $flatDocument, $doc_key)
    {
        abort_if(! \Auth::user()->can('flat_document_view'), 403);
        try {
            $documents = $flatDocument->documents ?? [];
            if (!isset($documents[$doc_key]) && isset($documents[urldecode($doc_key)])) {
                $doc_key = urldecode($doc_key);
            }
            if (!isset($documents[$doc_key])) {
                abort(404, 'File not found in submission');
            }

            $doc = $documents[$doc_key];
            $filePath = storage_path('app/public/' . $doc['file_path']);

            if (! file_exists($filePath)) {
                abort(404, 'File not found on disk');
            }

            $flat = $flatDocument->flat;
            $prefix = '';
            if ($flat) {
                $blockName = $flat->block ? $flat->block->block_name : '';
                $prefix = trim($blockName . '-' . $flat->flat_no, '-');
            }

            $docTitle = $doc['title'] ?? $doc_key;
            $docTitle = preg_replace('/[^A-Za-z0-9_\- ]/', '', $docTitle);
            $docTitle = str_replace(' ', '_', $docTitle);

            $extension = pathinfo($doc['original_name'], PATHINFO_EXTENSION);
            $downloadName = $prefix ? "{$prefix}_{$docTitle}.{$extension}" : "{$docTitle}.{$extension}";

            if (request()->has('inline')) {
                return response()->file($filePath);
            }

            return response()->download($filePath, $downloadName);
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in FlatDocumentController@download: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred downloading file: ' . $e->getMessage());
        }
    }

    public function destroy(FlatDocument $flatDocument)
    {
        abort_if(! \Auth::user()->can('flat_document_delete'), 403);
        try {
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
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in FlatDocumentController@destroy: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function deleteDocument(FlatDocument $flatDocument, $doc_key)
    {
        abort_if(! \Auth::user()->can('flat_document_delete'), 403);
        try {
            $documents = $flatDocument->documents ?? [];
            if (!isset($documents[$doc_key]) && isset($documents[urldecode($doc_key)])) {
                $doc_key = urldecode($doc_key);
            }

            if (isset($documents[$doc_key])) {
                $doc = $documents[$doc_key];
                if (isset($doc['file_path']) && Storage::disk('public')->exists($doc['file_path'])) {
                    Storage::disk('public')->delete($doc['file_path']);
                }
                unset($documents[$doc_key]);
                $flatDocument->documents = !empty($documents) ? $documents : [];
                $flatDocument->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Document deleted successfully.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Document not found.',
            ], 404);
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in FlatDocumentController@deleteDocument: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateDocument(Request $request, FlatDocument $flatDocument, $doc_key)
    {
        abort_if(! \Auth::user()->can('flat_document_edit'), 403);
        try {
            $maxSizeKb = (float) Setting::get('max_document_size', 2) * 1024;
            $request->validate([
                'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:' . $maxSizeKb,
            ]);

            $documents = $flatDocument->documents ?? [];
            if (!isset($documents[$doc_key]) && isset($documents[urldecode($doc_key)])) {
                $doc_key = urldecode($doc_key);
            }

            $oldDoc = $documents[$doc_key] ?? null;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $originalName = $file->getClientOriginalName();
                $fileSize = $file->getSize();
                $fileType = $file->getClientOriginalExtension();

                $safeName = preg_replace('/[^A-Za-z0-9_\-\.]/', '', $originalName);
                $fileName = time() . '_' . str_replace(' ', '_', $safeName);
                $path = $file->storeAs("documents/flats/{$flatDocument->flat_id}/{$flatDocument->user_id}", $fileName, 'public');

                // Delete old file
                if ($oldDoc && isset($oldDoc['file_path']) && Storage::disk('public')->exists($oldDoc['file_path'])) {
                    Storage::disk('public')->delete($oldDoc['file_path']);
                }

                $title = $oldDoc['title'] ?? ucfirst(str_replace('_', ' ', str_replace('req_doc_' . $flatDocument->resident_type . '_', '', $doc_key)));
                if (!$oldDoc) {
                    $enabledDocs = $this->enabledDocumentsFor($flatDocument->resident_type);
                    if (isset($enabledDocs[$doc_key])) {
                        $title = $enabledDocs[$doc_key]['label'];
                    }
                }

                // Update json
                $documents[$doc_key] = [
                    'title' => $title,
                    'file_path' => $path,
                    'original_name' => $originalName,
                    'file_size' => $fileSize,
                    'file_type' => $fileType,
                ];

                $flatDocument->documents = !empty($documents) ? $documents : [];
                $flatDocument->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Document updated successfully.',
                ]);
            }

            return response()->json(['success' => false, 'message' => 'No file provided.'], 400);
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in FlatDocumentController@updateDocument: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
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

            ],
            'rental' => [
                'passport_photo' => 'Passport Size Photo',
                'adhar_card' => 'Aadhar Card',
                'pan_card' => 'PAN Card',
                'rent_agreement' => 'Rent Agreement',
                'police_verification' => 'Police Verification',
                'permanent_address_proof' => 'Permanent Address Proof',
                // contact_no and email fields/documents removed from upload flow
                // contact_no & email are shown only as resident info (not as upload-required documents)

                // (removed contact_no & email from upload-required docs)
            ],
        ];
    }

    private function enabledDocumentsFor(string $residentType): array
    {
        $settings = Setting::getAll();
        $documents = $this->documentRequirements()[$residentType] ?? [];
        $enabledDocuments = [];

        foreach ($documents as $key => $label) {
            $settingKey = 'req_doc_' . $residentType . '_' . $key;
            $val = $settings[$settingKey] ?? '1';

            if ($val == '1' || $val == '2') {
                $enabledDocuments[$settingKey] = [
                    'label' => $label,
                    'required' => ($val == '1')
                ];
            }
        }

        return $enabledDocuments;
    }
}
