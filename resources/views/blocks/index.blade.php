<x-user-page>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Block Management</h4>
        <a href="{{ route('blocks.create') }}" class="btn btn-primary">+ Add Block</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Block Name</th>
                            <th>Total Floor</th>
                            <th>Total Flats</th>
                            <th width="160">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($blocks as $block)
                            <tr>
                                <td>{{ $block->id }}</td>
                                <td>{{ $block->block_name }}</td>
                                <td>{{ $block->total_floor }}</td>
                                <td>{{ $block->total_flats }}</td>
                                <td>
                                    <a href="{{ route('blocks.edit', $block->id) }}"
                                        class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form action="{{ route('blocks.destroy', $block->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Delete this block?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No blocks found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-user-page>
