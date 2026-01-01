<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->order_number }}</title>
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
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            text-align: right;
            color: #4F46E5;
        }
        .invoice-info {
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
            <div class="invoice-title">INVOICE</div>
        </div>
    </div>

    <div class="invoice-info">
        <div class="info-row">
            <span class="info-label">Invoice Number:</span>
            <span>{{ $order->order_number }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Invoice Date:</span>
            <span>{{ $order->created_at->format('d F Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Payment Status:</span>
            <span style="text-transform: uppercase; font-weight: bold; color: #059669;">{{ $order->payment_status }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Order Status:</span>
            <span style="text-transform: uppercase;">{{ $order->status }}</span>
        </div>
    </div>

    <div style="display: flex; justify-content: space-between; margin-bottom: 30px;">
        <div class="section" style="width: 48%;">
            <div class="section-title">Bill To (Buyer)</div>
            <div><strong>{{ $buyer->name }}</strong></div>
            <div>{{ $buyer->email }}</div>
            @if($buyer->business_name)
                <div>{{ $buyer->business_name }}</div>
            @endif
        </div>
        <div class="section" style="width: 48%;">
            <div class="section-title">Sold By (Seller)</div>
            <div><strong>{{ $seller->name }}</strong></div>
            <div>{{ $seller->email }}</div>
            @if($seller->business_name)
                <div>{{ $seller->business_name }}</div>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Order Details</div>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th class="text-right">Quantity</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>{{ $product->name }}</strong><br>
                        <small>{{ $product->description ? \Illuminate\Support\Str::limit($product->description, 100) : 'No description' }}</small>
                    </td>
                    <td class="text-right">{{ $order->quantity }}</td>
                    <td class="text-right">Rp {{ number_format($order->price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" class="text-right"><strong>Total Amount:</strong></td>
                    <td class="text-right"><strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    @if($order->license_key)
    <div class="section">
        <div class="section-title">License Key</div>
        <div style="background-color: #F3F4F6; padding: 10px; border-radius: 4px; font-family: monospace;">
            {{ $order->license_key }}
        </div>
    </div>
    @endif

    <div class="footer">
        <p>This is an automatically generated invoice from Noteds Platform.</p>
        <p>For any inquiries, please contact us through our contact page.</p>
        <p>Thank you for your business!</p>
    </div>
</body>
</html>

