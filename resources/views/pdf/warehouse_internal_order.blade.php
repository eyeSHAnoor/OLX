@php
    // Helper to get the latest partial shipment for an item (most recent shipment)
    function currentPartialShipment($item)
    {
        return $item->histories
            ? $item->histories->where('status', 'partial')->last()
            : null;
    }
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Internal Order #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        .order-header {
            display: flex;
            justify-content: flex-start;
            /* Align details to the left */
            gap: 50px;
            margin-bottom: 20px;
        }

        .order-details p,
        .order-details h2 {
            margin: 3px 0;
            font-size: 12px;
            color: #333;
        }

        .order-details h2 {
            font-size: 18px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            word-wrap: break-word;
            margin: 0 auto;
            /* Center the table horizontally */
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
            /* Horizontal left align */
            vertical-align: middle;
            /* Vertical center align */
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .received-step {
            font-weight: normal;
            font-size: 10px;
            color: #333;
        }

        .confirmed-step {
            font-weight: bold;
            color: green;
            font-size: 10px;
        }

        .correction-step {
            font-weight: bold;
            color: red;
            font-size: 10px;
        }
    </style>
</head>

<body>
    <div class="order-header">
        <div class="order-details">
            <h2>Internal Order #{{ $order->order_number }}</h2>
            <p><strong>Date:</strong> {{ $order->created_at->format('d M Y') }}</p>
            <!-- <p><strong>Customer:</strong> {{ $order->customer_name ?? $order->customer->name ?? 'N/A' }}</p> -->
            <p><strong>Comments:</strong> {{ $order->warehouse_comment ?? 'N/A' }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Item Name</th>
                <th>Catalog #</th>
                <th>Requested (Qty)</th>
                <th>Shipped (Confirmed)</th>
                <th>Received by Branch</th>
                <th>Correction Needed</th>
                <th>Unit</th>
                <th>Customer</th>
                <th>Status</th>
                <th>Confirmed By</th>
                <th>Signature</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($order->items as $index => $item)
                @php
                    $currentPartial = currentPartialShipment($item);
                    $confirmedHistory = $item->histories
                        ? $item->histories->where('status', 'confirmed')->last()
                        : null;
                @endphp

                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product_name ?? '-' }}</td>
                    <td>{{ $item->catalog_number ?? '-' }}</td>
                    <td>{{ $item->quantity ?? '-' }}</td>
                    <td class="confirmed-step">
                        {{ $item->confirmed_quantity ?? ($confirmedHistory ? $confirmedHistory->quantity : '-') }}
                    </td>
                    <td class="received-step">
                        {{ $item->received_quantity ?? ($currentPartial ? $currentPartial->quantity : '-') }}
                    </td>
                    <td class="correction-step">
                        {{ $item->correction_quantity ?? '-' }}
                    </td>
                    <td>{{ $item->unit ?? '-' }}</td>
                    <td>{{ $item->customer_name ?? '-' }}</td>
                    <td>{{ ucfirst($item->status) }}</td>
                    <td>{{ optional($item->confirmer)->name ?? '-' }}</td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>