<x-user-page>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Flat Details</h4>
            <p class="text-muted mb-0">View flat information.</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('flats.edit', $flat->id) }}" class="btn btn-outline-primary">Edit</a>
            <a href="{{ route('flats.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4"><strong>ID:</strong> {{ $flat->id }}</div>
                <div class="col-md-4"><strong>Block ID:</strong> {{ $flat->block_id }}</div>
                <div class="col-md-4"><strong>Flat No:</strong> {{ $flat->flat_no }}</div>
                <div class="col-md-4 mt-2"><strong>Floor No:</strong> {{ $flat->floor_no }}</div>
                <div class="col-md-4 mt-2"><strong>Flat Type:</strong> {{ $flat->flat_type }}</div>
                <div class="col-md-4 mt-2"><strong>Maintenance:</strong> {{ $flat->maintenance_amount }}</div>
                <div class="col-md-4 mt-2"><strong>Status:</strong> {{ $flat->status }}</div>
                <div class="col-md-4 mt-2"><strong>Created At:</strong> {{ $flat->created_at }}</div>
                <div class="col-md-4 mt-2"><strong>Updated At:</strong> {{ $flat->updated_at }}</div>
            </div>
        </div>
    </div>
</x-user-page>
