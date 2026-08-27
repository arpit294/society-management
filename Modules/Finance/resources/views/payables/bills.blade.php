<x-user-page>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Vendor Invoices & Bills</h2>
            <p class="text-muted small mb-0">Record incoming bills from contractors and suppliers with automatic expense journal posting</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.vendors.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                <i class="fa-solid fa-users-gear me-1"></i> Manage Vendors
            </a>
            <button type="button" class="btn btn-primary btn-sm px-3" data-coreui-toggle="modal" data-coreui-target="#modalAddBill">
                <i class="fa-solid fa-plus me-1"></i> Record Vendor Bill
            </button>
        </div>
    </div>

    <!-- DataTable Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <div class="table-responsive">
                {!! $dataTable->table(['class' => 'table table-hover align-middle w-100']) !!}
            </div>
        </div>
    </div>
</div>

<!-- Modal: Record Vendor Bill -->
<div class="modal fade" id="modalAddBill" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formAddBill" action="{{ route('finance.vendor-bills.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold">Record Incoming Vendor Bill</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Vendor / Contractor <span class="text-danger">*</span></label>
                        <select name="vendor_id" class="form-select" required>
                            <option value="">-- Select Vendor --</option>
                            @foreach($vendors as $v)
                                <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->service_type }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Expense Head (Chart of Accounts) <span class="text-danger">*</span></label>
                        <select name="expense_account_id" class="form-select" required>
                            @foreach($expenseAccounts as $exp)
                                <option value="{{ $exp->id }}">{{ $exp->code }} - {{ $exp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Vendor Bill Number <span class="text-danger">*</span></label>
                            <input type="text" name="bill_number" class="form-control" required placeholder="e.g. INV-8821">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Bill Subtotal (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="subtotal" class="form-control" required placeholder="0.00">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Bill Date <span class="text-danger">*</span></label>
                            <input type="date" name="bill_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Due Date <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+15 days')) }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">GST / Tax Amount (Optional)</label>
                        <input type="number" step="0.01" name="tax_amount" class="form-control" value="0.00">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bill Attachment (Invoice scan / PDF)</label>
                        <input type="file" name="attachment" class="form-control">
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-coreui-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Post Vendor Bill</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Create Payment Voucher for Bill -->
<div class="modal fade" id="modalCreateVoucher" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formCreateVoucher" action="{{ route('finance.vouchers.store') }}" method="POST">
                @csrf
                <input type="hidden" name="vendor_bill_id" id="vch_bill_id">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold">Create Outward Payment Voucher</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Vendor:</span>
                            <span class="fw-bold" id="vch_vendor_name">-</span>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <span class="text-muted">Bill Number:</span>
                            <span class="fw-bold" id="vch_bill_number">-</span>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <span class="text-muted">Balance Due:</span>
                            <span class="fw-bold text-danger fs-5" id="vch_balance_text">₹0.00</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Voucher Payout Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" id="vch_amount" class="form-control form-control-lg fw-bold text-danger" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Paying Bank / Cash Account <span class="text-danger">*</span></label>
                        <select name="bank_account_id" class="form-select" required>
                            @foreach($bankAccounts as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->bank_name }} (Avail: ₹{{ number_format($bank->current_balance, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Mode <span class="text-danger">*</span></label>
                        <select name="payment_mode" class="form-select" required>
                            <option value="bank_transfer">Bank Transfer (NEFT / RTGS)</option>
                            <option value="cheque">Cheque</option>
                            <option value="upi">UPI</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Cheque No. / Transaction Ref</label>
                        <input type="text" name="reference_no" class="form-control" placeholder="e.g. Cheque #554411 / UTR">
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-coreui-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success px-4">Generate Voucher</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
{!! $dataTable->scripts() !!}
<script>
$(document).ready(function() {
    $('#formAddBill').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                $('#modalAddBill').modal('hide');
                window.LaravelDataTables["finance-vendor-bills-table"].ajax.reload();
                alert(res.message);
            },
            error: function(err) {
                alert(err.responseJSON ? err.responseJSON.message : 'Error saving vendor bill');
            }
        });
    });

    $(document).on('click', '.btn-create-voucher', function() {
        var billId = $(this).data('id');
        var balance = $(this).data('balance');
        var vendor = $(this).data('vendor');
        var billNum = $(this).data('bill');

        $('#vch_bill_id').val(billId);
        $('#vch_vendor_name').text(vendor);
        $('#vch_bill_number').text(billNum);
        $('#vch_balance_text').text('₹' + parseFloat(balance).toFixed(2));
        $('#vch_amount').val(balance).attr('max', balance);

        $('#modalCreateVoucher').modal('show');
    });

    $('#formCreateVoucher').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                $('#modalCreateVoucher').modal('hide');
                window.LaravelDataTables["finance-vendor-bills-table"].ajax.reload();
                alert(res.message);
            },
            error: function(err) {
                alert(err.responseJSON ? err.responseJSON.message : 'Error creating voucher');
            }
        });
    });
});
</script>
@endpush
</x-user-page>
