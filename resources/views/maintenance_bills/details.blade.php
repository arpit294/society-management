<x-user-page>
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h4><a href="{{ route('maintenance-bills.index') }}" class="text-decoration-none text-dark"><i class="fa-solid fa-arrow-left"></i></a> Bill Details</h4>
            <a href="{{ route('maintenance-bills.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-8 mx-auto">
        <div class="card shadow-sm">
            @php
                $societyGstin = \App\Models\Setting::get('society_gstin');
                $societyRegNo = \App\Models\Setting::get('society_registration_no');
            @endphp
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                <div>
                    <h5 class="mb-1 fw-bold">{{ !empty($societyGstin) ? 'TAX INVOICE' : 'MAINTENANCE INVOICE' }} DETAILS</h5>
                    <div class="small text-white-50">{{ \App\Models\Setting::get('society_name', 'Society Name') }}</div>
                    @if(!empty($societyGstin))
                        <div class="small fw-semibold text-warning" style="font-size: 0.85rem;">GSTIN: {{ $societyGstin }}</div>
                    @endif
                    @if(!empty($societyRegNo))
                        <div class="small text-light" style="font-size: 0.8rem;">Reg No: {{ $societyRegNo }}</div>
                    @endif
                </div>
                <div class="text-end">
                    <span class="badge {{ $bill->status === 'paid' ? 'bg-success' : ($bill->status === 'due' ? 'bg-warning text-dark' : 'bg-danger') }} fs-6 px-3 py-2 text-uppercase">{{ $bill->status }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        @php
                            $resident = $bill->flat ? ($bill->flat->residents()->where('user_id', $bill->user_id)->latest()->first() ?? $bill->flat->owner ?? $bill->flat->tenant) : null;
                            $businessName = $resident ? $resident->business_name : null;
                            $contactPerson = $resident ? ($resident->contact_person ?? ($bill->user->name ?? null)) : ($bill->user->name ?? null);
                            $isCommercialOccupant = !empty($businessName) || ($resident && $resident->occupant_category !== 'individual') || in_array(strtolower($bill->flat->unit_type ?? ''), ['shop', 'office', 'commercial', 'it_arcade', 'warehouse']);
                        @endphp
                        <strong class="text-uppercase text-muted small d-block mb-1">Billed To / {{ \App\Models\Setting::label('resident', 'Resident') }}</strong>
                        @if($isCommercialOccupant && !empty($businessName))
                            <div class="fs-6 fw-bold text-dark">{{ $businessName }}</div>
                            <div class="small text-secondary"><strong>Attn:</strong> {{ $contactPerson }}</div>

                        @else
                            <div class="fs-6 fw-bold text-dark">{{ $bill->user->name ?? 'N/A' }}</div>
                        @endif
                        <div class="small text-muted mt-1">
                            <strong>Email:</strong> {{ $bill->user->email ?? 'N/A' }}<br>
                            <strong>Phone:</strong> {{ $bill->user->phone ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        @php
                            $unitTypeStr = strtolower($bill->flat->unit_type ?? 'flat');
                            $dynamicUnitLabel = match($unitTypeStr) {
                                'shop' => 'Shop No',
                                'office' => 'Office No',
                                'villa' => 'Villa No',
                                'row_house', 'rowhouse' => 'Row House No',
                                'plot' => 'Plot No',
                                'it_arcade', 'commercial' => 'Arcade Unit No',
                                default => \App\Models\Setting::label('unit', 'Flat') . ' No'
                            };
                            $dynamicBlockLabel = match($unitTypeStr) {
                                'shop', 'office', 'it_arcade', 'commercial' => \App\Models\Setting::label('block', 'Complex / Wing'),
                                'villa', 'row_house', 'rowhouse', 'plot' => \App\Models\Setting::label('block', 'Sector / Township'),
                                default => \App\Models\Setting::label('block', 'Block / Wing')
                            };
                            $blockName = $bill->flat->block->block_name ?? null;
                            $showBlock = !empty($blockName) && $blockName !== '-' && $blockName !== '0' && strtolower($blockName) !== 'none';
                        @endphp
                        @if($showBlock)
                            <strong>{{ $dynamicBlockLabel }}:</strong> {{ $blockName }} &nbsp;|&nbsp; 
                        @endif
                        <strong>{{ $dynamicUnitLabel }}:</strong> {{ $bill->flat->flat_no ?? 'N/A' }}<br>
                        <strong>Property Category:</strong> <span class="badge bg-info text-dark fw-bold">{{ $bill->flat->flatType->name ?? 'Standard' }}</span><br>
                        @php
                            $isCommercial = in_array(strtolower($bill->flat->unit_type ?? ''), ['shop', 'office', 'showroom', 'warehouse']);
                            $flatType = $bill->flat->flatType ?? null;
                            $sqftRate = (float) \App\Models\Setting::get('commercial_rate_per_sqft', 0);
                            if ($sqftRate <= 0) $sqftRate = (float) \App\Models\Setting::get('maintenance_rate_per_sqft', 10);
                            if ($sqftRate <= 0) $sqftRate = 10.00;
                            $isPerSqft = $isCommercial;
                        @endphp
                        @if($bill->flat && $bill->flat->area_sqft > 0)
                            <strong>Carpet Area:</strong> <span class="badge bg-secondary">{{ number_format($bill->flat->area_sqft, 2) }} Sq. Ft.</span><br>
                        @endif
                        @if($isPerSqft && $sqftRate > 0)
                            <strong>Applied Rate:</strong> {{ \App\Helpers\CurrencyHelper::formatCurrency($sqftRate) }} / Sq. Ft. <small class="text-muted">(Settings Rate)</small><br>
                        @endif
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
                        <strong>Paid At:</strong> {{ $bill->paid_at ? $bill->paid_at->format('d M, Y h:i A') : 'N/A' }}<br>
                        <strong>Payment Mode:</strong> {{ ucfirst($bill->payment_method) ?? 'N/A' }}<br>
                        <strong>Received By:</strong> {{ $bill->receivedBy->name ?? 'N/A' }}<br>
                        @if($bill->payment_method === 'upi')
                            <strong>UTR Number:</strong> {{ $bill->transaction_id ?? 'N/A' }}<br>
                            @if($bill->payment_slip)
                                <strong>Payment Slip:</strong> <a href="{{ asset('storage/' . $bill->payment_slip) }}" target="_blank">View Screenshot</a>
                            @endif
                        @endif
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
                            <td>
                                Base Maintenance Fee
                                @if($bill->flat && $bill->flat->area_sqft > 0 && $isCommercial && $sqftRate > 0)
                                    <div class="text-muted small mt-1">
                                        <i class="fa-solid fa-calculator me-1"></i>
                                        <em>Commercial Calculation: {{ number_format($bill->flat->area_sqft, 2) }} Sq. Ft. &times; {{ \App\Helpers\CurrencyHelper::formatCurrency($sqftRate) }} / Sq. Ft. (Settings Rate)</em>
                                    </div>
                                @elseif($flatType)
                                    <div class="text-muted small mt-1">
                                        <i class="fa-solid fa-check-circle me-1"></i>
                                        <em>Fixed Residential Category Rate: {{ $flatType->name }}</em>
                                    </div>
                                @endif
                            </td>
                            <td class="text-end">{{ \App\Helpers\CurrencyHelper::formatCurrency($bill->amount) }}</td>
                        </tr>
                        @if($bill->penalty_amount > 0)
                        <tr>
                            <td>Penalty Amount</td>
                            <td class="text-end">{{ \App\Helpers\CurrencyHelper::formatCurrency($bill->penalty_amount) }}</td>
                        </tr>
                        @endif
                        <tr class="fw-bold">
                            <td>Total Amount</td>
                            <td class="text-end">{{ \App\Helpers\CurrencyHelper::formatCurrency($bill->total_amount) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-center border-top border-secondary">
                <a href="{{ route('maintenance-bills.download-invoice', $bill->id) }}" class="btn btn-primary no-loader" download>
                    <i class="fa-solid fa-download me-1"></i> Download Invoice
                </a>
            </div>
        </div>
    </div>
</div>
</x-user-page>
