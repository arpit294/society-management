<x-user-page>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Financial Reports & Statements</h2>
            <p class="text-muted small mb-0">Generate standard accounting statements, dues aging, and member passbooks</p>
        </div>
    </div>

    <!-- Reports Hub Grid -->
    <div class="row g-4">
        <!-- 1. Trial Balance -->
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="bg-primary-subtle text-primary p-3 rounded-3 d-inline-block mb-3">
                        <i class="fa-solid fa-scale-balanced fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Trial Balance</h5>
                    <p class="text-muted small mb-4">Verify ledger debit and credit equality across all society accounts.</p>
                </div>
                <a href="{{ route('finance.reports.trial-balance') }}" class="btn btn-outline-primary btn-sm w-100">
                    Open Trial Balance <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <!-- 2. Income & Expenditure (P&L) -->
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="bg-success-subtle text-success p-3 rounded-3 d-inline-block mb-3">
                        <i class="fa-solid fa-chart-line fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Income & Expenditure</h5>
                    <p class="text-muted small mb-4">Profit & Loss summary comparing revenues vs operational expenses.</p>
                </div>
                <a href="{{ route('finance.reports.income-expenditure') }}" class="btn btn-outline-success btn-sm w-100">
                    Open Statement <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <!-- 3. Balance Sheet -->
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="bg-info-subtle text-info p-3 rounded-3 d-inline-block mb-3">
                        <i class="fa-solid fa-building-columns fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Balance Sheet</h5>
                    <p class="text-muted small mb-4">Society financial health: Total Assets vs Liabilities & Sinking Reserves.</p>
                </div>
                <a href="{{ route('finance.reports.balance-sheet') }}" class="btn btn-outline-info btn-sm w-100">
                    Open Balance Sheet <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <!-- 4. Dues Aging & Defaulters -->
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="bg-danger-subtle text-danger p-3 rounded-3 d-inline-block mb-3">
                        <i class="fa-solid fa-clock-rotate-left fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Dues Aging & Defaulters</h5>
                    <p class="text-muted small mb-4">Arrears buckets (0-30, 31-60, 61-90, 90+ days) and overdue member tracker.</p>
                </div>
                <a href="{{ route('finance.reports.dues-aging') }}" class="btn btn-outline-danger btn-sm w-100">
                    Open Aging Report <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <!-- 5. Member Passbook -->
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="bg-warning-subtle text-warning p-3 rounded-3 d-inline-block mb-3">
                        <i class="fa-solid fa-address-book fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Member Statement of Account</h5>
                    <p class="text-muted small mb-4">Individual resident passbook with invoice and payment ledger timeline.</p>
                </div>
                <a href="{{ route('finance.reports.member-passbook') }}" class="btn btn-outline-warning text-dark btn-sm w-100">
                    Open Member Passbook <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>
</x-user-page>
