<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Bill - {{ $bill->billing_period_label }} - {{ $bill->consumer->id_no }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: A4;
            margin: 10mm;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            background: #fff;
        }

        .bill-container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 10mm;
        }

        /* Header */
        .header {
            text-align: center;
            border-bottom: 2px solid #0066cc;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .header .logo {
            margin-bottom: 5px;
        }

        .header .logo img {
            height: 60px;
            width: auto;
        }

        .header .association-name {
            font-size: 24px;
            font-weight: bold;
            color: #0066cc;
            letter-spacing: 2px;
        }

        .header .association-full-name {
            font-size: 12px;
            font-weight: 600;
            margin-top: 2px;
        }

        .header .association-address {
            font-size: 10px;
            color: #555;
            margin-top: 3px;
        }

        .header .association-tin {
            font-size: 10px;
            color: #555;
            font-style: italic;
        }

        .header .bill-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
            background: #0066cc;
            color: #fff;
            padding: 5px 15px;
            display: inline-block;
        }

        /* Info Sections */
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .info-box {
            flex: 1;
            border: 1px solid #ddd;
            padding: 8px;
            margin: 0 5px;
        }

        .info-box:first-child {
            margin-left: 0;
        }

        .info-box:last-child {
            margin-right: 0;
        }

        .info-box .title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #666;
            border-bottom: 1px solid #eee;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }

        .info-box .value {
            font-size: 12px;
        }

        .info-box .value.large {
            font-size: 16px;
            font-weight: bold;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }

        table th {
            background: #f5f5f5;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .highlight-row {
            background: #e6f3ff;
            font-weight: bold;
        }

        .total-row {
            background: #0066cc;
            color: #fff;
            font-weight: bold;
            font-size: 13px;
        }

        .balance-row {
            background: #ff9800;
            color: #fff;
            font-weight: bold;
            font-size: 14px;
        }

        /* Important Dates */
        .dates-section {
            display: flex;
            justify-content: space-around;
            margin-bottom: 15px;
            padding: 10px;
            border: 2px solid #0066cc;
            background: #f8faff;
        }

        .date-item {
            text-align: center;
        }

        .date-item .label {
            font-size: 9px;
            text-transform: uppercase;
            color: #666;
        }

        .date-item .value {
            font-size: 13px;
            font-weight: bold;
        }

        .date-item.warning .value {
            color: #ff5722;
        }

        /* Footer */
        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
            text-align: center;
            font-size: 9px;
            color: #666;
        }

        /* Tear-off Stub */
        .tear-line {
            border-top: 2px dashed #000;
            margin: 20px 0;
            position: relative;
        }

        .tear-line::before {
            content: '✂ CUT HERE';
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%);
            background: #fff;
            padding: 0 10px;
            font-size: 10px;
            color: #666;
        }

        .stub {
            padding: 10px;
            border: 1px solid #000;
        }

        .stub-header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 12px;
        }

        .stub-info {
            display: flex;
            justify-content: space-between;
        }

        .stub-info .left,
        .stub-info .right {
            flex: 1;
        }

        .stub-info .item {
            margin-bottom: 5px;
        }

        .stub-info .label {
            font-size: 9px;
            color: #666;
        }

        .stub-info .value {
            font-weight: bold;
        }

        .stub-amount {
            text-align: center;
            margin-top: 10px;
            padding: 10px;
            background: #f5f5f5;
            border: 1px solid #000;
        }

        .stub-amount .label {
            font-size: 10px;
        }

        .stub-amount .value {
            font-size: 20px;
            font-weight: bold;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .bill-container {
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }

        /* Print button */
        .print-btn {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 10px 20px;
            background: #0066cc;
            color: #fff;
            border: none;
            cursor: pointer;
            font-size: 14px;
            border-radius: 5px;
        }

        .print-btn:hover {
            background: #0055aa;
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">🖨️ Print Bill</button>

    <div class="bill-container">
        {{-- Header --}}
        <div class="header">
            <div class="logo">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" onerror="this.style.display='none'">
            </div>
            <div class="association-name">{{ $association['name'] }}</div>
            <div class="association-full-name">{{ $association['full_name'] }}</div>
            <div class="association-address">{{ $association['address'] }}</div>
            <div class="association-tin">Non-Vat Reg. TIN: {{ $association['tin'] }}</div>
            <div class="bill-title">Water Bill for the month of {{ $bill->billing_period_label }}</div>
        </div>

        {{-- Consumer & Account Info --}}
        <div class="info-row">
            <div class="info-box">
                <div class="title">Account Information</div>
                <div class="value"><strong>ID No:</strong> {{ $bill->consumer->id_no }}</div>
                <div class="value"><strong>Name:</strong> {{ $bill->consumer->full_name }}</div>
                <div class="value"><strong>Address:</strong> {{ $bill->consumer->address }}</div>
            </div>
            <div class="info-box">
                <div class="title">Billing Period</div>
                <div class="value"><strong>From:</strong> {{ $bill->period_from->format('M d, Y') }}</div>
                <div class="value"><strong>To:</strong> {{ $bill->period_to->format('M d, Y') }}</div>
                <div class="value"><strong>Bill Date:</strong> {{ $bill->created_at->format('M d, Y') }}</div>
            </div>
        </div>

        {{-- Meter Reading --}}
        <table>
            <thead>
                <tr>
                    <th>Previous Reading</th>
                    <th>Present Reading</th>
                    <th>Consumption (cu.m)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">{{ number_format($bill->previous_reading) }}</td>
                    <td class="text-center">{{ number_format($bill->present_reading) }}</td>
                    <td class="text-center"><strong>{{ number_format($bill->consumption) }}</strong></td>
                </tr>
            </tbody>
        </table>

        {{-- Charges Breakdown --}}
        <table>
            <thead>
                <tr>
                    <th colspan="2">Charges Breakdown</th>
                </tr>
            </thead>
            <tbody>
                @if($chargeBreakdown['base_charge'] > 0)
                    <tr>
                        <td>Minimum Charge (0-{{ $chargeBreakdown['base_covers'] }} cu.m)</td>
                        <td class="text-right">₱{{ number_format($chargeBreakdown['base_charge'], 2) }}</td>
                    </tr>
                @endif
                @foreach($chargeBreakdown['tiers'] as $tier)
                    <tr>
                        <td>{{ $tier['range'] }} ({{ $tier['units'] }} cu.m × ₱{{ number_format($tier['rate'], 2) }})</td>
                        <td class="text-right">₱{{ number_format($tier['amount'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="highlight-row">
                    <td>Water Charge Subtotal</td>
                    <td class="text-right">₱{{ number_format($bill->water_charge, 2) }}</td>
                </tr>

                @if($bill->arrears > 0)
                    <tr>
                        <td>Arrears (Previous Balance)</td>
                        <td class="text-right">₱{{ number_format($bill->arrears, 2) }}</td>
                    </tr>
                @endif

                @if($bill->penalty > 0)
                    <tr style="color: #d32f2f;">
                        <td>Late Payment Penalty</td>
                        <td class="text-right">₱{{ number_format($bill->penalty, 2) }}</td>
                    </tr>
                @endif

                @if($bill->other_charges > 0)
                    <tr>
                        <td>Other Charges (Materials/Services)</td>
                        <td class="text-right">₱{{ number_format($bill->other_charges, 2) }}</td>
                    </tr>
                @endif

                @if($bill->meter_installment > 0)
                    <tr>
                        <td>Meter Installment</td>
                        <td class="text-right">₱{{ number_format($bill->meter_installment, 2) }}</td>
                    </tr>
                @endif


                <tr class="total-row">
                    <td>TOTAL AMOUNT</td>
                    <td class="text-right">₱{{ number_format($bill->total_amount, 2) }}</td>
                </tr>

                @if($bill->amount_paid > 0)
                    <tr style="color: #2e7d32;">
                        <td>Amount Paid</td>
                        <td class="text-right">₱{{ number_format($bill->amount_paid, 2) }}</td>
                    </tr>
                @endif

                <tr class="balance-row">
                    <td>BALANCE DUE</td>
                    <td class="text-right">₱{{ number_format($bill->balance, 2) }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Important Dates --}}
        <div class="dates-section">
            <div class="date-item">
                <div class="label">Pay Before (No Penalty)</div>
                <div class="value">{{ $bill->disconnection_date->format('M d, Y') }}</div>
            </div>
            <div class="date-item warning">
                <div class="label">Grace Period (With ₱{{ number_format(\App\Models\AppSetting::getValue('penalty_fee', 50), 0) }} Penalty)</div>
                <div class="value">{{ $bill->due_date_start->format('M d') }} - {{ $bill->due_date_end->format('M d, Y') }}</div>
            </div>
            <div class="date-item warning">
                <div class="label">Disconnection After</div>
                <div class="value">{{ $bill->due_date_end->format('M d, Y') }}</div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p><strong>Payment Reminder:</strong> Please pay your bill on or before the disconnection date to avoid penalties.</p>
            <p>For inquiries, email: {{ $association['email'] }}</p>
            <p>Thank you for being a valued member of {{ $association['name'] }}!</p>
        </div>

        {{-- Tear-off Payment Stub --}}
        <div class="tear-line"></div>

        <div class="stub">
            <div class="stub-header">PAYMENT STUB - {{ $association['name'] }}</div>
            <div class="stub-info">
                <div class="left">
                    <div class="item">
                        <div class="label">ID No.</div>
                        <div class="value">{{ $bill->consumer->id_no }}</div>
                    </div>
                    <div class="item">
                        <div class="label">Name</div>
                        <div class="value">{{ $bill->consumer->full_name }}</div>
                    </div>
                </div>
                <div class="right">
                    <div class="item">
                        <div class="label">Billing Period</div>
                        <div class="value">{{ $bill->billing_period_label }}</div>
                    </div>
                    <div class="item">
                        <div class="label">Due Date</div>
                        <div class="value">{{ $bill->disconnection_date->format('M d, Y') }}</div>
                    </div>
                </div>
            </div>
            <div class="stub-amount">
                <div class="label">AMOUNT DUE</div>
                <div class="value">₱{{ number_format($bill->balance, 2) }}</div>
            </div>
        </div>
    </div>

    <script>
        // Auto-print when opened
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>

