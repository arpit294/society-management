<x-user-page>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Edit Block</h4>
            <p class="text-muted mb-0">Update block details.</p>
        </div>

        <a href="{{ route('blocks.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('blocks.update', $block->id) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Block Name</label>
                        <input type="text" name="block_name" class="form-control"
                            value="{{ old('block_name', $block->block_name) }}" required>
                        @error('block_name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Total Floor</label>
                        <input type="number" name="total_floor" class="form-control"
                            value="{{ old('total_floor', $block->total_floor) }}" min="0" required>
                        @error('total_floor')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Total Flats</label>
                        <input type="number" name="total_flats" class="form-control"
                            value="{{ old('total_flats', $block->total_flats) }}" min="0" required>
                        @error('total_flats')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('blocks.index') }}" class="btn btn-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-user-page>
