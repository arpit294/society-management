<x-user-page>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Treasury & Bank Accounts</h2>
            <p class="text-muted small mb-0">Society bank accounts, cash registers, real-time balances, and passbook logs</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.banking.reconciliation.index') }}" class="btn btn-outline-info btn-sm px-3">
                <i class="fa-solid fa-scale-balanced me-1"></i> Bank Reconciliation (BRS)
            </a>
            <button type="button" class="btn btn-primary btn-sm px-3" data-coreui-toggle="modal" data-coreui-target="#modalAddBank">
                <i class="fa-solid fa-plus me-1"></i> Add Bank Account
            </button>
        </div>
    </div>

    <!-- Accounts Cards Grid -->
    <div class="row g-4 mb-4">
        @foreach($bankAccounts as $bank)
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge {{ $bank->account_type === 'cash' ? 'bg-warning text-dark' : 'bg-primary-subtle text-primary border border-primary-subtle' }}">
                                {{ strtoupper($bank->account_type) }} ACCOUNT
                            </span>
                            <span class="badge bg-success">Active</span>
                        </div>
                        <h5 class="fw-bold mb-1">{{ $bank->bank_name }}</h5>
                        <p class="text-muted small mb-2">{{ $bank->account_name }}</p>
                        <p class="small mb-3">
                            <strong>A/C No:</strong> {{ $bank->account_number }}<br>
                            @if($bank->ifsc_code) <strong>IFSC:</strong> {{ $bank->ifsc_code }}<br> @endif
                            @if($bank->branch) <strong>Branch:</strong> {{ $bank->branch }} @endif
                        </p>
                    </div>
                    <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Current Balance</span>
                            <h4 class="fw-bold text-success mb-0">₹{{ number_format($bank->current_balance, 2) }}</h4>
                        </div>
                        <a href="{{ route('finance.banking.accounts.show', $bank->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-book-open me-1"></i> Passbook
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Modal: Add Bank Account -->
<div class="modal fade" id="modalAddBank" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formAddBank" action="{{ route('finance.banking.accounts.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold">Add Society Bank / Cash Account</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bank Name <span class="text-danger">*</span></label>
                        <input type="text" name="bank_name" class="form-control" required placeholder="e.g. HDFC Bank, ICICI Bank">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Account Display Name <span class="text-danger">*</span></label>
                        <input type="text" name="account_name" class="form-control" required placeholder="e.g. Society General Collection A/c">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Account Number <span class="text-danger">*</span></label>
                            <input type="text" name="account_number" class="form-control" required placeholder="Account No.">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Account Type <span class="text-danger">*</span></label>
                            <select name="account_type" class="form-select" required>
                                <option value="savings">Savings Account</option>
                                <option value="current">Current Account</option>
                                <option value="escrow">Escrow Account</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">IFSC Code</label>
                            <input type="text" name="ifsc_code" class="form-control" placeholder="e.g. HDFC0001234">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Branch Name</label>
                            <input type="text" name="branch" class="form-control" placeholder="Branch location">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Opening Balance (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="opening_balance" class="form-control" value="0.00" required>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-coreui-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#formAddBank').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                alert(res.message);
                window.location.reload();
            },
            error: function(err) {
                alert(err.responseJSON ? err.responseJSON.message : 'Error adding bank account');
            }
        });
    });
});
</script>
@endpush
</x-user-page>
