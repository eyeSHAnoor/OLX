<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Tracking Order #{{ $order->order_number }}</title>
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

        .files-section {
            margin-bottom: 20px;
        }

        .files-section ul {
            list-style: none;
            padding-left: 0;
        }

        .files-section li {
            margin-bottom: 5px;
        }
    </style>
</head>

<body>

    <!-- Order Header -->
    <div class="order-header">
        <div class="order-details">
            <h2>Tracking Order #{{ $order->order_number }}</h2>
            <p><strong>Date:</strong> {{ $order->created_at->format('d M Y') }}</p>
            <p><strong>Created By:</strong> {{ $order->creator->name ?? 'N/A' }}</p>
            <p><strong>Supplier:</strong> {{ $order->supplier ?? 'N/A' }}</p>
            <p><strong>Comments:</strong> {{ $order->comments ?? 'N/A' }}</p>
        </div>
    </div>

    <!-- Carrier Details -->
    <h3>Carrier Details</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Tracking ID</th>
                <th>Forwarder</th>
                <!-- <th>Carrier</th> -->
                <th>Expected Delivery Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->carriers as $i => $carrier)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $carrier->tracking_id ?? '-' }}</td>
                    <td>{{ $carrier->forwarder ?? '-' }}</td>
                    <!-- <td>{{ $carrier->carrier ?? '-' }}</td> -->
                    <td>{{ $carrier->expected_delivery_date ? $carrier->expected_delivery_date->format('d M Y') : '-' }}
                    </td>
                </tr>
            @endforeach

            @if($order->carriers->isEmpty())
                <tr>
                    <td colspan="5" class="empty-message">No carrier details available.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Items Table -->
    <h3>Order Items</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product Name</th>
                <th>SKU</th>
                <th>Customer</th>
                <th>PO Number</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Supplier</th>
                <th>Status</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->item_name ?? '-' }}</td>
                    <td>{{ $item->sku ?? '-' }}</td>
                    <td>{{ $item->customer_name ?? '-' }}</td>
                    <td>{{ $item->po_number ?? '-' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>${{ $item->price }}</td>
                    <td>{{ $item->supplier ?? '-' }}</td>
                    <td>{{ ucfirst($item->status) ?? '-' }}</td>
                    <td>{{ $item->remarks ?? '-' }}</td>
                </tr>
            @endforeach

            @if($order->items->isEmpty())
                <tr>
                    <td colspan="10" class="empty-message">No items available for this order.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Attached Files -->
    @if($order->files->count())
        <div class="files-section">
            <h3>Attached Files</h3>
            <ul>
                @foreach($order->files as $file)
                    <li>{{ $file->file_name }} ({{ ucfirst(str_replace('_', ' ', $file->collection)) }})</li>
                @endforeach
            </ul>
        </div>
    @endif

</body>

</html>