<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $invoiceNumber }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }
        
        .container {
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .header .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #27ae60;
            margin-bottom: 10px;
        }
        
        .header .company-info {
            font-size: 11px;
            color: #666;
        }
        
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .invoice-info .left,
        .invoice-info .right {
            width: 48%;
        }
        
        .invoice-info h3 {
            font-size: 14px;
            color: #2c3e50;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .invoice-info p {
            margin-bottom: 5px;
        }
        
        .invoice-details {
            margin-bottom: 20px;
        }
        
        .invoice-details table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .invoice-details table th {
            background-color: #27ae60;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 12px;
        }
        
        .invoice-details table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .invoice-details table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .totals {
            width: 50%;
            margin-left: auto;
            margin-bottom: 30px;
        }
        
        .totals table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .totals table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        
        .totals table tr.total-row {
            background-color: #27ae60;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        
        .totals table tr.total-row td {
            border-bottom: none;
        }
        
        .payment-info {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .payment-info h3 {
            font-size: 14px;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .payment-info p {
            margin-bottom: 5px;
            font-size: 11px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 12px;
        }
        
        .status-paid {
            background-color: #27ae60;
            color: white;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 11px;
            color: #666;
        }
        
        .footer p {
            margin-bottom: 5px;
        }
        
        .thank-you {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #27ae60;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>INVOICE</h1>
            <div class="company-name">Durian Lovers</div>
            <div class="company-info">
                Jl. Durian No. 123, Jakarta, Indonesia<br>
                WhatsApp: +62 812-3456-7890<br>
                Email: info@durianlovers.com
            </div>
        </div>
        
        <!-- Invoice & Customer Info -->
        <div class="invoice-info">
            <div class="left">
                <h3>Informasi Invoice</h3>
                <p><strong>Nomor Invoice:</strong> {{ $invoiceNumber }}</p>
                <p><strong>Tanggal:</strong> {{ $issuedDate }}</p>
                <p><strong>Nomor Order:</strong> {{ $order->order_number }}</p>
            </div>
            <div class="right">
                <h3>Data Pelanggan</h3>
                <p><strong>{{ $order->user->name }}</strong></p>
                <p>{{ $order->user->email }}</p>
                <p>{{ $order->phone }}</p>
                <p>{{ $order->shipping_address }}</p>
            </div>
        </div>
        
        <!-- Order Items -->
        <div class="invoice-details">
            <table>
                <thead>
                    <tr>
                        <th style="width: 40%;">Produk</th>
                        <th style="width: 15%; text-align: center;">Qty</th>
                        <th style="width: 20%; text-align: right;">Harga Satuan</th>
                        <th style="width: 25%; text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td style="text-align: center;">{{ $item->quantity }}</td>
                        <td style="text-align: right;">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td style="text-align: right;">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Totals -->
        <div class="totals">
            <table>
                <tr>
                    <td>Subtotal</td>
                    <td style="text-align: right;">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Ongkos Kirim</td>
                    <td style="text-align: right;">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td style="text-align: right;">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
        
        <!-- Payment Info -->
        <div class="payment-info">
            <h3>Informasi Pembayaran</h3>
            <p><strong>Metode Pembayaran:</strong> {{ $order->payment_method }}</p>
            <p><strong>Status Pembayaran:</strong> 
                <span class="status-badge status-paid">✓ LUNAS</span>
            </p>
            @if($payment->payment_method)
            <p><strong>Via:</strong> {{ $payment->payment_method }}</p>
            @endif
        </div>
        
        <!-- Thank You Message -->
        <div class="thank-you">
            Terima kasih telah berbelanja di Durian Lovers!
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>Invoice ini dibuat secara otomatis dan sah tanpa tanda tangan.</p>
            <p>Untuk pertanyaan, hubungi kami di WhatsApp: +62 812-3456-7890</p>
            <p>&copy; {{ date('Y') }} Durian Lovers. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
