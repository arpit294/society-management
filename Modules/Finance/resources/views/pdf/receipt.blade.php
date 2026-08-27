<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt #{{ $payment->receipt_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #333; line-height: 1.5; padding: 25px; }
        .receipt-box { border: 2px solid #10b981; border-radius: 8px; padding: 20px; }
        .header-table { width: 100%; margin-bottom: 20px; border-bottom: 2px solid #10b981; padding-bottom: 12px; }
        .header-table td { vertical-align: top; }
        .brand-title { font-size: 22px; font-weight: bold; color: #065f46; }
        .rec-title { font-size: 20px; font-weight: bold; color: #10b981; text-align: right; }
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { padding: 8px 6px; border-bottom: 1px dashed #e2e8f0; }
        .text-right { text-align: right; }
        .amount-box { background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; padding: 15px; margin: 20px 0; text-align: center; }
        .amount-val { font-size: 24px; font-weight: bold; color: #065f46; }
        .footer { margin-top: 30px; font-size: 11px; color: #64748b; text-align: center; }
    </style>
</head>
<body>
    <div class="receipt-box">
        <table class="header-table">
            <tr>
                <td>
                    <div class="brand-title">{{ \App\Models\Setting::get('society_name') ?: 'Society Management' }}</div>
                    <small>{{ \App\Models\Setting::get('society_address') ?: 'Residential Housing Society' }}</small>
                </td>
                <td class="text-right">
                    <div class="rec-title">PAYMENT RECEIPT</div>
                    <div><strong>#{{ $payment->receipt_number }}</strong></div>
                    <small>Date: {{ $payment->payment_date->format('d M Y') }}</small>
                </td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <td style="width: 30%; color: #64748b;">Received From:</td>
                <td><strong>{{ $payment->user ? $payment->user->name : 'N/A' }}</strong></td>
            </tr>
            <tr>
                <td style="color: #64748b;">Property Unit:</td>
                <td><strong>{{ $payment->flat ? ($payment->flat->block ? 'Block ' . $payment->flat->block->block_name . ' - ' : '') . $payment->flat->flat_no : 'N/A' }}</strong></td>
            </tr>
            @if($payment->invoice)
            <tr>
                <td style="color: #64748b;">Invoice Reference:</td>
                <td>{{ $payment->invoice->invoice_number }} ({{ $payment->invoice->bill_month }} {{ $payment->invoice->bill_year }})</td>
            </tr>
            @endif
            <tr>
                <td style="color: #64748b;">Payment Mode:</td>
                <td><strong>{{ strtoupper(str_replace('_', ' ', $payment->payment_mode)) }}</strong></td>
            </tr>
            @if($payment->transaction_reference)
            <tr>
                <td style="color: #64748b;">Reference / UTR / Cheque:</td>
                <td>{{ $payment->transaction_reference }}</td>
            </tr>
            @endif
            <tr>
                <td style="color: #64748b;">Deposited Into:</td>
                <td>{{ $payment->bankAccount ? $payment->bankAccount->bank_name . ' (' . $payment->bankAccount->account_number . ')' : 'Cash' }}</td>
            </tr>
        </table>

        <div class="amount-box">
            <div style="font-size: 12px; text-transform: uppercase; color: #065f46; letter-spacing: 1px;">Amount Received</div>
            <div class="amount-val">&#8377;{{ number_format($payment->amount, 2) }}</div>
        </div>

        <div class="footer">
            Thank you for your payment! This is an official digital receipt.
        </div>
    </div>
</body>
</html>
