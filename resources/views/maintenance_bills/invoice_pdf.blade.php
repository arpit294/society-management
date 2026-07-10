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
                        <h1>INVOICE</h1>
                        <div class="society-name">{{ \App\Models\Setting::get('society_name', 'Society Name') }}</div>
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
                        <div class="info-title">Billed To</div>
                        <div class="info-content">
                            {{ $bill->user->name ?? 'N/A' }}<br>
                            <span style="font-size: 13px; color: #666; font-weight: normal;">
                                <strong>Email:</strong> {{ $bill->user->email ?? 'N/A' }}<br>
                                <strong>Phone:</strong> {{ $bill->user->phone ?? 'N/A' }}
                            </span>
                        </div>
                    </td>
                    <td style="width: 20px; background: transparent; border: none;"></td>
                    <td class="right-col">
                        <div class="info-title">Property Details</div>
                        <div class="info-content">
                            {{ \App\Models\Setting::label('block', 'Block/Wing') }} {{ $bill->flat->block->block_name ?? 'N/A' }}, {{ \App\Models\Setting::label('unit', 'Flat') }} {{ $bill->flat->flat_no ?? 'N/A' }}<br>
                            <span style="font-size: 13px; color: #666; font-weight: normal;">
                                <strong>Category:</strong> {{ $bill->flat->flatType->name ?? 'N/A' }}<br>
                                @php
                                    $flatType = $bill->flat->flatType ?? null;
                                    $calcMethod = $flatType ? ($flatType->calculation_method ?? 'fixed') : 'fixed';
                                    $globalMethod = \App\Models\Setting::get('maintenance_billing_method', 'fixed');
                                    $isPerSqft = ($calcMethod === 'per_sqft' || $calcMethod === 'hybrid' || $globalMethod === 'per_sqft');
                                    $sqftRate = $flatType && $flatType->rate_per_sqft > 0 ? $flatType->rate_per_sqft : (float) \App\Models\Setting::get('maintenance_rate_per_sqft', 0);
                                @endphp
                                @if($bill->flat && $bill->flat->area_sqft > 0)
                                    <strong>Carpet Area:</strong> {{ number_format($bill->flat->area_sqft, 2) }} Sq. Ft.<br>
                                @endif
                                @if($isPerSqft && $sqftRate > 0)
                                    <strong>Applied Rate:</strong> {{ \App\Helpers\CurrencyHelper::formatCurrency($sqftRate) }} / Sq. Ft.<br>
                                @endif
                                <strong>Payment Mode:</strong> {{ strtoupper($bill->payment_method) }}
                                @if($bill->receivedBy)
                                    <br><strong>Received By:</strong> {{ $bill->receivedBy->name }}
                                @endif
                                @if($bill->transaction_id)
                                    <br><strong>UTR Number:</strong> {{ $bill->transaction_id }}
                                @endif
                            </span>
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
