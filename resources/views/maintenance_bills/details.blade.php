<x-user-page>
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h4><a href="{{ route('maintenance-bills.show', $bill->maintenance_id) }}" class="text-decoration-none text-dark"><i class="fa-solid fa-arrow-left"></i></a> Bill Details</h4>
            <a href="{{ route('maintenance-bills.show', $bill->maintenance_id) }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-8 mx-auto">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Invoice Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Resident:</strong> {{ $bill->user->name ?? 'N/A' }}<br>
                        <strong>Email:</strong> {{ $bill->user->email ?? 'N/A' }}<br>
                        <strong>Phone:</strong> {{ $bill->user->resident->phone_number ?? 'N/A' }}
                    </div>
                    <div class="col-md-6 text-md-end">
                        <strong>Block & Flat:</strong> {{ $bill->flat->block->block_name ?? '' }}-{{ $bill->flat->flat_no ?? '' }}<br>
                        <strong>Flat Type:</strong> {{ $bill->flat->flatType->name ?? '' }}<br>
                        <strong>Billing Period:</strong> {{ $bill->maintenance->month }} {{ $bill->maintenance->year }}
                    </div>
                </div>

                <hr>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Status:</strong> 
                        @if($bill->status === 'paid')
                            <span class="badge bg-success">Paid</span>
                        @elseif($bill->status === 'due')
                            <span class="badge bg-danger">Due</span>
                        @else
                            <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                        <br>
                        <strong>Generated Date:</strong> {{ $bill->generated_date ? $bill->generated_date->format('d M, Y') : 'N/A' }}<br>
                        <strong>Due Date:</strong> {{ $bill->maintenance->due_date ? \Carbon\Carbon::parse($bill->maintenance->due_date)->format('d M, Y') : 'N/A' }}<br>
                        @if($bill->status === 'paid')
                        <strong>Paid At:</strong> {{ $bill->paid_at ? $bill->paid_at->format('d M, Y h:i A') : 'N/A' }}
                        @endif
                    </div>
                </div>

                <hr>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Base Maintenance Fee</td>
                            <td class="text-end">{{ setting('currency_symbol', '$') }}{{ number_format($bill->amount, 2) }}</td>
                        </tr>
                        @if($bill->penalty_amount > 0)
                        <tr>
                            <td>Penalty Amount</td>
                            <td class="text-end">{{ setting('currency_symbol', '$') }}{{ number_format($bill->penalty_amount, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="fw-bold">
                            <td>Total Amount</td>
                            <td class="text-end">{{ setting('currency_symbol', '$') }}{{ number_format($bill->total_amount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-center border-top border-secondary">
                <a href="{{ route('maintenance-bills.download-invoice', $bill->id) }}" class="btn btn-primary">
                    <i class="fa-solid fa-download me-1"></i> Download Invoice
                </a>
            </div>
        </div>
    </div>
</div>
</x-user-page>
