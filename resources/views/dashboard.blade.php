<x-layout>
    <div id="users-toasts" class="users-toast-container" aria-live="polite" aria-atomic="true"></div>

    @if (session('success'))
        <div id="users-toast-source" data-message="{{ e(session('success')) }}" data-type="success" hidden></div>
    @endif

    <x-sidebar />
    <div class="wrapper d-flex flex-column min-vh-100">
        <x-header />
        <div class="body flex-grow-1">
            <div class="container-lg px-4">
                
                <!-- TOP CARDS ROW -->
                <div class="row g-4 mb-4">
                    <!-- Flats Card -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card text-white bg-primary h-100">
                            <div class="card-body pb-3 d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fs-4 fw-semibold">{{ $totalFlats }}</div>
                                    <div class="text-uppercase fw-bold small">Total Flats</div>
                                </div>
                                <div class="fs-1 opacity-50">
                                    <i class="fas fa-building"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Residents Card -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card text-white bg-info h-100">
                            <div class="card-body pb-3 d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fs-4 fw-semibold">{{ $totalResidents }}</div>
                                    <div class="text-uppercase fw-bold small">Total Residents</div>
                                </div>
                                <div class="fs-1 opacity-50">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Complaints Card -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card text-white bg-danger h-100">
                            <div class="card-body pb-3 d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fs-4 fw-semibold">{{ $totalComplaints }}</div>
                                    <div class="text-uppercase fw-bold small">Total Complaints</div>
                                </div>
                                <div class="fs-1 opacity-50">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue Card -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card text-white bg-success h-100">
                            <div class="card-body pb-3 d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fs-4 fw-semibold">${{ number_format($totalRevenue, 2) }}</div>
                                    <div class="text-uppercase fw-bold small">Total Revenue</div>
                                </div>
                                <div class="fs-1 opacity-50">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHARTS ROW -->
                <div class="row g-4 mb-4">
                    <!-- Main Chart: Revenue vs Expenses -->
                    <div class="col-lg-8">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0 pt-1">Revenue vs Expenses ({{ date('Y') }})</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="mainChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Secondary Chart: Bill Status -->
                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0 pt-1">Bill Status Tracker</h5>
                            </div>
                            <div class="card-body d-flex justify-content-center align-items-center">
                                <canvas id="statusChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <x-footer />
    </div>
</x-layout>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Set Chart.js defaults for dark/light mode compatibility
        Chart.defaults.color = getComputedStyle(document.documentElement).getPropertyValue('--cui-body-color') || '#8a93a2';
        Chart.defaults.scale.grid.color = getComputedStyle(document.documentElement).getPropertyValue('--cui-border-color-translucent') || 'rgba(0,0,0,0.1)';

        // --- 1. Revenue vs Expenses Bar Chart ---
        const mainChartCtx = document.getElementById('mainChart').getContext('2d');
        const months = {!! json_encode($months) !!};
        const revenueData = {!! json_encode($chartDataRevenue) !!};
        const expenseData = {!! json_encode($chartDataExpenses) !!};

        new Chart(mainChartCtx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Revenue (Paid Bills)',
                        backgroundColor: 'rgba(46, 184, 92, 0.8)', // success
                        borderColor: 'rgba(46, 184, 92, 1)',
                        borderWidth: 1,
                        data: revenueData
                    },
                    {
                        label: 'Society Expenses',
                        backgroundColor: 'rgba(229, 83, 83, 0.8)', // danger
                        borderColor: 'rgba(229, 83, 83, 1)',
                        borderWidth: 1,
                        data: expenseData
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value, index, values) {
                                return '$' + value;
                            }
                        }
                    }
                }
            }
        });

        // --- 2. Bill Status Doughnut Chart ---
        const statusChartCtx = document.getElementById('statusChart').getContext('2d');
        const statusData = {!! json_encode($billStatusData) !!};

        new Chart(statusChartCtx, {
            type: 'doughnut',
            data: {
                labels: ['Paid', 'Pending', 'Due'],
                datasets: [{
                    data: [statusData.paid, statusData.pending, statusData.due],
                    backgroundColor: [
                        '#2eb85c', // success
                        '#f9b115', // warning
                        '#e55353'  // danger
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                },
                cutout: '70%'
            }
        });
    });
</script>
