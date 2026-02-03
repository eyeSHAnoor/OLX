<?php

namespace App\Services\Stores;

interface PlatformAdapterInterface
{
    /**
     * Return an array of normalized orders (each contains order + items)
     * [
     *  'platform_order_id' => '123',
     *  'platform' => 'shopify',
     *  'platform_created_at' => '2025-08-01T12:34:00Z',
     *  // financial fields...
     *  'shipping_address' => [...],
     *  'items' => [
     *     ['platform_item_id'=>..., 'sku'=>..., 'product_name'=>..., 'quantity'=>..., 'unit_price'=>...],
     *  ],
     *  'raw' => (original raw payload)
     * ]
     */
    public function normalizeOrder(array $platformOrderJson): array;

    /**
     * Fetch (or paginate fetch) orders from platform API.
     * Returns raw platform json order arrays.
     */
    public function fetchOrders(array $params = []): array;


    /**
     * Return an array of normalized products
     */
    public function normalizeProduct(array $platformProductJson): array;

    /**
     * Fetch (or paginate fetch) products from platform API.
     * Returns raw platform json product arrays.
     */
    public function fetchProducts(array $params = []): array;

}
