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
                    <div class="fs-4 fw-semibold">₹{{ number_format($maintenance->total_additional_cost, 2) }}</div>
                    <div>Total Additional Cost</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg mb-3">
        <div class="card text-white bg-info h-100">
            <div class="card-body pb-3 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold" id="total-amount-display">₹{{ number_format($maintenance->maintenanceBills->sum('total_amount'), 2) }}</div>
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
        function openPayModal(billId) {
            var form = document.getElementById('payForm');
            form.action = '/maintenance-bills/' + billId + '/update-status';
            var modal = new coreui.Modal(document.getElementById('payModal'));
            modal.show();
        }

        document.addEventListener('DOMContentLoaded', function() {
            $('.dropify').dropify();
            const paymentMethodSelect = document.getElementById('bill_payment_method');
            const upiDetails = document.getElementById('bill-upi-details');
            const paymentSlip = document.getElementById('bill_payment_slip');

            paymentMethodSelect.addEventListener('change', function() {
                if (this.value === 'upi') {
                    upiDetails.classList.remove('d-none');
                    paymentSlip.setAttribute('required', 'required');
                } else {
                    upiDetails.classList.add('d-none');
                    paymentSlip.removeAttribute('required');
                }
            });

            const payForm = document.getElementById('payForm');
            payForm.addEventListener('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        var modal = coreui.Modal.getInstance(document.getElementById('payModal'));
                        modal.hide();
                        payForm.reset();
                        upiDetails.classList.add('d-none');
                        
                        $('#maintenancedetails-table').DataTable().ajax.reload();
                        document.getElementById('paid-count-display').innerText = data.paidCount + '/' + data.totalCount;
                        document.getElementById('total-amount-display').innerText = '₹' + data.totalAmountExpected;
                    } else {
                        let msg = data.message || 'Error updating status';
                        if(data.errors) {
                            msg += '\n' + Object.values(data.errors).join('\n');
                        }
                        alert(msg);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating status');
                });
            });
        });
    </script>
@endpush

<!-- Pay Modal -->
<div class="modal fade" id="payModal" tabindex="-1" aria-labelledby="payModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="payForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="status" value="paid">
                <div class="modal-header">
                    <h5 class="modal-title" id="payModalLabel">Pay Maintenance Bill</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="bill_payment_method" class="form-label">Payment Mode <span class="text-danger">*</span></label>
                        <select name="payment_method" id="bill_payment_method" class="form-select" required>
                            <option value="">Select Mode</option>
                            <option value="cash">Cash</option>
                            <option value="upi">UPI</option>
                        </select>
                    </div>
                    <div id="bill-upi-details" class="d-none">
                        <div class="mb-3">
                            <label for="bill_transaction_id" class="form-label">Transaction ID (Optional)</label>
                            <input type="text" name="transaction_id" id="bill_transaction_id" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="bill_payment_slip" class="form-label">Payment Slip Screenshot <span class="text-danger">*</span></label>
                            <input type="file" name="payment_slip" id="bill_payment_slip" class="dropify" accept="image/*" data-height="150">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

</x-user-page>
