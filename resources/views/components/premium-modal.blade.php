<!-- Global Premium Feature Upsell Modal -->
<div class="modal fade" id="premiumFeatureModal" tabindex="-1" aria-labelledby="premiumFeatureModalLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border border-secondary border-opacity-25 shadow-2xl overflow-hidden rounded-4 text-white" style="background: #0f172a; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);">
            
            <!-- Modal Header / Banner -->
            <div class="modal-header border-bottom border-white border-opacity-10 text-white p-4 position-relative" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                <div class="position-absolute end-0 top-0 p-3" style="opacity: 0.12; transform: translate(10px, -10px); pointer-events: none;">
                    <i class="fa-solid fa-crown" style="font-size: 8rem; color: #f59e0b;"></i>
                </div>

                <div class="d-flex align-items-center gap-3 position-relative z-1">
                    <div class="d-flex align-items-center justify-content-center rounded-3 bg-warning text-dark shadow-sm" style="width: 52px; height: 52px; font-size: 1.5rem; flex-shrink: 0;">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill" style="font-size: 0.72rem; letter-spacing: 0.05em;">PREMIUM EXTENSION</span>
                            <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-15 px-2.5 py-1 rounded-pill" style="font-size: 0.72rem;">MODULE REQUIRED</span>
                        </div>
                        <h4 class="modal-title fw-bold text-white mb-0" id="premiumFeatureModalLabel">
                            <span id="premiumFeatureSpecificName">Finance & Billing Module</span>
                        </h4>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white position-relative z-1" data-bs-dismiss="modal" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4" style="background: #0f172a;">
                
                <!-- Notice Box -->
                <div class="p-3 rounded-3 border border-white border-opacity-10 mb-4 shadow-sm" style="background: rgba(255, 255, 255, 0.04);">
                    <div class="d-flex align-items-start gap-3">
                        <div class="text-warning fs-3 mt-1 flex-shrink-0"><i class="fa-solid fa-circle-info"></i></div>
                        <div>
                            <h6 class="fw-bold mb-1 text-white">This is a Premium Feature</h6>
                            <p class="text-white-50 small mb-0" style="line-height: 1.5;">
                                This section is part of the <strong class="text-white">Finance & Accounting Module</strong>. To activate live billing, automated penalties, expense receipts, and audit reporting, enable the Finance module in your system.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Feature Highlights Grid -->
                <h6 class="text-uppercase fw-bold text-white-50 fs-8 mb-3" style="letter-spacing: 0.08em;">What's included in the Finance Module:</h6>
                
                <div class="row g-3 mb-2">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border border-white border-opacity-10 h-100 d-flex gap-3 align-items-start shadow-sm" style="background: rgba(255, 255, 255, 0.03);">
                            <div class="rounded-2 bg-primary bg-opacity-20 text-info p-2.5 fs-5 flex-shrink-0">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                            </div>
                            <div>
                                <h6 class="fw-semibold mb-1 text-white small">Maintenance Billing</h6>
                                <p class="text-white-50 fs-8 mb-0">Generate monthly & quarterly bills, penalty tracking, UTR verification & invoices.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border border-white border-opacity-10 h-100 d-flex gap-3 align-items-start shadow-sm" style="background: rgba(255, 255, 255, 0.03);">
                            <div class="rounded-2 bg-danger bg-opacity-20 text-danger-emphasis p-2.5 fs-5 flex-shrink-0" style="color: #f87171 !important;">
                                <i class="fa-solid fa-money-bill-transfer"></i>
                            </div>
                            <div>
                                <h6 class="fw-semibold mb-1 text-white small">Society Expenses</h6>
                                <p class="text-white-50 fs-8 mb-0">Categorize expenditures, log receipts, vendor invoices and track cash outflows.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border border-white border-opacity-10 h-100 d-flex gap-3 align-items-start shadow-sm" style="background: rgba(255, 255, 255, 0.03);">
                            <div class="rounded-2 bg-warning bg-opacity-20 text-warning p-2.5 fs-5 flex-shrink-0">
                                <i class="fa-solid fa-right-left"></i>
                            </div>
                            <div>
                                <h6 class="fw-semibold mb-1 text-white small">Ownership Transfers</h6>
                                <p class="text-white-50 fs-8 mb-0">Manage flat title change requests, transfer fees, owner vetting & approval workflows.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border border-white border-opacity-10 h-100 d-flex gap-3 align-items-start shadow-sm" style="background: rgba(255, 255, 255, 0.03);">
                            <div class="rounded-2 bg-success bg-opacity-20 text-success p-2.5 fs-5 flex-shrink-0" style="color: #4ade80 !important;">
                                <i class="fa-solid fa-chart-line"></i>
                            </div>
                            <div>
                                <h6 class="fw-semibold mb-1 text-white small">Financial Reports & Audits</h6>
                                <p class="text-white-50 fs-8 mb-0">Export yearly and monthly Excel reports for Revenue vs Expense audit trails.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top border-white border-opacity-10 p-3 px-4 d-flex justify-content-between align-items-center" style="background: #0b1120;">
                <span class="text-white-50 small">
                    <i class="fa-solid fa-shield-halved text-success me-1"></i> Ready to plug into your project anytime
                </span>
                <button type="button" class="btn btn-warning text-dark fw-bold px-4 py-2 rounded-pill shadow-sm" data-bs-dismiss="modal" data-coreui-dismiss="modal">
                    Got It
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    window.openPremiumModal = function(featureName = 'Finance & Billing Module') {
        const modalEl = document.getElementById('premiumFeatureModal');
        if (modalEl) {
            const titleEl = document.getElementById('premiumFeatureSpecificName');
            if (titleEl && featureName) {
                titleEl.textContent = featureName;
            }
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            } else if (typeof coreui !== 'undefined' && coreui.Modal) {
                const modal = coreui.Modal.getOrCreateInstance(modalEl);
                modal.show();
            } else {
                $(modalEl).modal('show');
            }
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        @if(session('show_premium_modal'))
            setTimeout(function() {
                window.openPremiumModal(@json(session('show_premium_modal')));
            }, 300);
        @endif

        document.addEventListener('click', function(e) {
            const trigger = e.target.closest('.js-premium-feature, .js-premium-feature-btn');
            if (trigger) {
                e.preventDefault();
                e.stopPropagation();
                const feature = trigger.getAttribute('data-feature') || 'Finance & Accounting Module';
                window.openPremiumModal(feature);
            }
        });
    });
</script>
