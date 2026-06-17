<x-user-page>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Flat Types</h1>
        <button type="button" class="btn btn-primary" id="btn-add-flat-type"
            data-url="{{ route('flat-types.create') }}" data-title="Add Flat Type">
            <i class="fa-solid fa-plus me-1"></i> Add Flat Type
        </button>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-body">
                <div class="table-responsive">
                    {{ $dataTable->table(['class' => 'table table-bordered table-striped table-hover w-100', 'id' => 'flat-types-table']) }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Flat Type Modal -->
<div class="modal fade" id="flat-type-modal" tabindex="-1" aria-labelledby="flatTypeModalLabel" aria-hidden="true" data-coreui-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content" id="flat-type-modal-content">
            <!-- Modal Content will be loaded via AJAX -->
        </div>
    </div>
</div>

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
@endpush
</x-user-page>
