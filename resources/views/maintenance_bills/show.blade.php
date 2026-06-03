<x-user-page>
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h4><a href="{{ route('maintenance-bills.index') }}" class="text-decoration-none text-dark"><i class="fa-solid fa-arrow-left"></i></a> Maintenance Details</h4>
            <a href="{{ route('maintenance-bills.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-sm-6 col-lg mb-3">
        <div class="card text-white bg-primary h-100">
            <div class="card-body pb-3 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ $maintenance->month }}, {{ $maintenance->year }}</div>
                    <div>Month & Year</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg mb-3">
        <div class="card text-white bg-danger h-100">
            <div class="card-body pb-3 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ \Carbon\Carbon::parse($maintenance->due_date)->format('F d, Y') }}</div>
                    <div>Due Date</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg mb-3">
        <div class="card text-white bg-success h-100">
            <div class="card-body pb-3 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold" id="paid-count-display">{{ $maintenance->maintenanceBills->where('status', 'paid')->count() }}/{{ $maintenance->maintenanceBills->count() }}</div>
                    <div>Paid Maintenance Apartments</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg mb-3">
        <div class="card text-white bg-warning h-100">
            <div class="card-body pb-3 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">${{ number_format($maintenance->total_additional_cost, 2) }}</div>
                    <div>Total Additional Cost</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg mb-3">
        <div class="card text-white bg-info h-100">
            <div class="card-body pb-3 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold" id="total-amount-display">${{ number_format($maintenance->maintenanceBills->sum('total_amount'), 2) }}</div>
                    <div>Total Amount Expected</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    {{ $dataTable->table() }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    <script>
        $(document).ready(function() {
            $(document).on('submit', '.ajax-status-form', function(e) {
                e.preventDefault();
                var form = $(this);
                var url = form.attr('action');
                var formData = form.serialize();
                var submitBtn = form.find('button[type="submit"]');
                var originalText = submitBtn.html();
                
                submitBtn.html('<i class="fa-solid fa-spinner fa-spin"></i>').prop('disabled', true);

                $.ajax({
                    type: 'POST',
                    url: url,
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            if (window.LaravelDataTables && window.LaravelDataTables["maintenancedetails-table"]) {
                                window.LaravelDataTables["maintenancedetails-table"].ajax.reload(null, false);
                            }
                            
                            // Update summary cards
                            if (response.paidCount !== undefined && response.totalCount !== undefined) {
                                $('#paid-count-display').text(response.paidCount + '/' + response.totalCount);
                            }
                            if (response.totalAmountExpected !== undefined) {
                                $('#total-amount-display').text('$' + response.totalAmountExpected);
                            }
                            
                            if (typeof toastr !== 'undefined') {
                                toastr.success(response.message);
                            }
                        } else {
                            if (typeof toastr !== 'undefined') {
                                toastr.error(response.message || 'Error updating status');
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error('Error updating status', xhr);
                        if (typeof toastr !== 'undefined') {
                            toastr.error('Error updating status');
                        }
                    },
                    complete: function() {
                        submitBtn.html(originalText).prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endpush
</x-user-page>
