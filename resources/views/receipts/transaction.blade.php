<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $transaction->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
        }
        .header {
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-info {
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #4F46E5;
            margin-bottom: 5px;
        }
        .receipt-title {
            font-size: 28px;
            font-weight: bold;
            text-align: right;
            color: #4F46E5;
        }
        .receipt-info {
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .info-row {
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #4F46E5;
            border-bottom: 1px solid #E5E7EB;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #4F46E5;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #E5E7EB;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background-color: #F3F4F6;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
            font-size: 10px;
            color: #6B7280;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div class="company-info">
                <div class="company-name">Noteds</div>
                <div>Platform Digital Marketplace</div>
                <div>Indonesia</div>
            </div>
            <div class="receipt-title">RECEIPT</div>
        </div>
    </div>

    <div class="receipt-info">
        <div class="info-row">
            <span class="info-label">Receipt Number:</span>
            <span>{{ substr($transaction->id, 0, 8) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Date:</span>
            <span>{{ $transaction->created_at->format('d F Y H:i:s') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Transaction Type:</span>
            <span style="text-transform: uppercase; font-weight: bold;">{{ $transaction->type }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span style="text-transform: uppercase; font-weight: bold; color: #059669;">{{ $transaction->status }}</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Account Information</div>
        <div><strong>{{ $transaction->user->name }}</strong></div>
        <div>{{ $transaction->user->email }}</div>
        @if($transaction->user->business_name)
            <div>{{ $transaction->user->business_name }}</div>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Transaction Details</div>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $transaction->description ?: 'Transaction' }}</td>
                    <td class="text-right">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td><strong>Balance Before:</strong></td>
                    <td class="text-right">Rp {{ number_format($transaction->balance_before, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td><strong>Transaction Amount:</strong></td>
                    <td class="text-right">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td><strong>Balance After:</strong></td>
                    <td class="text-right"><strong>Rp {{ number_format($transaction->balance_after, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    @if($order)
    <div class="section">
        <div class="section-title">Related Order</div>
        <div class="info-row">
            <span class="info-label">Order Number:</span>
            <span>{{ $order->order_number }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Product:</span>
            <span>{{ $order->product->name }}</span>
        </div>
    </div>
    @endif

    <div class="footer">
        <p>This is an automatically generated receipt from Noteds Platform.</p>
        <p>For any inquiries, please contact us through our contact page.</p>
        <p>Thank you for your business!</p>
    </div>
</body>
</html>

