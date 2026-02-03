<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Ship Order #{{ $shipOrder->shipment_id }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 13px;
            margin: 30px;
            color: #333;
            background-color: #f5f5f5;
        }

        .order-header {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 25px;
        }

        .order-details h2 {
            margin: 0;
            font-size: 22px;
            color: #222;
        }

        .order-details p {
            margin: 2px 0;
            font-size: 13px;
        }

        .order-details p strong {
            color: #555;
        }

        .shipment-header {
            margin-top: 20px;
            margin-bottom: 5px;
            background-color: #e0e0e0;
            padding: 8px 12px;
            font-weight: bold;
            color: #333;
            border-left: 4px solid #888;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            background-color: #fff;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 0 3px rgba(0, 0, 0, 0.05);
        }

        th,
        td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #ccc;
            vertical-align: middle;
            word-wrap: break-word;
        }

        th {
            background-color: #ddd;
            color: #333;
            font-weight: 600;
            font-size: 13px;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tbody tr:hover {
            background-color: #eaeaea;
        }

        .empty-message {
            text-align: center;
            color: #888;
            padding: 12px;
            font-style: italic;
        }
    </style>
</head>

<body>
    <!-- Order Header -->
    <div class="order-header">
        <div class="order-details">
            <h2>Ship Order #{{ $shipOrder->shipment_id }}</h2>
            <p><strong>Shipment Date:</strong> {{ $shipOrder->shipment_date ?? '-' }}</p>
            <p><strong>Internal Order #:</strong> {{ $shipOrder->internalOrder->order_number ?? '-' }}</p>
            <p><strong>Customer:</strong> {{ $shipOrder->internalOrder->customer->name ?? '-' }}</p>
            <p><strong>Total Items:</strong> {{ $shipOrder->total_items ?? 0 }}</p>
        </div>
    </div>

    <!-- Shipment Items Table -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product Name</th>
                <th>Catalog #</th>
                <th>Confirmed Qty (Shipped)</th>
                <th>Type</th>
                <th>Customer Name</th>
            </tr>
        </thead>
        <tbody>
            @forelse($shipOrder->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product_name ?? '-' }}</td>
                    <td>{{ $item->internalOrderItem->catalog_number ?? '-' }}</td>
                    <td>{{ $item->confirmed_quantity_to_ship ?? '-' }}</td>
                    <td>{{ ucfirst($item->type) ?? '-' }}</td>
                    <td>{{ $item->customer_name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty-message">No items shipped in this shipment</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>