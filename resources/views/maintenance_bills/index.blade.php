<x-user-page>

<!-- Summary Cards Row -->
<!-- Summary Cards Row -->
<div class="row g-4 mb-4">
    <!-- Total Collected -->
    <div class="col-sm-6 col-xl-4">
        <div class="card kpi-hero-card kpi-theme-emerald h-100 border-0">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="kpi-icon-pedestal">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <span class="kpi-status-pill">
                        <span class="kpi-status-dot"></span> Collected
                    </span>
                </div>
                <div class="mt-2">
                    <div class="kpi-label mb-1">Total Collected</div>
                    <div class="kpi-number" style="font-size: 1.85rem;">{{ \App\Helpers\CurrencyHelper::formatCurrency($totalCollected) }}</div>
                </div>
                <div class="kpi-glow-orb"></div>
            </div>
        </div>
    </div>
    <!-- Cash Collected -->
    <div class="col-sm-6 col-xl-4">
        <div class="card kpi-hero-card kpi-theme-indigo h-100 border-0">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="kpi-icon-pedestal">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <span class="kpi-status-pill">
                        <span class="kpi-status-dot"></span> Cash
                    </span>
                </div>
                <div class="mt-2">
                    <div class="kpi-label mb-1">Cash Collections</div>
                    <div class="kpi-number" style="font-size: 1.85rem;">{{ \App\Helpers\CurrencyHelper::formatCurrency($cashCollected) }}</div>
                </div>
                <div class="kpi-glow-orb"></div>
            </div>
        </div>
    </div>
    <!-- UPI Collected -->
    <div class="col-sm-6 col-xl-4">
        <div class="card kpi-hero-card kpi-theme-cyan h-100 border-0">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="kpi-icon-pedestal">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <span class="kpi-status-pill">
                        <span class="kpi-status-dot"></span> Digital
                    </span>
                </div>
                <div class="mt-2">
                    <div class="kpi-label mb-1">UPI Collections</div>
                    <div class="kpi-number" style="font-size: 1.85rem;">{{ \App\Helpers\CurrencyHelper::formatCurrency($upiCollected) }}</div>
                </div>
                <div class="kpi-glow-orb"></div>
            </div>
        </div>
    </div>
</div>

<!-- Chart Row -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-transparent border-0 pt-4 pb-0">
                <h5 class="card-title mb-0 fw-bold">Collection Trends ({{ date('Y') }})</h5>
            </div>
            <div class="card-body">
                <canvas id="paymentsChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Hidden data for chart -->
<div id="payments-chart-data" 
     data-months="{{ json_encode($months) }}" 
     data-revenue="{{ json_encode($chartDataRevenue) }}"
     data-currency="{{ \App\Helpers\CurrencyHelper::getCurrencyCode() }}"
     data-currency-symbol="{{ \App\Helpers\CurrencyHelper::getCurrencySymbol() }}"
     class="d-none"></div>

<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Payments</h4>
                <div>
                    @can('maintenance_bill_create')
                    <button type="button" data-url="{{ route('maintenance-bills.create') }}" id="btn-record-payment" class="btn btn-primary me-2">
                        <i class="fa-solid fa-plus me-1"></i> Record Payment
                    </button>
                    @endcan
                </div>
            </div>
            <div class="card-body">


                <div class="mb-3">
                    <div class="d-flex flex-wrap gap-2 align-items-end justify-content-start">
                        <div class="filter-col" style="min-width: 220px;">
                            <label class="form-label mb-1" for="maintenance-bills-filter-block">Filter by Block</label>
                            <select id="maintenance-bills-filter-block" class="form-select select2-filter" style="width: 100%;">
                                <option value="">All Blocks</option>
                                @foreach($blocks as $block)
                                    <option value="{{ $block->block_name }}">{{ $block->block_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-col" style="min-width: 280px;">
                            <label class="form-label mb-1" for="maintenance-bills-filter-resident">Filter by Resident</label>
                            <select id="maintenance-bills-filter-resident" class="form-select select2-filter" style="width: 100%;">
                                <option value="">All Residents</option>
                                @foreach($residents as $resident)
                                    <option value="{{ $resident->user->name ?? '' }}">
                                        {{ $resident->user->name ?? 'Unknown' }} ({{ $resident->flat->block->block_name ?? '' }} - {{ $resident->flat->flat_no ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-col" style="min-width: 150px;">
                            <label class="form-label mb-1" for="maintenance-bills-filter-year">Filter by Year</label>
                            <select id="maintenance-bills-filter-year" class="form-select select2-filter" style="width: 100%;">
                                <option value="">All Years</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-col" style="min-width: 220px;">
                            <label class="form-label mb-1" for="maintenance-bills-filter-method">Filter by Method</label>
                            <select id="maintenance-bills-filter-method" class="form-select select2-filter" style="width: 100%;">
                                <option value="">All Methods</option>
                                <option value="cash">CASH</option>
                                <option value="upi">UPI</option>
                            </select>
                        </div>
                        <div class="filter-col d-none" id="maintenance-bills-filter-reset-col" style="min-width: 200px;">
                            <button type="button" id="maintenance-bills-filter-reset" class="btn btn-outline-secondary w-100">
                                Reset filter
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    {{ $dataTable->table(['class' => 'table table-bordered table-striped table-hover w-100', 'id' => 'maintenance-bills-table']) }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Maintenance Bill Modal -->
<div class="modal fade" id="maintenance-bill-modal" tabindex="-1" aria-labelledby="maintenanceBillModalLabel" aria-hidden="true" data-coreui-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="maintenance-bill-modal-content">
            <!-- Modal Content will be loaded via AJAX -->
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="status-maintenance-modal" tabindex="-1" aria-hidden="true" data-coreui-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="status-maintenance-form" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold">Update Bill Status</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold text-uppercase">Status</label>
                        <select name="status" id="maintenance-bill-status-select" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="due">Due</option>
                            <option value="paid">Paid</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </div>
                    <div class="mb-3" id="maintenance-payment-method-container" style="display: none;">
                        <label class="form-label text-muted small fw-semibold text-uppercase">Payment Method</label>
                        <select name="payment_method" id="maintenance-status-payment-method" class="form-select">
                            <option value="cash">Cash</option>
                            <option value="upi">UPI</option>
                        </select>
                    </div>
                    <div class="mb-3" id="maintenance-upi-container" style="display: none;">
                        <label class="form-label text-muted small fw-semibold text-uppercase">UTR Number</label>
                        <input type="text" name="transaction_id" class="form-control" placeholder="12 digit UTR number" maxlength="12">
                    </div>
                </div>
                <div class="modal-footer border-top-0 bg-light">
                    <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="btn-save-maintenance-status">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    <script>
        let currentMaintenanceStatusUrl = "";
        $(document).on("click", ".btn-status-maintenance", function () {
            currentMaintenanceStatusUrl = $(this).data("url");
            let currentStatus = $(this).data("status") || "due";
            $("#maintenance-bill-status-select").val(currentStatus.toLowerCase()).trigger("change");
            $("#status-maintenance-modal").modal("show");
        });

        $(document).on("change", "#maintenance-bill-status-select", function () {
            if ($(this).val() === "paid") {
                $("#maintenance-payment-method-container").show();
                $("#maintenance-status-payment-method").trigger("change");
            } else {
                $("#maintenance-payment-method-container").hide();
                $("#maintenance-upi-container").hide();
            }
        });

        $(document).on("change", "#maintenance-status-payment-method", function () {
            if ($("#maintenance-bill-status-select").val() === "paid" && $(this).val() === "upi") {
                $("#maintenance-upi-container").show();
            } else {
                $("#maintenance-upi-container").hide();
            }
        });

        $(document).on("submit", "#status-maintenance-form", function (e) {
            e.preventDefault();
            let btn = $("#btn-save-maintenance-status");
            let originalText = btn.html();
            btn.html('<span class="spinner-border spinner-border-sm"></span> Saving...').prop("disabled", true);

            let formData = new FormData(this);
            $.ajax({
                url: currentMaintenanceStatusUrl,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $("#status-maintenance-modal").modal("hide");
                    if (typeof window.LaravelDataTables !== "undefined" && window.LaravelDataTables["maintenance-bills-table"]) {
                        window.LaravelDataTables["maintenance-bills-table"].ajax.reload(null, false);
                    }
                    toastr.success(response.message || "Status updated successfully.");
                },
                error: function (xhr) {
                    let msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : "Error updating status";
                    toastr.error(msg);
                },
                complete: function () {
                    btn.html(originalText).prop("disabled", false);
                }
            });
        });
    </script>
@endpush
</x-user-page>
