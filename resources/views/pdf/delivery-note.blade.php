<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 20px; }
        .company-info { float: left; width: 50%; }
        .delivery-info { float: right; width: 45%; text-align: right; }
        .clear { clear: both; }
        .section { margin: 20px 0; }
        .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; }
        .table th { background-color: #f4f4f4; }
        .total-section { float: right; width: 300px; margin-top: 20px; }
        .signature { margin-top: 50px; }
        .signature-line { border-top: 1px solid #000; width: 300px; margin: 40px 0 10px 0; }
        .barcode-container { text-align: center; margin: 30px 0; }
        .barcode { font-family: monospace; font-size: 18px; letter-spacing: 2px; }
        .qr-code { display: inline-block; margin: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <h2>Your Company Name</h2>
            <p>Company Address</p>
            <p>Phone: +123 456 7890</p>
            <p>Email: info@company.com</p>
        </div>
        
        <div class="delivery-info">
            <h1>DELIVERY NOTE</h1>
            <p><strong>Note No:</strong> {{ $order->order_number }}</p>
            <p><strong>Date:</strong> {{ now()->format('d/m/Y') }}</p>
            <p><strong>Delivery Date:</strong> {{ $order->expected_date?->format('d/m/Y') ?? 'ASAP' }}</p>
        </div>
        <div class="clear"></div>
    </div>

    <div class="section">
        <div style="float: left; width: 50%;">
            <h3>Customer Information</h3>
            <p><strong>Name:</strong> {{ $order->customer_name }}</p>
            <p><strong>Vehicle:</strong> {{ $order->vehicle ?? 'N/A' }}</p>
            <p><strong>Notes:</strong> {{ $order->notes ?? 'N/A' }}</p>
        </div>
        <div class="clear"></div>
    </div>

    <div class="section">
        <h3>Delivery Items</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>SKU</th>
                    <th>Description</th>
                    <th>Qty Ordered</th>
                    <th>Qty Delivered</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->sku }}</td>
                        <td>{{ $item->item_name }}</td>
                        <td>{{ $item->qty }}</td>
                        <td>{{ $item->picked_qty ?? $item->qty }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="barcode-container">
        <div class="qr-code">
            {!! $qrCode !!}
        </div>
        <p class="barcode">{{ $order->delivery_barcode }}</p>
        <p>Scan this barcode upon delivery</p>
    </div>

    <div class="signature">
        <div style="float: left; width: 300px;">
            <p>___________________________</p>
            <p>Customer Signature</p>
        </div>
        
        <div style="float: right; width: 300px; text-align: right;">
            <p>___________________________</p>
            <p>Company Representative</p>
        </div>
        <div class="clear"></div>
    </div>

    <div class="footer" style="margin-top: 100px; text-align: center; font-size: 0.8em; color: #666;">
        <p>Thank you for your business!</p>
        <p>This is an automated delivery note. No signature required for digital delivery.</p>
    </div>
</body>
</html>