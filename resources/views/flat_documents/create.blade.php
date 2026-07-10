<div class="modal-header">
    <h5 class="modal-title text-body" id="addDocumentModalLabel">Upload {{ \App\Models\Setting::label('unit', 'Flat') }} Documents</h5>
    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
</div>
<form id="addDocumentForm" class="add-document-form" action="{{ route('flat-documents.store') }}" method="POST"
    enctype="multipart/form-data" data-settings="{{ json_encode($settings) }}"
    data-requirements="{{ json_encode($documentRequirements) }}">
    @csrf
    <div class="modal-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="block_id" class="form-label text-body">{{ \App\Models\Setting::label('block', 'Block') }} <span class="text-danger">*</span></label>
                <select name="block_id" id="block_id" class="form-select select2" required style="width: 100%;">
                    <option value="">Select {{ \App\Models\Setting::label('block', 'Block') }}</option>
                    @foreach ($blocks as $block)
                        <option value="{{ $block->id }}">{{ $block->block_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="flat_id" class="form-label text-body">{{ \App\Models\Setting::label('unit', 'Flat') }} <span class="text-danger">*</span></label>
                <select name="flat_id" id="flat_id" class="form-select select2" required style="width: 100%;">
                    <option value="">Select {{ \App\Models\Setting::label('unit', 'Flat') }}</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="user_id" class="form-label text-body">{{ \App\Models\Setting::label('resident', 'Resident') }} <span class="text-danger">*</span></label>
                <select name="user_id" id="user_id" class="form-select select2" required style="width: 100%;">
                    <option value="">Select {{ \App\Models\Setting::label('resident', 'Resident') }}</option>
                </select>
                <input type="hidden" name="resident_type" id="resident_type" value="">
            </div>
        </div>



        <hr>
        <h6 class="fw-bold mb-3 text-body">Required Documents</h6>
        <div id="dynamic_documents_container">
            <p class="text-muted small">Please select a {{ strtolower(\App\Models\Setting::label('resident', 'resident')) }} to view required documents.</p>
        </div>

    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>Upload Documents</button>
    </div>
</form>
