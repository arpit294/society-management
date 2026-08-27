<x-user-page>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Invoices & Billing</h2>
            <p class="text-muted small mb-0">Automated maintenance billing, auxiliary invoices, and resident collections</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success btn-sm px-3" data-coreui-toggle="modal" data-coreui-target="#modalBatchGenerate">
                <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Generate Monthly Bills
            </button>
            <button type="button" class="btn btn-primary btn-sm px-3" data-coreui-toggle="modal" data-coreui-target="#modalCreateInvoice">
                <i class="fa-solid fa-plus me-1"></i> Create Custom Invoice
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

<!-- Modal: Batch Generate Bills -->
<div class="modal fade" id="modalBatchGenerate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formBatchGenerate" action="{{ route('finance.invoices.batch-generate') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold">Generate Monthly Maintenance Bills</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Billing Month <span class="text-danger">*</span></label>
                        <select name="month" class="form-select" required>
                            @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m)
                                <option value="{{ $m }}" {{ $m === date('F') ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Billing Year <span class="text-danger">*</span></label>
                        <input type="number" name="year" class="form-control" value="{{ date('Y') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-20') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Target Block (Optional)</label>
                        <select name="block_id" class="form-select">
                            <option value="">All Blocks (All Occupied Units)</option>
                            @foreach($blocks as $b)
                                <option value="{{ $b->id }}">Block {{ $b->block_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-coreui-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success px-4" id="btnSubmitBatch">
                        <i class="fa-solid fa-bolt me-1"></i> Generate Invoices
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Collect Payment -->
<div class="modal fade" id="modalCollectPayment" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formCollectPayment" action="{{ route('finance.payments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="invoice_id" id="pay_invoice_id">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold">Collect Invoice Payment</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Invoice:</span>
                            <span class="fw-bold" id="pay_invoice_number">-</span>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <span class="text-muted">Remaining Balance Due:</span>
                            <span class="fw-bold text-danger fs-5" id="pay_balance_text">₹0.00</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Amount to Collect (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" id="pay_amount" class="form-control form-control-lg fw-bold text-success" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deposited Into Bank Account <span class="text-danger">*</span></label>
                        <select name="bank_account_id" class="form-select" required>
                            @foreach($bankAccounts as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->bank_name }} ({{ $bank->account_number }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Payment Mode <span class="text-danger">*</span></label>
                            <select name="payment_mode" class="form-select" required>
                                <option value="bank_transfer">Bank Transfer / NEFT / IMPS</option>
                                <option value="upi">UPI / QR Code</option>
                                <option value="cheque">Cheque</option>
                                <option value="cash">Cash</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Transaction Reference / Cheque No.</label>
                        <input type="text" name="transaction_reference" class="form-control" placeholder="e.g. UTR / Cheque #123456">
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-coreui-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success px-4" id="btnSubmitPayment">
                        <i class="fa-solid fa-check me-1"></i> Save & Generate Receipt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Create Custom / Auxiliary Invoice -->
<div class="modal fade" id="modalCreateInvoice" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formCreateInvoice" action="{{ route('finance.invoices.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold">Create Auxiliary / One-Off Invoice</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Property Unit <span class="text-danger">*</span></label>
                        <select name="flat_id" id="aux_flat_id" class="form-select" required>
                            <option value="">-- Choose Unit --</option>
                            @foreach($flats as $f)
                                @php $res = $f->residents->first(); @endphp
                                <option value="{{ $f->id }}" data-user-id="{{ $res?->user_id }}">
                                    {{ $f->block ? 'Block ' . $f->block->block_name . ' - ' : '' }}{{ $f->flat_no }} ({{ $res?->user?->name ?? 'No Resident' }})
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="user_id" id="aux_user_id">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Income Category <span class="text-danger">*</span></label>
                        <select name="income_account_id" class="form-select" required>
                            @foreach($incomeAccounts as $inc)
                                <option value="{{ $inc->id }}">{{ $inc->code }} - {{ $inc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Invoice Type <span class="text-danger">*</span></label>
                            <select name="invoice_type" class="form-select" required>
                                <option value="name_transfer">Name Transfer Fee</option>
                                <option value="noc">NOC Charge</option>
                                <option value="amenity_booking">Clubhouse / Amenity</option>
                                <option value="penalty">Penalty / Fine</option>
                                <option value="custom">Other Custom Charge</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" class="form-control" required placeholder="0.00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bill Item Title <span class="text-danger">*</span></label>
                        <input type="text" name="item_name" class="form-control" required placeholder="e.g. NOC Processing Fee">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-coreui-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4" id="btnSubmitCustomInv">Create Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
{!! $dataTable->scripts() !!}
<script>
$(document).ready(function() {
    // Select resident auto-assign on flat pick
    $('#aux_flat_id').on('change', function() {
        var userId = $(this).find(':selected').data('user-id');
        $('#aux_user_id').val(userId);
    });

    // Batch Generate AJAX
    $('#formBatchGenerate').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btnSubmitBatch');
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Generating...');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                $('#modalBatchGenerate').modal('hide');
                window.LaravelDataTables["finance-invoices-table"].ajax.reload();
                alert(res.message);
                $btn.prop('disabled', false).html('<i class="fa-solid fa-bolt me-1"></i> Generate Invoices');
            },
            error: function(err) {
                alert(err.responseJSON ? err.responseJSON.message : 'Error generating bills');
                $btn.prop('disabled', false).html('<i class="fa-solid fa-bolt me-1"></i> Generate Invoices');
            }
        });
    });

    // Collect Payment Modal Trigger
    $(document).on('click', '.btn-collect-payment', function() {
        var invId = $(this).data('id');
        var balance = $(this).data('balance');
        var invNum = $(this).data('number');

        $('#pay_invoice_id').val(invId);
        $('#pay_invoice_number').text(invNum);
        $('#pay_balance_text').text('₹' + parseFloat(balance).toFixed(2));
        $('#pay_amount').val(balance).attr('max', balance);

        $('#modalCollectPayment').modal('show');
    });

    // Collect Payment AJAX Submit
    $('#formCollectPayment').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btnSubmitPayment');
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Saving...');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                $('#modalCollectPayment').modal('hide');
                window.LaravelDataTables["finance-invoices-table"].ajax.reload();
                alert(res.message);
                $btn.prop('disabled', false).html('<i class="fa-solid fa-check me-1"></i> Save & Generate Receipt');
            },
            error: function(err) {
                alert(err.responseJSON ? err.responseJSON.message : 'Error processing payment');
                $btn.prop('disabled', false).html('<i class="fa-solid fa-check me-1"></i> Save & Generate Receipt');
            }
        });
    });

    // Custom Invoice AJAX Submit
    $('#formCreateInvoice').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btnSubmitCustomInv');
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Creating...');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                $('#modalCreateInvoice').modal('hide');
                window.LaravelDataTables["finance-invoices-table"].ajax.reload();
                alert(res.message);
                $btn.prop('disabled', false).text('Create Invoice');
            },
            error: function(err) {
                alert(err.responseJSON ? err.responseJSON.message : 'Error creating invoice');
                $btn.prop('disabled', false).text('Create Invoice');
            }
        });
    });
});
</script>
@endpush
</x-user-page>
