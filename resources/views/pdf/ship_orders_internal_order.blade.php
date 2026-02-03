<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Internal Order #{{ $order->order_number }} - Shipments</title>
    <style>
        /* General Body Styling */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 13px;
            margin: 30px;
            color: #333;
            background-color: #f5f5f5;
        }

        /* Order Header */
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

        /* Shipment Header */
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

        /* Table Styling */
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

        /* Empty table message */
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
            <h2>Internal Order #{{ $order->order_number }}</h2>
            <p><strong>Date:</strong> {{ $order->created_at->format('d M Y') }}</p>
            <p><strong>Customer:</strong> {{ $order->customer_name ?? $order->customer->name ?? 'N/A' }}</p>
            <p><strong>Comments:</strong> {{ $order->warehouse_comment ?? 'N/A' }}</p>
        </div>
    </div>

    <!-- Shipments Loop -->
    @forelse($order->shipOrders as $shipIndex => $ship)
        <div class="shipment-header">
            Shipment #{{ $ship->shipment_id }} - Date: {{ $ship->shipment_date ?? '-' }} - Total Items:
            {{ $ship->total_items ?? 0 }}
        </div>

        <!-- Shipment Summary Table -->
        <!-- <table>
                <thead>
                    <tr>
                        <th>Shipment #</th>
                        <th>Shipment Date</th>
                        <th>Total Items</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $ship->shipment_id }}</td>
                        <td>{{ $ship->shipment_date ?? '-' }}</td>
                        <td>{{ $ship->total_items ?? 0 }}</td>
                    </tr>
                </tbody>
            </table> -->

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
                @foreach($ship->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->product_name ?? '-' }}</td>
                        <td>{{ $item->catalog_number ?? '-' }}</td>
                        <td>{{ $item->confirmed_quantity_to_ship ?? '-' }}</td>
                        <td>{{ ucfirst($item->type) ?? '-' }}</td>
                        <td>{{ $item->customer_name ?? '-' }}</td>
                    </tr>
                @endforeach

                @if($ship->items->isEmpty())
                    <tr>
                        <td colspan="6" class="empty-message">No items shipped in this shipment</td>
                    </tr>
                @endif
            </tbody>
        </table>
    @empty
        <p class="empty-message">No shipments available for this order.</p>
    @endforelse
</body>

</html>