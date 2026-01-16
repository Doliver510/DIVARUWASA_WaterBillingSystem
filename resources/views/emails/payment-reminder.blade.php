<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Reminder - Bill #{{ $bill->id }}</title>
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
            background-color: {{ $reminderType === 'penalty_day' ? '#dc3545' : '#ffc107' }};
            color: {{ $reminderType === 'penalty_day' ? '#fff' : '#333' }};
            padding: 20px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
        }
        .content {
            padding: 30px;
        }
        .alert-box {
            background-color: {{ $reminderType === 'penalty_day' ? '#f8d7da' : '#fff3cd' }};
            border: 2px solid {{ $reminderType === 'penalty_day' ? '#dc3545' : '#ffc107' }};
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .alert-box h3 {
            margin: 0 0 10px;
            color: {{ $reminderType === 'penalty_day' ? '#721c24' : '#856404' }};
        }
        .alert-box p {
            margin: 0;
            color: {{ $reminderType === 'penalty_day' ? '#721c24' : '#856404' }};
        }
        .amount-due {
            font-size: 32px;
            font-weight: bold;
            color: {{ $reminderType === 'penalty_day' ? '#dc3545' : '#0d6efd' }};
            text-align: center;
            margin: 20px 0;
        }
        .bill-details {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .bill-details h4 {
            margin: 0 0 15px;
            color: #333;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .detail-row:last-child {
            border-bottom: none;
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
            background-color: {{ $reminderType === 'penalty_day' ? '#dc3545' : '#0d6efd' }};
            color: #fff;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 16px;
            margin: 20px 0;
        }
        .penalty-warning {
            background-color: #dc3545;
            color: #fff;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            @if($reminderType === 'penalty_day')
                <h1>⚠️ PENALTY APPLIED</h1>
                <p>Immediate Payment Required</p>
            @else
                <h1>⏰ Payment Reminder</h1>
                <p>Due Date Approaching</p>
            @endif
        </div>
        
        <div class="content">
            <p>Dear <strong>{{ $bill->consumer->user->full_name ?? 'Valued Consumer' }}</strong>,</p>
            
            @if($reminderType === 'penalty_day')
                <div class="alert-box">
                    <h3>⚠️ PENALTY HAS BEEN APPLIED</h3>
                    <p>Your bill is now overdue. A penalty fee of <strong>₱{{ number_format(\App\Models\AppSetting::getValue('penalty_fee', 50), 2) }}</strong> has been added to your account.</p>
                </div>
                
                <p>This is to inform you that your water bill for <strong>{{ $bill->billing_period_label }}</strong> is now overdue and a penalty has been applied to your account.</p>
            @else
                <div class="alert-box">
                    <h3>📅 Due Date Approaching</h3>
                    <p>Your bill is due on <strong>{{ $bill->due_date_end->format('F j, Y') }}</strong></p>
                </div>
                
                <p>This is a friendly reminder that your water bill for <strong>{{ $bill->billing_period_label }}</strong> is due soon. Please settle your payment to avoid penalties.</p>
            @endif
            
            <div class="amount-due">
                ₱{{ number_format($bill->balance, 2) }}
                <div style="font-size: 14px; font-weight: normal; color: #666;">Amount Due</div>
            </div>
            
            <div class="bill-details">
                <h4>Bill Details</h4>
                <div class="detail-row">
                    <span>Account Number</span>
                    <span>{{ $bill->consumer->id_no }}</span>
                </div>
                <div class="detail-row">
                    <span>Bill Number</span>
                    <span>#{{ $bill->id }}</span>
                </div>
                <div class="detail-row">
                    <span>Billing Period</span>
                    <span>{{ $bill->billing_period_label }}</span>
                </div>
                <div class="detail-row">
                    <span>Consumption</span>
                    <span>{{ number_format($bill->consumption) }} cubic meters</span>
                </div>
                <div class="detail-row">
                    <span>Due Date</span>
                    <span>{{ $bill->due_date_end->format('M d, Y') }}</span>
                </div>
            </div>
            
            @if($reminderType === 'penalty_day')
                <div class="penalty-warning">
                    <strong>⚠️ Warning:</strong> Continued non-payment may result in water service disconnection.
                </div>
            @else
                <p><strong>⚠️ Important:</strong> A penalty fee of <strong>₱{{ number_format(\App\Models\AppSetting::getValue('penalty_fee', 50), 2) }}</strong> will be added if payment is not received by the due date.</p>
            @endif
            
            <p style="text-align: center;">
                <a href="{{ config('app.url') }}" class="cta-button">Pay Now at Our Office</a>
            </p>
            
            <p>If you have already made a payment, please disregard this notice. For any concerns or questions, please visit our office.</p>
            
            <p>Thank you for your prompt attention to this matter.</p>
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

