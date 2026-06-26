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
            <h6 class="alert-heading fw-bold"><i class="fa-solid fa-circle-info me-2"></i>Current Owner</h6>
            <p class="mb-0">
                <strong>Name:</strong> {{ $currentOwner->user->name ?? 'Unknown' }}<br>
                <strong>Move-in Date:</strong>
                {{ $currentOwner->move_in_date ? \Carbon\Carbon::parse($currentOwner->move_in_date)->format('d M Y') : 'N/A' }}
            </p>
            <p class="mb-0 mt-2 small text-muted">
                Transferring ownership will set the move-out date for the current owner and generate a Name Transfer
                Bill for the new owner.
            </p>
        </div>

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
                    <div class="col-md-12 mb-3">
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
                            class="form-label text-muted small fw-semibold text-uppercase">Transfer ID
                            (Optional)</label>
                        <input type="text" class="form-control" id="transaction_id" name="transaction_id">
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

    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-warning" id="btn-save-transfer">Transfer Ownership</button>
    </div>
</form>
