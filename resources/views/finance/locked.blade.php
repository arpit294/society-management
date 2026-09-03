<x-layout>
    <x-sidebar />
    <div class="wrapper d-flex flex-column min-vh-100">
        <x-header />
        <div class="body flex-grow-1 py-4">
            <div class="container-lg px-4">

                <!-- Hero Upsell Banner -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                    <div class="card-body p-4 p-md-5 text-white position-relative">
                        <div class="position-absolute end-0 top-0 p-4 d-none d-md-block" style="opacity: 0.1; transform: translate(20px, -20px);">
                            <i class="fa-solid fa-crown" style="font-size: 14rem; color: #f59e0b;"></i>
                        </div>

                        <div class="position-relative z-1" style="max-width: 750px;">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill fs-8">
                                    <i class="fa-solid fa-crown me-1"></i> PRO EXTENSION
                                </span>
                                <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-15 px-3 py-1.5 rounded-pill fs-8">
                                    FINANCE MODULE
                                </span>
                            </div>

                            <h2 class="fw-bold text-white mb-3 display-6">
                                Unlock Society Financial Management & Accounting
                            </h2>
                            
                            <p class="text-white-50 fs-6 mb-4 lead" style="line-height: 1.6;">
                                You are viewing a feature that requires the <strong>Finance Module</strong>. When activated, this module unlocks complete billing automation, expense logging, flat title transfers, and financial audit reports for your society.
                            </p>

                            <div class="d-flex flex-wrap gap-3">
                                <button type="button" class="btn btn-warning px-4 py-2.5 rounded-pill text-dark fw-bold shadow-sm js-premium-feature-btn" data-feature="Finance & Accounting Module">
                                    <i class="fa-solid fa-sparkles me-1"></i> Explore Module Benefits
                                </button>
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-light px-4 py-2.5 rounded-pill fw-semibold">
                                    <i class="fa-solid fa-arrow-left me-1"></i> Return to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Features & Changes Breakdown -->
                <div class="mb-4">
                    <h5 class="fw-bold mb-1 text-body"><i class="fa-solid fa-list-check text-warning me-2"></i>What's Included in the Finance Module</h5>
                    <p class="text-muted small mb-3">Discover the automated capabilities enabled by this module:</p>

                    <div class="row g-4">
                        <!-- Card 1 -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card glass-card h-100 border-0 shadow-sm rounded-3 p-4">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 fs-4">
                                        <i class="fa-solid fa-file-invoice-dollar"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-body">Maintenance Billing</h6>
                                </div>
                                <p class="text-muted small mb-0">
                                    Generate monthly, quarterly, and annual maintenance bills in bulk. Track payments, verify UTR transaction receipts, and print official PDF bills.
                                </p>
                            </div>
                        </div>

                        <!-- Card 2 -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card glass-card h-100 border-0 shadow-sm rounded-3 p-4">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="rounded-3 bg-danger bg-opacity-10 text-danger p-3 fs-4">
                                        <i class="fa-solid fa-money-bill-transfer"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-body">Expense Management</h6>
                                </div>
                                <p class="text-muted small mb-0">
                                    Categorize society expenses (repairs, utilities, staff salaries, security). Upload bill receipts, track cash outflows, and monitor budgets.
                                </p>
                            </div>
                        </div>

                        <!-- Card 3 -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card glass-card h-100 border-0 shadow-sm rounded-3 p-4">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-3 fs-4">
                                        <i class="fa-solid fa-right-left"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-body">Ownership Transfers</h6>
                                </div>
                                <p class="text-muted small mb-0">
                                    Manage ownership transfer applications, calculate transfer fee bills, verify seller/buyer NOCs, and maintain full transition history.
                                </p>
                            </div>
                        </div>

                        <!-- Card 4 -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card glass-card h-100 border-0 shadow-sm rounded-3 p-4">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 fs-4">
                                        <i class="fa-solid fa-chart-pie"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-body">Financial Audit Reports</h6>
                                </div>
                                <p class="text-muted small mb-0">
                                    Export detailed yearly and monthly Excel reports for Revenue vs Expense analysis, balance sheets, and society audit records.
                                </p>
                            </div>
                        </div>

                        <!-- Card 5 -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card glass-card h-100 border-0 shadow-sm rounded-3 p-4">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="rounded-3 bg-danger bg-opacity-10 text-danger p-3 fs-4">
                                        <i class="fa-solid fa-percent"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-body">Late Penalties</h6>
                                </div>
                                <p class="text-muted small mb-0">
                                    Automatically compute interest and late penalty fines for overdue invoices based on customizable grace periods and rates.
                                </p>
                            </div>
                        </div>

                        <!-- Card 6 -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card glass-card h-100 border-0 shadow-sm rounded-3 p-4">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 fs-4">
                                        <i class="fa-solid fa-tags"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-body">Prepayment Discounts</h6>
                                </div>
                                <p class="text-muted small mb-0">
                                    Offer automatic discount incentives to residents paying their maintenance fees 3, 6, or 12 months in advance.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <x-footer />
    </div>
</x-layout>
