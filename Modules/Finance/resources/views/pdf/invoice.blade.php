<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #333; line-height: 1.5; padding: 20px; }
        .header-table { width: 100%; margin-bottom: 25px; border-bottom: 2px solid #3b82f6; padding-bottom: 15px; }
        .header-table td { vertical-align: top; }
        .brand-title { font-size: 22px; font-weight: bold; color: #1e3a8a; }
        .inv-title { font-size: 20px; font-weight: bold; color: #3b82f6; text-align: right; }
        .meta-table { width: 100%; margin-bottom: 25px; }
        .meta-table td { vertical-align: top; width: 50%; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .items-table th { background-color: #f1f5f9; color: #475569; font-size: 11px; text-transform: uppercase; padding: 8px 10px; border: 1px solid #e2e8f0; }
        .items-table td { padding: 8px 10px; border: 1px solid #e2e8f0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .badge-paid { background-color: #dcfce7; color: #166534; }
        .badge-unpaid { background-color: #fee2e2; color: #991b1b; }
        .footer { margin-top: 40px; font-size: 11px; color: #64748b; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 15px; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td>
                <div class="brand-title">{{ \App\Models\Setting::get('society_name') ?: 'Society Management' }}</div>
                <small>{{ \App\Models\Setting::get('society_address') ?: 'Residential Housing Society' }}</small>
            </td>
            <td class="text-right">
                <div class="inv-title">MAINTENANCE INVOICE</div>
                <div><strong>#{{ $invoice->invoice_number }}</strong></div>
                <div style="margin-top: 5px;">
                    <span class="badge {{ $invoice->status === 'paid' ? 'badge-paid' : 'badge-unpaid' }}">
                        {{ strtoupper($invoice->status) }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td>
                <strong>Billed To:</strong><br>
                {{ $invoice->user ? $invoice->user->name : 'N/A' }}<br>
                {{ $invoice->flat ? ($invoice->flat->block ? 'Block ' . $invoice->flat->block->block_name . ' - ' : '') . $invoice->flat->flat_no : 'N/A' }}<br>
                Phone: {{ $invoice->user?->phone ?? 'N/A' }}
            </td>
            <td class="text-right">
                <strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('d M Y') }}<br>
                <strong>Due Date:</strong> {{ $invoice->due_date->format('d M Y') }}<br>
                <strong>Billing Period:</strong> {{ $invoice->bill_month }} {{ $invoice->bill_year }}
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 40px;">#</th>
                <th>Description</th>
                <th class="text-right" style="width: 120px;">Amount (INR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $idx => $item)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>
                    <strong>{{ $item->item_name }}</strong>
                    @if($item->description) <br><small style="color: #64748b;">{{ $item->description }}</small> @endif
                </td>
                <td class="text-right">&#8377;{{ number_format($item->amount, 2) }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="2" class="text-right" style="font-weight: bold;">Subtotal:</td>
                <td class="text-right" style="font-weight: bold;">&#8377;{{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            @if($invoice->late_fee > 0)
            <tr>
                <td colspan="2" class="text-right" style="color: #dc2626;">Late Payment Surcharge:</td>
                <td class="text-right" style="color: #dc2626;">+&#8377;{{ number_format($invoice->late_fee, 2) }}</td>
            </tr>
            @endif
            <tr style="background-color: #f8fafc;">
                <td colspan="2" class="text-right" style="font-weight: bold; font-size: 14px;">Total Invoiced:</td>
                <td class="text-right" style="font-weight: bold; font-size: 14px; color: #1e3a8a;">&#8377;{{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="2" class="text-right" style="color: #166534;">Paid to Date:</td>
                <td class="text-right" style="color: #166534;">&#8377;{{ number_format($invoice->paid_amount, 2) }}</td>
            </tr>
            <tr style="background-color: #fef2f2;">
                <td colspan="2" class="text-right" style="font-weight: bold; color: #991b1b;">Net Balance Due:</td>
                <td class="text-right" style="font-weight: bold; color: #991b1b; font-size: 14px;">&#8377;{{ number_format($invoice->balance_due, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        This is a computer generated invoice and does not require a physical signature.<br>
        Thank you for your timely contribution towards our society maintenance!
    </div>
</body>
</html>
