<x-user-page>
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Prepayments (Unused)</h4>
                <a href="{{ route('prepayments.create') }}" class="btn btn-primary">
                    <i class="fa-solid fa-plus me-1"></i> Add Prepayment
                </a>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th>Resident</th>
                                <th>Flat</th>
                                <th>Period</th>
                                <th>Usage</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($prepayments as $prepayment)
                                <tr>
                                    <td>{{ $prepayment->user->name ?? 'N/A' }}</td>
                                    <td>{{ $prepayment->flat->block->block_name ?? '' }} - {{ $prepayment->flat->flat_no ?? '' }}</td>
                                    <td>
                                        {{ $prepayment->month }} {{ $prepayment->year }}
                                        @if($prepayment->months > 1 && $prepayment->end_month)
                                            <br><small class="text-muted">to {{ $prepayment->end_month }} {{ $prepayment->end_year }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $prepayment->months_used }} / {{ $prepayment->months }} used</td>
                                    <td>{{ number_format($prepayment->amount_paid, 2) }}</td>
                                    <td>
                                        Mode: {{ ucfirst($prepayment->payment_method) ?? 'N/A' }}<br>
                                        @if($prepayment->payment_method === 'upi')
                                            Txn: {{ $prepayment->transaction_id ?? 'N/A' }}<br>
                                            @if($prepayment->payment_slip)
                                                <a href="{{ asset('storage/' . $prepayment->payment_slip) }}" target="_blank">Slip</a>
                                            @endif
                                        @endif
                                    </td>
                                    <td><span class="badge bg-success">{{ $prepayment->status }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No unused prepayments found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</x-user-page>
