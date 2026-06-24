<x-layout>
    <div id="users-toasts" class="users-toast-container" aria-live="polite" aria-atomic="true"></div>

    @if (session('success'))
        <div id="users-toast-source" data-message="{{ e(session('success')) }}" data-type="success" hidden></div>
    @endif

    <x-sidebar />
    <div class="wrapper d-flex flex-column min-vh-100">
        <x-header />
        <div class="body flex-grow-1">
            <style>
                .dash-card {
                    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                    border-radius: 1.2rem;
                    overflow: hidden;
                    position: relative;
                    z-index: 1;
                    color: white;
                }
                .dash-card::before {
                    content: '';
                    position: absolute;
                    top: 0; left: 0; right: 0; bottom: 0;
                    background: inherit;
                    filter: blur(20px);
                    z-index: -1;
                    opacity: 0.4;
                    transition: opacity 0.4s;
                }
                .dash-card:hover {
                    transform: translateY(-8px) scale(1.02);
                }
                .dash-card:hover::before {
                    opacity: 0.8;
                }
                .card-flats { background: linear-gradient(135deg, #FF9A9E 0%, #FECFEF 99%, #FECFEF 100%); color: #4a0011; }
                .card-residents { background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%); color: #2a114f; }
                .card-complaints { background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); color: #014421; }
                .card-revenue { background: linear-gradient(135deg, #fccb90 0%, #d57eeb 100%); color: #420657; }
                
                .dash-icon-bg {
                    background: rgba(255,255,255,0.3);
                    width: 60px;
                    height: 60px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 50%;
                    font-size: 1.5rem;
                    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
                    backdrop-filter: blur(5px);
                }
            </style>
            <div class="container-lg px-4">
                
                <!-- TOP CARDS ROW -->
                <div class="row g-4 mb-4">
                    <!-- Flats Card -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card dash-card card-flats h-100 border-0">
                            <div class="card-body d-flex justify-content-between align-items-center p-4">
                                <div>
                                    <div class="text-uppercase fw-bold small opacity-75 mb-1 tracking-wide">Total Flats</div>
                                    <div class="fs-2 fw-bolder counter-animate" data-target="{{ $totalFlats }}">0</div>
                                </div>
                                <div class="dash-icon-bg">
                                    <i class="fas fa-building"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Residents Card -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card dash-card card-residents h-100 border-0">
                            <div class="card-body d-flex justify-content-between align-items-center p-4">
                                <div>
                                    <div class="text-uppercase fw-bold small opacity-75 mb-1 tracking-wide">Total Residents</div>
                                    <div class="fs-2 fw-bolder counter-animate" data-target="{{ $totalResidents }}">0</div>
                                </div>
                                <div class="dash-icon-bg">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Complaints Card -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card dash-card card-complaints h-100 border-0">
                            <div class="card-body d-flex justify-content-between align-items-center p-4">
                                <div>
                                    <div class="text-uppercase fw-bold small opacity-75 mb-1 tracking-wide">Total Complaints</div>
                                    <div class="fs-2 fw-bolder counter-animate" data-target="{{ $totalComplaints }}">0</div>
                                </div>
                                <div class="dash-icon-bg">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue Card -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card dash-card card-revenue h-100 border-0">
                            <div class="card-body d-flex justify-content-between align-items-center p-4">
                                <div>
                                    <div class="text-uppercase fw-bold small opacity-75 mb-1 tracking-wide">Total Revenue</div>
                                    <div class="fs-2 fw-bolder">₹<span class="counter-animate" data-target="{{ $totalRevenue }}">0</span></div>
                                </div>
                                <div class="dash-icon-bg">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHARTS ROW 1 -->
                <div class="row g-4 mb-4">
                    <!-- Main Chart: Revenue vs Expenses -->
                    <div class="col-lg-12">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-header bg-transparent border-0 pt-4 pb-0">
                                <h5 class="card-title mb-0 fw-bold">Revenue vs Expenses ({{ date('Y') }})</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="mainChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHARTS ROW 2 -->
                <div class="row g-4 mb-4">
                    <!-- Expense Breakdown Chart -->
                    <div class="col-lg-6">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-header bg-transparent border-0 pt-4 pb-0">
                                <h5 class="card-title mb-0 fw-bold">Expense Breakdown ({{ date('Y') }})</h5>
                            </div>
                            <div class="card-body d-flex justify-content-center align-items-center" style="min-height: 300px;">
                                <canvas id="expenseBreakdownChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Occupancy Rates Chart -->
                    <div class="col-lg-6">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-header bg-transparent border-0 pt-4 pb-0">
                                <h5 class="card-title mb-0 fw-bold">Occupancy Rates</h5>
                            </div>
                            <div class="card-body d-flex justify-content-center align-items-center" style="min-height: 300px;">
                                <canvas id="occupancyChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ACTIVITY FEED ROW -->
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-3">
                                <h5 class="card-title mb-0 fw-bold"><i class="fa-solid fa-bolt text-warning me-2"></i> Recent Activity</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    @forelse($activities as $activity)
                                        <div class="list-group-item px-4 py-3 d-flex align-items-center transition-hover" style="transition: background-color 0.2s;">
                                            <div class="bg-secondary bg-opacity-10 rounded-circle p-3 me-3 d-flex justify-content-center align-items-center shadow-sm" style="width: 48px; height: 48px;">
                                                <i class="{{ $activity->icon }} fs-5"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 fw-bold">{{ $activity->title }}</h6>
                                                <p class="mb-0 text-muted small">{{ $activity->description }}</p>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-secondary bg-opacity-10 text-body border">{{ $activity->time }}</span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-5 text-muted">
                                            <i class="fa-regular fa-clock fs-1 mb-3 opacity-50"></i>
                                            <p>No recent activities found.</p>
                                        </div>
                                    @endforelse
                                </div>
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
         data-status='{{ json_encode($billStatusData) }}'
         data-occupancy='{{ json_encode($occupancyData) }}'
         data-expense-labels='{{ json_encode($expenseBreakdownLabels) }}'
         data-expense-data='{{ json_encode($expenseBreakdownData) }}'>
    </div>

    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Counter Animation
                const counters = document.querySelectorAll('.counter-animate');
                const speed = 200;

                counters.forEach(counter => {
                    const updateCount = () => {
                        const target = +counter.getAttribute('data-target');
                        const count = +counter.innerText.replace(/,/g, '');
                        const inc = target / speed;

                        if (count < target) {
                            counter.innerText = Math.ceil(count + inc).toLocaleString();
                            setTimeout(updateCount, 10);
                        } else {
                            counter.innerText = target.toLocaleString();
                        }
                    };
                    updateCount();
                });
            });
        </script>
    @endpush
</x-layout>
