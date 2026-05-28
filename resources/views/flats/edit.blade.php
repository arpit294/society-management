<x-user-page>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Edit Flat</h4>
            <p class="text-muted mb-0">Update flat details.</p>
        </div>

        <a href="{{ route('flats.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('flats.update', $flat->id) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Block ID</label>
                        <input type="number" name="block_id" class="form-control"
                            value="{{ old('block_id', $flat->block_id) }}">
                        @error('block_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Flat No</label>
                        <input type="text" name="flat_no" class="form-control"
                            value="{{ old('flat_no', $flat->flat_no) }}" required>
                        @error('flat_no')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Floor No</label>
                        <input type="text" name="floor_no" class="form-control"
                            value="{{ old('floor_no', $flat->floor_no) }}" required>
                        @error('floor_no')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Flat Type</label>
                        <input type="text" name="flat_type" class="form-control"
                            value="{{ old('flat_type', $flat->flat_type) }}" required>
                        @error('flat_type')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Maintenance Amount</label>
                        <input type="number" step="0.01" name="maintenance_amount" class="form-control"
                            value="{{ old('maintenance_amount', $flat->maintenance_amount) }}" required>
                        @error('maintenance_amount')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <input type="text" name="status" class="form-control"
                            value="{{ old('status', $flat->status) }}" required>
                        @error('status')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('flats.index') }}" class="btn btn-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-user-page>
