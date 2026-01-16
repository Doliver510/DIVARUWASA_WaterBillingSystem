<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Bill - {{ $bill->billing_period_label }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #0d6efd;
            color: #fff;
            padding: 20px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .bill-summary {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .bill-summary h3 {
            margin: 0 0 15px;
            color: #0d6efd;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 10px;
        }
        .bill-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .bill-row:last-child {
            border-bottom: none;
        }
        .bill-row.total {
            font-weight: bold;
            font-size: 18px;
            color: #0d6efd;
            border-top: 2px solid #0d6efd;
            margin-top: 10px;
            padding-top: 15px;
        }
        .due-date-box {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px 20px;
            margin: 20px 0;
            text-align: center;
        }
        .due-date-box h4 {
            margin: 0 0 5px;
            color: #856404;
        }
        .due-date-box p {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            color: #856404;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 15px 0;
        }
        .info-item {
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 4px;
        }
        .info-item label {
            display: block;
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
        }
        .info-item span {
            font-weight: bold;
            color: #333;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .cta-button {
            display: inline-block;
            background-color: #0d6efd;
            color: #fff;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>💧 Water Bill Statement</h1>
            <p>Billing Period: {{ $bill->billing_period_label }}</p>
        </div>
        
        <div class="content">
            <p>Dear <strong>{{ $bill->consumer->user->full_name ?? 'Valued Consumer' }}</strong>,</p>
            
            <p>Your water bill for <strong>{{ $bill->billing_period_label }}</strong> has been generated. Please find the details below:</p>
            
            <div class="info-grid">
                <div class="info-item">
                    <label>Account No.</label>
                    <span>{{ $bill->consumer->id_no }}</span>
                </div>
                <div class="info-item">
                    <label>Bill No.</label>
                    <span>#{{ $bill->id }}</span>
                </div>
                <div class="info-item">
                    <label>Previous Reading</label>
                    <span>{{ number_format($bill->previous_reading) }} cubic meters</span>
                </div>
                <div class="info-item">
                    <label>Present Reading</label>
                    <span>{{ number_format($bill->present_reading) }} cubic meters</span>
                </div>
            </div>
            
            <div class="bill-summary">
                <h3>Bill Summary</h3>
                <div class="bill-row">
                    <span>Water Consumption ({{ number_format($bill->consumption) }} cubic meters)</span>
                    <span>₱{{ number_format($bill->water_charge, 2) }}</span>
                </div>
                @if($bill->arrears > 0)
                <div class="bill-row">
                    <span>Previous Balance (Arrears)</span>
                    <span>₱{{ number_format($bill->arrears, 2) }}</span>
                </div>
                @endif
                @if($bill->penalty > 0)
                <div class="bill-row">
                    <span>Penalty</span>
                    <span>₱{{ number_format($bill->penalty, 2) }}</span>
                </div>
                @endif
                @if($bill->other_charges > 0)
                <div class="bill-row">
                    <span>Other Charges (Materials)</span>
                    <span>₱{{ number_format($bill->other_charges, 2) }}</span>
                </div>
                @endif
                <div class="bill-row total">
                    <span>TOTAL AMOUNT DUE</span>
                    <span>₱{{ number_format($bill->total_amount, 2) }}</span>
                </div>
            </div>
            
            <div class="due-date-box">
                <h4>📅 Payment Due Date</h4>
                <p>{{ $bill->due_date_start->format('M d') }} - {{ $bill->due_date_end->format('M d, Y') }}</p>
                <small style="color: #856404;">A penalty fee will be applied after the due date.</small>
            </div>
            
            <p><strong>Payment Reminder:</strong> Please settle your bill on or before the due date to avoid penalties and possible disconnection.</p>
            
            <p style="text-align: center;">
                <a href="{{ config('app.url') }}" class="cta-button">Visit Our Office to Pay</a>
            </p>
            
            <p>Thank you for being a valued consumer of DIVARUWASA.</p>
        </div>
        
        <div class="footer">
            <p><strong>DIVARUWASA Water District</strong></p>
            <p>For inquiries, please visit our office during business hours.</p>
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>© {{ date('Y') }} DIVARUWASA. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

