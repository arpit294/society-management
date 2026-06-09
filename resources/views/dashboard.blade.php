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
                                    <div class="fs-4 fw-semibold">₹{{ number_format($totalRevenue, 2) }}</div>
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

    <!-- Pass Chart Data to script.js -->
    <div id="dashboard-chart-data" style="display:none" 
         data-months='{{ json_encode($months) }}' 
         data-revenue='{{ json_encode($chartDataRevenue) }}' 
         data-expenses='{{ json_encode($chartDataExpenses) }}'
         data-status='{{ json_encode($billStatusData) }}'>
    </div>
</x-user-page>
