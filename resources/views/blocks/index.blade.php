<x-user-page>



    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ \App\Models\Setting::label('block', 'Block') }} Management</h4>

        @can('block_create')
        <button type="button" class="btn btn-primary" id="btn-add-block" data-url="{{ route('blocks.create') }}"
            data-title="Add {{ \App\Models\Setting::label('block', 'Block') }}">Add {{ \App\Models\Setting::label('block', 'Block') }}</button>
        @endcan
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            {{ $dataTable->table() }}
        </div>
    </div>

    <div class="modal fade" id="block-modal" tabindex="-1" aria-labelledby="block-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" id="block-modal-content"></div>
        </div>
    </div>

    @push('scripts')
        {{ $dataTable->scripts(attributes: ['type' => 'module']) }}

    @endpush
</x-user-page>
