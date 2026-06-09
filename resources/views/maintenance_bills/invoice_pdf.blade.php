<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    <link rel="stylesheet" href="{{ public_path('css/style.css') }}">
</head>
<body>
    <div class="invoice-pdf">
        <div class="header">
            <h2>{{ setting('society_name', 'Society Name') }}</h2>
            <h1>Maintenance Invoice</h1>
            <p>Billing Period: {{ $bill->maintenance->month }} {{ $bill->maintenance->year }}</p>
        </div>

        <table class="details-section">
            <tr>
                <td style="padding-right: 10px;">
                    <div class="details-box">
                        <h3>Resident Details</h3>
                        <p>
                            <strong>Name:</strong> {{ $bill->user->name ?? 'N/A' }}<br>
                            <strong>Email:</strong> {{ $bill->user->email ?? 'N/A' }}<br>
                            <strong>Phone:</strong> {{ $bill->user->resident->phone_number ?? 'N/A' }}
                        </p>
                    </div>
                </td>
                <td style="padding-left: 10px;">
                    <div class="details-box">
                        <h3>Apartment Details</h3>
                        <p>
                            <strong>Block:</strong> {{ $bill->flat->block->block_name ?? 'N/A' }}<br>
                            <strong>Flat No:</strong> {{ $bill->flat->flat_no ?? 'N/A' }}<br>
                            <strong>Type:</strong> {{ $bill->flat->flatType->name ?? 'N/A' }}
                        </p>
                    </div>
                </td>
            </tr>
        </table>

        <div style="margin-bottom: 20px;">
            <strong>Status:</strong> 
            @if($bill->status === 'paid')
                <span class="badge badge-paid">Paid</span>
            @elseif($bill->status === 'due')
                <span class="badge badge-due">Due</span>
            @else
                <span class="badge badge-pending">Pending</span>
            @endif
            
            <br><br>
            <strong>Generated Date:</strong> {{ $bill->generated_date ? $bill->generated_date->format('d M, Y') : 'N/A' }}<br>
            <strong>Due Date:</strong> {{ $bill->maintenance->due_date ? \Carbon\Carbon::parse($bill->maintenance->due_date)->format('d M, Y') : 'N/A' }}<br>
            @if($bill->status === 'paid')
            <strong>Paid At:</strong> {{ $bill->paid_at ? $bill->paid_at->format('d M, Y h:i A') : 'N/A' }}
            @endif
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Base Maintenance Fee</td>
                    <td class="text-right">{{ setting('currency_symbol', '$') }}{{ number_format($bill->amount, 2) }}</td>
                </tr>
                @if($bill->penalty_amount > 0)
                <tr>
                    <td>Penalty Amount (Late Fee)</td>
                    <td class="text-right">{{ setting('currency_symbol', '$') }}{{ number_format($bill->penalty_amount, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>Total Amount</td>
                    <td class="text-right">{{ setting('currency_symbol', '$') }}{{ number_format($bill->total_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            <p>{{ setting('invoice_notes', 'Thank you for your payment. If you have any questions concerning this invoice, please contact the society management.') }}</p>
        </div>
    </div>
</body>
</html>
