<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Collections Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #0066cc; padding-bottom: 10px; }
        .header h1 { color: #0066cc; margin: 0; font-size: 18px; }
        .header p { margin: 5px 0; color: #666; }
        .summary { margin-bottom: 20px; }
        .summary-box { display: inline-block; width: 30%; text-align: center; padding: 10px; background: #f5f5f5; margin-right: 3%; }
        .summary-box .label { font-size: 10px; color: #666; }
        .summary-box .value { font-size: 16px; font-weight: bold; color: #0066cc; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #0066cc; color: white; font-size: 10px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { background: #e6f3ff; font-weight: bold; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>DIVARUWASA</h1>
        <p>Diamond Valley Rural Waterworks and Sanitation Association, INC.</p>
        <h2 style="margin-top: 15px;">Collections Report</h2>
        <p>Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</p>
    </div>

    <div class="summary">
        <div class="summary-box">
            <div class="label">Total Collections</div>
            <div class="value">₱{{ number_format($totalAmount, 2) }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Total Transactions</div>
            <div class="value">{{ $payments->count() }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Average per Transaction</div>
            <div class="value">₱{{ $payments->count() > 0 ? number_format($totalAmount / $payments->count(), 2) : '0.00' }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Receipt No.</th>
                <th>Date & Time</th>
                <th>Consumer ID</th>
                <th>Consumer Name</th>
                <th>Bill Period</th>
                <th class="text-right">Amount</th>
                <th>Processed By</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
                <tr>
                    <td>{{ $payment->receipt_number }}</td>
                    <td>{{ $payment->paid_at->format('M d, Y h:i A') }}</td>
                    <td>{{ $payment->consumer->id_no }}</td>
                    <td>{{ $payment->consumer->full_name }}</td>
                    <td>{{ $payment->bill->billing_period }}</td>
                    <td class="text-right">₱{{ number_format($payment->amount, 2) }}</td>
                    <td>{{ $payment->processedBy->full_name }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="5"><strong>TOTAL</strong></td>
                <td class="text-right"><strong>₱{{ number_format($totalAmount, 2) }}</strong></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Generated: {{ now()->format('F d, Y h:i A') }}</p>
    </div>
</body>
</html>

