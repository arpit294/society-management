<form action="{{ route('flat-types.update', $flatType->id) }}" method="POST" id="flat-type-ajax-form">
    @csrf
    @method('PUT')
    <div class="modal-header">
        <h5 class="modal-title">Edit Flat Type</h5>
        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12 mb-3">
                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $flatType->name }}" required>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="maintenance_fee" class="form-label">Maintenance Fee <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" class="form-control" id="maintenance_fee" name="maintenance_fee" value="{{ $flatType->maintenance_fee }}" required>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select class="form-select" id="status" name="status" required>
                    <option value="active" {{ $flatType->status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $flatType->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Update Flat Type</button>
    </div>
</form>
