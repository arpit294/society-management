<x-user-page>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">{{ \App\Models\Setting::label('block', 'Block') }} Details</h4>
            <p class="text-muted mb-0">View {{ strtolower(\App\Models\Setting::label('block', 'block')) }} information.</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('blocks.edit', $block->id) }}" class="btn btn-outline-primary">Edit</a>
            <a href="{{ route('blocks.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6"><strong>ID:</strong> {{ $block->id }}</div>
                <div class="col-md-6"><strong>{{ \App\Models\Setting::label('block', 'Block') }} Name:</strong> {{ $block->block_name }}</div>
                <div class="col-md-6 mt-2"><strong>Total Floors / Levels:</strong> {{ $block->total_floor }}</div>
                <div class="col-md-6 mt-2"><strong>Total {{ \App\Models\Setting::label('unit_plural', 'Flats') }} Capacity:</strong> {{ $block->total_flats }}</div>
                <div class="col-md-6 mt-2"><strong>Created At:</strong> {{ $block->created_at }}</div>
            </div>
        </div>
    </div>
</x-user-page>
