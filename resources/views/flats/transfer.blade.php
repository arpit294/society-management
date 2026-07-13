<form id="flat-transfer-form" action="{{ route('flats.transfer.store', $flat->id) }}" method="POST"
    enctype="multipart/form-data">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title" id="flat-modal-label">Transfer Ownership - {{ $flat->block->block_name ?? '' }}
            {{ $flat->flat_no }}</h5>
        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="alert alert-warning mb-4">
            <h6 class="alert-heading fw-bold"><i class="fa-solid fa-circle-info me-2"></i>Current {{ \App\Models\Setting::label('resident', 'Owner') }}</h6>
            <p class="mb-0">
                <strong>Name:</strong> {{ $currentOwner->user->name ?? 'Unknown' }}<br>
                <strong>Move-in Date:</strong>
                {{ $currentOwner->move_in_date ? \Carbon\Carbon::parse($currentOwner->move_in_date)->format('d M Y') : 'N/A' }}
            </p>
            <p class="mb-0 mt-2 small text-dark">
                Transferring ownership will set the move-out date for the current {{ strtolower(\App\Models\Setting::label('resident', 'owner')) }} and generate a Name Transfer
                Bill for the new {{ strtolower(\App\Models\Setting::label('resident', 'owner')) }}.
            </p>
        </div>

        @php
            $pendingCount = isset($pendingBills) ? $pendingBills->count() : 0;
            $pendingAmount = isset($pendingBills) ? $pendingBills->sum('total_amount') : 0;
        @endphp

        @if($pendingCount > 0)
        <div class="alert alert-danger mb-4 shadow-sm border-danger" id="pending-dues-alert">
            <div class="d-flex align-items-center mb-2">
                <i class="fa-solid fa-triangle-exclamation fs-5 me-2 text-danger"></i>
                <h6 class="alert-heading fw-bold mb-0 text-danger">Pending Maintenance Dues Detected!</h6>
            </div>
            <p class="mb-2 small">
                The current owner has <strong>{{ $pendingCount }} unpaid maintenance bill(s)</strong> totaling <strong class="text-danger fs-6">{{ \App\Helpers\CurrencyHelper::formatCurrency($pendingAmount) }}</strong>.
            </p>
            <div class="bg-white p-2 rounded border mb-2" style="max-height: 110px; overflow-y: auto;">
                <ul class="mb-0 small ps-3">
                    @foreach($pendingBills as $pb)
                        <li>
                            <strong>{{ $pb->maintenance->month ?? '' }} {{ $pb->maintenance->year ?? '' }}</strong>: 
                            {{ \App\Helpers\CurrencyHelper::formatCurrency($pb->total_amount) }} 
                            <span class="badge bg-danger ms-1" style="font-size: 0.7em;">{{ strtoupper($pb->status) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <p class="mb-2 small fw-semibold text-dark">
                <i class="fa-solid fa-lock me-1"></i> Ownership transfer is restricted until all pending maintenance dues are paid by the current owner.
            </p>
            <div class="mt-3 pt-2 border-top border-danger-subtle d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="small text-danger fw-bold"><i class="fa-solid fa-circle-exclamation me-1"></i> Please clear dues before transferring</span>
                <button type="button" class="btn btn-sm btn-danger fw-bold shadow-sm d-inline-flex align-items-center gap-2" id="btn-open-pay-dues-modal">
                    <i class="fa-solid fa-credit-card"></i> Pay Now ({{ \App\Helpers\CurrencyHelper::formatCurrency($pendingAmount) }})
                </button>
            </div>
        </div>
        @else
        <div class="alert alert-success mb-4 shadow-sm border-success d-flex align-items-center">
            <i class="fa-solid fa-circle-check fs-4 me-3 text-success"></i>
            <div>
                <h6 class="alert-heading fw-bold mb-1 text-success">No Pending Maintenance Dues</h6>
                <p class="mb-0 small text-dark">All maintenance bills for this flat have been paid. You have permission to proceed with ownership transfer.</p>
            </div>
        </div>
        @endif

        <fieldset {{ $pendingCount > 0 ? 'disabled' : '' }} class="{{ $pendingCount > 0 ? 'opacity-50 pointer-events-none' : '' }}">
            <h6 class="fw-bold mb-3 border-bottom pb-2">New Owner Details</h6>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="new_owner_name" class="form-label text-muted small fw-semibold text-uppercase">Full Name
                        <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="new_owner_name" name="new_owner_name" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="new_owner_email" class="form-label text-muted small fw-semibold text-uppercase">Email <span
                            class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="new_owner_email" name="new_owner_email" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="new_owner_phone"
                        class="form-label text-muted small fw-semibold text-uppercase">Phone</label>
                    <input type="text" class="form-control" id="new_owner_phone" name="new_owner_phone">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="new_owner_aadhar" class="form-label text-muted small fw-semibold text-uppercase">Aadhar ID
                        <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="new_owner_aadhar" name="new_owner_aadhar"
                        inputmode="numeric" pattern="[0-9]{12}" maxlength="12" required>
                </div>
                <div class="col-md-12 mb-3">
                    <label for="transfer_date" class="form-label text-muted small fw-semibold text-uppercase">Transfer Date
                        <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="transfer_date" name="transfer_date"
                        value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-12 mb-3 border-top pt-3 mt-2">
                    <h6 class="fw-bold mb-3">Fee & Payment Details</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="transfer_fee"
                                class="form-label text-muted small fw-semibold text-uppercase">Transfer Fee (₹) <span
                                    class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control" id="transfer_fee" name="transfer_fee"
                                value="{{ isset($defaultFee) ? $defaultFee : 0 }}" required placeholder="Enter transfer fee amount">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="payment_method"
                                class="form-label text-muted small fw-semibold text-uppercase">Payment Method <span
                                    class="text-danger">*</span></label>
                            <select name="payment_method" id="transfer_payment_method" class="form-select" required>
                                <option value="pending">Pending (Unpaid)</option>
                                <option value="cash">Cash</option>
                                <option value="upi">UPI</option>
                            </select>
                        </div>
                    </div>
                    <div class="row" id="upi_details_container" style="display: none;">
                        <div class="col-md-6 mb-3">
                            <label for="transaction_id"
                                class="form-label text-muted small fw-semibold text-uppercase">UTR Number <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="transaction_id" name="transaction_id"
                                inputmode="numeric" pattern="[0-9]{12}" maxlength="12"
                                placeholder="Enter 12 digit UTR number">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="payment_slip"
                                class="form-label text-muted small fw-semibold text-uppercase">Screenshot (Required) <span
                                    class="text-danger">*</span></label>
                            <input type="file" class="dropify" id="payment_slip" name="payment_slip" accept="image/*"
                                data-height="120">
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-warning fw-bold" id="btn-save-transfer" {{ $pendingCount > 0 ? 'disabled' : '' }}>
            <i class="fa-solid fa-right-left me-1"></i> Transfer Ownership
        </button>
    </div>
</form>

<!-- Instant Pay Pending Dues Modal -->
<div class="modal fade" id="payPendingDuesModal" tabindex="-1" aria-labelledby="payPendingDuesModalLabel" aria-hidden="true" data-coreui-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold" id="payPendingDuesModalLabel"><i class="fa-solid fa-credit-card me-2"></i>Pay Pending Maintenance Dues</h5>
                <button type="button" class="btn-close btn-close-white" id="btn-close-pay-modal" aria-label="Close"></button>
            </div>
            <form id="pay-pending-dues-form">
                @csrf
                <div class="modal-body p-4 text-start">
                    <div class="bg-light p-3 rounded mb-3 border">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">{{ \App\Models\Setting::label('unit', 'Flat') }}:</span>
                            <strong class="text-dark">{{ $flat->block->block_name ?? '' }} - {{ $flat->flat_no }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Current {{ \App\Models\Setting::label('resident', 'Owner') }}:</span>
                            <strong class="text-dark">{{ $currentOwner->user->name ?? 'Unknown' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Bills Count:</span>
                            <strong class="text-dark">{{ $pendingCount }} Unpaid Bill(s)</strong>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark">Total Amount Due:</span>
                            <span class="fs-5 fw-bold text-danger">{{ \App\Helpers\CurrencyHelper::formatCurrency($pendingAmount) }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="modal_payment_method" class="form-label fw-bold text-dark small text-uppercase">Select Payment Mode <span class="text-danger">*</span></label>
                        <select name="payment_method" id="modal_payment_method" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="upi">UPI / Online</option>
                        </select>
                    </div>

                    <div class="mb-3" id="modal_upi_details" style="display: none;">
                        <label for="modal_transaction_id" class="form-label fw-bold text-dark small text-uppercase">Transaction ID / UTR <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modal_transaction_id" name="transaction_id" placeholder="Enter transaction or UTR number">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" id="btn-cancel-pay-modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold px-4" id="btn-confirm-pay-dues">
                        <i class="fa-solid fa-check-circle me-1"></i> Confirm & Pay Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="flat-transfer-config" class="d-none"
    data-pending-count="{{ isset($pendingBills) ? $pendingBills->count() : 0 }}"
    data-pay-dues-url="{{ route('flats.pay-pending-dues', $flat->id) }}"
    data-transfer-url="{{ route('flats.transfer.create', $flat->id) }}">
</div>
