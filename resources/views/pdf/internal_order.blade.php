@php
    // Helper to get the latest partial shipment for an item (most recent shipment)
    function currentPartialShipment($item)
    {
        return $item->histories
            ? $item->histories->where('status', 'partial')->last()
            : null;
    }

    // Helper to calculate max number of partial steps
    function maxPartialSteps($items)
    {
        $max = 0;
        foreach ($items as $item) {
            $count = $item->histories ? $item->histories->where('status', 'partial')->count() : 0;
            if ($count > $max)
                $max = $count;
        }
        return $max > 0 ? $max : 1;
    }

    $maxSteps = maxPartialSteps($order->items);
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
            /* Left-aligned details */
            margin-bottom: 20px;
        }

        .order-details h2 {
            margin: 0 0 5px 0;
            font-size: 18px;
            font-weight: bold;
        }

        .order-details p {
            margin: 2px 0;
            font-size: 12px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            word-wrap: break-word;
            margin: 0 auto;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
            /* Horizontal left */
            vertical-align: middle;
            /* Vertical center */
            font-size: 11px;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: left;
            /* Headings center for better readability */
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
    </style>
</head>

<body>

    <div class="order-header">
        <div class="order-details">
            <h2>Internal Order #{{ $order->order_number }}</h2>
            <p><strong>Date:</strong> {{ $order->created_at->format('d M Y') }}</p>
            <p><strong>Customer:</strong> {{ $order->customer_name ?? $order->customer->name ?? 'N/A' }}</p>
            <p><strong>Comments:</strong> {{ $order->warehouse_comment ?? 'N/A' }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2">#</th>
                <th rowspan="2">Item Name</th>
                <th rowspan="2">Catalog</th>
                <th rowspan="2">Quantity</th>
                <th colspan="{{ $maxSteps }}">Partial Shipments</th>
                <th rowspan="2">Final Confirmed</th>
                <th rowspan="2">Unit</th>
                <th rowspan="2">Customer</th>
                <th rowspan="2">Status</th>
                <th rowspan="2">Confirmed By</th>
                <th rowspan="2">Signature</th>
            </tr>
            <tr>
                @for ($i = 1; $i <= $maxSteps; $i++)
                    <th>{{ $i }}</th>
                @endfor
            </tr>
        </thead>

        <tbody>
            @foreach ($order->items as $index => $item)
                @php
                    $partialHistories = $item->histories
                        ? $item->histories->where('status', 'partial')->values()
                        : collect();

                    $confirmedHistory = $item->histories
                        ? $item->histories->where('status', 'confirmed')->last()
                        : null;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product_name ?? '-' }}</td>
                    <td>{{ $item->catalog_number ?? '-' }}</td>
                    <td>{{ $item->quantity ?? '-' }}</td>

                    @for ($i = 0; $i < $maxSteps; $i++)
                        <td class="received-step">
                            {{ $partialHistories[$i]->quantity ?? '-' }}
                        </td>
                    @endfor

                    <td class="confirmed-step">{{ $confirmedHistory ? $confirmedHistory->quantity : '-' }}</td>
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