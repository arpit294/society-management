<form id="block-ajax-form" method="POST" action="{{ route('blocks.update', $block->id) }}">
    @csrf
    @method('PUT')

    <div class="modal-header">
        <div>
            <h5 class="modal-title mb-1">Edit Block</h5>
            <p class="text-muted mb-0 small">Update the details of the block.</p>
        </div>
        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <div id="user-form-errors" class="alert alert-danger d-none"></div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Block Name</label>
                <input type="text" name="block_name" class="form-control"
                    value="{{ old('block_name', $block->block_name) }}">
                @error('block_name')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Total Floor</label>
                <input type="number" name="total_floor" class="form-control"
                    value="{{ old('total_floor', $block->total_floor) }}" min="0">
                @error('total_floor')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Total Flats</label>
                <input type="number" name="total_flats" class="form-control"
                    value="{{ old('total_flats', $block->total_flats) }}" min="0">
                @error('total_flats')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Update</button>
    </div>
</form>
