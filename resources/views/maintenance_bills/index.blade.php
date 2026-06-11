<x-user-page>

<!-- Summary Cards Row -->
<div class="row g-4 mb-4">
    <!-- Total Collected -->
    <div class="col-md-4">
        <div class="card dash-card card-revenue h-100 shadow-sm border-0">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-3 fw-bold">₹{{ number_format($totalCollected, 2) }}</div>
                    <div class="text-uppercase fw-semibold small opacity-75">Total Collected</div>
                </div>
                <div class="fs-1">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
        </div>
    </div>
    <!-- Cash Collected -->
    <div class="col-md-4">
        <div class="card dash-card card-flats h-100 shadow-sm border-0">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-3 fw-bold">₹{{ number_format($cashCollected, 2) }}</div>
                    <div class="text-uppercase fw-semibold small opacity-75">Cash Collections</div>
                </div>
                <div class="fs-1">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>
    </div>
    <!-- UPI Collected -->
    <div class="col-md-4">
        <div class="card dash-card card-residents h-100 shadow-sm border-0">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-3 fw-bold">₹{{ number_format($upiCollected, 2) }}</div>
                    <div class="text-uppercase fw-semibold small opacity-75">UPI Collections</div>
                </div>
                <div class="fs-1">
                    <i class="fas fa-qrcode"></i>
                </div>
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
     class="d-none"></div>

<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Payments</h4>
                <div>
                    <a href="{{ route('payments.create') }}" class="btn btn-primary me-2">
                        <i class="fa-solid fa-plus me-1"></i> Record Payment
                    </a>
                </div>
            </div>
            <div class="card-body">


                <div class="mb-3">
                    <div class="d-flex flex-wrap gap-2 align-items-end justify-content-start">
                        <div class="filter-col" style="min-width: 220px;">
                            <label class="form-label mb-1" for="maintenance-bills-filter-method">Filter by Method</label>
                            <select id="maintenance-bills-filter-method" class="form-select" style="max-width: 320px;">
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

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartDataEl = document.getElementById("payments-chart-data");
            if (chartDataEl) {
                const months = JSON.parse(chartDataEl.getAttribute("data-months"));
                const revenueData = JSON.parse(chartDataEl.getAttribute("data-revenue"));

                const ctx = document.getElementById("paymentsChart").getContext("2d");
                
                let gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(46, 184, 92, 0.5)'); // Green
                gradient.addColorStop(1, 'rgba(46, 184, 92, 0.0)');

                new Chart(ctx, {
                    type: "line",
                    data: {
                        labels: months,
                        datasets: [
                            {
                                label: "Monthly Collections",
                                backgroundColor: gradient,
                                borderColor: "#2eb85c",
                                borderWidth: 3,
                                pointBackgroundColor: "#2eb85c",
                                pointBorderColor: "#fff",
                                pointHoverBackgroundColor: "#fff",
                                pointHoverBorderColor: "#2eb85c",
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                fill: true,
                                tension: 0.4,
                                data: revenueData,
                            }
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: { 
                                position: "top",
                                labels: {
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    padding: 20
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                titlePadding: 10,
                                bodyPadding: 10,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function (context) {
                                        let label = context.dataset.label || "";
                                        if (label) {
                                            label += ": ";
                                        }
                                        if (context.parsed.y !== null) {
                                            label += new Intl.NumberFormat("en-IN", {
                                                style: "currency",
                                                currency: "INR",
                                            }).format(context.parsed.y);
                                        }
                                        return label;
                                    },
                                },
                            },
                        },
                        scales: {
                            y: { 
                                beginAtZero: true,
                                grid: { borderDash: [4, 4] }
                            },
                            x: {
                                grid: { display: false }
                            }
                        },
                    },
                });
            }
        });
    </script>
@endpush
</x-user-page>
