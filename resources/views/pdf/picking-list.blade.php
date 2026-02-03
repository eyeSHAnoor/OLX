<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .section { margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; }
        .table th { background-color: #f4f4f4; text-align: left; }
        .locations { font-size: 0.9em; color: #666; }
        .barcode { font-family: monospace; font-size: 16px; }
        .footer { margin-top: 50px; text-align: center; font-size: 0.8em; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Picking List</h1>
        <h2>Order: {{ $order->order_number }}</h2>
        <p>Customer: {{ $order->customer_name }}</p>
        <p>Date: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="section">
        <h3>Order Information</h3>
        <p><strong>Vehicle:</strong> {{ $order->vehicle ?? 'N/A' }}</p>
        <p><strong>Notes:</strong> {{ $order->notes ?? 'N/A' }}</p>
        <p><strong>Picker:</strong> {{ $order->picker?->name ?? 'Not assigned' }}</p>
        <p><strong>Status:</strong> {{ $order->status }}</p>
    </div>

    <div class="section">
        <h3>Items to Pick</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>SKU</th>
                    <th>Product</th>
                    <th>Brand</th>
                    <th>Qty</th>
                    <th>Available</th>
                    <th>Locations (FIFO)</th>
                    <th>Barcode</th>
                    <th>Status</th>
                    <th>Pick</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $index => $item)
                    @php
                        $product = $item->product;
                        // Already eager loaded reserved stock movements
                        $reservedMovements = $product->stockMovements ?? collect();
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->sku }}</td>
                        <td>{{ $item->item_name ?? $product->name ?? 'N/A' }}</td>
                        <td>{{ $product->brand ?? 'N/A' }}</td>
                        <td>{{ $item->qty }}</td>
                        <td>{{ $item->available_qty ?? 0 }}</td>
                        <td class="locations">
                            @if($reservedMovements->isNotEmpty())
                                @foreach($reservedMovements as $movement)
                                    @if($movement->warehouseLocation)
                                        {{ $movement->warehouseLocation->code ?? 'Unknown' }}
                                        ({{ $movement->reserved_quantity }} pcs)
                                        {{ !$loop->last ? ', ' : '' }}
                                    @endif
                                @endforeach
                            @else
                                No reserved locations
                            @endif
                        </td>
                        <td class="barcode">{{ $product->barcode ?? $product->ean ?? 'N/A' }}</td>
                        <td>{{ $item->status }}</td>
                        <td>____ / {{ $item->qty }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Picking Instructions</h3>
        <ol>
            <li>Pick items from locations in the order listed (oldest stock first)</li>
            <li>Scan each item's barcode after picking</li>
            <li>Mark the quantity picked in the "Pick" column</li>
            <li>Report any discrepancies to supervisor</li>
        </ol>
    </div>

    <div class="footer">
        <p>Printed on: {{ now()->format('d/m/Y H:i') }}</p>
        <p>Picking List - {{ $order->order_number }} - Page 1 of 1</p>
    </div>
</body>
</html>
