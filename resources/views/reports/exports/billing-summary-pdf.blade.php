<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Billing Summary Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #0066cc; padding-bottom: 10px; }
        .header h1 { color: #0066cc; margin: 0; font-size: 18px; }
        .header p { margin: 5px 0; color: #666; }
        .summary { margin-bottom: 20px; }
        .summary-box { display: inline-block; width: 23%; text-align: center; padding: 8px; background: #f5f5f5; margin-right: 1%; }
        .summary-box .label { font-size: 9px; color: #666; }
        .summary-box .value { font-size: 14px; font-weight: bold; }
        .summary-box.green .value { color: #28a745; }
        .summary-box.orange .value { color: #ff9800; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; font-size: 9px; }
        th { background: #0066cc; color: white; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { background: #e6f3ff; font-weight: bold; }
        .status-paid { color: #28a745; }
        .status-partial { color: #ff9800; }
        .status-unpaid { color: #666; }
        .status-overdue { color: #dc3545; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>DIVARUWASA</h1>
        <p>Diamond Valley Rural Waterworks and Sanitation Association, INC.</p>
        <h2 style="margin-top: 15px;">Billing Summary Report</h2>
        <p>Period: {{ \Carbon\Carbon::createFromFormat('Y-m', $period)->format('F Y') }}</p>
    </div>

    <div class="summary">
        <div class="summary-box">
            <div class="label">Total Billed</div>
            <div class="value">₱{{ number_format($summary['total_billed'], 2) }}</div>
        </div>
        <div class="summary-box green">
            <div class="label">Total Collected</div>
            <div class="value">₱{{ number_format($summary['total_collected'], 2) }}</div>
        </div>
        <div class="summary-box orange">
            <div class="label">Outstanding</div>
            <div class="value">₱{{ number_format($summary['total_outstanding'], 2) }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Collection Rate</div>
            <div class="value">{{ $summary['total_billed'] > 0 ? number_format(($summary['total_collected'] / $summary['total_billed']) * 100, 1) : 0 }}%</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID No.</th>
                <th>Consumer Name</th>
                <th class="text-center">Consumption</th>
                <th class="text-right">Total Amount</th>
                <th class="text-right">Amount Paid</th>
                <th class="text-right">Balance</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bills as $bill)
                <tr>
                    <td>{{ $bill->consumer->id_no }}</td>
                    <td>{{ $bill->consumer->full_name }}</td>
                    <td class="text-center">{{ number_format($bill->consumption) }}</td>
                    <td class="text-right">₱{{ number_format($bill->total_amount, 2) }}</td>
                    <td class="text-right">₱{{ number_format($bill->amount_paid, 2) }}</td>
                    <td class="text-right">₱{{ number_format($bill->balance, 2) }}</td>
                    <td class="text-center status-{{ $bill->status }}">{{ $bill->status_label }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3"><strong>TOTAL</strong></td>
                <td class="text-right"><strong>₱{{ number_format($summary['total_billed'], 2) }}</strong></td>
                <td class="text-right"><strong>₱{{ number_format($summary['total_collected'], 2) }}</strong></td>
                <td class="text-right"><strong>₱{{ number_format($summary['total_outstanding'], 2) }}</strong></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Generated: {{ now()->format('F d, Y h:i A') }}</p>
    </div>
</body>
</html>

