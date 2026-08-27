<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Voucher #{{ $voucher->voucher_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #333; line-height: 1.5; padding: 25px; }
        .voucher-box { border: 2px solid #64748b; border-radius: 8px; padding: 20px; }
        .header-table { width: 100%; margin-bottom: 20px; border-bottom: 2px solid #64748b; padding-bottom: 12px; }
        .header-table td { vertical-align: top; }
        .brand-title { font-size: 22px; font-weight: bold; color: #1e293b; }
        .vch-title { font-size: 20px; font-weight: bold; color: #475569; text-align: right; }
        .info-table { width: 100%; margin-bottom: 25px; border-collapse: collapse; }
        .info-table td { padding: 8px 6px; border-bottom: 1px dashed #e2e8f0; }
        .amount-box { background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 15px; margin: 20px 0; text-align: center; }
        .amount-val { font-size: 24px; font-weight: bold; color: #dc2626; }
        .sig-table { width: 100%; margin-top: 40px; }
        .sig-table td { width: 33%; text-align: center; vertical-align: bottom; }
        .sig-line { border-top: 1px solid #333; margin-top: 50px; padding-top: 5px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="voucher-box">
        <table class="header-table">
            <tr>
                <td>
                    <div class="brand-title">{{ \App\Models\Setting::get('society_name') ?: 'Society Management' }}</div>
                    <small>{{ \App\Models\Setting::get('society_address') ?: 'Residential Housing Society' }}</small>
                </td>
                <td style="text-align: right;">
                    <div class="vch-title">PAYMENT VOUCHER</div>
                    <div><strong>#{{ $voucher->voucher_number }}</strong></div>
                    <small>Date: {{ $voucher->voucher_date->format('d M Y') }}</small>
                </td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <td style="width: 30%; color: #64748b;">Paid To (Payee / Vendor):</td>
                <td><strong>{{ $voucher->vendor ? $voucher->vendor->name : 'N/A' }}</strong></td>
            </tr>
            @if($voucher->bill)
            <tr>
                <td style="color: #64748b;">Vendor Bill Ref:</td>
                <td>Bill #{{ $voucher->bill->bill_number }}</td>
            </tr>
            @endif
            <tr>
                <td style="color: #64748b;">Payment Mode:</td>
                <td><strong>{{ strtoupper(str_replace('_', ' ', $voucher->payment_mode)) }}</strong></td>
            </tr>
            @if($voucher->reference_no)
            <tr>
                <td style="color: #64748b;">Cheque No. / Transaction Ref:</td>
                <td>{{ $voucher->reference_no }}</td>
            </tr>
            @endif
            <tr>
                <td style="color: #64748b;">Disbursing Account:</td>
                <td>{{ $voucher->bankAccount ? $voucher->bankAccount->bank_name . ' (' . $voucher->bankAccount->account_number . ')' : 'Cash' }}</td>
            </tr>
            <tr>
                <td style="color: #64748b;">Description / Purpose:</td>
                <td>{{ $voucher->description }}</td>
            </tr>
        </table>

        <div class="amount-box">
            <div style="font-size: 12px; text-transform: uppercase; color: #64748b; letter-spacing: 1px;">Disbursement Amount</div>
            <div class="amount-val">&#8377;{{ number_format($voucher->amount, 2) }}</div>
        </div>

        <table class="sig-table">
            <tr>
                <td>
                    <div class="sig-line">Prepared By</div>
                    <small>{{ $voucher->creator?->name ?? 'Accountant' }}</small>
                </td>
                <td>
                    <div class="sig-line">Approved By</div>
                    <small>{{ $voucher->approver?->name ?? 'Treasurer' }}</small>
                </td>
                <td>
                    <div class="sig-line">Payee Signature</div>
                    <small>Received Payment</small>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
