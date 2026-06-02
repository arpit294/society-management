<x-layout>
    <div id="users-toasts" class="users-toast-container" aria-live="polite" aria-atomic="true"></div>

    <x-sidebar />
    <div class="wrapper d-flex flex-column min-vh-100">
        <x-header />
        <div class="body flex-grow-1">
            <div class="container-lg px-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">Expenses</h4>
                    <button class="btn btn-primary" id="btn-add-expense" data-url="{{ route('expenses.create') }}" data-title="Add New Expense">
                        <i class="fa-solid fa-plus me-2"></i> Add Expense
                    </button>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            {{ $dataTable->table(['class' => 'table table-bordered table-striped table-hover w-100']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Expense Modal -->
    <div class="modal fade" id="expense-modal" tabindex="-1" aria-labelledby="expenseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="expense-modal-content">
                <!-- Content will be loaded via AJAX -->
            </div>
        </div>
    </div>

    @push('scripts')
        {{ $dataTable->scripts() }}
    @endpush
</x-layout>
