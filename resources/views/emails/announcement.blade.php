<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $announcement->title }}</title>
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
            background-color: {{ $announcement->type === 'urgent' ? '#dc3545' : ($announcement->type === 'warning' ? '#ffc107' : '#0d6efd') }};
            color: {{ $announcement->type === 'warning' ? '#333' : '#fff' }};
            padding: 20px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
            background-color: rgba(255,255,255,0.2);
        }
        .content {
            padding: 30px;
        }
        .content p {
            margin: 0 0 15px;
        }
        .announcement-content {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            border-left: 4px solid {{ $announcement->type === 'urgent' ? '#dc3545' : ($announcement->type === 'warning' ? '#ffc107' : '#0d6efd') }};
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <span class="badge">
                @if($announcement->type === 'urgent')
                    ⚠️ URGENT
                @elseif($announcement->type === 'warning')
                    ⚡ IMPORTANT
                @else
                    ℹ️ INFORMATION
                @endif
            </span>
            <h1>{{ $announcement->title }}</h1>
        </div>
        
        <div class="content">
            <p>Dear Valued Consumer,</p>
            
            <p>Please be informed of the following announcement from DIVARUWASA:</p>
            
            <div class="announcement-content">
                {!! nl2br(e($announcement->content)) !!}
            </div>
            
            <p style="margin-top: 20px;">
                <strong>Effective Date:</strong> {{ $announcement->starts_at->format('F j, Y') }}
                @if($announcement->ends_at)
                    <br><strong>Until:</strong> {{ $announcement->ends_at->format('F j, Y') }}
                @endif
            </p>
            
            <p>If you have any questions or concerns, please visit our office or contact us.</p>
            
            <p>Thank you for your attention.</p>
        </div>
        
        <div class="footer">
            <p><strong>DIVARUWASA Water District</strong></p>
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>© {{ date('Y') }} DIVARUWASA. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

