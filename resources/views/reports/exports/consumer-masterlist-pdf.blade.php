<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Consumer Masterlist</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #0066cc; padding-bottom: 10px; }
        .header h1 { color: #0066cc; margin: 0; font-size: 18px; }
        .header p { margin: 5px 0; color: #666; }
        .summary { margin-bottom: 15px; text-align: center; }
        .summary span { margin: 0 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; font-size: 9px; }
        th { background: #0066cc; color: white; }
        .text-center { text-align: center; }
        .status-active { color: #28a745; font-weight: bold; }
        .status-disconnected { color: #dc3545; font-weight: bold; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>DIVARUWASA</h1>
        <p>Diamond Valley Rural Waterworks and Sanitation Association, INC.</p>
        <h2 style="margin-top: 15px;">Consumer Masterlist</h2>
        <p>As of {{ now()->format('F d, Y') }}</p>
    </div>

    <div class="summary">
        <span><strong>Total:</strong> {{ $consumers->count() }}</span>
        <span><strong>Active:</strong> {{ $consumers->where('status', 'Active')->count() }}</span>
        <span><strong>Disconnected:</strong> {{ $consumers->where('status', 'Disconnected')->count() }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID No.</th>
                <th>Last Name</th>
                <th>First Name</th>
                <th>Middle Name</th>
                <th>Email</th>
                <th>Block</th>
                <th>Lot</th>
                <th class="text-center">Status</th>
                <th>Registered</th>
            </tr>
        </thead>
        <tbody>
            @foreach($consumers as $consumer)
                <tr>
                    <td>{{ $consumer->id_no }}</td>
                    <td>{{ $consumer->user->last_name }}</td>
                    <td>{{ $consumer->user->first_name }}</td>
                    <td>{{ $consumer->user->middle_name ?? '' }}</td>
                    <td>{{ $consumer->user->email }}</td>
                    <td>{{ $consumer->block?->name ?? 'N/A' }}</td>
                    <td>{{ $consumer->lot_number }}</td>
                    <td class="text-center status-{{ strtolower($consumer->status) }}">{{ $consumer->status }}</td>
                    <td>{{ $consumer->created_at->format('Y-m-d') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Generated: {{ now()->format('F d, Y h:i A') }}</p>
    </div>
</body>
</html>

