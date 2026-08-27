<x-user-page>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Sinking Funds & Fixed Deposits</h2>
            <p class="text-muted small mb-0">Track long-term society reserves, repair corpus, bank FDs, and maturity schedules</p>
        </div>
        <button type="button" class="btn btn-primary btn-sm px-3" data-coreui-toggle="modal" data-coreui-target="#modalAddFund">
            <i class="fa-solid fa-plus me-1"></i> New Fund / Fixed Deposit
        </button>
    </div>

    <!-- Funds Cards Grid -->
    <div class="row g-4 mb-4">
        @foreach($funds as $f)
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge {{ $f->type === 'fixed_deposit' ? 'bg-info text-dark' : 'bg-primary' }}">
                                {{ strtoupper(str_replace('_', ' ', $f->type)) }}
                            </span>
                            <span class="badge bg-success">{{ ucfirst($f->status) }}</span>
                        </div>
                        <h5 class="fw-bold mb-1">{{ $f->name }}</h5>
                        @if($f->certificate_no)
                            <p class="small text-muted mb-2">Cert #: <strong>{{ $f->certificate_no }}</strong></p>
                        @endif
                        <p class="small mb-3">
                            <strong>Interest Rate:</strong> {{ $f->interest_rate ? $f->interest_rate . '% p.a.' : 'N/A' }}<br>
                            @if($f->start_date) <strong>Start Date:</strong> {{ $f->start_date->format('d M Y') }}<br> @endif
                            @if($f->maturity_date) <strong>Maturity:</strong> {{ $f->maturity_date->format('d M Y') }} @endif
                        </p>
                    </div>
                    <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Current Valuation</span>
                            <h4 class="fw-bold text-success mb-0">₹{{ number_format($f->current_balance, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Modal: Add Fund / FD -->
<div class="modal fade" id="modalAddFund" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formAddFund" action="{{ route('finance.funds.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold">Add Sinking Fund / Fixed Deposit</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Fund / Investment Title <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Sinking Fund FD #1, Painting Reserve">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Fund Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="sinking_fund">Sinking Fund</option>
                                <option value="reserve_fund">Repair / Reserve Fund</option>
                                <option value="fixed_deposit">Bank Fixed Deposit (FD)</option>
                                <option value="corpus_fund">General Corpus</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Ledger Head <span class="text-danger">*</span></label>
                            <select name="account_id" class="form-select" required>
                                @foreach($equityAccounts as $eq)
                                    <option value="{{ $eq->id }}">{{ $eq->code }} - {{ $eq->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Principal / Current Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="principal_amount" class="form-control" required placeholder="0.00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Interest Rate (% p.a.)</label>
                            <input type="number" step="0.01" name="interest_rate" class="form-control" placeholder="e.g. 7.25">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Maturity Date</label>
                            <input type="date" name="maturity_date" class="form-control" value="{{ date('Y-m-d', strtotime('+1 year')) }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">FD Certificate / Reference Number</label>
                        <input type="text" name="certificate_no" class="form-control" placeholder="e.g. FD-SBI-998811">
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-coreui-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Create Fund</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#formAddFund').on('submit', function(e) {
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
                alert(err.responseJSON ? err.responseJSON.message : 'Error creating fund');
            }
        });
    });
});
</script>
@endpush
</x-user-page>
