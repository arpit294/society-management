<x-user-page>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Dashboard Analytics</h4>
        <a href="{{ route('expenses.index') }}" class="btn btn-primary">
            <i class="fa-solid fa-file-invoice-dollar me-2"></i> Manage Expenses
        </a>
    </div>

    <!-- Counters Row -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card text-white bg-primary">
                <div class="card-body pb-3 d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-semibold">{{ $total_residents }}</div>
                        <div>Total Residents</div>
                    </div>
                    <i class="fa-solid fa-users fa-2x opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card text-white bg-info">
                <div class="card-body pb-3 d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-semibold">{{ $total_flats }}</div>
                        <div>Total Flats</div>
                    </div>
                    <i class="fa-solid fa-building fa-2x opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card text-white bg-warning">
                <div class="card-body pb-3 d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-semibold">{{ $total_blocks }}</div>
                        <div>Total Blocks</div>
                    </div>
                    <i class="fa-solid fa-cubes fa-2x opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card text-white bg-danger">
                <div class="card-body pb-3 d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-semibold">{{ $active_complaints }}</div>
                        <div>Active Complaints</div>
                    </div>
                    <i class="fa-solid fa-clipboard-list fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <strong>Flat Occupancy</strong>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <canvas id="occupancyChart" 
                        data-occupied="{{ $occupied_flats }}" 
                        data-empty="{{ $empty_flats }}" 
                        style="max-height: 300px;">
                    </canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <strong>Complaints Received (Month-over-Month)</strong>
                </div>
                <div class="card-body">
                    <canvas id="complaintsChart" 
                        data-labels="{{ json_encode($monthly_complaints->pluck('month')) }}" 
                        data-counts="{{ json_encode($monthly_complaints->pluck('count')) }}" 
                        style="max-height: 300px;">
                    </canvas>
                </div>
            </div>
        </div>
    </div>
</x-user-page>
