<form id="flat-ajax-form" method="POST" action="{{ route('flats.update', $flat->id) }}">
    @csrf
    @method('PUT')

    <div class="modal-header">
        <div>
            <h5 class="modal-title mb-1">Edit Flat</h5>
            <p class="text-muted mb-0 small">Update the flat record details.</p>
        </div>
        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <div id="user-form-errors" class="alert alert-danger d-none"></div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Block</label>
                <select name="block_id" class="form-control">
                    <option value="">Select Block</option>
                    @foreach($blocks as $block)
                        <option value="{{ $block->id }}" data-total-floor="{{ $block->total_floor }}" {{ old('block_id', $flat->block_id) == $block->id ? 'selected' : '' }}>{{ $block->block_name }}</option>
                    @endforeach
                </select>
                @error('block_id')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Flat No</label>
                <input type="text" name="flat_no" class="form-control" value="{{ old('flat_no', $flat->flat_no) }}">
                @error('flat_no')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Floor No</label>
                <input type="number" id="floor_no" name="floor_no" class="form-control" value="{{ old('floor_no', $flat->floor_no) }}" min="0">
                <small class="text-muted d-none" id="floor-help">Max floors: <span></span></small>
                @error('floor_no')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Flat Type <span class="text-danger">*</span></label>
                <select name="flat_type_id" class="form-select" required>
                    <option value="">Select Flat Type</option>
                    @foreach ($flatTypes as $type)
                        <option value="{{ $type->id }}" {{ old('flat_type_id', $flat->flat_type_id) == $type->id ? 'selected' : '' }}>
                            {{ $type->name }} (₹{{ number_format($type->maintenance_fee, 2) }})
                        </option>
                    @endforeach
                </select>
                @error('flat_type_id')
                    <div class="invalid-feedback d-block field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Select Status</option>
                    <option value="Empty" {{ old('status', $flat->status) == 'Empty' ? 'selected' : '' }}>Empty</option>
                    <option value="Occupied" {{ old('status', $flat->status) == 'Occupied' ? 'selected' : '' }}>Occupied</option>
                </select>
                @error('status')
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

