<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice - {{ $bill->batch_id ?? 'Receipt' }}</title>
    <style>
        /* Base styles */
        @page {
            margin: 30px 40px;
        }
        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #4b4b5a;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            font-size: 13px;
            line-height: 1.4;
        }

        /* Layout & Colors */
        .invoice-container {
            width: 100%;
            margin: 0 auto;
        }
        
        /* Header section with brand color */
        .header-top {
            border-bottom: 2px solid #7367f0;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header-top h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
            color: #7367f0;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .header-top .society-name {
            font-size: 16px;
            font-weight: normal;
            color: #555;
            margin-top: 5px;
        }

        /* Invoice Meta info */
        .meta-info {
            width: 100%;
            margin-bottom: 20px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            vertical-align: top;
        }
        
        /* Status Badge */
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .badge-paid { background-color: rgba(40, 199, 111, 0.15); color: #28c76f; border: 1px solid #28c76f; }
        .badge-due { background-color: rgba(255, 159, 67, 0.15); color: #ff9f43; border: 1px solid #ff9f43; }
        .badge-pending { background-color: rgba(234, 84, 85, 0.15); color: #ea5455; border: 1px solid #ea5455; }

        /* Information Grid */
        .info-grid {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            width: 48%;
            padding: 15px;
            background: #f8f8fb;
            border-top: 3px solid #7367f0;
            vertical-align: top;
            border-radius: 4px;
        }
        .info-table td.right-col {
            border-top: 3px solid #00cfe8;
        }
        .info-title {
            font-size: 11px;
            text-transform: uppercase;
            color: #888;
            letter-spacing: 1px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .info-content {
            font-size: 14px;
            font-weight: normal;
            color: #333;
        }
        .info-content strong {
            color: #666;
            display: inline-block;
            width: 65px;
            font-weight: normal;
        }

        /* Line Items */
        .items-section {
            margin-bottom: 20px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .items-table th {
            background-color: #f1f1f5;
            color: #555;
            padding: 10px 12px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #ddd;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            color: #333;
        }
        .items-table th.text-right,
        .items-table td.text-right {
            text-align: right;
        }

        /* Totals */
        .totals-section {
            width: 100%;
            margin-top: 15px;
            page-break-inside: avoid;
        }
        .totals-table {
            width: 45%;
            float: right;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 8px 12px;
            font-size: 14px;
        }
        .total-row {
            background-color: #f8f8fb;
            border-top: 2px solid #7367f0;
            font-weight: bold;
            font-size: 16px;
            color: #333;
        }
        .text-danger { color: #ea5455; }
        .text-success { color: #28c76f; }

        /* Clearfix for totals float */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            text-align: center;
            border-top: 1px solid #eee;
            color: #888;
            font-size: 12px;
            page-break-inside: avoid;
        }
        
        .accent-text {
            color: #7367f0;
            font-weight: bold;
        }
        .item-desc-title {
            font-weight: bold; color: #333; margin-bottom: 2px; font-size: 14px;
        }
        .item-desc-sub {
            font-size: 11px; color: #777;
        }
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
                        <div class="society-name">{{ setting('society_name', 'Society Name') }}</div>
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
                            Block {{ $bill->flat->block->block_name ?? 'N/A' }}, Flat {{ $bill->flat->flat_no ?? 'N/A' }}<br>
                            <span style="font-size: 13px; color: #666; font-weight: normal;">
                                <strong>Type:</strong> {{ $bill->flat->flatType->name ?? 'N/A' }}<br>
                                <strong>Method:</strong> {{ strtoupper($bill->payment_method) }}
                                @if($bill->transaction_id)
                                    <br><strong>Trx ID:</strong> {{ $bill->transaction_id }}
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
                        </td>
                        <td class="text-right">{{ setting('currency_symbol', '₹') }}{{ number_format($totalBase, 2) }}</td>
                    </tr>
                    @if($totalPenalty > 0)
                    <tr>
                        <td>
                            <div class="item-desc-title">Penalty Amount</div>
                            <div class="item-desc-sub">Late Fee applied</div>
                        </td>
                        <td class="text-right text-danger">+ {{ setting('currency_symbol', '₹') }}{{ number_format($totalPenalty, 2) }}</td>
                    </tr>
                    @endif
                    @if($totalDiscount > 0)
                    <tr>
                        <td>
                            <div class="item-desc-title">Discount Applied</div>
                        </td>
                        <td class="text-right text-success">- {{ setting('currency_symbol', '₹') }}{{ number_format($totalDiscount, 2) }}</td>
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
                    <td class="text-right" style="font-weight: bold;">{{ setting('currency_symbol', '₹') }}{{ number_format($totalBase + $totalPenalty, 2) }}</td>
                </tr>
                @if($totalDiscount > 0)
                <tr>
                    <td style="color: #666; font-weight: bold;">Discount</td>
                    <td class="text-right text-success">- {{ setting('currency_symbol', '₹') }}{{ number_format($totalDiscount, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td style="padding: 15px;">TOTAL</td>
                    <td class="text-right" style="padding: 15px;">{{ setting('currency_symbol', '₹') }}{{ number_format($grandTotal, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <strong>Thank You!</strong><br>
            {{ setting('invoice_notes', 'Thank you for your payment. If you have any questions concerning this invoice, please contact the society management.') }}
        </div>
        
    </div>
</body>
</html>
