<x-user-page>
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Financial Overview</h2>
            <p class="text-muted small mb-0">Double-entry accounting, live balances, billing, and cash flow</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.invoices.index') }}" class="btn btn-primary btn-sm px-3">
                <i class="fa-solid fa-file-invoice me-1"></i> Invoicing
            </a>
            <a href="{{ route('finance.reports.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                <i class="fa-solid fa-chart-pie me-1"></i> Reports
            </a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <!-- Collections -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm rounded-3" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">Collections This Month</span>
                        <i class="fa-solid fa-wallet fs-4 opacity-75"></i>
                    </div>
                    <h3 class="fw-bold mb-0">₹{{ number_format($collectionsThisMonth, 2) }}</h3>
                </div>
            </div>
        </div>

        <!-- Outstanding Dues -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm rounded-3" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">Total Outstanding Dues</span>
                        <i class="fa-solid fa-triangle-exclamation fs-4 opacity-75"></i>
                    </div>
                    <h3 class="fw-bold mb-0">₹{{ number_format($totalOutstandingDues, 2) }}</h3>
                </div>
            </div>
        </div>

        <!-- Expenses Disbursed -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm rounded-3" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">Expenses This Month</span>
                        <i class="fa-solid fa-receipt fs-4 opacity-75"></i>
                    </div>
                    <h3 class="fw-bold mb-0">₹{{ number_format($expensesThisMonth, 2) }}</h3>
                </div>
            </div>
        </div>

        <!-- Total Liquid Bank & Cash -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm rounded-3" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">Liquid Bank & Cash</span>
                        <i class="fa-solid fa-building-columns fs-4 opacity-75"></i>
                    </div>
                    <h3 class="fw-bold mb-0">₹{{ number_format($totalLiquidCash, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Grid: Recent Invoices & Recent Collections -->
    <div class="row g-3 mb-4">
        <!-- Recent Invoices -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100 rounded-3">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-semibold fs-6">Recent Invoices</h5>
                    <a href="{{ route('finance.invoices.index') }}" class="btn btn-link btn-sm text-primary p-0">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Unit</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentInvoices as $inv)
                                <tr>
                                    <td>
                                        <a href="{{ route('finance.invoices.show', $inv->id) }}" class="fw-semibold text-primary">
                                            {{ $inv->invoice_number }}
                                        </a>
                                    </td>
                                    <td>{{ $inv->flat ? ($inv->flat->block ? 'Block ' . $inv->flat->block->block_name . ' - ' : '') . $inv->flat->flat_no : 'N/A' }}</td>
                                    <td>₹{{ number_format($inv->total_amount, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $inv->status === 'paid' ? 'bg-success' : ($inv->status === 'partially_paid' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                            {{ ucfirst($inv->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">No invoices generated yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Payments Received -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100 rounded-3">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-semibold fs-6">Recent Collections & Receipts</h5>
                    <a href="{{ route('finance.payments.index') }}" class="btn btn-link btn-sm text-primary p-0">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Receipt #</th>
                                    <th>Resident</th>
                                    <th>Amount</th>
                                    <th>Mode</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPayments as $pay)
                                <tr>
                                    <td>
                                        <a href="{{ route('finance.payments.receipt-pdf', $pay->id) }}" target="_blank" class="fw-semibold text-info">
                                            {{ $pay->receipt_number }}
                                        </a>
                                    </td>
                                    <td>{{ $pay->user ? $pay->user->name : 'N/A' }}</td>
                                    <td class="text-success fw-bold">₹{{ number_format($pay->amount, 2) }}</td>
                                    <td><span class="badge bg-secondary">{{ strtoupper($pay->payment_mode) }}</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">No payments recorded yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Vouchers requiring approval -->
    @if(count($pendingVouchers) > 0)
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-semibold fs-6 text-warning">
                <i class="fa-solid fa-clock-rotate-left me-1"></i> Payment Vouchers Requiring Approval
            </h5>
            <a href="{{ route('finance.vouchers.index') }}" class="btn btn-link btn-sm text-primary p-0">Manage Vouchers</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Voucher #</th>
                            <th>Payee / Vendor</th>
                            <th>Amount</th>
                            <th>Bank</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingVouchers as $vch)
                        <tr>
                            <td class="fw-semibold">{{ $vch->voucher_number }}</td>
                            <td>{{ $vch->vendor ? $vch->vendor->name : 'N/A' }}</td>
                            <td class="text-danger fw-bold">₹{{ number_format($vch->amount, 2) }}</td>
                            <td>{{ $vch->bankAccount ? $vch->bankAccount->bank_name : 'Cash' }}</td>
                            <td><span class="badge bg-warning text-dark">{{ ucfirst($vch->approval_status) }}</span></td>
                            <td>
                                <a href="{{ route('finance.vouchers.index') }}" class="btn btn-sm btn-outline-primary">Review</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
</x-user-page>
