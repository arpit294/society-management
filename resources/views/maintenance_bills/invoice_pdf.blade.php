<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice - {{ $bill->batch_id ?? 'Receipt' }}</title>
    <style>
        {!! file_get_contents(public_path('css/invoice.css')) !!}
    </style>
</head>
<body>
    <div class="invoice-container">
        
        <!-- Header -->
        <div class="header-top">
            <table width="100%">
                <tr>
                    <td>
                        @php
                            $societyGstin = \App\Models\Setting::get('society_gstin');
                            $societyRegNo = \App\Models\Setting::get('society_registration_no');
                        @endphp
                        <h1>{{ !empty($societyGstin) ? 'TAX INVOICE' : 'MAINTENANCE INVOICE' }}</h1>
                        <div class="society-name">{{ \App\Models\Setting::get('society_name', 'Society Name') }}</div>
                        @if(!empty($societyGstin))
                            <div style="font-size: 11px; font-weight: bold; color: #2d3748; margin-top: 3px;">
                                GSTIN: {{ $societyGstin }}
                            </div>
                        @endif
                        @if(!empty($societyRegNo))
                            <div style="font-size: 11px; color: #4a5568; margin-top: 2px;">
                                Reg No: {{ $societyRegNo }}
                            </div>
                        @endif
                    </td>
                    <td align="right" style="vertical-align: bottom;">
                        <div>
                            @if($bill->status === 'paid')
                                <div class="badge badge-paid">PAID</div>
                            @elseif($bill->status === 'due')
                                <div class="badge badge-due">DUE</div>
                            @else
                                <div class="badge badge-pending">PENDING</div>
                            @endif
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <br>

        <!-- Invoice Details (Date, Period) -->
        <div class="meta-info">
            <table class="meta-table">
                <tr>
                    <td width="50%">
                        <span class="accent-text">Invoice Date:</span> <br>
                        {{ $bill->generated_date ? $bill->generated_date->format('d M, Y') : 'N/A' }}
                    </td>
                    <td width="50%" align="right">
                        <span class="accent-text">Billing Period:</span> <br>
                        @if($bills->count() > 1)
                            {{ $bills->first()->maintenance->month }} {{ $bills->first()->maintenance->year }} - {{ $bills->last()->maintenance->month }} {{ $bills->last()->maintenance->year }}
                        @else
                            {{ $bill->maintenance->month }} {{ $bill->maintenance->year }}
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- Resident & Apartment Info -->
        <div class="info-grid">
            <table class="info-table">
                <tr>
                    <td style="margin-right: 10px;">
                        @php
                            $resident = $bill->flat ? ($bill->flat->residents()->where('user_id', $bill->user_id)->latest()->first() ?? $bill->flat->owner ?? $bill->flat->tenant) : null;
                            $businessName = $resident ? ($resident->business_name ?? $resident->company_name) : null;
                            $contactPerson = $resident ? ($resident->contact_person ?? ($bill->user->name ?? null)) : ($bill->user->name ?? null);
                            $gstNumber = $resident ? ($resident->gst_number ?? $resident->gstin) : null;
                            $isCommercialOccupant = !empty($businessName) || !empty($gstNumber) || ($resident && $resident->occupant_category !== 'individual') || in_array(strtolower($bill->flat->unit_type ?? ''), ['shop', 'office', 'commercial', 'it_arcade', 'warehouse']);
                        @endphp
                        <div class="info-title">Billed To</div>
                        <div class="info-content">
                            @if($isCommercialOccupant && !empty($businessName))
                                <div style="font-weight: bold; color: #1a202c; font-size: 15px; margin-bottom: 4px;">{{ $businessName }}</div>
                            @else
                                <div style="font-weight: bold; color: #1a202c; font-size: 15px; margin-bottom: 4px;">{{ $bill->user->name ?? 'N/A' }}</div>
                            @endif
                            <table class="details-inner-table" width="100%" cellpadding="0" cellspacing="0">
                                @if($isCommercialOccupant && !empty($businessName) && !empty($contactPerson))
                                <tr>
                                    <td class="billed-label">Attn:</td>
                                    <td class="prop-value">{{ $contactPerson }}</td>
                                </tr>
                                @endif
                                @if(!empty($gstNumber))
                                <tr>
                                    <td class="billed-label">GSTIN:</td>
                                    <td class="prop-value" style="font-weight: bold; color: #2d3748;">{{ $gstNumber }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="billed-label">Email:</td>
                                    <td class="prop-value">{{ $bill->user->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="billed-label">Phone:</td>
                                    <td class="prop-value">{{ $bill->user->phone ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </td>
                    <td style="width: 20px; background: transparent; border: none;"></td>
                    <td class="right-col">
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
                        <div class="info-title">Property Details</div>
                        <div class="info-content">
                            <div style="font-size: 14px; color: #1a202c; margin-bottom: 6px; padding-bottom: 5px; border-bottom: 1px dashed #ced4da;">
                                @if($showBlock)
                                    <span style="color: #666; font-weight: bold;">{{ $dynamicBlockLabel }}:</span> <span style="font-weight: bold; color: #111;">{{ $blockName }}</span> &nbsp;&nbsp;|&nbsp;&nbsp; 
                                @endif
                                <span style="color: #666; font-weight: bold;">{{ $dynamicUnitLabel }}:</span> <span style="font-weight: bold; color: #111;">{{ $bill->flat->flat_no ?? 'N/A' }}</span>
                            </div>
                            @php
                                $flatType = $bill->flat->flatType ?? null;
                                $calcMethod = $flatType ? ($flatType->calculation_method ?? 'fixed') : 'fixed';
                                $globalMethod = \App\Models\Setting::get('maintenance_billing_method', 'fixed');
                                $isPerSqft = ($calcMethod === 'per_sqft' || $calcMethod === 'hybrid' || $globalMethod === 'per_sqft');
                                $sqftRate = $flatType && $flatType->rate_per_sqft > 0 ? $flatType->rate_per_sqft : (float) \App\Models\Setting::get('maintenance_rate_per_sqft', 0);
                            @endphp
                            <table class="details-inner-table" width="100%" cellpadding="0" cellspacing="0">
                                @if(!empty($bill->flat->flatType->name) && $bill->flat->flatType->name !== 'N/A')
                                <tr>
                                    <td class="prop-label">Category:</td>
                                    <td class="prop-value">{{ $bill->flat->flatType->name }}</td>
                                </tr>
                                @endif
                                @if($bill->flat && $bill->flat->area_sqft > 0)
                                <tr>
                                    <td class="prop-label">Carpet Area:</td>
                                    <td class="prop-value">{{ number_format($bill->flat->area_sqft, 2) }} Sq. Ft.</td>
                                </tr>
                                @endif
                                @if($isPerSqft && $sqftRate > 0)
                                <tr>
                                    <td class="prop-label">Applied Rate:</td>
                                    <td class="prop-value">{{ \App\Helpers\CurrencyHelper::formatCurrency($sqftRate) }} / Sq. Ft.</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="prop-label">Payment Mode:</td>
                                    <td class="prop-value">{{ strtoupper($bill->payment_method) }}</td>
                                </tr>
                                @if($bill->receivedBy)
                                <tr>
                                    <td class="prop-label">Received By:</td>
                                    <td class="prop-value">{{ $bill->receivedBy->name }}</td>
                                </tr>
                                @endif
                                @if($bill->transaction_id)
                                <tr>
                                    <td class="prop-label">UTR Number:</td>
                                    <td class="prop-value">{{ $bill->transaction_id }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Invoice Items -->
        <div class="items-section">
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalBase = $bills->sum('amount');
                        $totalPenalty = $bills->sum('penalty_amount');
                        $totalDiscount = $bills->sum('discount_amount');
                        $grandTotal = $bills->sum('total_amount');
                    @endphp
                    <tr>
                        <td>
                            <div class="item-desc-title">Base Maintenance Fee</div>
                            <div class="item-desc-sub">
                                @if($bills->count() > 1)
                                    For {{ $bills->count() }} Months
                                @else
                                    For {{ $bill->maintenance->month }} {{ $bill->maintenance->year }}
                                @endif
                            </div>
                            @if($bill->flat && $bill->flat->area_sqft > 0 && $isPerSqft && $sqftRate > 0)
                                <div style="font-size: 11px; color: #555; margin-top: 4px; font-style: italic;">
                                    Carpet Area Calculation: {{ number_format($bill->flat->area_sqft, 2) }} Sq. Ft. &times; {{ \App\Helpers\CurrencyHelper::formatCurrency($sqftRate) }} / Sq. Ft.
                                </div>
                            @endif
                        </td>
                        <td class="text-right">{{ \App\Helpers\CurrencyHelper::formatCurrency($totalBase) }}</td>
                    </tr>
                    @if($totalPenalty > 0)
                    <tr>
                        <td>
                            <div class="item-desc-title">Penalty Amount</div>
                            <div class="item-desc-sub">Late Fee applied</div>
                        </td>
                        <td class="text-right text-danger">+ {{ \App\Helpers\CurrencyHelper::formatCurrency($totalPenalty) }}</td>
                    </tr>
                    @endif
                    @if($totalDiscount > 0)
                    <tr>
                        <td>
                            <div class="item-desc-title">Discount Applied</div>
                        </td>
                        <td class="text-right text-success">- {{ \App\Helpers\CurrencyHelper::formatCurrency($totalDiscount) }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="totals-section clearfix">
            <table class="totals-table">
                <tr>
                    <td style="color: #666; font-weight: bold;">Subtotal</td>
                    <td class="text-right" style="font-weight: bold;">{{ \App\Helpers\CurrencyHelper::formatCurrency($totalBase + $totalPenalty) }}</td>
                </tr>
                @if($totalDiscount > 0)
                <tr>
                    <td style="color: #666; font-weight: bold;">Discount</td>
                    <td class="text-right text-success">- {{ \App\Helpers\CurrencyHelper::formatCurrency($totalDiscount) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td style="padding: 15px;">TOTAL</td>
                    <td class="text-right" style="padding: 15px;">{{ \App\Helpers\CurrencyHelper::formatCurrency($grandTotal) }}</td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <strong>Thank You!</strong><br>
            {{ \App\Models\Setting::get('invoice_notes', 'Thank you for your payment. If you have any questions concerning this invoice, please contact the society management.') }}
        </div>
        
    </div>
</body>
</html>
