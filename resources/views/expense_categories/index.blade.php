<x-user-page>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Expense Categories</h4>
        <button type="button" class="btn btn-primary" id="btn-add-expense-category"
            data-url="{{ route('expense-categories.create') }}" data-title="Add Expense Category">
            <i class="fa-solid fa-plus me-2"></i>Add Category
        </button>
    </div>

    <div class="card">
        <div class="card-body p-0 p-lg-3">
            <div class="table-responsive">
                {{ $dataTable->table() }}
            </div>
        </div>
    </div>

    <!-- Expense Category Modal -->
    <div class="modal fade" id="expense-category-modal" tabindex="-1" aria-labelledby="expenseCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content" id="expense-category-modal-content">
                <!-- Content will be loaded here via AJAX -->
            </div>
        </div>
    </div>

    @push('scripts')
        {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    @endpush
</x-user-page>
