<x-user-page>
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Expense Categories</h4>
                @can('expense_category_create')
                <button type="button" class="btn btn-primary" id="btn-add-expense-category"
                    data-url="{{ route('expense-categories.create') }}" data-title="Add Expense Category">
                    <i class="fa-solid fa-plus me-1"></i> Add Category
                </button>
                @endcan
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    {{ $dataTable->table(['class' => 'table table-bordered table-striped table-hover w-100', 'id' => 'expense-categories-table']) }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Expense Category Modal -->
<div class="modal fade" id="expense-category-modal" tabindex="-1" aria-labelledby="expenseCategoryModalLabel" aria-hidden="true" data-coreui-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content" id="expense-category-modal-content">
            <!-- Modal Content will be loaded via AJAX -->
        </div>
    </div>
</div>

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
@endpush
</x-user-page>
