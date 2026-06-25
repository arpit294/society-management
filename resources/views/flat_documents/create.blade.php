<div class="modal-header">
    <h5 class="modal-title text-white" id="addDocumentModalLabel">Upload Flat Documents</h5>
    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
</div>
<form id="addDocumentForm" class="add-document-form" action="{{ route('flat-documents.store') }}" method="POST" enctype="multipart/form-data" data-settings="{{ json_encode($settings) }}" data-requirements="{{ json_encode($documentRequirements) }}">
    @csrf
    <div class="modal-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="block_id" class="form-label text-white">Block <span class="text-danger">*</span></label>
                <select name="block_id" id="block_id" class="form-select select2" required style="width: 100%;">
                    <option value="">Select Block</option>
                    @foreach ($blocks as $block)
                        <option value="{{ $block->id }}">{{ $block->block_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="flat_id" class="form-label text-white">Flat <span class="text-danger">*</span></label>
                <select name="flat_id" id="flat_id" class="form-select select2" required style="width: 100%;">
                    <option value="">Select Flat</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="user_id" class="form-label text-white">Resident <span class="text-danger">*</span></label>
                <select name="user_id" id="user_id" class="form-select select2" required style="width: 100%;">
                    <option value="">Select Resident</option>
                </select>
                <input type="hidden" name="resident_type" id="resident_type" value="">
            </div>
        </div>

        <div class="row mb-3 d-none" id="resident_info_container">
            <div class="col-md-6">
                <label class="form-label text-white small">Contact Number</label>
                <input type="text" class="form-control bg-white text-dark" id="resident_phone" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label text-white small">Email Address</label>
                <input type="text" class="form-control bg-white text-dark" id="resident_email" readonly>
            </div>
        </div>

        <hr>
        <h6 class="fw-bold mb-3 text-white">Required Documents</h6>
        <div id="dynamic_documents_container">
            <p class="text-light small">Please select a resident to view required documents.</p>
        </div>

    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>Upload Documents</button>
    </div>
</form>


