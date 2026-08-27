<x-user-page>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('finance.invoices.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Invoices
            </a>
            <h2 class="h3 fw-bold text-dark mb-0">Invoice: {{ $invoice->invoice_number }}</h2>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.invoices.pdf', $invoice->id) }}" class="btn btn-outline-info btn-sm no-loader" target="_blank">
                <i class="fa-solid fa-file-pdf me-1"></i> Download PDF
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Invoice Details Card -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Invoice Summary</span>
                    <span class="badge {{ $invoice->status === 'paid' ? 'bg-success' : ($invoice->status === 'partially_paid' ? 'bg-warning text-dark' : 'bg-danger') }} px-3 py-2">
                        {{ strtoupper(str_replace('_', ' ', $invoice->status)) }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-4">
                        <div class="col-sm-6">
                            <h6 class="text-muted text-uppercase fs-7 mb-2">Billed To:</h6>
                            <h5 class="fw-bold mb-1">{{ $invoice->user ? $invoice->user->name : 'N/A' }}</h5>
                            <p class="text-muted mb-0">
                                Property Unit: <strong>{{ $invoice->flat ? ($invoice->flat->block ? 'Block ' . $invoice->flat->block->block_name . ' - ' : '') . $invoice->flat->flat_no : 'N/A' }}</strong><br>
                                Phone: {{ $invoice->user?->phone ?? 'N/A' }}<br>
                                Email: {{ $invoice->user?->email ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                            <h6 class="text-muted text-uppercase fs-7 mb-2">Invoice Meta:</h6>
                            <p class="mb-1"><strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('d M Y') }}</p>
                            <p class="mb-1"><strong>Due Date:</strong> {{ $invoice->due_date->format('d M Y') }}</p>
                            <p class="mb-0"><strong>Period:</strong> {{ $invoice->bill_month }} {{ $invoice->bill_year }}</p>
                        </div>
                    </div>

                    <!-- Line Items Table -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Description / Item</th>
                                    <th>Revenue Account Head</th>
                                    <th class="text-end">Amount (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items as $idx => $item)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $item->item_name }}</div>
                                        @if($item->description)
                                            <small class="text-muted">{{ $item->description }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $item->account ? $item->account->code . ' - ' . $item->account->name : 'General Revenue' }}</td>
                                    <td class="text-end fw-semibold">₹{{ number_format($item->amount, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Subtotal:</td>
                                    <td class="text-end fw-bold">₹{{ number_format($invoice->subtotal, 2) }}</td>
                                </tr>
                                @if($invoice->late_fee > 0)
                                <tr>
                                    <td colspan="3" class="text-end text-danger fw-semibold">Late Fee:</td>
                                    <td class="text-end text-danger fw-semibold">+₹{{ number_format($invoice->late_fee, 2) }}</td>
                                </tr>
                                @endif
                                <tr class="table-light">
                                    <td colspan="3" class="text-end fs-6 fw-bold">Total Invoiced Amount:</td>
                                    <td class="text-end fs-6 fw-bold text-primary">₹{{ number_format($invoice->total_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end text-success fw-semibold">Paid to Date:</td>
                                    <td class="text-end text-success fw-semibold">₹{{ number_format($invoice->paid_amount, 2) }}</td>
                                </tr>
                                <tr class="table-warning">
                                    <td colspan="3" class="text-end fs-6 fw-bold text-danger">Remaining Balance Due:</td>
                                    <td class="text-end fs-6 fw-bold text-danger">₹{{ number_format($invoice->balance_due, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if($invoice->notes)
                    <div class="p-3 bg-light rounded-3">
                        <small class="fw-bold text-muted text-uppercase">Notes & Instructions:</small>
                        <p class="mb-0 small text-secondary mt-1">{{ $invoice->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Linked Payment Receipts -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold fs-6">Payment Receipts for this Invoice</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Receipt #</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Mode</th>
                                    <th>Bank Account</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoice->payments as $pay)
                                <tr>
                                    <td class="fw-semibold">{{ $pay->receipt_number }}</td>
                                    <td>{{ $pay->payment_date->format('d M Y') }}</td>
                                    <td class="text-success fw-bold">₹{{ number_format($pay->amount, 2) }}</td>
                                    <td><span class="badge bg-secondary">{{ strtoupper($pay->payment_mode) }}</span></td>
                                    <td>{{ $pay->bankAccount ? $pay->bankAccount->bank_name : 'Cash' }}</td>
                                    <td>
                                        <a href="{{ route('finance.payments.receipt-pdf', $pay->id) }}" target="_blank" class="btn btn-sm btn-outline-info no-loader">
                                            <i class="fa-solid fa-receipt me-1"></i> Receipt
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-3 text-muted">No payments recorded for this invoice yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Accounting Journal & Fast Actions -->
        <div class="col-lg-4">
            <!-- Double Entry Ledger Verification Card -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold fs-6">
                        <i class="fa-solid fa-scale-balanced text-primary me-1"></i> Double-Entry Ledger Entry
                    </h5>
                </div>
                <div class="card-body p-3">
                    @if($invoice->journalEntry)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small text-muted">Journal Voucher:</span>
                            <span class="fw-bold">{{ $invoice->journalEntry->entry_number }}</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered small mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Account</th>
                                        <th class="text-end">Dr (₹)</th>
                                        <th class="text-end">Cr (₹)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoice->journalEntry->items as $jItem)
                                    <tr>
                                        <td>{{ $jItem->account?->name }}</td>
                                        <td class="text-end">{{ $jItem->debit > 0 ? number_format($jItem->debit, 2) : '-' }}</td>
                                        <td class="text-end">{{ $jItem->credit > 0 ? number_format($jItem->credit, 2) : '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted small mb-0">No journal entry linked.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</x-user-page>
