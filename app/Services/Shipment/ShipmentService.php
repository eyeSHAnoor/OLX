<?php

namespace App\Services\Shipment;

use App\Models\OrderItem;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShipmentService
{
    public function processPendingItems()
    {
        // Find unallocated order items
        $items = OrderItem::query()
            ->where(function ($q) {
                $q->whereNull('shipment_id')
                    ->orWhere('shipment_id', 0);
            })
            ->whereHas('order', fn($q) => $q->relatedRecords())
            ->with('order')
            ->get();

//        dd($items->toArray());

        if (!$items || count($items) <= 0) {
//            return throw ValidationException::withMessages(
//                ['shipments' => ['No order has been found with unprocessed shipment']]
//            );
            return back()->with('error', 'No order has been found with unprocessed shipment');
        }

        // Group by recipient details
        $groups = $items->groupBy(function ($item) {
            return md5(strtolower(
                $item->order->recipient_name . '|' .
                $item->order->shipping_country
            ));
        });

        DB::transaction(function () use ($groups) {
            foreach ($groups as $group) {
                $first = $group->first();
                $order = $first->order;

                // Create a new shipment
                $shipment = Shipment::create([
                    'merchant_id' => auth()->id(),
                    'recipient_name' => $order->recipient_name,
                    'recipient_phone' => $order->recipient_phone,
                    'recipient_address_1' => Arr::get($order, 'shipping_address.address_1'),
                    'recipient_address_2' => Arr::get($order, 'shipping_address.address_2'),
                    'recipient_zip' => $order->shipping_postal_code,
                    'recipient_city' => Arr::get($order, 'shipping_address.city'),
                    'recipient_state' => Arr::get($order, 'shipping_address.state'),
                    'recipient_country' => $order->shipping_country,
                    'status' => 'pending',
//                    'tracking_number',
//                    'carrier_code',
//                    'label_url',
                ]);

                foreach ($group as $item) {
                    // Attach order_item to shipment
                    $item->update(['shipment_id' => $shipment->id]);

                    ShipmentItem::create([
                        'shipment_id' => $shipment->id,
                        'order_item_id' => $item->id,
                    ]);
                }
            }
        });
    }
}
