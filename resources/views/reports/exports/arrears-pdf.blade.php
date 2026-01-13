<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Arrears Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #dc3545; padding-bottom: 10px; }
        .header h1 { color: #0066cc; margin: 0; font-size: 18px; }
        .header p { margin: 5px 0; color: #666; }
        .header h2 { color: #dc3545; }
        .summary { margin-bottom: 20px; text-align: center; }
        .summary-box { display: inline-block; padding: 15px 30px; background: #fee; border: 2px solid #dc3545; }
        .summary-box .label { font-size: 10px; color: #666; }
        .summary-box .value { font-size: 20px; font-weight: bold; color: #dc3545; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #dc3545; color: white; font-size: 10px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { background: #fee; font-weight: bold; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>DIVARUWASA</h1>
        <p>Diamond Valley Rural Waterworks and Sanitation Association, INC.</p>
        <h2 style="margin-top: 15px;">Arrears / Outstanding Report</h2>
        <p>As of {{ now()->format('F d, Y') }}</p>
    </div>

    <div class="summary">
        <div class="summary-box">
            <div class="label">Total Outstanding Arrears</div>
            <div class="value">₱{{ number_format($totalArrears, 2) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID No.</th>
                <th>Consumer Name</th>
                <th>Address</th>
                <th class="text-center">Status</th>
                <th class="text-center">Unpaid Bills</th>
                <th class="text-right">Total Arrears</th>
            </tr>
        </thead>
        <tbody>
            @foreach($consumers as $consumer)
                <tr>
                    <td>{{ $consumer->id_no }}</td>
                    <td>{{ $consumer->full_name }}</td>
                    <td>{{ $consumer->address }}</td>
                    <td class="text-center">{{ $consumer->status }}</td>
                    <td class="text-center">{{ $consumer->bills->count() }}</td>
                    <td class="text-right">₱{{ number_format($consumer->total_arrears, 2) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4"><strong>TOTAL ({{ $consumers->count() }} consumers)</strong></td>
                <td class="text-center"><strong>{{ $consumers->sum(fn($c) => $c->bills->count()) }}</strong></td>
                <td class="text-right"><strong>₱{{ number_format($totalArrears, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Generated: {{ now()->format('F d, Y h:i A') }}</p>
    </div>
</body>
</html>

