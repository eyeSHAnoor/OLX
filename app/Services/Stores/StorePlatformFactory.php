<?php

namespace App\Services\Stores;

use App\Models\Store;
use Dotenv\Store\StoreInterface;

class StorePlatformFactory
{
    public static function make(Store $store)
    {
        return match ($store->platform) {
            'woocommerce' => new \App\Services\Stores\WooCommerceAdapter($store),
            'shopify' => new \App\Services\Stores\ShopifyAdapter($store),
            default => throw new \Exception('Unsupported platform'),
        };
    }
}
