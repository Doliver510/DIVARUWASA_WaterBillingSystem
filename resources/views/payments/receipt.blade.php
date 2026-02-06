<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $payment->receipt_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: A5;
            margin: 8mm;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            background: #fff;
        }

        .receipt-container {
            max-width: 148mm;
            margin: 0 auto;
            padding: 8mm;
            border: 2px solid #0066cc;
        }

        /* Header */
        .header {
            text-align: center;
            border-bottom: 2px solid #0066cc;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .header .association-name {
            font-size: 20px;
            font-weight: bold;
            color: #0066cc;
            letter-spacing: 1px;
        }

        .header .association-full-name {
            font-size: 10px;
            font-weight: 600;
            margin-top: 2px;
        }

        .header .association-address {
            font-size: 9px;
            color: #555;
            margin-top: 2px;
        }

        .header .association-tin {
            font-size: 9px;
            color: #555;
            font-style: italic;
        }

        /* OR Title */
        .or-title {
            text-align: center;
            margin: 15px 0;
        }

        .or-title .title {
            font-size: 18px;
            font-weight: bold;
            color: #0066cc;
            text-transform: uppercase;
            letter-spacing: 3px;
        }

        .or-title .or-number {
            font-size: 16px;
            font-weight: bold;
            margin-top: 5px;
            padding: 5px 15px;
            background: #0066cc;
            color: #fff;
            display: inline-block;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 15px;
        }

        .info-item {
            padding: 5px 0;
            border-bottom: 1px dotted #ccc;
        }

        .info-item .label {
            font-size: 9px;
            text-transform: uppercase;
            color: #666;
        }

        .info-item .value {
            font-size: 12px;
            font-weight: 600;
        }

        .info-item.full-width {
            grid-column: span 2;
        }

        /* Amount Box */
        .amount-box {
            border: 3px solid #0066cc;
            padding: 15px;
            text-align: center;
            margin: 20px 0;
            background: #f0f7ff;
        }

        .amount-box .label {
            font-size: 12px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 5px;
        }

        .amount-box .amount {
            font-size: 32px;
            font-weight: bold;
            color: #0066cc;
        }

        .amount-box .payment-method {
            font-size: 11px;
            margin-top: 5px;
            color: #28a745;
            font-weight: bold;
        }

        /* Balance Info */
        .balance-info {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            background: #f5f5f5;
            margin: 10px 0;
            border-radius: 4px;
        }

        .balance-info .item {
            text-align: center;
        }

        .balance-info .item .label {
            font-size: 9px;
            color: #666;
        }

        .balance-info .item .value {
            font-size: 13px;
            font-weight: bold;
        }

        .balance-info .item.remaining .value {
            color: #ff9800;
        }

        .balance-info .item.paid .value {
            color: #28a745;
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
        }

        .footer .signature-line {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .footer .signature-box {
            text-align: center;
            width: 45%;
        }

        .footer .signature-box .line {
            border-top: 1px solid #000;
            margin-bottom: 5px;
        }

        .footer .signature-box .label {
            font-size: 9px;
            color: #666;
        }

        .footer .note {
            text-align: center;
            font-size: 9px;
            color: #666;
            margin-top: 15px;
        }

        .footer .timestamp {
            text-align: center;
            font-size: 8px;
            color: #999;
            margin-top: 10px;
        }

        /* Print Styles */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .receipt-container {
                border: none;
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

        /* Watermark for paid */
        .paid-stamp {
            position: relative;
        }

        .paid-stamp::after {
            content: '';
        }

        @if($payment->balance_after <= 0)
        .paid-stamp::after {
            content: 'FULLY PAID';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            font-size: 48px;
            font-weight: bold;
            color: rgba(40, 167, 69, 0.15);
            text-transform: uppercase;
            letter-spacing: 5px;
            pointer-events: none;
            white-space: nowrap;
        }
        @endif
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">Print Receipt</button>

    <div class="receipt-container paid-stamp">
        {{-- Header --}}
        <div class="header">
            <div class="association-name">{{ $association['name'] }}</div>
            <div class="association-full-name">{{ $association['full_name'] }}</div>
            <div class="association-address">{{ $association['address'] }}</div>
            <div class="association-tin">Non-Vat Reg. TIN: {{ $association['tin'] }}</div>
        </div>

        {{-- OR Title --}}
        <div class="or-title">
            <div class="title">Payment Receipt</div>
            <div class="or-number">{{ $payment->receipt_number }}</div>
        </div>

        {{-- Payment Info --}}
        <div class="info-grid">
            <div class="info-item">
                <div class="label">Date</div>
                <div class="value">{{ $payment->paid_at->format('F d, Y') }}</div>
            </div>
            <div class="info-item">
                <div class="label">Time</div>
                <div class="value">{{ $payment->paid_at->format('h:i A') }}</div>
            </div>
            <div class="info-item">
                <div class="label">Consumer ID</div>
                <div class="value">{{ $payment->consumer->id_no }}</div>
            </div>
            <div class="info-item">
                <div class="label">Payment For</div>
                <div class="value">
                    @if($payment->isBillPayment() && $payment->bill)
                        Water Bill - {{ $payment->bill->billing_period_label }}
                    @elseif($payment->isMaintenancePayment())
                        Maintenance Materials
                    @else
                        Payment
                    @endif
                </div>
            </div>
            <div class="info-item full-width">
                <div class="label">Consumer Name</div>
                <div class="value">{{ $payment->consumer->full_name }}</div>
            </div>
            <div class="info-item full-width">
                <div class="label">Address</div>
                <div class="value">{{ $payment->consumer->address }}</div>
            </div>
        </div>

        {{-- Maintenance Materials Detail (for maintenance payments) --}}
        @if($payment->isMaintenancePayment() && $payment->maintenanceRequest)
            <div style="margin: 15px 0; padding: 10px; background: #f9f9f9; border: 1px solid #ddd;">
                <div style="font-size: 10px; color: #666; text-transform: uppercase; margin-bottom: 8px;">Materials Used - Request #{{ $payment->maintenanceRequest->id }}</div>
                <table style="width: 100%; font-size: 10px; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid #ccc;">
                            <th style="text-align: left; padding: 4px;">Material</th>
                            <th style="text-align: center; padding: 4px;">Qty</th>
                            <th style="text-align: right; padding: 4px;">Unit Price</th>
                            <th style="text-align: right; padding: 4px;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payment->maintenanceRequest->maintenanceMaterials as $mm)
                            <tr style="border-bottom: 1px dotted #ddd;">
                                <td style="padding: 4px;">{{ $mm->material->name }}</td>
                                <td style="text-align: center; padding: 4px;">{{ $mm->quantity }} {{ $mm->material->unit }}</td>
                                <td style="text-align: right; padding: 4px;">₱{{ number_format($mm->unit_price, 2) }}</td>
                                <td style="text-align: right; padding: 4px;">₱{{ number_format($mm->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Amount Box --}}
        <div class="amount-box">
            <div class="label">Amount Received</div>
            <div class="amount">₱{{ number_format($payment->amount, 2) }}</div>
            <div class="payment-method">{{ strtoupper($payment->payment_method) }} PAYMENT</div>
        </div>

        {{-- Balance Info (for bill payments) --}}
        @if($payment->isBillPayment() && $payment->bill)
            <div class="balance-info">
                <div class="item">
                    <div class="label">Total Bill</div>
                    <div class="value">₱{{ number_format($payment->bill->total_amount, 2) }}</div>
                </div>
                <div class="item">
                    <div class="label">Before Payment</div>
                    <div class="value">₱{{ number_format($payment->balance_before, 2) }}</div>
                </div>
                <div class="item {{ $payment->balance_after <= 0 ? 'paid' : 'remaining' }}">
                    <div class="label">Remaining Balance</div>
                    <div class="value">₱{{ number_format($payment->balance_after, 2) }}</div>
                </div>
            </div>
        @else
            {{-- For maintenance payments - simpler display --}}
            <div class="balance-info">
                <div class="item paid">
                    <div class="label">Total Amount</div>
                    <div class="value">₱{{ number_format($payment->amount, 2) }}</div>
                </div>
                <div class="item paid">
                    <div class="label">Status</div>
                    <div class="value">FULLY PAID</div>
                </div>
            </div>
        @endif

        @if($payment->remarks)
            <div class="info-item full-width" style="margin-top: 10px;">
                <div class="label">Remarks</div>
                <div class="value">{{ $payment->remarks }}</div>
            </div>
        @endif

        {{-- Footer --}}
        <div class="footer">
            <div class="signature-line">
                <div class="signature-box">
                    <div class="line"></div>
                    <div class="label">Customer Signature</div>
                </div>
                <div class="signature-box">
                    <div class="line"></div>
                    <div class="label">Cashier: {{ $payment->processedBy->full_name }}</div>
                </div>
            </div>

            <div class="note">
                <p>This serves as your Payment Receipt. Please keep this for your records.</p>
                <p>For inquiries: {{ $association['email'] }}</p>
            </div>

            <div class="timestamp">
                Printed: {{ now()->format('M d, Y h:i A') }}
            </div>
        </div>
    </div>
</body>
</html>

