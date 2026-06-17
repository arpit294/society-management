<div class="modal-header">
    <h5 class="modal-title" id="addDocumentModalLabel">Upload Flat Document</h5>
    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
</div>
<form id="addDocumentForm" action="{{ route('flat-documents.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="modal-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="block_id" class="form-label">Block <span class="text-danger">*</span></label>
                <select name="block_id" id="block_id" class="form-select select2" required style="width: 100%;">
                    <option value="">Select Block</option>
                    @foreach ($blocks as $block)
                        <option value="{{ $block->id }}">{{ $block->block_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label for="flat_id" class="form-label">Flat <span class="text-danger">*</span></label>
                <select name="flat_id" id="flat_id" class="form-select select2" required style="width: 100%;">
                    <option value="">Select Flat</option>
                </select>
            </div>
        </div>
        <div class="mb-3">
            <label for="resident_type" class="form-label">Document Belongs To <span class="text-danger">*</span></label>
            <select name="resident_type" id="resident_type" class="form-select" required>
                <option value="owner">Owner</option>
                <option value="rental">Tenant (Rental)</option>
                <option value="both">Both</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="title" class="form-label">Document Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="title" name="title" required placeholder="E.g. Property Deed">
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description (Optional)</label>
            <textarea class="form-control" id="description" name="description" rows="2" placeholder="Brief description of the document..."></textarea>
        </div>
        <div class="mb-3">
            <label for="document" class="form-label">Select File <span class="text-danger">*</span></label>
            <input type="file" class="form-control" id="document" name="document" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
            <div class="form-text">Max file size: 5MB. Allowed types: PDF, DOCX, JPG, PNG.</div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Upload Document</button>
    </div>
</form>
