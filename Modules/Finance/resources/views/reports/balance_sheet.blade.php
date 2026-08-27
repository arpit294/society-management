<x-user-page>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('finance.reports.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fa-solid fa-arrow-left me-1"></i> Reports Hub
            </a>
            <h2 class="h3 fw-bold text-dark mb-0">Balance Sheet</h2>
            <p class="text-muted small mb-0">As of {{ date('d F Y', strtotime($asOfDate)) }}</p>
        </div>
        <div>
            <form method="GET" class="d-flex gap-2 align-items-center">
                <input type="date" name="as_of_date" class="form-control form-control-sm" value="{{ $asOfDate }}">
                <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
            </form>
        </div>
    </div>

    <!-- Dual Column Balance Sheet -->
    <div class="row g-4">
        <!-- Assets (Left Column) -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-bold text-primary fs-6">
                        <i class="fa-solid fa-building-columns me-1"></i> ASSETS
                    </h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Account</th>
                                <th class="text-end">Balance (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['assets'] as $ast)
                            <tr>
                                <td>{{ $ast->name }} (<code>{{ $ast->code }}</code>)</td>
                                <td class="text-end fw-semibold">₹{{ number_format($ast->current_balance, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold fs-6">
                                <td>TOTAL ASSETS:</td>
                                <td class="text-end text-primary">₹{{ number_format($data['total_assets'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Liabilities & Sinking Reserves (Right Column) -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-bold text-danger fs-6">
                        <i class="fa-solid fa-scale-unbalanced me-1"></i> LIABILITIES & CAPITAL RESERVES
                    </h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Account</th>
                                <th class="text-end">Balance (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="table-light fw-bold">
                                <td colspan="2"><small class="text-uppercase text-secondary">Liabilities</small></td>
                            </tr>
                            @foreach($data['liabilities'] as $liab)
                            <tr>
                                <td>{{ $liab->name }} (<code>{{ $liab->code }}</code>)</td>
                                <td class="text-end fw-semibold">₹{{ number_format($liab->current_balance, 2) }}</td>
                            </tr>
                            @endforeach

                            <tr class="table-light fw-bold">
                                <td colspan="2"><small class="text-uppercase text-secondary">Capital & Sinking Funds</small></td>
                            </tr>
                            @foreach($data['equity'] as $eq)
                            <tr>
                                <td>{{ $eq->name }} (<code>{{ $eq->code }}</code>)</td>
                                <td class="text-end fw-semibold">₹{{ number_format($eq->current_balance, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold fs-6">
                                <td>TOTAL LIABILITIES & FUNDS:</td>
                                <td class="text-end text-danger">₹{{ number_format($data['total_liabilities_and_equity'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</x-user-page>
