<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Consumption Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #0066cc; padding-bottom: 10px; }
        .header h1 { color: #0066cc; margin: 0; font-size: 18px; }
        .header p { margin: 5px 0; color: #666; }
        .summary { margin-bottom: 20px; text-align: center; }
        .summary-box { display: inline-block; padding: 15px 30px; background: #e6f3ff; border: 2px solid #0066cc; }
        .summary-box .label { font-size: 10px; color: #666; }
        .summary-box .value { font-size: 20px; font-weight: bold; color: #0066cc; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #0066cc; color: white; }
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
        <h2 style="margin-top: 15px;">Water Consumption Report</h2>
        <p>Year: {{ $year }}</p>
    </div>

    <div class="summary">
        <div class="summary-box">
            <div class="label">Total Water Consumption</div>
            <div class="value">{{ number_format($totalConsumption) }} cu.m</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th class="text-center">Number of Readings</th>
                <th class="text-right">Total Consumption (cu.m)</th>
                <th class="text-right">Average per Consumer (cu.m)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($monthlyData as $data)
                <tr>
                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $data->billing_period)->format('F') }}</td>
                    <td class="text-center">{{ $data->reading_count }}</td>
                    <td class="text-right">{{ number_format($data->total_consumption) }}</td>
                    <td class="text-right">{{ number_format($data->avg_consumption, 1) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td><strong>TOTAL</strong></td>
                <td class="text-center"><strong>{{ $monthlyData->sum('reading_count') }}</strong></td>
                <td class="text-right"><strong>{{ number_format($totalConsumption) }}</strong></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Generated: {{ now()->format('F d, Y h:i A') }}</p>
    </div>
</body>
</html>

